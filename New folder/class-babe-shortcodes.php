<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * BABE_shortcodes Class.
 * Shortcodes for the BA Book Everything plugin.
 * 
 * @class 		BABE_shortcodes
 * @version		1.2.0
 * @author 		Booking Algorithms
 */

BABE_shortcodes::init(); 

class BABE_shortcodes {
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {
        
        add_shortcode( 'all-items', array( __CLASS__, 'shortcode_all_items' ) );

        add_shortcode( 'babe-search-form', array( __CLASS__, 'shortcode_search_form' ) );

        add_shortcode( 'babe-booking-form', array( __CLASS__, 'shortcode_booking_form' ) );

        add_shortcode( 'babe-item-stars', array( __CLASS__, 'shortcode_item_stars' ) );

        add_shortcode( 'babe-item-address-map', array( __CLASS__, 'shortcode_item_address_map' ) );

        add_shortcode( 'babe-item-meeting-points', array( __CLASS__, 'shortcode_item_meeting_points' ) );

        add_shortcode( 'babe-item-calendar', array( __CLASS__, 'shortcode_item_calendar' ) );

        add_shortcode( 'babe-item-slideshow', array( __CLASS__, 'shortcode_item_slideshow' ) );

        add_shortcode( 'babe-item-faqs', array( __CLASS__, 'shortcode_item_faqs' ) );

        add_shortcode( 'babe-item-steps', array( __CLASS__, 'shortcode_item_steps' ) );

        add_shortcode( 'babe-item-custom-section', array( __CLASS__, 'shortcode_item_custom_section' ) );

        add_shortcode( 'babe-item-price-from', array( __CLASS__, 'shortcode_item_price_from' ) );

        add_shortcode( 'babe-item-related', array( __CLASS__, 'shortcode_item_related' ) );

        ///// transforms 'babe-' prefix to 'get_' in method name
        add_shortcode( 'babe-order-customer-name', array( __CLASS__, 'shortcode_order_router' ) );

        add_shortcode( 'babe-order-customer-details', array( __CLASS__, 'shortcode_order_router' ) );

        add_shortcode( 'babe-order-items', array( __CLASS__, 'shortcode_order_router' ) );

        add_shortcode( 'babe-order-amount-to-pay', array( __CLASS__, 'shortcode_order_router' ) );

        add_shortcode( 'babe-order-number', array( __CLASS__, 'shortcode_order_router' ) );

        add_shortcode( 'babe-email-button', array( __CLASS__, 'shortcode_order_router' ) );

        add_shortcode( 'babe-email-header-image', array( __CLASS__, 'shortcode_email_header_image' ) );

        add_shortcode( 'babe-email-body-title', array( __CLASS__, 'shortcode_email_body_title' ) );

        add_shortcode( 'babe-email-body-content', array( __CLASS__, 'shortcode_email_body_content' ) );
                
	}

