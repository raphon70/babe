<?php
/**
 * Integration with WP import/export
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calling to setup class.
 */
BABE_Import_export::init();


class BABE_Import_export {

	private static $babe_import = false;

    public static $shortcodes_to_watch = [];
	private static $posts_with_shortcodes = [];

	public static $redux_options_name = [ 'bahotel_settings', 'bat_settings' ];

	// mappings from old post data to new
    /**
     * @var array
     */
    private static $processed_terms = [];
    private static $processed_posts = [];
    private static $processed_attachments = [];
    private static $processed_comments = [];
    private static $redux_data = [];
    private static $rates_data = '';
    private static $avcal_data = '';
    private static $booking_rules_data = '';
    private static $discount_data = '';

	//////////////////////////////////////////////////
	/**
	 * Setup function.
	 * @return void
	 */
	public static function init() {

	    // BA Settings
		add_action( 'babe_settings_after_email_fields', array( __CLASS__, 'create_import_settings' ), 50);

		if (class_exists('BABE_Settings')){
			add_filter( 'babe_sanitize_'.BABE_Settings::$option_name, array( __CLASS__, 'sanitize_settings' ), 10, 2);
		}

		// Import actions

        self::init_shortcodes_array();

		add_filter( 'import_start', array( __CLASS__, 'import_taxonomy_first' ) );
		add_filter( 'wp_import_terms', array( __CLASS__, 'correct_taxonomy_id' ) );
		add_filter( 'wp_import_post_data_raw', array( __CLASS__, 'post_parser' ) );
		add_filter( 'import_end', array( __CLASS__, 'correct_imported_data' ) );
		add_filter( 'wp_import_post_comments', array( __CLASS__, 'map_comment' ), 10, 3 );
		add_action( 'wp_import_insert_comment', array( __CLASS__, 'map_imported_comment' ), 10, 4 );
		add_action( 'wp_import_posts', array( __CLASS__, 'validate_posts_before_import' ) );

		/// Export
        add_action( 'export_wp', array( __CLASS__, 'save_price_and_avcal' ) );
		add_action( 'init', array( __CLASS__, 'init_delete_temp_import_posts' ), 10 );
	}

	/**
	 * Delete temp posts created during WP Export.
	 * @return void
	 */
	public static function delete_temp_import_posts() {

	    $post_list = [
	        'babe_rates_slug',
            'babe_rates_meta_slug',
            'babe_booking_rules_slug',
            'babe_discount_slug',
            'babe_avcal_slug',
        ];

	    foreach ( self::$redux_options_name as $redux_options_name){
            $post_list[] = $redux_options_name;
        }

		foreach ( $post_list as $item ) {
			$args = array(
			    'post_status' => 'draft',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'data_type',
						'value' => $item,
						'compare' => '=',
					)
				)
			);
			$find_post = new WP_Query($args);

