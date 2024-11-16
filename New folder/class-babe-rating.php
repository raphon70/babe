<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

BABE_Rating::init();

/**
 * BABE_Rating Class.
 * Get general settings
 * @class 		BABE_Rating
 * @version		1.0.0
 * @author 		Booking Algorithms
 */

class BABE_Rating {
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {
        
        add_filter( 'comment_form_field_comment', array( __CLASS__, 'comment_form_field_comment'), 10 );
        add_action( 'comment_post', array( __CLASS__, 'new_comment_added'), 10, 3);
        add_action( 'transition_comment_status', array( __CLASS__, 'transition_comment_status'), 10, 3 );
        
        add_filter( 'get_comment_text', array( __CLASS__, 'get_comment_text'), 10, 3);
        
        add_filter( 'manage_'.BABE_Post_types::$booking_obj_post_type.'_posts_columns', array( __CLASS__, 'booking_obj_table_head'));
        add_action( 'manage_'.BABE_Post_types::$booking_obj_post_type.'_posts_custom_column', array( __CLASS__, 'booking_obj_table_content'), 10, 2 );
	}

    public static function delete_post_rating( $post_id ){

        $rating_criteria = BABE_Settings::get_rating_criteria();

        foreach ($rating_criteria as $rating_name => $rating_title){
            delete_post_meta( $post_id, '_rating_score_'.$rating_name );
            delete_post_meta( $post_id, '_rating_votes_'.$rating_name );
        }
        delete_post_meta( $post_id, '_rating');
    }

/////////////////////////////////
    /**
    * Fires when the comment status is in transition.
    *
    * @since 2.7.0
    *
    * @param int|string $new_status The new comment status: unapproved, approved, spam, trash
    * @param int|string $old_status The old comment status.
    * @param WP_Comment     $comment    The comment data.
    */
    public static function transition_comment_status( $new_status, $old_status, $comment ){

        $post_id = $comment->comment_post_ID;

        $comments = get_comments([
            'status' => 1,
            'post_id' => $post_id,
        ]);

        if ( isset($comment_statuses[$new_status]) && $comment_statuses[$new_status] === 'approved' ){
            $comments[] = $comment;
        }

        self::recalculate_post_rating( $post_id, $comments );
    }