    //////////////////////////
    /**
     * Get email button
     *
     * @param int $order_id
     * @param array $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function get_email_button($order_id, $atts, $content, $tag ) {

        $args = shortcode_atts( array(
            'title'      => '',
            'type'      => 'home_url',
        ), $atts, $tag );

        switch ($args['type']){
            case 'admin_confirmation_success':
                $output = BABE_html_emails::email_get_row_button( $args['title'], BABE_Order::get_admin_confirmation_page($order_id, 'confirm'), 1);
                break;
            case 'admin_confirmation_reject':
                $output = BABE_html_emails::email_get_row_button( $args['title'], BABE_Order::get_admin_confirmation_page($order_id, 'reject'), 2);
                break;
            case 'my_account':
                $output = BABE_html_emails::email_get_row_button( $args['title'], BABE_Settings::get_my_account_page_url());
                break;
            case 'pay_now':
                $output = BABE_html_emails::email_get_row_button( $args['title'], BABE_Order::get_order_payment_page($order_id));
                break;
            case 'home_url':
            default:
                $output = BABE_html_emails::email_get_row_button( $args['title'], home_url());
                break;
        }

        return $output;
    }

    //////////////////////////////
    /**
     * Get email body content
     *
     * @param array $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function shortcode_email_body_content( $atts, $content, $tag ) {

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, $tag );

        return BABE_html_emails::email_get_row_content( do_shortcode($content) );
    }

    //////////////////////////////
    /**
     * Get email body title
     *
     * @param array $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function shortcode_email_body_title( $atts, $content, $tag ) {

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, $tag );

        return BABE_html_emails::email_get_row_title( do_shortcode($content) );
    }

    //////////////////////////////
    /**
     * Get email header image
     *
     * @param array $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function shortcode_email_header_image( $atts, $content, $tag ) {

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, $tag );

        return BABE_html_emails::email_get_row_header_image();
    }

    //////////////////////////////
    /**
     * Order items
     *
     * @param int $order_id
     * @param array $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function get_order_items($order_id, $atts, $content, $tag ) {

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, $tag );

        return BABE_html::order_items($order_id);
    }

    //////////////////////////////
    /**
     * Order number
     *
     * @param int $order_id
     * @param array $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function get_order_number($order_id, $atts, $content, $tag ) {

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, $tag );

        return BABE_Order::get_order_number($order_id);
    }

    //////////////////////////////
    /**
     * Order amount to pay
     *
     * @param int $order_id
     * @param array $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function get_order_amount_to_pay($order_id, $atts, $content, $tag ) {

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, $tag );

        return BABE_Currency::get_currency_price(BABE_Order::get_order_prepaid_amount($order_id), BABE_Order::get_order_currency($order_id));
    }

//////////////////////////////
    /**
     * Order customer details
     *
     * @param int $order_id
     * @param array $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function get_order_customer_details($order_id, $atts, $content, $tag ) {

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, $tag );

        return BABE_html::order_customer_details($order_id);
    }

//////////////////////////////
    /**
     * Order customer name
     *
     * @param int $order_id
     * @param array $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function get_order_customer_name($order_id, $atts, $content, $tag) {

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, $tag );

        $customer_details = BABE_Order::get_order_customer_details($order_id);

        return $customer_details['first_name'].' '.$customer_details['last_name'];
    }

//////////////////////////////
    /**
     * Order methods router
     *
     * @param $atts
     * @param string $content
     * @param string $tag
     * @return string
     */
    public static function shortcode_order_router($atts, $content, $tag ) {

        global $post; // should be order post

        $output = '';

        if ( empty($post->post_type) || $post->post_type !== BABE_Post_types::$order_post_type) {
            return $output;
        }

        $method = str_replace(array('-', 'babe'), array('_', 'get'), $tag);

        if ( !method_exists(__CLASS__, $method) ){
            return $output;
        }

        return self::$method($post->ID, $atts, $content, $tag);
    }

//////////////////////////////
    /*
     * Item related
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_item_related( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type) {

            $args = shortcode_atts( array(
                'title'      => '',
            ), $atts, 'babe-item-related' );

            $babe_post = BABE_Post_types::get_post($post->ID);

            $output .= $args['title'] ? '<h3 class="babe_post_content_title">'.esc_html($args['title']).'</h3>' : '';

            $output .= BABE_html::block_related($babe_post);

        }

        return $output;
    }

//////////////////////////////
    /*
     * Item price from
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_item_price_from( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type) {

            $args = shortcode_atts( array(
                'title'      => '',
            ), $atts, 'babe-item-price-from' );

            $babe_post = BABE_Post_types::get_post($post->ID);

            $output .= BABE_html::block_price_from($babe_post);

        }

        return $output;
    }

//////////////////////////////
    /*
     * Item steps
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_item_steps( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type) {

            $args = shortcode_atts( array(
                'title'      => '',
            ), $atts, 'babe-item-steps' );

            $babe_post = BABE_Post_types::get_post($post->ID);

            $output .= $args['title'] ? '<h3 class="babe_post_content_title">'.esc_html($args['title']).'</h3>' : '';

            $output .= BABE_html::block_steps($babe_post);

        }

        return $output;
    }

    public static function shortcode_item_custom_section( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( !is_single() || $post->post_type !== BABE_Post_types::$booking_obj_post_type) {
            return $output;
        }

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, 'babe-item-custom-section' );

        $babe_post = BABE_Post_types::get_post($post->ID);

        $output .= BABE_html::block_custom_section($babe_post);

        return $output;
    }

//////////////////////////////
    /*
     * Item faqs
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_item_faqs( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type) {

            $args = shortcode_atts( array(
                'title'      => '',
            ), $atts, 'babe-item-faqs' );

            $babe_post = BABE_Post_types::get_post($post->ID);

            $output .= $args['title'] ? '<h3 class="babe_post_content_title">'.esc_html($args['title']).'</h3>' : '';

            $output .= BABE_html::block_faqs($babe_post);

        }

        return $output;
    }

//////////////////////////////
    /*
     * Item slideshow
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_item_slideshow( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type) {

            $args = shortcode_atts( array(
                'title'      => '',
            ), $atts, 'babe-item-slideshow' );

            $babe_post = BABE_Post_types::get_post($post->ID);

            $output .= BABE_html::block_slider($babe_post);

        }

        return $output;
    }

//////////////////////////////
    /*
     * Item calendar
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_item_calendar( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type) {

            $args = shortcode_atts( array(
                'title'      => '',
            ), $atts, 'babe-item-calendar' );

            $babe_post = BABE_Post_types::get_post($post->ID);

            $output .= $args['title'] ? '<h3 class="babe_post_content_title">'.esc_html($args['title']).'</h3>' : '';

            $output .= BABE_html::block_calendar($babe_post);

        }

        return $output;
    }

//////////////////////////////
    /*
     * Item meeting points
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_item_meeting_points( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type) {

            $args = shortcode_atts( array(
                'title'      => '',
            ), $atts, 'babe-item-meeting-points' );

            $babe_post = BABE_Post_types::get_post($post->ID);

            $output .= $args['title'] ? '<h3 class="babe_post_content_title">'.esc_html($args['title']).'</h3>' : '';

            $output .= BABE_html::block_meeting_points($babe_post);

        }

        return $output;
    }

//////////////////////////////
    /*
     * Item address map
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_item_address_map( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type) {

            $args = shortcode_atts( array(
                'title'      => '',
            ), $atts, 'babe-item-address-map' );

            $babe_post = BABE_Post_types::get_post($post->ID);

            $output .= $args['title'] ? '<h3 class="babe_post_content_title">'.esc_html($args['title']).'</h3>' : '';

            $output .= BABE_html::block_address_map($babe_post);

        }

        return $output;
    }

//////////////////////////////
    /*
     * Item stars
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_item_stars( $atts, $content = null ) {

        global $post;

        $output = '';

        if ( is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type) {

            $args = shortcode_atts( array(
                'title'      => '',
            ), $atts, 'babe-item-stars' );

            $output .= BABE_Rating::post_stars_rendering($post->ID);

        }

        return $output;
    }

//////////////////////////////
    /*
     * Booking form
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_booking_form( $atts, $content = null ) {

        $output = '';

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, 'babe-booking-form' );

        $output .= $args['title'] ? '<h3 class="babe_post_content_title">'.esc_html($args['title']).'</h3>' : '';

        $output .= BABE_html::booking_form();

        return $output;
    }

//////////////////////////////
    /*
     * Search form
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public static function shortcode_search_form( $atts, $content = null ) {

        $output = '';

        $args = shortcode_atts( array(
            'title'      => '',
        ), $atts, 'babe-search-form' );

        $output .= BABE_Search_From::render_form($args['title']);

        return $output;
    }
    
//////////////////////////////
        /*
		 * Gets all items.
		 * 
		 * @param array $atts
		 * @param string $content
		 *
		 * @return string
		 */
		public static function shortcode_all_items( $atts, $content = null ) {
			
			$output = '';
            
            $args = shortcode_atts( array(
				'title'      => '',
				'ids'        => '',
                'category_ids' => '', //// term_taxonomy_ids from categories
                'term_ids' => '', //// term_taxonomy_ids from custom taxonomies in $taxonomies_list
				'per_page'   => BABE_Settings::$settings['posts_per_taxonomy_page'],
				'pagination' => 0,
				'view' => 'grid',
				'fa_icon'    => '',
				'sort'       => 'price_from', /// price_from, rating, post_title, av_date_from
				'sortby'     => 'ASC',
				'classes'    => '',
				'bg_img_url' => '',
                'bg_img_id' => '',
                'date_from' => '', //// d/m/Y or m/d/Y format
                'date_to' => '',
                'post_author' => 0,
                'keyword' => '',
                'return_total_count' => 1,
                'without_av_check' => 0,
                'group_results_by_date' => 0,
                'not_scheduled' => 0,
			), $atts, 'all-items' );

            $args['pagination'] = (int)$args['pagination'];

            if (!$args['pagination']){
                $args['paged'] = 1;
            }
            
            if (!$args['date_to']){
                $date_to_obj = new DateTime('+10 years');
                $args['date_to'] = $date_to_obj->format(BABE_Settings::$settings['date_format']);
            }
			
			$post_args = array(
				'sort' => $args['sort'],
				'sort_by' => $args['sortby'],
				'posts_per_page' => (int)$args['per_page'],
                'date_from' => $args['date_from'],
                'date_to' => $args['date_to'],
                'post_author' => (int)$args['post_author'],
                'keyword' => sanitize_text_field($args['keyword']),
                'return_total_count' => (int)$args['return_total_count'],
                'without_av_check' => (int)$args['without_av_check'],
                'group_results_by_date' => (int)$args['group_results_by_date'],
                'not_scheduled' => (int)$args['not_scheduled'],
			);
			
			if ( $args['ids'] ) {
				$ids = explode(",", $args['ids']);
				$ids = array_map('intval', $ids);
				$ids = array_unique($ids);
				
				if ( ! empty( $ids ) ) {
					$post_args['post__in'] = $ids;
				}
			}
            
            if ( $args['category_ids'] ) {
				$category_ids = explode(",", $args['category_ids']);
				$category_ids = array_map('intval', $category_ids);
				$category_ids = array_unique($category_ids);
				
				if ( ! empty( $category_ids ) ) {
					$post_args['categories'] = $category_ids;
				}
			}

            if ( $args['term_ids'] ) {
                $term_ids = explode(",", $args['term_ids']);
                $term_ids = array_map('intval', $term_ids);
                $term_ids = array_unique($term_ids);

                if ( ! empty( $term_ids ) ) {
                    $post_args['terms'] = $term_ids;
                }
            }
            
            $post_args = apply_filters('babe_shortcode_all_items_post_args', $post_args);

            $image_url = !empty( $args['bg_img_id'] ) ? wp_get_attachment_image_url( (int)$args['bg_img_id'], 'large') : $args['bg_img_url'];
			
			$bg_style = $image_url ? $bg_style = "style=\"background-image: url('" . esc_url( $image_url ) . "');\"" : '' ;
			
			$classes = $args['classes'] ? $args['classes'] : '';
			
			$fa_icon = $args['fa_icon'] ? '<i class="' . esc_attr($args['fa_icon']) . '"></i>' : '';
			
			$content = $content ? '<div class="babe_shortcode_block_description">' . $content . '</div>' : '';

            $posts = BABE_Post_types::get_posts($post_args);
            $posts_pages = BABE_Post_types::$get_posts_pages;

            foreach($posts as $post){
                $output .= BABE_html::get_post_preview_html($post, $args['view']);
            } /// end foreach $posts

            $pagination = '';

            if ( $args['pagination'] ){
                $pagination = BABE_Functions::pager($posts_pages);
            }
			
			$output = apply_filters('babe_shortcode_all_items_html', '
				<div class="babe_shortcode_block sc_all_items ' . esc_attr($classes) . '" ' . $bg_style . '>
					<div class="babe_shortcode_block_bg_inner">
						<h2 class="babe_shortcode_block_title">' . $fa_icon . esc_html($args['title']) . '</h2>
						' . $content . '
						<div class="babe_shortcode_block_inner">
							' . $output . '
						</div>'.$pagination.'
					</div>
				</div>
			', $args, $post_args);
			
			return $output;
		}
        
//////////////////////////////
		/**
		 * Gets posts tile view.
		 * 
		 * @param array $post_args
		 * 
		 * @return string
		 */
		public static function get_posts_tile_view($post_args) {
		     
            $output = '';
             
            $posts = BABE_Post_types::get_posts( $post_args );
			
			$thumbnail = apply_filters('babe_shortcodes_all_item_thumbnail', 'ba-thumbnail');
            $excerpt_length = apply_filters('babe_shortcodes_all_item_excerpt_length', 13);
			
			foreach( $posts as $post ) {
             
             $image_srcs = wp_get_attachment_image_src( get_post_thumbnail_id( $post['ID'] ), $thumbnail );
				
			 $item_url = BABE_Functions::get_page_url_with_args($post['ID'], $_GET);
				
			 $image = $image_srcs ? '<a href="' . $item_url . '"><img src="' . $image_srcs[0] . '"></a>' : '';
				
			 $price_old = $post['discount_price_from'] < $post['price_from'] ? '<span class="item_info_price_old">' . BABE_Currency::get_currency_price( $post['price_from'] ) . '</span>' : '';
				
			 $discount = $post['discount'] ? '<div class="item_info_price_discount">-' . $post['discount'] . '%</div>' : '';
				
			 $babe_post = BABE_Post_types::get_post( $post['ID'] );
				
			 $output .= apply_filters('babe_shortcode_all_items_item_html', '
					<div class="babe_all_items_item">
						<div class="babe_all_items_item_inner">
							<div class="item_img">
								'.$image.'
							</div>
							<div class="item_text">
                                <div class="item_title">
                                   <a href="' . $item_url . '">' . apply_filters('translate_text', $post['post_title']) . '</a>
                                   ' . BABE_Rating::post_stars_rendering( $post['ID'] ) . '
                                </div>
								<div class="item_info_price">
									<label>' . __( 'from', 'ba-book-everything' ) . '</label>
									' . $price_old . '
									<span class="item_info_price_new">' . BABE_Currency::get_currency_price( $post['discount_price_from'] ) . '</span>
                                   ' . $discount . ' 
								</div>
								
								<div class="item_description">
									' . BABE_Post_types::get_post_excerpt( $post, $excerpt_length ) . '
								</div>
							</div>
						</div>
					</div>
				', $post, $babe_post);
            }    
            
            return $output;
             
		}        

//////////////////////////////    

}