			if ( !empty($find_post->post->ID) ){
                wp_delete_post( $find_post->post->ID, true);
            }
		}
	}

    /**
     * Init delete temp posts created during WP Export.
     * @return void
     */
    public static function init_delete_temp_import_posts(){

        if ( !get_option('babe_wp_export_ran', 0) ){
            return;
        }

        delete_option('babe_wp_export_ran');
        self::delete_temp_import_posts();
    }

	/**
	 * Declare list of shortcodes which need to be corrected.
	 * @return void
	 */
	public static function init_shortcodes_array(){

	    self::$shortcodes_to_watch = [
			'gallery' => '',
			'ba-gallery' => '',
			'story-video' => '',
			'reviews' => '',
			'info-bar' => '',
			'news' => '',
			'counters-bar' => '',
			'services' => '',
			'story' => '',
			'address-item' => '',
			'booking-items' => '',
			'why-choose-us' => '',
			'countto' => '',
			'contact-form-bar' => '',
			'address-contact-form' => '',
			'discount-bar' => '',
            'selected-pages' => '',
            'selected-tours' => '',
            'special-tours' => '',
            'top-pages' => '',
            'top-tours' => '',
            'tours-map' => '',
		];
	}

	/**
	 * Initialise settings.
	 * @param array $new_input
	 * @param array $input
	 * @return array
	 */
	public static function sanitize_settings($new_input, $input) {

		$new_input['use_extended_wp_import'] = isset($input['use_extended_wp_import']) ?  $input['use_extended_wp_import'] : 1;
		return $new_input;
	}

	///////////////////////////////////////
	/**
	 * Register user extra fields.
     * @return void
	 */
	public static function create_import_settings() {

		add_settings_section(
			'setting_section_export_import', // ID
			__('Export/import', 'ba-book-everything'), // Title
			'__return_false', // Callback
			BABE_Settings::$option_menu_slug // Page
		);

		add_settings_field(
			'use_extended_wp_import', // ID
			__('Extend WP export/import with BA Book Everything data', 'ba-book-everything'), // Title
			array( 'BABE_Settings_admin', 'is_active_callback' ), // Callback
			BABE_Settings::$option_menu_slug, // Page
			'setting_section_export_import', // Section
			array('option' => 'use_extended_wp_import', 'settings_name' => BABE_Settings::$option_name) // Args array
		);
	}

	/**
	 * Save availability calendar to database.
	 * @return void
	 */
	public static function save_price_and_avcal(){

		self::is_extended_export_active();

		if (!self::$babe_import){
            return;
        }

		global $wpdb;

		$rates = $wpdb->get_results("SELECT * FROM " . BABE_Prices::$table_rate);

		$args = array(
			'meta_query' => array(
				array(
					'key' => 'data_type',
					'value' => 'babe_rates_slug',
					'compare' => '=',
				)
			)
		);
		$find_rates_post = new WP_Query($args);
		if ( !empty($find_rates_post->posts) ){
			$rates_post_id = $find_rates_post->post->ID;

			$args = array();
			$args['ID'] = $rates_post_id;
			$args['post_content'] = serialize($rates);
			wp_update_post( wp_slash($args) );
		} else {
			$post_data = array(
				'post_title'    => 'babe_rates_slug',
				'post_content'  => serialize($rates),
				'post_status'   => 'draft',
				'post_author'   => 1,
				'post_type'     => 'post',
			);
			$rates_post_id = wp_insert_post( $post_data );
		}
		update_post_meta($rates_post_id, 'data_type', 'babe_rates_slug');

        ////////////////////////////

		$av_cal = $wpdb->get_results("SELECT * FROM " . BABE_Calendar_functions::$table_av_cal);

		$args = array(
			'meta_query' => array(
				array(
					'key' => 'data_type',
					'value' => 'babe_avcal_slug',
					'compare' => '=',
				)
			)
		);
		$find_avcal_post = new WP_Query($args);
		if ( !empty($find_avcal_post->posts) ){
			$avcal_post_id = $find_avcal_post->post->ID;
			$args = array();
			$args['ID'] = $avcal_post_id;
			$args['post_content'] = serialize($av_cal);
			wp_update_post( wp_slash($args) );
		} else {
			$post_data = array(
				'post_title'    => 'babe_avcal_slug',
				'post_content'  => serialize($av_cal),
				'post_status'   => 'draft',
				'post_author'   => 1,
				'post_type'     => 'post',
			);
			$avcal_post_id = wp_insert_post( $post_data );
		}
		update_post_meta($avcal_post_id, 'data_type', 'babe_avcal_slug');

		//////////////////////////////

        $booking_rules = $wpdb->get_results("SELECT * FROM " . BABE_Booking_Rules::$table_booking_rules);

        $args = array(
            'meta_query' => array(
                array(
                    'key' => 'data_type',
                    'value' => 'babe_booking_rules_slug',
                    'compare' => '=',
                )
            )
        );
        $find_booking_rules_post = new WP_Query($args);
        if ( !empty($find_booking_rules_post->posts) ){
            $post_id = $find_booking_rules_post->post->ID;
            $args = array();
            $args['ID'] = $post_id;
            $args['post_content'] = serialize($booking_rules);
            wp_update_post( wp_slash($args) );
        } else {
            $post_data = array(
                'post_title'    => 'babe_booking_rules_slug',
                'post_content'  => serialize($booking_rules),
                'post_status'   => 'draft',
                'post_author'   => 1,
                'post_type'     => 'post',
            );
            $post_id = wp_insert_post( $post_data );
        }
        update_post_meta($post_id, 'data_type', 'babe_booking_rules_slug');

        //////////////////////////////

        $discounts = $wpdb->get_results("SELECT * FROM " . BABE_Prices::$table_discount );

        $args = array(
            'meta_query' => array(
                array(
                    'key' => 'data_type',
                    'value' => 'babe_discount_slug',
                    'compare' => '=',
                )
            )
        );
        $find_discount_post = new WP_Query($args);
        if ( !empty($find_discount_post->posts) ){
            $post_id = $find_discount_post->post->ID;
            $args = array();
            $args['ID'] = $post_id;
            $args['post_content'] = serialize($discounts);
            wp_update_post( wp_slash($args) );
        } else {
            $post_data = array(
                'post_title'    => 'babe_discount_slug',
                'post_content'  => serialize($discounts),
                'post_status'   => 'draft',
                'post_author'   => 1,
                'post_type'     => 'post',
            );
            $post_id = wp_insert_post( $post_data );
        }
        update_post_meta($post_id, 'data_type', 'babe_discount_slug');

        ///////////////////////////

        foreach ( self::$redux_options_name as $redux_options_name){

            $redux_options = get_option( $redux_options_name );

            if ( !empty($redux_options) ){
                $args = array(
                    'meta_query' => array(
                        array(
                            'key' => 'data_type',
                            'value' => 'babe_'.$redux_options_name,
                            'compare' => '=',
                        )
                    )
                );
                $find_redux_post = new WP_Query($args);
                if (!empty ($find_redux_post->posts) ){
                    $redux_post_id = $find_redux_post->post->ID;
                    $args = array();
                    $args['ID'] = $redux_post_id;
                    $args['post_content'] = serialize($redux_options);
                    wp_update_post( wp_slash($args) );
                } else {
                    $post_data = array(
                        'post_title'    => 'babe_'.$redux_options_name,
                        'post_content'  => serialize($redux_options),
                        'post_status'   => 'draft',
                        'post_author'   => 1,
                        'post_type'     => 'post',
                    );
                    $redux_post_id = wp_insert_post( $post_data );
                }
                update_post_meta($redux_post_id, 'data_type', 'babe_'.$redux_options_name);
            }
        }

		update_option('babe_wp_export_ran', 1);

	}

	/**
	 * Validate if extended import/export is activated.
	 * @param array $posts
	 */
	public static function is_extended_export_active( $posts = [] ){

        self::$babe_import = false;

	    if (
	        !empty(BABE_Settings::$settings['use_extended_wp_import'])
            && (
                (
                    !empty($posts)
                    && in_array( BABE_Post_types::$booking_obj_post_type, array_column( $posts, 'post_type' ))
                )
                || (
                    isset($_GET["submit"])
                    && $_GET["submit"] === 'Download Export File'
                )
            )
        ){
			self::$babe_import = true;
	    }
	}

	/**
	 * Extract settings from temp posts and remove them from posts array.
     *
	 * @param array $posts
	 * @return array $posts
	 */
	public static function validate_posts_before_import($posts){

		self::is_extended_export_active($posts);

		$redux_post_titles = [];
        foreach ( self::$redux_options_name as $redux_options_name){
            $redux_post_titles['babe_'.$redux_options_name] = $redux_options_name;
        }

		if ( self::$babe_import && !empty($posts) ){
			foreach ($posts as $post_id => $post){
				if ( isset( $redux_post_titles[$post["post_title"]] ) ){
					self::$redux_data[ $redux_post_titles[$post["post_title"]] ] = maybe_unserialize($post["post_content"]);
					unset ( $posts[$post_id] );
				} elseif ( $post["post_title"] === 'babe_rates_slug' ){
                    self::$rates_data = maybe_unserialize($post["post_content"]);
                    unset ( $posts[$post_id] );
                } elseif ( $post["post_title"] === 'babe_avcal_slug' ){
                    self::$avcal_data = maybe_unserialize($post["post_content"]);
                    unset ( $posts[$post_id] );
                } elseif ( $post["post_title"] === 'babe_booking_rules_slug' ){
                    self::$booking_rules_data = maybe_unserialize($post["post_content"]);
                    unset ( $posts[$post_id] );
                } elseif ( $post["post_title"] === 'babe_discount_slug' ){
                    self::$discount_data = maybe_unserialize($post["post_content"]);
                    unset ( $posts[$post_id] );
                } elseif ( $post['post_type'] === 'attachment' ){
                    self::$processed_attachments[$post_id] = $post_id;
				} elseif ( $post['post_type'] === 'page' ){
				    self::$posts_with_shortcodes[$post["post_id"]] = [];
				}
			}
		}
		return $posts;
	}

	/**
	 * Save prices to database.
	 * @return void
	 */
	public static function save_rate_settings(){

        if ( empty(self::$rates_data) ){
            return;
        }

        global $wpdb;

        $rates_count = $wpdb->get_var("SELECT count(rate_id) FROM ". BABE_Prices::$table_rate);

        if (!empty($rates_count)){
            $wpdb->query( "DELETE FROM ".BABE_Prices::$table_rate );
        }

        foreach ( self::$rates_data as $rate ) {
            $rate->booking_obj_id = self::$processed_posts[$rate->booking_obj_id];
            $wpdb->insert(
                BABE_Prices::$table_rate,
                (array)$rate
            );
        }
	}

    /**
     * Save booking rules to database.
     * @return void
     */
    public static function save_booking_rules(){

        if ( empty(self::$booking_rules_data) ){
            return;
        }

        global $wpdb;

        $rates_count = $wpdb->get_var("SELECT count(rule_id) FROM ". BABE_Booking_Rules::$table_booking_rules);

        if (!empty($rates_count)){
            $wpdb->query( "DELETE FROM ".BABE_Booking_Rules::$table_booking_rules );
        }

        foreach ( self::$booking_rules_data as $booking_rule ) {
            $wpdb->insert(
                BABE_Booking_Rules::$table_booking_rules,
                (array)$booking_rule
            );
        }
    }

    /**
     * Save discounts to database.
     * @return void
     */
    public static function save_discount(){

        if ( empty(self::$discount_data) ){
            return;
        }

        global $wpdb;

        $count = $wpdb->get_var("SELECT count(id) FROM ". BABE_Prices::$table_discount);

        if (!empty($count)){
            $wpdb->query( "DELETE FROM ".BABE_Prices::$table_discount );
        }

        foreach ( self::$discount_data as $discount_data ) {
            $wpdb->insert(
                BABE_Prices::$table_discount,
                (array)$discount_data
            );
        }
    }

	/**
	 * Save availability calendar to database.
	 * @return int
	 */
	public static function save_avcal_settings(){

	    if ( empty(self::$avcal_data) ){
	        return 0;
        }

        global $wpdb;

        $fields = (array)self::$avcal_data[0];
        //unset ($fields['id']);
        $query = "INSERT INTO " . BABE_Calendar_functions::$table_av_cal . ' (' . implode( ', ', array_keys($fields) ) . ") VALUES ";
        foreach ( self::$avcal_data as $cal ) {
            //unset ($cal->id);
            $cal->booking_obj_id = self::$processed_posts[$cal->booking_obj_id];
            $cal = (array)$cal;
            $query .= ' (';
            $start_loop = true;
            foreach ( $cal as $id => $item ){
                if (!$start_loop) {
                    $query .= ", ";
                } else {
                    $start_loop = false;
                }
                $query .= ( $id === "date_from" ) ? "'" . $item . "'" : $item;
            }
            $query .=  '), ';
        }

        $query = substr($query, 0, -2);

        $query .= " ON DUPLICATE KEY UPDATE guests=VALUES(guests),in_schedule=VALUES(in_schedule)";

        $wpdb->query($query);

        return 1;
	}

	/**
	 * Map new comment ID with old one in case comment already exists.
	 * @param array $comments
	 * @param int $post_id
	 * @param array $post
	 * @return array
	 */
	public static function map_comment( $comments, $post_id, $post){

		if ( ! empty($comments)){
			global $wpdb;
			$comment = $wpdb->get_results( "SELECT * FROM {$wpdb->comments} WHERE comment_post_ID = " . $post_id, ARRAY_A );
			$new_comment_id = empty($comment) ? $comments[0]["comment_id"] : $comment[0]["comment_ID"];
			self::$processed_comments[$comments[0]["comment_id"]] = $new_comment_id;

            foreach ( $comments as $comment ) {
                self::$processed_comments[$comment['comment_id']] = $comment['comment_id'];
            }

		}
		return $comments;
	}

	/**
	 * Map new comment ID with old one in case comment created.
	 * @return void
	 */
	public static function map_imported_comment($key, $comment, $comment_post_ID, $post){

		self::$processed_comments[$post["comments"][0]["comment_id"]] = $key;
	}

	/**
	 * Find all declared shortcodes in all pages.
	 * @return void
	 */
	public static function correct_imported_data(){

		if ( ! self::$babe_import ) {
            return;
        }

        self::$processed_posts = $GLOBALS['wp_import']->processed_posts; /// old / new

        foreach ( self::$processed_attachments as $post_id => $post_prev_id ){
            if ( !empty(self::$processed_posts[$post_id]) ){
                self::$processed_attachments[$post_id] = self::$processed_posts[$post_id];
            }
        }

		self::save_rate_settings();
		self::save_booking_rules();
		self::save_discount();
        self::save_avcal_settings();
        self::save_redux_settings();

		foreach ( self::$posts_with_shortcodes as $post_old_id => $post_arr ){

		    if (
		        empty(self::$processed_posts[$post_old_id])
                || get_post_meta( self::$processed_posts[$post_old_id], 'data_corrected', true)
            ){
                continue;
            }

			$post = get_post(self::$processed_posts[$post_old_id]);

			foreach ( self::$shortcodes_to_watch as $shortcode => $string){
				$pattern = '/\[' . str_replace('-', '\-', $shortcode) . '[^\]]*\]/';
				preg_match_all($pattern, $post->post_content, $matches);
				if (!empty($matches[0])){
					foreach ($matches as $match){
						self::$posts_with_shortcodes[$post_old_id][$shortcode] = $match;
					}
				}
			}

            if ( empty(self::$posts_with_shortcodes[$post_old_id]) ){
                continue;
            }

            self::$posts_with_shortcodes[$post_old_id]['post_id'] = $post->ID;
            self::$posts_with_shortcodes[$post_old_id]['content'] = $post->post_content;

            $updated_content = self::correct_shortcodes_data( self::$posts_with_shortcodes[$post_old_id] );

            if ($updated_content){
                update_post_meta(self::$posts_with_shortcodes[$post_old_id]["post_id"], 'data_corrected', 1);
                $args = [];
                $args['ID'] = self::$posts_with_shortcodes[$post_old_id]["post_id"];
                $args['post_content'] = $updated_content;
                wp_update_post( wp_slash($args) );
            }
		}

		self::delete_temp_import_posts();
	}

	/**
	 * Correct shortcodes data with actual ID and resources.
	 * @param array $post_arr
	 * @return string
	 */
	public static function correct_shortcodes_data( $post_arr ){

		foreach ($post_arr as $tag => $shortcode){

			if (empty($shortcode)){
                continue;
            }

			switch ($tag){

                case "info-bar":
                case "discount-bar":
                case "counters-bar":
                case "news":
                case "story-video" :
					foreach ($shortcode as $old_sortcode){
						preg_match('/image_id="(?<id>\d+)"/', $old_sortcode, $matches);
						if (isset($matches['id'])){
							$new_id = self::$processed_posts[$matches['id']];
							if ( (int)$matches['id'] ===  $new_id ) {
                                continue;
                            }
							$edited_shortcode = str_replace('"' . $matches['id'] . '"', '"' . $new_id . '"', $old_sortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
						}
					}
					break;
				case "reviews":
					foreach ($shortcode as $old_sortcode){
						preg_match_all('/(?<id>\d+)+/', $old_sortcode, $matches);
						if (isset($matches['id'])){
							$new_ids = array();
							$old_ids = '';
							foreach ($matches['id'] as $id => $match){
								$new_ids[$id] = self::$processed_comments[$match];
								$old_ids .= $match . ', ';
							}
							if ( implode(', ', $new_ids) ===  substr($old_ids, 0, -2) ) {
                                continue;
                            }

							$edited_shortcode = str_replace(substr($old_ids, 0, -2), implode(', ', $new_ids), $old_sortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
						}
						unset($new_ids);
					}
					break;
                case "services":
                case "selected-pages":
                case "selected-tours":
                case "top-pages":
                case "tours-map":
					foreach ($shortcode as $old_sortcode){
						preg_match_all('/(?<id>\d+)+/', $old_sortcode, $matches);
						if (isset($matches['id'])){
							$new_ids = array();
							$old_ids = '';
							foreach ($matches['id'] as $id => $match){
								$new_ids[$id] = self::$processed_posts[$match];
								$old_ids .= $match . ', ';
							}
							if ( implode(', ', $new_ids) ===  substr($old_ids, 0, -2) ) {
                                continue;
                            }

							$edited_shortcode = str_replace(substr($old_ids, 0, -2), implode(', ', $new_ids), $old_sortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
						}
						unset($new_ids);
					}
				case "story":
					foreach ($shortcode as $old_sortcode){
						preg_match('/image_id="(?<id>\d+)"(([^\/]+)(?<img>[^\"]+)+)?/', $old_sortcode, $matches);
						if (isset($matches['id']) || isset($matches['img'])){
							$new_img = (isset($matches['img'])) ? self::get_attachment_url_by_slug($matches['img']) : '';
							$new_id = self::$processed_posts[$matches['id']];
							if ( (int)$matches['id'] ===  $new_id && ( isset($matches['img']) && (int)$matches['img'] === $new_img))
								continue;
							$edited_shortcode = (!empty($matches['img'])) ? str_replace('"' . $matches['img'] . '"', '"' . $new_img . '"', $old_sortcode, $count) : $old_sortcode;
							$edited_shortcode = str_replace('"' . $matches['id'] . '"', '"' . $new_id . '"', $edited_shortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
						}
					}
					break;
				case "why-choose-us":
					foreach ($shortcode as $old_sortcode){
						preg_match('/post_id="(?<id>\d+)"/', $old_sortcode, $matches);
						if (isset($matches['id'])){
							$new_id = self::$processed_posts[$matches['id']];
							if ( (int)$matches['id'] ===  $new_id ) {
                                continue;
                            }
							$edited_shortcode = str_replace('"' . $matches['id'] . '"', '"' . $new_id . '"', $old_sortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
						}
					}
					break;
				case "countto":
                case "address-item":
                    foreach ($shortcode as $old_sortcode){
                        preg_match('/([^\/]+)(?<img>[^\"]+)+/', $old_sortcode, $matches);
                        if (isset($matches['img'])){
                            $new_img = self::get_attachment_url_by_slug($matches['img']);
                            if ( $matches['img'] ===  $new_img ) {
                                continue;
                            }
                            $edited_shortcode = str_replace($matches['img'], '"' . $new_img . '"', $old_sortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
                        }
                    }
                    break;
                case "top-pages":
                    foreach ($shortcode as $old_sortcode){
                        preg_match('/([^\/]+)(?<img_url>[^\"]+)+/', $old_sortcode, $matches);
                        if (isset($matches['img_url'])){
                            $new_img = self::get_attachment_url_by_slug($matches['img_url']);
                            if ( $matches['img_url'] ===  $new_img ) {
                                continue;
                            }
                            $edited_shortcode = str_replace($matches['img_url'], '"' . $new_img . '"', $old_sortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
                        }
                    }
                    break;
				case "gallery":
				case "ba-gallery":
                case "booking-items":
                    foreach ($shortcode as $old_sortcode){
                        preg_match('/ids="(?<id>[^"]+)/', $old_sortcode, $matches);
                        if (isset($matches['id'])){
                            $matches = self::extended_explode( $matches['id'] );
                            $new_ids = array();
                            $old_ids = '';
                            foreach ($matches as $id => $match){
                                $new_ids[$id] = self::$processed_posts[$match];
                                $old_ids .= $match . ', ';
                            }
                            if ( implode(', ', $new_ids) ===  substr($old_ids, 0, -2) ) {
                                continue;
                            }

                            $edited_shortcode = str_replace(substr($old_ids, 0, -2), implode(', ', $new_ids), $old_sortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
                        }
                        unset($new_ids);
                    }
                    break;
				case "contact-form-bar":
					foreach ($shortcode as $old_sortcode){
						preg_match('/form7_id="(?<contact>\d+)".*image_id="(?<id>\d+)"/', $old_sortcode, $matches);
						if (isset($matches['id']) || isset($matches['contact'])){
							$new_form = self::get_contact7_form();
							$new_id = self::$processed_posts[$matches['id']];
							if ( (int)$matches['id'] ===  $new_id && (int)$matches['contact'] === $new_form) {
                                continue;
                            }
							$edited_shortcode = str_replace('"' . $matches['contact'] . '"', '"' . $new_form . '"', $old_sortcode, $count);
							$edited_shortcode = str_replace('"' . $matches['id'] . '"', '"' . $new_id . '"', $edited_shortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
						}
					}
					break;
				case "address-contact-form":
					foreach ($shortcode as $old_sortcode){
						preg_match('/form7_id="(?<contact>\d+)"/', $old_sortcode, $matches);
						if (isset($matches['contact'])){
							$new_form = self::get_contact7_form();
							if ( (int)$matches['contact'] ===  $new_form ) {
                                continue;
                            }
							$edited_shortcode = str_replace('"' . $matches['contact'] . '"', '"' . $new_form . '"', $old_sortcode, $count);
                            $post_arr["content"] = str_replace($old_sortcode, $edited_shortcode, $post_arr["content"], $count);
						}
					}
					break;
			}
		}

		return $post_arr["content"];
	}

	/**
	 * Explode string.
	 * @param string $post
	 * @return array
	 */
	public static function extended_explode($string){

        $string = str_replace( ', ',',', $string);
        return explode(',', $string);
	}

	/**
	 * Find contact form ID.
	 * @return int
	 */
	public static function get_contact7_form(){

		$args = array(
			'post_type' => 'wpcf7_contact_form',
			'orderby'     => 'ID',
			'order'       => 'DESC',
			'posts_per_page' => 1,
		);
		$_header = get_posts( $args );
		if ( empty($_header) ) {
			return '';
		}

		return array_pop($_header)->ID;
	}

	/**
	 * Find actual file name.
	 * @return string
	 */
	public static function get_attachment_url_by_slug( $slug ) {

		$args = array(
			'post_type' => 'attachment',
			'name' => $slug,
			'posts_per_page' => 1,
			'post_status' => 'inherit',
		);
		$_header = get_posts( $args );
		if ( empty($_header) ){
			return '';
		}

		$header = array_pop($_header);
		$img =  parse_url ( wp_get_attachment_url($header->ID) );
		return !empty($img['path']) ? $img['path'] : '';
	}

	/**
	 * Correct and save Redux dashboard data.
	 *
     * @return void
	 */
	public static function save_redux_settings() {

        foreach ( self::$redux_options_name as $redux_options_name){

            if ( empty(self::$redux_data[$redux_options_name]) ){
                continue;
            }

            self::$redux_data[$redux_options_name] = self::correct_redux_data( self::$redux_data[$redux_options_name] );
            update_option( $redux_options_name, self::$redux_data[$redux_options_name]);
        }
	}

    /**
     * Correct Redux data.
     * @param array|string $redux_data
     * @return array
     */
	public static function correct_redux_data( $redux_data ){

        $redux_data = maybe_unserialize($redux_data);

		if ( empty($redux_data) ){
		    return $redux_data;
        }

		foreach ( $redux_data as $setting_id => $parameters ){

		    if (
		        empty($parameters['url'])
                || empty($parameters['thumbnail'])
                || empty($parameters['id'])
                || empty ($parameters['title'])
            ){
		        continue;
            }

		    if (
		        empty(self::$processed_attachments[$parameters['id']])
                || $setting_id === 'logo'
                || $setting_id === 'footer_logo'
            ){
                $redux_data[$setting_id]['url'] = '';
                $redux_data[$setting_id]['thumbnail'] = '';
                $redux_data[$setting_id]['id'] = '';
                $redux_data[$setting_id]['title'] = '';
                $redux_data[$setting_id]['height'] = '';
                $redux_data[$setting_id]['width'] = '';
                continue;
            }

            $img_thumbnail_url = '';

		    if ( $redux_data[$setting_id]['url'] != $redux_data[$setting_id]['thumbnail'] ){
                $img_thumbnail_src = wp_get_attachment_image_src( $parameters['id'], 'thumbnail' );
                $img_thumbnail_url = $img_thumbnail_src[0];
            }

		    $img_full_src = wp_get_attachment_image_src( $parameters['id'], 'full' );

            $redux_data[$setting_id]['url'] = $img_full_src[0];
            $redux_data[$setting_id]['thumbnail'] = $img_thumbnail_url ?: $img_full_src[0];

            $image_meta = wp_get_attachment_metadata( $parameters['id'] );

            if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
                $redux_data[$setting_id]['height'] = $image_meta['height'];
                $redux_data[$setting_id]['width'] = $image_meta['width'];
            }
		}

        return $redux_data;
	}

	/**
	 * Save posts with shortcodes.
	 * @param array $post
	 * @return array
	 */
	public static function post_parser($post){

		if (isset(self::$posts_with_shortcodes[$post['post_id']])){
			self::$posts_with_shortcodes[$post['post_id']]['post_id'] = $post['post_id'];
		}
		return $post;
	}

	/**
     * Correct taxonomy ID from old to new ones.
	 * @return array
	 */
	public static function correct_taxonomy_id($terms) {

		foreach ($terms as $id => &$term){
			if ($term["term_taxonomy"] === 'categories'){
				if (isset($term["termmeta"]) && !empty($term["termmeta"])){
					foreach ($term["termmeta"] as &$termmeta){
						if ($termmeta["key"]  === 'categories_taxonomies'){
							$new_value = array();
							$terms_meta_array = maybe_unserialize($termmeta["value"]);
							if (is_array($terms_meta_array)){
								foreach ( $terms_meta_array as $key => $value ){
									$new_value[$key] = (int)self::$processed_terms[$value];
								}
								$termmeta["value"] = maybe_serialize($new_value);
							}
						}
					}
				}
			}
		}
		return $terms;
	}

	/**
     * Set BABE taxonomy and save new IDs.
	 * @return void
	 */
	public static function import_taxonomy_first() {

        add_filter( 'http_request_host_is_external', '__return_true' );

		$terms =  $GLOBALS['wp_import']->terms;
		$all_terms = array();
		foreach ($terms as $id => $term){

			if ( $term["term_taxonomy"] === BABE_Post_types::$taxonomies_list_tax ){
				// if the term already exists in the correct taxonomy leave it alone
				$term_id = term_exists( $term['slug'], $term['term_taxonomy'] );
				if (!$term_id) {

					$args        = array(
						'slug'        => $term['slug'],
						'description' => '',
					);

					$id = wp_insert_term( wp_slash( $term['term_name'] ), $term['term_taxonomy'], $args );
					if ( ! is_wp_error( $id ) ) {
						update_term_meta($id['term_id'], 'gmap_active', 0);
						update_term_meta($id['term_id'], 'select_mode', 'multi_checkbox');
						update_term_meta($id['term_id'], 'frontend_style', 'col_3');
						self::$processed_terms[$term['term_id']] = $id['term_id']; // old/new
					} else {
						printf( __( 'Failed to import %s %s', 'wordpress-importer' ), esc_html($term['term_taxonomy']), esc_html($term['term_name']) );
						if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
							echo ': ' . $id->get_error_message();
						echo '<br />';
						continue;
					}
				} else {
					self::$processed_terms[$term['term_id']] = $term_id['term_id']; // old /new
				}

			} else {
				$all_terms[] = $term;
			}

		}
		BABE_Post_types::init_taxonomies_list();
		$GLOBALS['wp_import']->terms = $all_terms;
	}
}