    public static function recalculate_post_rating( $post_id, $comments )
    {
        if ( empty($comments) ){
            self::delete_post_rating( $post_id );
            return;
        }

        $rating_criteria = BABE_Settings::get_rating_criteria();
        $stars_num = BABE_Settings::get_rating_stars_num();
        $sanitized_rating_arr = [];
        $rating_scores = [];
        $rating_votes = [];

        foreach( $comments as $approved_comment ){

            $rating_arr = self::get_comment_rating($approved_comment->comment_ID);

            foreach ($rating_criteria as $rating_name => $rating_title){

                if ( !isset($rating_arr[$rating_name]) ){
                    continue;
                }

                $rating = absint($rating_arr[$rating_name]);
                $rating = $rating > 0 && $rating <= $stars_num ? $rating : $stars_num;

                $rating_scores[$rating_name] = !empty($rating_scores[$rating_name])
                    ? $rating_scores[$rating_name] + $rating
                    : $rating;

                $rating_votes[$rating_name] = !empty($rating_votes[$rating_name])
                    ? $rating_votes[$rating_name] + 1
                    : 1;
            }
        }

        if ( empty($rating_scores) ){
            self::delete_post_rating( $post_id );
            return;
        }

        foreach( $rating_scores as $rating_name => $rating_score ){
            $sanitized_rating_arr[$rating_name] = $rating_scores[$rating_name]/$rating_votes[$rating_name];
            update_post_meta( $post_id, '_rating_score_'.$rating_name, $rating_scores[$rating_name] );
            update_post_meta( $post_id, '_rating_votes_'.$rating_name, $rating_votes[$rating_name] );
        }

        $rating_total = self::calculate_rating($sanitized_rating_arr);
        update_post_meta( $post_id, '_rating', $rating_total );
    }
    
///////////////////////////////////
    /**
     * Filters the text of a comment.
     *
     * @since 1.5.0
     *
     * @see Walker_Comment::comment()
     *
     * @param string     $comment_content Text of the comment.
     * @param WP_Comment $comment         The comment object.
     * @param array      $args            An array of arguments.
     */
    public static function get_comment_text( $comment_content, $comment, $args ) {
        
        $comment_rating_arr = self::get_comment_rating($comment->comment_ID);
        
        if (!empty($comment_rating_arr)){
            $comment_content = self::comment_stars_rendering($comment->comment_ID).$comment_content;
        }
        
        return $comment_content;
    }
    
////////////////////////
     /**
	 * Render comment stars
     * @param int $comment_id
     * @return string
	 */
     public static function comment_stars_rendering($comment_id = 0){
        
        $output = '';
        
        $comment_rating_arr = $comment_id ? self::get_comment_rating($comment_id) : array();
        $class_prefix = $comment_id ? 'comment' : 'comment-form';
        
        $rating_criteria = BABE_Settings::get_rating_criteria();
        $stars_num = BABE_Settings::get_rating_stars_num();
        $criteria_num = $comment_id ? count($comment_rating_arr) : count($rating_criteria);
        
        foreach ($rating_criteria as $rating_name => $rating_title){
           if (!$comment_id || ($comment_id && isset($comment_rating_arr[$rating_name]))){ 
            
            if ($criteria_num > 1){
                $output .= '<li><span class="'.$class_prefix.'-rating-criterion">'.$rating_title.'</span>';
            }
            
            $output .= '<span class="'.$class_prefix.'-rating-stars stars" data-rating-cr="'.$rating_name.'">';
            
            $rating = isset($comment_rating_arr[$rating_name]) ? (float)$comment_rating_arr[$rating_name] : 0;
            for ($i = 1; $i<= $stars_num; $i++){
                $output .= self::star_rendering($i, $rating);
            }
            
            $output .= '</span>';
            
            if ($criteria_num > 1){
                $output .= '</li>';
            }
            
           } //// end if  !$comment_id
        } /// end foreach $rating_criteria
        
        if ($criteria_num > 1){
            $output = '<ul class="'.$class_prefix.'-rating-ul">'.$output.'</ul>';
        }
        
        if ($comment_id){
          //// get total rating stars  
            $total_rating = self::get_comment_total_rating($comment_id);
            if ($total_rating){
            
            $total_stars = '<span class="'.$class_prefix.'-total-rating-stars stars">';
            
            for ($i = 1; $i<= $stars_num; $i++){
                $total_stars .= self::star_rendering($i, $total_rating);
            }
            
            $total_stars .= '<span class="'.$class_prefix.'-total-rating-value">'.round($total_rating, 2).'</span>';
            
            $total_stars .= '</span>';
            
            $output =  $criteria_num > 1 ? $total_stars.$output : $total_stars;
            } else {
                $output = '';
            }
        }
        
        
        return $output;
        
     }
     
////////////////////////
     /**
	 * Render star
     * @param int $star_num
     * @param float $rating
     * @return string
	 */
     public static function star_rendering($star_num, $rating = 0){
        
        $output = '';
        
        $ceil = ceil($rating);
        
        $floor = floor($rating);
        
        $star_img = $star_num <= $floor || ($star_num == $ceil && ($rating + 0.5) > $ceil) ? '<i class="fas fa-star"></i>' : ($star_num == $ceil ? '<i class="fas fa-star-half-alt"></i>' : '<i class="far fa-star"></i>');
        
        $output = '<span class="star star-'.$star_num.'" data-rating-val="'.$star_num.'">'.$star_img.'</span>';
        
        $output = apply_filters('babe_star_rendering', $output, $star_num, $rating, $star_img);
        
        return $output;
        
     }
     
////////////////////////
     /**
	 * Render post stars
     * @param int $post_id
     * @return string
	 */
     public static function post_stars_rendering($post_id){
        
        $output = '';
        
        $total_rating = self::get_post_total_rating($post_id);
        $total_votes = self::get_post_total_votes($post_id);
        
        if ($total_rating){
        
        $rating_arr = self::get_post_rating($post_id);
        
        $rating_criteria = BABE_Settings::get_rating_criteria();
        $stars_num = BABE_Settings::get_rating_stars_num();
        $criteria_num = count($rating_arr);
        
        foreach ($rating_criteria as $rating_name => $rating_title){
           if (isset($rating_arr[$rating_name])){ 
            
            if ($criteria_num > 1){
                $output .= '<li><span class="post-rating-criterion">'.$rating_title.'</span>';
            }
            
            $output .= '<span class="post-rating-stars stars" data-rating-cr="'.$rating_name.'">';
            
            $rating = (float)$rating_arr[$rating_name];
            for ($i = 1; $i<= $stars_num; $i++){
                $output .= self::star_rendering($i, $rating);
            }
            
            $output .= '<span class="post-rating-value">'.round($rating, 2).'</span>';
            
            $output .= '</span>';
            
            if ($criteria_num > 1){
                $output .= '</li>';
            }
            
           } //// end if isset($rating_arr[$rating_name])
        } /// end foreach $rating_criteria
        
        if ($criteria_num > 1){
            $output = '<ul class="post-rating-ul">'.$output.'</ul>';
        } 
        
        //// get total rating stars  
        $total_stars = '<span class="post-total-rating-stars stars">';
            
        for ($i = 1; $i<= $stars_num; $i++){
           $total_stars .= self::star_rendering($i, $total_rating);
        }
        
        $by_reviews_text = $total_votes > 1 ? sprintf(__( 'by %d reviews', 'ba-book-everything' ), $total_votes) : '';
            
        $total_stars .= '<span class="post-total-rating-value">'.round($total_rating, 2).' '.$by_reviews_text.'</span>';
            
        $total_stars .= '</span>';
            
        $output =  $criteria_num > 1 ? $total_stars.$output : $total_stars;
        
        $output = '<div class="post-total-rating">
        '.$output.'
        </div>';
        
        } /// end if $total_rating
        
        return $output;
        
     }              
    
////////////////////////
     /**
     * Fires immediately after a comment is inserted into the database.
     *
     * @since 1.2.0
     * @since 4.5.0 The `$commentdata` parameter was added.
     *
     * @param int        $comment_id       The comment ID.
     * @param int|string $comment_approved 1 if the comment is approved, 0 if not, 'spam' if spam.
     * @param array      $commentdata      Comment data.
     */
     public static function new_comment_added($comment_id, $comment_approved, $commentdata){
        
        if (isset($_POST['rating'])){
            
            //// sanitize $_POST['rating']
            $post_arr = (array)$_POST['rating'];
            $sanitized_post_arr = array();
            $stars_num = BABE_Settings::get_rating_stars_num();
            $rating_criteria = BABE_Settings::get_rating_criteria();
            foreach ($rating_criteria as $rating_name => $rating_title){
                if (isset($post_arr[$rating_name])){
                    $rating = absint($post_arr[$rating_name]);
                    $rating = $rating > 0 && $rating <= $stars_num ? $rating : $stars_num;
                    $sanitized_post_arr[$rating_name] = $rating;
                }
            }
            //// add comment rating
            self::update_comment_rating($comment_id, $sanitized_post_arr);

            if ($comment_approved == 1){ ///the comment is approved

                $comments = get_comments([
                    'status' => 1,
                    'post_id' => $commentdata['comment_post_ID'],
                ]);

                self::recalculate_post_rating( $commentdata['comment_post_ID'], $comments );
            }
        }
     }

////////////////////////
     /**
	 * Get comment rating total
     * @param string $field
     * @return string
	 */
     public static function comment_form_field_comment($field){
        
        global $post;

        if (is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type){
        
        $field = '<p class="comment-form-rating"><label>'.__('Rating:', 'ba-book-everything').'</label>
        '.self::comment_stars_rendering().'
        </p>
        '.$field.'
        '.self::get_comment_rating_hidden_fields();
        
        }
        
        return $field;
        
     }
     
////////////////////////
     /**
	 * Get comment rating hidden fields
     * @return string
	 */
     public static function get_comment_rating_hidden_fields(){
        
        $output = '';
        
        $rating_criteria = BABE_Settings::get_rating_criteria();
        
        foreach ($rating_criteria as $rating_name => $rating_title){
            $output .= '
            <input type="hidden" id="rating_'.$rating_name.'" class="rating_hidden_input" name="rating['.$rating_name.']" value="">
            ';
        }
        
        return $output;
        
     }         
    
////////////////////////
     /**
	 * Get comment rating total
     * @param int $comment_id
     * @return float
	 */
     public static function get_comment_rating_total($comment_id){
        
        return (float)get_comment_meta( $comment_id, '_rating', 1 );
        
     }    
    
////////////////////////
     /**
	 * Get comment rating for each criterion
     * @param int $comment_id
     * @return array
	 */
     public static function get_comment_rating($comment_id){
        
        $rating_arr = array();
        
        $rating_criteria = BABE_Settings::get_rating_criteria();
        
        foreach ($rating_criteria as $rating_name => $rating_title){
            
            $rating = get_comment_meta( $comment_id, '_rating_'.$rating_name, 1 );
            
            if ($rating){
              $rating_arr[$rating_name] = absint($rating);
            }  
        }
        
        return $rating_arr;
        
     }
     
////////////////////////
     /**
	 * Get post rating for each criterion
     * @param int $post_id
     * @return array
	 */
     public static function get_post_rating($post_id){
        
        $rating_arr = array();
        
        $rating_criteria = BABE_Settings::get_rating_criteria();
        
        foreach ($rating_criteria as $rating_name => $rating_title){
                
                $current_rating_score = absint(get_post_meta($post_id, '_rating_score_'.$rating_name, true));
                $current_rating_votes = absint(get_post_meta($post_id, '_rating_votes_'.$rating_name, true));
                if ($current_rating_score && $current_rating_votes){
                  $current_rating = $current_rating_score/$current_rating_votes;
                  $rating_arr[$rating_name] = $current_rating;
                }
        }
        
        return $rating_arr;
        
     }
     
////////////////////////
     /**
	 * Get post total rating
     * @param int $post_id
     * @return float
	 */
     public static function get_post_total_rating($post_id){
        return (float)get_post_meta($post_id, '_rating', true);
     }
     
////////////////////////
     /**
	 * Get post total votes
     * @param int $post_id
     * @return float
	 */
     public static function get_post_total_votes($post_id){
        
        $votes = 0;
        
        $rating_criteria = BABE_Settings::get_rating_criteria();
        
        foreach ($rating_criteria as $rating_name => $rating_title){
                
                $current_rating_votes = absint(get_post_meta($post_id, '_rating_votes_'.$rating_name, true));
                $votes = max($votes, $current_rating_votes);
        }
        
        return $votes;
        
     }               
     
////////////////////////
     /**
	 * Get comment total rating
     * @param int $comment_id
     * @return float
	 */
     public static function get_comment_total_rating($comment_id){
        
        return (float)get_comment_meta($comment_id, '_rating', 1);
        
     }     
     
////////////////////////
     /**
	 * Calculate total rating
     * @param array $rating_arr - $criterion => $rating
     * @return float
	 */
     public static function calculate_rating($rating_arr){
        
        $num = count($rating_arr);
        
        $num = $num ?: 1;
        
        $output = round(array_sum($rating_arr)/$num, 2);

         return apply_filters('babe_rating_calculate_total', $output, $rating_arr);
        
     }         

////////////////////////
     /**
	 * Update comment rating
     * @param int $comment_id
     * @param array $rating_arr - $criterion => $rating
     * @return void
	 */
     public static function update_comment_rating($comment_id, $rating_arr){
        
        $rating_criteria = BABE_Settings::get_rating_criteria();
        $stars_num = BABE_Settings::get_rating_stars_num();
        $sanitized_rating_arr = array();
        
        foreach ($rating_criteria as $rating_name => $rating_title){     
            if (isset($rating_arr[$rating_name])){
                $rating = absint($rating_arr[$rating_name]);
                $rating = $rating > 0 && $rating <= $stars_num ? $rating : $stars_num;
                update_comment_meta( $comment_id, '_rating_'.$rating_name, $rating );
                $sanitized_rating_arr[$rating_name] = $rating;
            }   
        }
        
        $rating_total = self::calculate_rating($sanitized_rating_arr);
        update_comment_meta( $comment_id, '_rating', $rating_total );
     }
     
////////////////////////
     /**
	 * Update post rating
     * @param int $post_id
     * @param array $rating_arr - $criterion => $rating
     * @param string $flag - add/remove comment rating to/from post rating
     * @return void
	 */
     public static function update_post_rating($post_id, $rating_arr, $flag = 'add'){
        
        if (!empty($rating_arr)){
        
        $rating_criteria = BABE_Settings::get_rating_criteria();
        $stars_num = BABE_Settings::get_rating_stars_num();
        $sanitized_rating_arr = array();
        
        foreach ($rating_criteria as $rating_name => $rating_title){     
            if (isset($rating_arr[$rating_name])){
                $rating = absint($rating_arr[$rating_name]);
                $rating = $rating > 0 && $rating <= $stars_num ? $rating : $stars_num;
                $current_rating_score = absint(get_post_meta($post_id, '_rating_score_'.$rating_name, true));
                $current_rating_votes = absint(get_post_meta($post_id, '_rating_votes_'.$rating_name, true));
                
                if ($flag == 'add'){
                  $current_rating_score += $rating;
                  $current_rating_votes += 1;
                } else {
                  $current_rating_score -= $rating;
                  $current_rating_votes -= 1;  
                }
                
                $current_rating = $current_rating_score/$current_rating_votes;
                
                update_post_meta( $post_id, '_rating_score_'.$rating_name, $current_rating_score );
                update_post_meta( $post_id, '_rating_votes_'.$rating_name, $current_rating_votes );
                
                $sanitized_rating_arr[$rating_name] = $current_rating;
            }   
        }
        
        $rating_total = self::calculate_rating($sanitized_rating_arr);
        update_post_meta( $post_id, '_rating', $rating_total );
        
        }
     }
     
/////////////////////
    /**
	 * Add booking obj custom column heads.
     * @param array $defaults
     * @return array
	 */
    public static function booking_obj_table_head( $defaults ) {

        $defaults['rating']   = __('Rating', 'ba-book-everything');

        return $defaults;
    }

///////////////////////////////////
    /**
	 * Add booking obj custom column content.
     * @param string $column_name
     * @param int $post_id
     * @return void
	 */
    public static function booking_obj_table_content( $column_name, $post_id ) {
        if ($column_name === 'rating') {
            echo self::post_stars_rendering($post_id);
        }
    }
        
////////////////////    
}