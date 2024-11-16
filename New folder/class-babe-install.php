<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Install Class.
 * 
 * @class 		BABE_Install
 * @version		1.3.20
 * @author 		Booking Algorithms
 */

class BABE_Install {
    
    static $demo_rules = array();
       
    static $demo_ages = array();
    
    static $demo_categories = array();
    
    static $demo_images = array();
    
    static $saved_demo_images = array();
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {
        
        self::init_class_settings();
        
        register_activation_hook( BABE_PLUGIN, array( __CLASS__, 'activation') );
        register_deactivation_hook( BABE_PLUGIN, array( __CLASS__, 'deactivation') );
        
        //add_filter( 'cron_schedules', array( __CLASS__, 'cron_add_ten_min' ));
        add_filter( 'cron_schedules', array( __CLASS__, 'cron_add_three_min' ));
        add_action( 'babe_remove_draft_orders', array( __CLASS__, 'remove_draft_orders'));
        add_action( 'babe_expire_coupons', array( __CLASS__, 'expire_coupons'));
        
        add_action( 'init', array( __CLASS__, 'db_upgrade' ), 1);
        add_action( 'init', array( __CLASS__, 'roles_upgrade' ), 1);
        add_action( 'init', array( __CLASS__, 'check_version' ), 5);
        add_action( 'init', array( __CLASS__, 'load_textdomain' ));
        add_action( 'after_setup_theme', array( __CLASS__, 'setup_thumbnails' ), 10 );
	}

    public static function check_version() {

        if ( wp_doing_ajax() ){
            return;
        }

        $last_version = get_option( 'BABE_version' );
        $requires_update = version_compare( $last_version, BABE_VERSION, '<' );
        if ( $requires_update ) {
            self::install();

            do_action( 'babe_updated' );

            if ( ! $last_version ) {
                do_action( 'babe_newly_installed' );
            }
        }
    }

    private static function is_installing() {
        return 'yes' === get_transient( 'babe_installing' );
    }

    public static function install() {
        if ( ! is_blog_installed() ) {
            return;
        }

        if ( self::is_installing() ) {
            return;
        }

        set_transient( 'babe_installing', 'yes', MINUTE_IN_SECONDS * 10 );

        self::create_roles();
        self::setup_environment();
        self::remove_events();
        self::schedule_events();

        self::update_babe_version();

        delete_transient( 'babe_installing' );

        flush_rewrite_rules();

        do_action( 'babe_installed' );
    }

    private static function update_babe_version() {
        update_option( 'BABE_version', BABE_VERSION );
    }

    private static function setup_environment() {
        BABE_Post_types::register_post_types();
        BABE_Post_types::register_taxonomies();
        BABE_API::add_endpoint();
    }
    
//////////////////////////////
	/**
	 * DB upgrades
     * 
     * @return void
	 */
	public static function db_upgrade() {

        /** var QM_DB $wpdb */
	    global $wpdb;

        if ( wp_doing_ajax() ){
            return;
        }

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }

        $babe_db_version = get_option('BABE_db_version');

        if ( !$babe_db_version || version_compare( $babe_db_version, '1.3.20', '<' ) ){

            if( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}babe_rates'") == $wpdb->prefix.'babe_rates' && !get_option('BABE_db_upgrading') ){
                update_option('BABE_db_upgrading', 1);
                $wpdb->hide_errors();

                //Add new keys
                $wpdb->query( "ALTER TABLE {$wpdb->prefix}babe_rates ADD KEY apply_days (apply_days(191)), ADD KEY start_days (start_days(191)), ADD KEY min_booking_period (min_booking_period), ADD KEY max_booking_period (max_booking_period)" );

                $wpdb->query( "ALTER TABLE {$wpdb->prefix}babe_booking_rules ADD KEY basic_booking_period (basic_booking_period),
ADD KEY hold (hold), 
ADD KEY stop_booking_before (stop_booking_before), 
ADD KEY ages (ages), 
ADD KEY payment_model (payment_model), 
ADD KEY deposit (deposit), 
ADD KEY recurrent_payments (recurrent_payments), 
ADD KEY booking_mode (booking_mode)" );

                $wpdb->query( "ALTER TABLE {$wpdb->prefix}babe_payment_tokens ADD KEY order_id (order_id), 
ADD KEY gateway_id (gateway_id(191)), 
ADD KEY amount (amount),  
ADD KEY is_default (is_default)" );

                update_option('BABE_db_version', '1.3.20');
                delete_option('BABE_db_upgrading');
            }
        }

        if ( version_compare( $babe_db_version, '1.3.34', '<' ) && !get_option('BABE_db_upgrading', false) ){

            update_option('BABE_db_upgrading', 1);

            $languages = BABE_Functions::get_all_languages();
            $settings = get_option('babe_settings', []);

            foreach( $languages as $lang_code => $language ) {
                if (get_option('babe_settings_' . $lang_code, 'option_not_exists') === 'option_not_exists') {
                   update_option( 'babe_settings_' . $lang_code, $settings );
                }
            }
            update_option('BABE_db_version', '1.3.34');
            delete_option('BABE_db_upgrading');
        }

        if ( version_compare( $babe_db_version, '1.4.22', '<' ) && !get_option('BABE_db_upgrading', false) ){

            update_option('BABE_db_upgrading', 1);

            if ( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}babe_rates_meta'") == $wpdb->prefix.'babe_rates_meta' ){
                $wpdb->query( "DROP TABLE {$wpdb->prefix}babe_rates_meta;" );
            }

            update_option('BABE_db_version', '1.4.22');
            delete_option('BABE_db_upgrading');
        }

        if ( version_compare( $babe_db_version, '1.5.7', '<' ) && !get_option('BABE_db_upgrading', false) ){

            update_option('BABE_db_upgrading', 1);

            self::expire_coupons();

            update_option('BABE_db_version', '1.5.7');
            delete_option('BABE_db_upgrading');
        }

        if ( version_compare( $babe_db_version, '1.5.25', '<' ) && !get_option('BABE_db_upgrading', false) ){

            if (function_exists('ini_set')){
                ini_set('max_execution_time', '300');
                ini_set('memory_limit','256M');
            }

            update_option('BABE_db_upgrading', 1);

            $wpdb->query( "CREATE TABLE {$wpdb->prefix}babe_category_deactivate_schedule (
  id int NOT NULL AUTO_INCREMENT,
  category_id bigint(20) DEFAULT NULL,
  deactivate_date_from datetime DEFAULT NULL,
  deactivate_date_to datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY category_id (category_id),
  KEY deactivate_date_from (deactivate_date_from),
  KEY deactivate_date_to (deactivate_date_to),
  KEY category_deactivate_date_from_to (category_id, deactivate_date_from, deactivate_date_to)
) $collate;" );

            $wpdb->query( "ALTER TABLE {$wpdb->prefix}babe_rates 
    ADD INDEX booking_obj_date_from_to (booking_obj_id, date_from, date_to),
    ADD INDEX booking_obj_date_from_to_apply_days (booking_obj_id, date_from, date_to, apply_days(64));" );

            $wpdb->query( "ALTER TABLE {$wpdb->prefix}babe_av_cal ADD av_guests INT DEFAULT 0, ADD KEY av_guests (av_guests), ADD KEY booking_obj_date_from_guests_in_schedule (booking_obj_id,date_from,guests,in_schedule), ADD KEY booking_obj_date_from_av_guests_in_schedule (booking_obj_id,date_from,av_guests,in_schedule)" );

            $wpdb->query( "UPDATE {$wpdb->prefix}babe_av_cal ac,
             ( 
               SELECT pm.post_id, pm.meta_value AS item_max_guests, pmt.items_number 
               FROM {$wpdb->postmeta} pm
               INNER JOIN ".$wpdb->term_relationships." tr ON pm.post_id = tr.object_id 
               INNER JOIN ".$wpdb->term_taxonomy." ct ON ct.term_taxonomy_id = tr.term_taxonomy_id 
               AND ct.taxonomy = '".BABE_Post_types::$categories_tax."'
               INNER JOIN ".$wpdb->terms." ctt ON ctt.term_id = ct.term_id
               LEFT JOIN 
               (
               SELECT GREATEST(CAST(COALESCE(meta_value, 1) AS DECIMAL), 1) AS items_number, post_id AS pmt_post_id, meta_key AS pmt_meta_key
               FROM ".$wpdb->postmeta."
               ) pmt ON ( pm.post_id = pmt.pmt_post_id AND pmt.pmt_meta_key = CONCAT('items_number_', ctt.slug) )
               WHERE pm.meta_key = 'guests'
            ) AS t
             SET ac.av_guests = (t.item_max_guests * t.items_number - ac.guests) WHERE ac.booking_obj_id = t.post_id AND (ac.av_guests IS NULL OR ac.av_guests = 0);" );

            update_option('BABE_db_version', '1.5.25');
            delete_option('BABE_db_upgrading');
        }

        if ( version_compare( $babe_db_version, '1.5.28', '<' ) && !get_option('BABE_db_upgrading', false) ){

            update_option('BABE_db_upgrading', 1);

            if ( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}babe_category_deactivate_schedule'") !== $wpdb->prefix.'babe_category_deactivate_schedule' ){

                $wpdb->query( "CREATE TABLE {$wpdb->prefix}babe_category_deactivate_schedule (
  id int NOT NULL AUTO_INCREMENT,
  category_id bigint(20) DEFAULT NULL,
  deactivate_date_from datetime DEFAULT NULL,
  deactivate_date_to datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY category_id (category_id),
  KEY deactivate_date_from (deactivate_date_from),
  KEY deactivate_date_to (deactivate_date_to),
  KEY category_deactivate_date_from_to (category_id, deactivate_date_from, deactivate_date_to)
) $collate;" );
            }

            $column = $wpdb->get_var("SHOW COLUMNS FROM {$wpdb->prefix}babe_av_cal LIKE 'av_guests'");

            if ( empty($column) ){
                $wpdb->query( "ALTER TABLE {$wpdb->prefix}babe_rates 
    ADD INDEX booking_obj_date_from_to (booking_obj_id, date_from, date_to),
    ADD INDEX booking_obj_date_from_to_apply_days (booking_obj_id, date_from, date_to, apply_days(64));" );

                $wpdb->query( "ALTER TABLE {$wpdb->prefix}babe_av_cal ADD av_guests INT DEFAULT 0, ADD KEY av_guests (av_guests), ADD KEY booking_obj_date_from_guests_in_schedule (booking_obj_id,date_from,guests,in_schedule), ADD KEY booking_obj_date_from_av_guests_in_schedule (booking_obj_id,date_from,av_guests,in_schedule)" );

                $wpdb->query( "UPDATE {$wpdb->prefix}babe_av_cal ac,
             ( 
               SELECT pm.post_id, pm.meta_value AS item_max_guests, pmt.items_number 
               FROM {$wpdb->postmeta} pm
               INNER JOIN ".$wpdb->term_relationships." tr ON pm.post_id = tr.object_id 
               INNER JOIN ".$wpdb->term_taxonomy." ct ON ct.term_taxonomy_id = tr.term_taxonomy_id 
               AND ct.taxonomy = '".BABE_Post_types::$categories_tax."'
               INNER JOIN ".$wpdb->terms." ctt ON ctt.term_id = ct.term_id
               LEFT JOIN 
               (
               SELECT GREATEST(CAST(COALESCE(meta_value, 1) AS DECIMAL), 1) AS items_number, post_id AS pmt_post_id, meta_key AS pmt_meta_key
               FROM ".$wpdb->postmeta."
               ) pmt ON ( pm.post_id = pmt.pmt_post_id AND pmt.pmt_meta_key = CONCAT('items_number_', ctt.slug) )
               WHERE pm.meta_key = 'guests'
            ) AS t
             SET ac.av_guests = (t.item_max_guests * t.items_number - ac.guests) WHERE ac.booking_obj_id = t.post_id AND (ac.av_guests IS NULL OR ac.av_guests = 0);" );
            }

            update_option('BABE_db_version', '1.5.28');
            delete_option('BABE_db_upgrading');
        }

        // just reset
        delete_option('BABE_db_upgrading');

        self::schedule_events();
   	}

//////////////////////////////
    /**
     * Roles upgrades
     *
     * @return void
     */
    public static function roles_upgrade() {

        if ( !wp_doing_ajax() ){
            return;
        }

        $babe_roles_version = get_option('BABE_roles_version');

        if ( !$babe_roles_version || version_compare( $babe_roles_version, '1.3.29', '<' ) ){
            BABE_Post_types::register_post_types();
            flush_rewrite_rules();
            self::remove_roles();
            self::create_roles();
            update_option('BABE_roles_version', '1.3.29');
        }
    }
 
//////////////////////////////
	/**
	 * Load Localisation files.
     * 
     * @return void
	 */
	public static function load_textdomain() {

        $locale = apply_filters( 'plugin_locale', get_locale(), 'ba-book-everything' );

		load_textdomain( 'ba-book-everything', WP_LANG_DIR . '/ba-book-everything/ba-book-everything-' . $locale . '.mo' );
		load_plugin_textdomain( 'ba-book-everything', false, BABE_PLUGIN_DIR . '/languages' );
	}
        
//////////////////////////////

    /**
	 * Install tables, roles, etc.
     * 
     * @return void
	 */
    public static function activation() {
        
       global $wpdb;
       
       BABE_Post_types::register_post_types();
       BABE_Post_types::register_taxonomies();
       
       self::create_roles();

       update_option('BABE_roles_version', BABE_VERSION);
       
       self::create_page('search-result', 'search_result_page', __('Search result', 'ba-book-everything'));
       self::create_page('checkout', 'checkout_page', __('Checkout', 'ba-book-everything'));
       self::create_page('add_services', 'services_page', __('Add Services', 'ba-book-everything'));
       self::create_page('confirmation', 'confirmation_page', __('Confirmation', 'ba-book-everything'));
       self::create_page('terms-conditions', 'terms_page', __('Terms & Conditions', 'ba-book-everything'), __('You have to edit "Terms & Conditions" page to replace this start content with your own.', 'ba-book-everything'));
       self::create_page('my-account', 'my_account_page', __('My Account', 'ba-book-everything'));
       self::create_page('admin-confirmation', 'admin_confirmation_page', __('Admin confirmation', 'ba-book-everything'));
       self::create_page_all_items();

        BABE_API::add_endpoint();
       
       flush_rewrite_rules();

	   $wpdb->hide_errors();
	   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	   dbDelta( self::get_schema() );

       update_option('BABE_db_version', BABE_VERSION);

       self::db_upgrade();

       self::schedule_events();
       set_transient( 'babe-payment-banner-notice', 1 );
       self::setup_rules();
	}

    private static function schedule_events() {
        if ( empty( wp_get_schedule('babe_remove_draft_orders') ) ){
            wp_schedule_event( time(), 'three_min', 'babe_remove_draft_orders' );
        }
        if ( empty( wp_get_schedule('babe_expire_coupons') ) ){
            wp_schedule_event( time(), 'hourly', 'babe_expire_coupons' );
        }
    }

    private static function remove_events() {
        wp_clear_scheduled_hook( 'babe_remove_draft_orders' );
        wp_clear_scheduled_hook( 'babe_expire_coupons' );
    }
    
//////////////////////////////

    /**
	 * Remove roles, etc.
     * @return void
	 */
    public static function deactivation() {
       
       self::remove_roles();
       flush_rewrite_rules();
       self::remove_events();
	}
    
////////////////////////////
    /**
	 * Create front-end page.
     * 
     * @return int - page ID
	 */
	public static function create_page( $slug, $option, $page_title = '', $page_content = '', $post_parent = 0 ) {
	   
    global $wpdb;
    
    $settings = !empty(get_option(BABE_Settings::$option_name)) ? get_option(BABE_Settings::$option_name) : [];
    $option_value = isset($settings[$option]) ? absint($settings[$option]) : 0;
    
    if ( $option_value && get_post( $option_value ) ){
      return $option_value;
    }  
      
    $page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '$slug' LIMIT 1;");
    
    if ( $page_found ) :
      if ( ! $option_value ){
        $settings[$option] = $page_found;
        update_option( BABE_Settings::$option_name, $settings );
      }
      return $page_found;  
    endif;
    
    $page_data = array(
          'post_status' 		=> 'publish',
          'post_type' 		=> 'page',
          'post_author' 		=> 1,
          'post_name' 		=> $slug,
          'post_title' 		=> $page_title,
          'post_content' 		=> $page_content,
          'post_parent' 		=> $post_parent,
          'comment_status' 	=> 'closed'
      );
    $page_id = wp_insert_post( $page_data );
      
    $settings[$option] = $page_id;
    update_option( BABE_Settings::$option_name, $settings );
    
    return $page_id;
}        
    
////////////////////////////
    /**
	 * Create roles and capabilities.
     * 
     * @return void
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Customer role
		add_role( 'customer', __( 'Customer', 'ba-book-everything' ), array(
			'read' 					=> true
		) );

		// Manager role
		add_role( 'manager', __( 'Manager', 'ba-book-everything' ), array(
			'read'                   => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true,
			'edit_users'             => true,
			'edit_posts'             => true,
			'edit_pages'             => true,
			'edit_published_posts'   => true,
			'edit_published_pages'   => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_others_posts'      => true,
			'edit_others_pages'      => true,
			'publish_posts'          => true,
			'publish_pages'          => true,
			'delete_posts'           => true,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'delete_others_posts'    => true,
			'delete_others_pages'    => true,
			'manage_categories'      => true,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'list_users'             => true
		) );

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->add_cap( 'manager', $cap );
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}
	}
    
/////////////////////////////////////    

	/**
	 * Get capabilities for admin/manager
	 *
	 * @return array
	 */
	 private static function get_core_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_bookeverything',
			'view_bookeverything_reports'
		);

		$capability_types = [
		    BABE_Post_types::$booking_obj_post_type,
            BABE_Post_types::$service_post_type,
            BABE_Post_types::$order_post_type,
            BABE_Post_types::$fee_post_type,
            BABE_Post_types::$coupon_post_type,
            BABE_Post_types::$faq_post_type,
            BABE_Post_types::$mpoints_post_type,
        ];

		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms"
			);
		}

		return $capabilities;
	}
    
////////////////////////////////////    

	/**
	 * Remove roles
     * 
     * @return void
	 */
	public static function remove_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->remove_cap( 'manager', $cap );
				$wp_roles->remove_cap( 'administrator', $cap );
			}
		}

		remove_role( 'customer' );
		remove_role( 'manager' );
	}    
    
//////////////////////////////
	/**
	 * Get Table schema.
     * 
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		/*
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 *
		 * This may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
		 * indexes first causes too much load on some servers/larger DB.
		 */
		$max_index_length = 191;

		$tables = "
CREATE TABLE {$wpdb->prefix}babe_rates (
  rate_id bigint(20) NOT NULL AUTO_INCREMENT,
  booking_obj_id bigint(20) DEFAULT NULL,
  rate_title text DEFAULT NULL,
  date_from datetime DEFAULT NULL,
  date_to datetime DEFAULT NULL,
  apply_days longtext DEFAULT NULL,
  start_days longtext DEFAULT NULL,
  min_booking_period int(4) DEFAULT '0',
  max_booking_period int(4) DEFAULT '0',
  price_from float DEFAULT '0',
  price_general longtext DEFAULT NULL,
  prices_conditional longtext DEFAULT NULL,
  color varchar(16) DEFAULT NULL,
  rate_order int(11) DEFAULT '0',
  PRIMARY KEY  (rate_id),
  KEY booking_obj_id (booking_obj_id),
  KEY date_from (date_from),
  KEY date_to (date_to),
  KEY price_from (price_from),
  KEY rate_order (rate_order),
  KEY apply_days (apply_days(191)),
  KEY start_days (start_days(191)),
  KEY min_booking_period (min_booking_period),
  KEY max_booking_period (max_booking_period),
  KEY booking_obj_date_from_to (booking_obj_id, date_from, date_to),
  KEY booking_obj_date_from_to_apply_days (booking_obj_id, date_from, date_to, apply_days(64))
) $collate;

CREATE TABLE {$wpdb->prefix}babe_av_cal (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  booking_obj_id bigint(20) DEFAULT NULL,
  date_from datetime DEFAULT NULL,
  guests bigint(20) DEFAULT '0',
  in_schedule int(1) DEFAULT '0',
  av_guests INT DEFAULT 0,
  PRIMARY KEY  (id),
  KEY booking_obj_id (booking_obj_id),
  KEY date_from (date_from),
  KEY guests (guests),
  KEY in_schedule (in_schedule),
  KEY av_guests (av_guests), 
  KEY booking_obj_date_from_guests_in_schedule (booking_obj_id,date_from,guests,in_schedule), 
  KEY booking_obj_date_from_av_guests_in_schedule (booking_obj_id,date_from,av_guests,in_schedule)
) $collate;

CREATE TABLE {$wpdb->prefix}babe_booking_rules (
  rule_id bigint(20) NOT NULL AUTO_INCREMENT,
  rule_title text DEFAULT NULL,
  basic_booking_period varchar(24) DEFAULT NULL,
  hold int DEFAULT '0',
  stop_booking_before int DEFAULT '0',
  ages int(1) DEFAULT '0',
  payment_model varchar(24) DEFAULT NULL,
  deposit float DEFAULT '0',
  recurrent_payments int(1) DEFAULT '0',
  booking_mode varchar(24) DEFAULT NULL,
  PRIMARY KEY  (rule_id),
  KEY basic_booking_period (basic_booking_period),
  KEY hold (hold), 
  KEY stop_booking_before (stop_booking_before), 
  KEY ages (ages),
  KEY payment_model (payment_model),
  KEY deposit (deposit),
  KEY recurrent_payments (recurrent_payments),
  KEY booking_mode (booking_mode)
) $collate;

CREATE TABLE {$wpdb->prefix}babe_discount (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  booking_obj_id bigint(20) DEFAULT NULL,
  date_from datetime DEFAULT NULL,
  date_to datetime DEFAULT NULL,
  discount float DEFAULT '0',
  PRIMARY KEY  (id),
  KEY booking_obj_id (booking_obj_id),
  KEY date_from (date_from),
  KEY date_to (date_to)
) $collate;

CREATE TABLE {$wpdb->prefix}babe_order_items (
  order_item_id bigint(20) NOT NULL AUTO_INCREMENT,
  booking_obj_id bigint(20) DEFAULT NULL,
  order_item_name longtext NOT NULL,
  order_id bigint(20) NOT NULL,
  PRIMARY KEY  (order_item_id),
  KEY booking_obj_id (booking_obj_id),
  KEY order_id (order_id)
) $collate;

CREATE TABLE {$wpdb->prefix}babe_order_itemmeta (
  meta_id bigint(20) NOT NULL AUTO_INCREMENT,
  order_item_id bigint(20) NOT NULL,
  meta_key varchar(255) default NULL,
  meta_value longtext NULL,
  PRIMARY KEY  (meta_id),
  KEY order_item_id (order_item_id),
  KEY meta_key (meta_key($max_index_length))
) $collate;

CREATE TABLE {$wpdb->prefix}babe_payment_tokens (
  token_id bigint(20) NOT NULL AUTO_INCREMENT,
  order_id bigint(20) NOT NULL,
  gateway_id varchar(255) NOT NULL,
  token text NOT NULL,
  amount float DEFAULT '0',
  user_id bigint(20) NOT NULL DEFAULT '0',
  type varchar(255) NOT NULL,
  is_default tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (token_id),
  KEY user_id (user_id),
  KEY order_id (order_id), 
  KEY gateway_id (gateway_id(191)),
  KEY amount (amount),
  KEY is_default (is_default)
) $collate;

CREATE TABLE {$wpdb->prefix}babe_payment_tokenmeta (
  meta_id bigint(20) NOT NULL AUTO_INCREMENT,
  payment_token_id bigint(20) NOT NULL,
  meta_key varchar(255) NULL,
  meta_value longtext NULL,
  PRIMARY KEY  (meta_id),
  KEY payment_token_id (payment_token_id),
  KEY meta_key (meta_key($max_index_length))
) $collate;

CREATE TABLE {$wpdb->prefix}babe_category_deactivate_schedule (
  id int NOT NULL AUTO_INCREMENT,
  category_id bigint(20) DEFAULT NULL,
  deactivate_date_from datetime DEFAULT NULL,
  deactivate_date_to datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY category_id (category_id),
  KEY deactivate_date_from (deactivate_date_from),
  KEY deactivate_date_to (deactivate_date_to),
  KEY category_deactivate_date_from_to (category_id, deactivate_date_from, deactivate_date_to)
) $collate;
		";

		return $tables;
	}

//////////////////////////////
	/**
	 * Setup thumbnails
     * 
	 * @return void
	 */
	public static function setup_thumbnails() {
	   
       if ( ! current_theme_supports( 'post-thumbnails' ) ) {
          add_theme_support( 'post-thumbnails' );
	   }
       
       add_image_size( 'ba-thumbnail', 350, 200, true );
       add_image_size( 'ba-thumbnail-sq', 500, 500, true ); 
    }
    
//////////////////////////////
	/**
	 * Add 10 minutes interval
     * 
     * @param array $schedules
	 * @return array
	 */
	public static function cron_add_ten_min( $schedules ) {
	  $schedules['ten_min'] = array(
		'interval' => 60 * 10,
		'display' => 'One time per 10 min'
	  );
	  return $schedules;
    }
    
//////////////////////////////
	/**
	 * Add 3 minutes interval
     * 
     * @param array $schedules
	 * @return array
	 */
	public static function cron_add_three_min( $schedules ) {
	  $schedules['three_min'] = array(
		'interval' => 60 * 3,
		'display' => 'One time per 3 min'
	  );
	  return $schedules;
    }        
    
//////////////////////////////
	/**
	 * Remove draft orders
     * 
	 * @return void
	 */
	public static function remove_draft_orders(){

    $args = array(
               'post_type' => BABE_Post_types::$order_post_type,// 'post_status' => 'draft',
               'posts_per_page'=> -1,
               'date_query' => array(
                 array(
                   'before'    => '10 minutes ago',
                   'inclusive' => true,
                 ),
                ),
                'meta_query' => array(
                  'status' => array(
                    'key'     => '_status',
                    'value'   => 'draft',
                    'compare' => '=',
                    ),
                 ),
                );           

     $the_query = new WP_Query( $args );
     while ( $the_query->have_posts() ) : $the_query->the_post();
       $order_id = get_the_ID();
       wp_delete_post( $order_id, true );
     endwhile;
     wp_reset_postdata();
     
     //////////////////////
     
     $waiting = absint(BABE_Settings::$settings['order_payment_processing_waiting']);
     
     if ($waiting < 20){
        $waiting = 20;
     }
     
     $args = array(
               'post_type' => BABE_Post_types::$order_post_type,
               'posts_per_page'=> -1,
               'date_query' => array(
                 array(
                   'before'    => $waiting.' minutes ago',
                   'inclusive' => true,
                 ),
                ),
                'meta_query' => array(
                  'status' => array(
                    'key'     => '_status',
                    'value'   => 'payment_processing',
                    'compare' => '=',
                    ),
                 ),
                );
                
     $the_query = new WP_Query( $args );
     while ( $the_query->have_posts() ) : $the_query->the_post();
       $order_id = get_the_ID();
       if (!BABE_Order::get_order_prepaid_received($order_id)){
        //// delete if it is only first payment for the order
          wp_delete_post( $order_id, true );
       }
     endwhile;
     wp_reset_postdata();
  }

  public static function expire_coupons(){

        global $wpdb;

        $d = BABE_Functions::datetime_local();
        $cast_format = BABE_Settings::$settings['date_format'] === 'd/m/Y' ? "%d/%m/%Y" : "%m/%d/%Y";

        $sql = "UPDATE {$wpdb->postmeta} st
        INNER JOIN {$wpdb->posts} p ON p.ID = st.post_id AND p.post_type ='".BABE_Post_types::$coupon_post_type."'
        AND st.meta_key = '_coupon_status'
        INNER JOIN {$wpdb->postmeta} expd ON st.post_id = expd.post_id AND expd.meta_key='_coupon_end_date' 
        AND STR_TO_DATE(expd.meta_value, '".$cast_format."') < '".$d->format('Y-m-d')."'
        SET st.meta_value = 'expired'
        WHERE st.meta_value != 'expired'
        ";

        $wpdb->query( $sql );
  }
  
//////////////////////////////

    /**
	 * Init class settings
     * 
     * @return
	 */
    public static function init_class_settings() {
        
         self::$demo_rules = array(
          'Tour' => array(
              'rule_id' => 0,
              'rule_title' => 'Tour',
              'basic_booking_period' => 'recurrent_custom',
              'stop_booking_before' => 2,
              'deposit' => 0,
              'ages' => 1,
              'payment_model' => 'full',
              'recurrent_payments' => 0,
              'booking_mode' => 'tickets',
          ),
          'Hotel' => array(
              'rule_id' => 0,
              'rule_title' => 'Hotel',
              'basic_booking_period' => 'night',
              'stop_booking_before' => 0,
              'deposit' => 0,
              'ages' => 0,
              'payment_model' => 'full',
              'recurrent_payments' => 0,
              'booking_mode' => 'object',
          ),
          'Hostel' => array(
              'rule_id' => 0,
              'rule_title' => 'Hostel',
              'basic_booking_period' => 'night',
              'stop_booking_before' => 0,
              'deposit' => 0,
              'ages' => 0,
              'payment_model' => 'full',
              'recurrent_payments' => 0,
              'booking_mode' => 'places',
          ),
          'Car' => array(
              'rule_id' => 0,
              'rule_title' => 'Car',
              'basic_booking_period' => 'day',
              'stop_booking_before' => 0,
              'deposit' => 0,
              'ages' => 0,
              'payment_model' => 'full',
              'recurrent_payments' => 0,
              'booking_mode' => 'object',
          ),
    /*      'Bike' => array(
	          'rule_id' => 0,
	          'rule_title' => 'Bike',
	          'basic_booking_period' => 'hour',
	          'stop_booking_before' => 0,
	          'deposit' => 0,
	          'ages' => 0,
	          'payment_model' => 'full',
	          'recurrent_payments' => 0,
	          'booking_mode' => 'object',
          ), */
          'One time event' => array(
              'rule_id' => 0,
              'rule_title' => 'One time event',
              'basic_booking_period' => 'single_custom',
              'stop_booking_before' => 0,
              'deposit' => 0,
              'ages' => 0,
              'payment_model' => 'full',
              'recurrent_payments' => 0,
              'booking_mode' => 'tickets',
          ),
        );
       
        self::$demo_ages = array(
          'adult' => array(
              'slug' => 'adult',
              'title' => __( 'Adult', 'ba-book-everything' ),
              'description' => __( '18+ years', 'ba-book-everything' ),
              'menu_order' => 1,
          ),
          'youth' => array(
              'slug' => 'youth',
              'title' => __( 'Youth', 'ba-book-everything' ),
              'description' => __( '13-17 years', 'ba-book-everything' ),
              'menu_order' => 2,
          ),
          'children' => array(
              'slug' => 'children',
              'title' => __( 'Children', 'ba-book-everything' ),
              'description' => __( '0-12 years', 'ba-book-everything' ),
              'menu_order' => 3,
          ),
        );
        
        self::$demo_images = array(
           'Tour' => 'StockSnap_7Y84D37QEG.jpg',
           'Hotel' => 'StockSnap_6SMA09U7Y8.jpg',
           'Hostel' => 'StockSnap_8B828310AC.jpg',
           'Car' => 'StockSnap_WPGJ8XT8MB.jpg',
           'One time event' => 'StockSnap_J45DTZD2VJ.jpg',
           'Place' => 'StockSnap_HGVQPNFUDV.jpg',
           'Service' => 'StockSnap_BRRVW36UIY.jpg',
	  //     'Bike' =>'StockSnap_WPGJ8XT8MB.jpg'
        );
        /**
        Copyrights:
        
        Bryce Canyon, Copyright Gert Boers
        License: CC0 1.0 Universal (CC0 1.0)
        Source: https://stocksnap.io/photo/7Y84D37QEG
        
        Interior room, Copyright Jaroslaw Ceborski
        License: CC0 1.0 Universal (CC0 1.0)
        Source: https://stocksnap.io/photo/6SMA09U7Y8
        
        Hostel room, Copyright Jay Mantri
        License: CC0 1.0 Universal (CC0 1.0)
        Source: https://stocksnap.io/photo/8B828310AC
        
        Car, Copyright Mike Birdy
        License: CC0 1.0 Universal (CC0 1.0)
        Source: https://stocksnap.io/photo/WPGJ8XT8MB
        
        Concert, Copyright Samuel Zeller
        License: CC0 1.0 Universal (CC0 1.0)
        Source: https://stocksnap.io/photo/J45DTZD2VJ
        
        Santorini, Copyright Andrzej
        License: CC0 1.0 Universal (CC0 1.0)
        Source: https://stocksnap.io/photo/HGVQPNFUDV
        
        Coffee, Copyright Atichart Wongubon
        License: CC0 1.0 Universal (CC0 1.0)
        Source: https://stocksnap.io/photo/BRRVW36UIY

        Bike, Copyright Anthony Ginsbrook
        License: CC0 1.0 Universal (CC0 1.0)
        Source: https://stocksnap.io/photo/EAYRJAXV9F

        */
        
        self::$saved_demo_images = (array) get_option('_babe_demo_images');
        
        return;        
        
    }  
  
///////////////////////////////////////    
    /**
	 * Setup demo rules.
     * 
     * @return
	 */
    public static function setup_rules(){
        
       $rules = BABE_Booking_Rules::get_all_rules();
       
       $demo_rules = self::$demo_rules;
       
       if (!empty($rules)){
          foreach ($rules as $rule){
              if ( isset($demo_rules[$rule['rule_title']]) ){
                 unset($demo_rules[$rule['rule_title']]);
                 self::$demo_rules[$rule['rule_title']]['rule_id'] = $rule['rule_id'];
              }
          }        
       }
       
       if (!empty($demo_rules)){
          foreach ($demo_rules as $demo_rule){
             $rule_id = BABE_Booking_Rules::add_rule($demo_rule);
             self::$demo_rules[$demo_rule['rule_title']]['rule_id'] = $rule_id;
          }
       }   
        
       return; 
    }
    
///////////////////////////////////////    
    /**
	 * Setup demo ages.
     * 
     * @return
	 */
    public static function setup_ages(){
        
       $demo_ages = self::$demo_ages;
       
       $age_terms = get_terms( array(
          'taxonomy' => BABE_Post_types::$ages_tax,
          'hide_empty' => false,
          'order' => 'ASC',
          'orderby' => 'meta_value_num',
          'meta_key' => 'menu_order',
       ) );
        
       if ( !empty($age_terms) ){
            foreach( $age_terms as $age_term ) {
                if ( isset($demo_ages[$age_term->slug]) ){
                 unset($demo_ages[$age_term->slug]);
                } 
            }
       }
       
       if (!empty($demo_ages)){
          foreach ($demo_ages as $demo_age){
             $inserted_term = wp_insert_term(
               $demo_age['title'],   // the term 
               BABE_Post_types::$ages_tax, // the taxonomy
               array(
                 'description' => $demo_age['description'],
                 'slug'        => $demo_age['slug'],
               )
             );
             if (!is_wp_error($inserted_term)){
                $term_id = $inserted_term['term_id'];
                update_term_meta($term_id, 'menu_order', $demo_age['menu_order']);
             }
          }  /// end foreach $demo_ages
          
          BABE_Post_types::clear_cache_ages();
       }  
        
       return; 
    }
    
///////////////////////////////////////    
    /**
	 * Setup demo "Features" taxonomy
     * 
     * @return int - term id (taxonomy id) from $taxonomies_list_tax
	 */
    public static function setup_tax_features(){
        
        $output = 0;
        
        //// get term by slug
        $tax_name_term = get_term_by( 'slug', 'features', BABE_Post_types::$taxonomies_list_tax );
        
        if ($tax_name_term):
            //// if exists - return tax id
            $output = $tax_name_term->term_id;
            
        else :
        
            //// insert term
            $inserted_term = wp_insert_term(
               __( 'Features', 'ba-book-everything' ),   // the term
               BABE_Post_types::$taxonomies_list_tax, // the taxonomy
               array(
                 'description' => '',
                 'slug'        => 'features',
               )
            );
            
            if (!is_wp_error($inserted_term)){
                BABE_Post_types::init_taxonomies_list();
                //// return tax id
                $output = $inserted_term['term_id'];
                
                update_term_meta($inserted_term['term_id'], 'gmap_active', 0);
                update_term_meta($inserted_term['term_id'], 'select_mode', 'multi_checkbox');
                update_term_meta($inserted_term['term_id'], 'frontend_style', 'col_3');
             } 
            
            //// register tax features
            $new_tax_slug = BABE_Post_types::$attr_tax_pref . 'features';
            $new_tax_name = __( 'Features', 'ba-book-everything' );
        
            $labels = array(
			 'name'              => $new_tax_name,
			 'singular_name'     => $new_tax_name,
			 'search_items'      => sprintf(__( 'Search %s', 'ba-book-everything' ), $new_tax_name),
			 'all_items'         => sprintf(__( 'All %s', 'ba-book-everything' ), $new_tax_name),
			 'parent_item'       => sprintf(__( 'Parent %s', 'ba-book-everything' ), $new_tax_name),
			 'parent_item_colon' => sprintf(__( 'Parent %s:', 'ba-book-everything' ), $new_tax_name),
			 'edit_item'         => sprintf(__( 'Edit %s', 'ba-book-everything' ), $new_tax_name),
			 'update_itm'        => sprintf(__( 'Update %s', 'ba-book-everything' ), $new_tax_name),
			 'add_new_item'      => sprintf(__( 'Add New %s', 'ba-book-everything' ), $new_tax_name),
			 'new_item_name'     => sprintf(__( 'New %s', 'ba-book-everything' ), $new_tax_name),
			 'menu_name'         => sprintf(__( '%s', 'ba-book-everything' ), $new_tax_name),
		    );

		    register_taxonomy( $new_tax_slug, BABE_Post_types::$booking_obj_post_type, array(
			 'labels'            => $labels,
			 'hierarchical'      => true,
			 'query_var'         => $new_tax_slug,
			 'public'            => true,
			 'show_ui'           => true,
             'show_in_nav_menus'   => false,
		 	 'show_admin_column' => false,
             'show_in_menu' => true,
             'show_in_rest' => true,
		    ) );
            
            //// Insert demo feature terms
            $inserted_term = wp_insert_term(
               __( 'Feature 1', 'ba-book-everything' ),
               $new_tax_slug
            );
            $inserted_term = wp_insert_term(
               __( 'Feature 2', 'ba-book-everything' ),
               $new_tax_slug
            );
            $inserted_term = wp_insert_term(
               __( 'Feature 3', 'ba-book-everything' ),
               $new_tax_slug
            );
        
        endif;
        
       return $output; 
    }
    
///////////////////////////////////////    
    /**
	 * Setup demo categories.
     * 
     * @return
	 */
    public static function setup_categories(){
        
       $demo_rules = self::$demo_rules;
       
       $demo_rules['One time event']['categories_slug'] = 'one-time-event';
       $demo_rules['One time event']['categories_remove_guests'] = 0;
       $demo_rules['One time event']['categories_address'] = 1;
       $demo_rules['One time event']['categories_gmap_active'] = 1;
       
       $demo_rules['Tour']['categories_slug'] = 'tour';
       $demo_rules['Tour']['categories_remove_guests'] = 0;
       $demo_rules['Tour']['categories_address'] = 1;
       $demo_rules['Tour']['categories_gmap_active'] = 1;
       
       $demo_rules['Hotel']['categories_slug'] = 'hotel';
       $demo_rules['Hotel']['categories_remove_guests'] = 0;
       $demo_rules['Hotel']['categories_address'] = 0;
       $demo_rules['Hotel']['categories_gmap_active'] = 0;
       
       $demo_rules['Hostel']['categories_slug'] = 'hostel';
       $demo_rules['Hostel']['categories_remove_guests'] = 0;
       $demo_rules['Hostel']['categories_address'] = 0;
       $demo_rules['Hostel']['categories_gmap_active'] = 0;
       
       $demo_rules['Car']['categories_slug'] = 'car';
       $demo_rules['Car']['categories_remove_guests'] = 1;
       $demo_rules['Car']['categories_address'] = 0;
       $demo_rules['Car']['categories_gmap_active'] = 0;

       /*
	    $demo_rules['Bike']['categories_slug'] = 'bike';
	    $demo_rules['Bike']['categories_remove_guests'] = 1;
	    $demo_rules['Bike']['categories_address'] = 0;
	    $demo_rules['Bike']['categories_gmap_active'] = 0;
       */
       
       if (!empty($demo_rules)){
          foreach ($demo_rules as $demo_rule_title => $demo_rule){
            
            $term_id = 0;
            
            $category_term = get_term_by( 'slug', $demo_rule['categories_slug'], BABE_Post_types::$categories_tax );
            
            if ($category_term){
                
                $term_id = $category_term->term_id;
                
                self::$demo_categories[$term_id] = $demo_rule_title;
                
            } else {
                
                //// insert term
                $inserted_term = wp_insert_term(
                   $demo_rule_title,
                   BABE_Post_types::$categories_tax,
                   array(
                     'slug' => $demo_rule['categories_slug'],
                   )
                );
                
                if (!is_wp_error($inserted_term)){
                    $term_id = $inserted_term['term_id'];
                    
                    self::$demo_categories[$term_id] = $demo_rule_title;
                }
            }
            
            if ($term_id):
            //// update_term_meta
            $tax_ids = array();
            foreach(BABE_Post_types::$taxonomies_list as $taxonomy_id => $taxonomy){
                $tax_ids[] = $taxonomy_id;
            }
            
            update_term_meta($term_id, 'categories_week', array( 0, 1, 2, 3, 4, 5, 6, 7 ));
            update_term_meta($term_id, 'categories_booking_rule', $demo_rule['rule_id']);
            update_term_meta($term_id, 'categories_add_taxes', 0);
            update_term_meta($term_id, 'categories_taxonomies', $tax_ids );
            update_term_meta($term_id, 'categories_step_by_step', 1);
            update_term_meta($term_id, 'categories_step_title', __( 'Description', 'ba-book-everything' ));
            update_term_meta($term_id, 'categories_services', 1);
            update_term_meta($term_id, 'categories_services_title', __( 'Services', 'ba-book-everything' ));
            update_term_meta($term_id, 'categories_faq', 1);
            update_term_meta($term_id, 'categories_faq_title', __( 'Questions & Answers', 'ba-book-everything' ));
            update_term_meta($term_id, 'categories_remove_guests', $demo_rule['categories_remove_guests']);
            update_term_meta($term_id, 'categories_address', $demo_rule['categories_address']);
            update_term_meta($term_id, 'categories_gmap_active', $demo_rule['categories_gmap_active']);
            
            endif;
            
          }
       }   
        
       return; 
    }
    
///////////////////////////////////////    
    /**
	 * Create "all items" page.
     * 
     * @return int - page ID
	 */
    public static function create_page_all_items(){
        
        return self::create_page('all-items', 'all_items_page', __('All items', 'ba-book-everything'), '[all-items]');
        
    }
    
///////////////////////////////////////    
    /**
	 * Create FAQ posts
     * 
     * @return
	 */
    public static function setup_posts_faq(){
        
        $args = array(
           'post_type'   => BABE_Post_types::$faq_post_type,
           'numberposts' => -1,
           'post_status' => 'publish',
        );
             
        $posts = get_posts( $args );
        
        if ( empty($posts) ){
            
          $post_id = wp_insert_post(array (
            'post_type' => BABE_Post_types::$faq_post_type,
            'post_title' => __('Do you speak spanish?', 'ba-book-everything'),
            'post_content' => 'Lorem ipsum dolor sit amet, utinam munere antiopam vel ad. Qui eros iusto te. Nec ad feugiat honestatis. Quo illum detraxit an. Ius eius quodsi molestiae at, nostrum definitiones his cu. Discere referrentur mea id, an pri novum possim deterruisset. Eum oratio reprehendunt cu. Nec te quem assum postea.',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'post_author'   => 1,
            'menu_order' => 1,
          ) );
          
          $post_id = wp_insert_post(array (
            'post_type' => BABE_Post_types::$faq_post_type,
            'post_title' => __('Question 2?', 'ba-book-everything'),
            'post_content' => 'Lorem ipsum dolor sit amet, utinam munere antiopam vel ad. Qui eros iusto te. Nec ad feugiat honestatis. Quo illum detraxit an. Ius eius quodsi molestiae at, nostrum definitiones his cu. Discere referrentur mea id, an pri novum possim deterruisset. Eum oratio reprehendunt cu. Nec te quem assum postea.',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'post_author'   => 1,
            'menu_order' => 2,
          ) );
            
        }
        
        return;
        
    }
    
///////////////////////////////////////    
    /**
	 * Create Service posts
     * 
     * @return
	 */
    public static function setup_posts_services(){
        
        $args = array(
           'post_type'   => BABE_Post_types::$service_post_type,
           'numberposts' => -1,
           'post_status' => 'publish',
        );
             
        $posts = get_posts( $args );
        
        if ( empty($posts) ){
            
          $prices_per_booking = array(
            0 => (float)30,
          );
          
          $prices_per_person = array(
            0 => (float)20,
          );
            
          $ages = BABE_Post_types::get_ages_arr();
          $i = 1;
          foreach ($ages as $age_arr){
              $prices_per_booking[$age_arr['age_id']] = '';
              $prices_per_person[$age_arr['age_id']] = $i <= 2 ? floatval($prices_per_person[0] - $i*3) : '' ; 
              $i++;
          }
            
          $post_id = wp_insert_post(array (
            'post_type' => BABE_Post_types::$service_post_type,
            'post_title' => __('Service per booking', 'ba-book-everything'),
            'post_content' => 'Lorem ipsum dolor sit amet, utinam munere antiopam vel ad. Qui eros iusto te. Nec ad feugiat honestatis. Quo illum detraxit an. Ius eius quodsi molestiae at, nostrum definitiones his cu. Discere referrentur mea id, an pri novum possim deterruisset. Eum oratio reprehendunt cu. Nec te quem assum postea.',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'post_author'   => 1,
            'menu_order' => 1,
            'meta_input' => array(
              'price_type' => 'amount',
              'service_type' => 'booking',
              'prices' => $prices_per_booking,
            ),
          ) );
          
          if ( isset(self::$saved_demo_images['Service']) ){
              set_post_thumbnail( $post_id, self::$saved_demo_images['Service'] );
          }
          
          $post_id = wp_insert_post(array (
            'post_type' => BABE_Post_types::$service_post_type,
            'post_title' => __('Service per person', 'ba-book-everything'),
            'post_content' => 'Lorem ipsum dolor sit amet, utinam munere antiopam vel ad. Qui eros iusto te. Nec ad feugiat honestatis. Quo illum detraxit an. Ius eius quodsi molestiae at, nostrum definitiones his cu. Discere referrentur mea id, an pri novum possim deterruisset. Eum oratio reprehendunt cu. Nec te quem assum postea.',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'post_author'   => 1,
            'menu_order' => 2,
            'meta_input' => array(
              'price_type' => 'amount',
              'service_type' => 'person',
              'prices' => $prices_per_person,
            ),
          ) );
          
          if ( isset(self::$saved_demo_images['Service']) ){
              set_post_thumbnail( $post_id, self::$saved_demo_images['Service'] );
          }
            
        }
        
        return;
        
    }
    
///////////////////////////////////////    
    /**
	 * Create Place posts
     * 
     * @return
	 */
    public static function setup_posts_places(){
        
      if (BABE_Settings::$settings['mpoints_active']){  
        
        $args = array(
           'post_type'   => BABE_Post_types::$mpoints_post_type,
           'numberposts' => -1,
           'post_status' => 'publish',
        );
             
        $posts = get_posts( $args );
        
        if ( empty($posts) ){
            
          $address = array(
            'address' => __('Santorini, Greece', 'ba-book-everything'),
            'latitude' => '36.3931562',
            'longitude' => '25.461509200000023',
          );
          
          $post_id = wp_insert_post(array (
            'post_type' => BABE_Post_types::$mpoints_post_type,
            'post_title' => __('Place 1', 'ba-book-everything'),
            'post_content' => 'Lorem ipsum dolor sit amet, utinam munere antiopam vel ad. Qui eros iusto te. Nec ad feugiat honestatis. Quo illum detraxit an. Ius eius quodsi molestiae at, nostrum definitiones his cu. Discere referrentur mea id, an pri novum possim deterruisset. Eum oratio reprehendunt cu. Nec te quem assum postea.',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'post_author'   => 1,
            'menu_order' => 1,
            'meta_input' => array(
              'address' => $address,
            ),
          ) );
          
          if ( isset(self::$saved_demo_images['Place']) ){
              set_post_thumbnail( $post_id, self::$saved_demo_images['Place'] );
          }
          
          $post_id = wp_insert_post(array (
            'post_type' => BABE_Post_types::$mpoints_post_type,
            'post_title' => __('Place 2', 'ba-book-everything'),
            'post_content' => 'Lorem ipsum dolor sit amet, utinam munere antiopam vel ad. Qui eros iusto te. Nec ad feugiat honestatis. Quo illum detraxit an. Ius eius quodsi molestiae at, nostrum definitiones his cu. Discere referrentur mea id, an pri novum possim deterruisset. Eum oratio reprehendunt cu. Nec te quem assum postea.',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'post_author'   => 1,
            'menu_order' => 2,
            'meta_input' => array(
              'address' => $address,
            ),
          ) );
          
          if ( isset(self::$saved_demo_images['Place']) ){
              set_post_thumbnail( $post_id, self::$saved_demo_images['Place'] );
          }
            
        }
        
       } 
        
       return;
        
    }    
    
///////////////////////////////////////    
    /**
	 * Setup demo images.
     * 
     * @return
	 */
    public static function setup_images(){
        
       $demo_images = self::$demo_images;
       
       if (!empty(self::$saved_demo_images)){
          foreach (self::$saved_demo_images as $saved_demo_image_category => $saved_demo_image_id){
              $image_srcs = wp_get_attachment_image_src( $saved_demo_image_id, 'full');
              if ( $image_srcs && isset($demo_images[$saved_demo_image_category]) ){ /// if image exists remove line from $demo_images
                 unset($demo_images[$saved_demo_image_category]);
              }
          }        
       }
       
       if (!empty($demo_images)){
          foreach ($demo_images as $demo_image_category => $demo_image_name){

             /// upload and save new image
             $attachment_id = self::upload_local_image( BABE_PLUGIN_DIR . '/includes/demo/' . $demo_image_name );
             /// update $saved_demo_images
             self::$saved_demo_images[$demo_image_category] = $attachment_id;
          }
          
          //// update option
          update_option('_babe_demo_images', self::$saved_demo_images);
       }   
        
       return; 
    }

///////////////////////////////////////
    /**
     * Upload local image
     *
     * @return int - created attachment ID
     */
    public static function upload_local_image( $file_path, $parent_post_id = 0 ){

        $filename = basename($file_path);

        $upload_file = wp_upload_bits($filename, null, file_get_contents($file_path));

        if ( $upload_file['error'] ) {
            return 0;
        }

        $wp_filetype = wp_check_filetype($filename, null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_parent' => $parent_post_id,
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );

        if ( is_wp_error($attachment_id) ) {
            return 0;
        }

        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
        wp_update_attachment_metadata( $attachment_id,  $attachment_data );

        return $attachment_id;

    }

///////////////////////////////////////    
    /**
	 * Create Booking object posts
     * 
     * @return array - created post IDs
	 */
    public static function setup_posts_booking_objects(){
        
        $date_from_obj = new DateTime('-3 days');
        $date_to_obj = new DateTime('+1 year');
        
        $created_post_ids = array();
        
        $i = 0;
        
        foreach ( self::$demo_categories as $term_category_id => $category_title ){
            
          $category_term = get_term_by( 'id', $term_category_id, BABE_Post_types::$categories_tax );
          $category_meta = BABE_Post_types::get_term_meta($term_category_id);
          
          $rules = BABE_Booking_Rules::get_rule_by_cat_slug($category_term->slug);
          
          $image_id = 0;
          $image_url = '';
          
          if ( isset(self::$saved_demo_images[$category_title]) ){
             $image_id = self::$saved_demo_images[$category_title];
             $image_srcs = wp_get_attachment_image_src( $image_id, 'full');
              if ( $image_srcs ){
                 $image_url = $image_srcs[0];
              }
          }
          
          if ( $category_title == 'One time event'){
              $date_from_obj = new DateTime('+1 month');
              $date_to_obj = new DateTime('+1 month');
          }
          
          $meta_input = array(
              '_thumbnail_id' => $image_id,
              'guests' => 1,
              'images' => array (
                 0 => array (
                    'image_id' => $image_id,
                    'image' => $image_url,
                    'description' => __('Image description (optional)', 'ba-book-everything'),
                 ),
               ),
               'discount' => array (
                 'discount' => '',
                 'date_from' => '',
                 'date_to' => '',
               ),
               'start_date' => BABE_Calendar_functions::date_from_sql( $date_from_obj->format('Y-m-d') ),
               'end_date' => BABE_Calendar_functions::date_from_sql( $date_to_obj->format('Y-m-d') ),
               'steps_'.$category_term->slug => array(
                  0 => array (
                     'title' => 'Attraction 1',
                     'attraction' => 'Lorem ipsum dolor sit amet, utinam munere antiopam vel ad. Qui eros iusto te. Nec ad feugiat honestatis. Quo illum detraxit an. Ius eius quodsi molestiae at, nostrum definitiones his cu. Discere referrentur mea id, an pri novum possim deterruisset.',
                  ),
                  1 => array (
                     'title' => 'Attraction 2',
                     'attraction' => 'Lorem ipsum dolor sit amet, utinam munere antiopam vel ad. Qui eros iusto te. Nec ad feugiat honestatis. Quo illum detraxit an. Ius eius quodsi molestiae at, nostrum definitiones his cu. Discere referrentur mea id, an pri novum possim deterruisset.',
                  ),   
               ),
          );
           
          // faq_one-time-event, faq_tours
             $args = array(
               'post_type'   => BABE_Post_types::$faq_post_type,
               'numberposts' => -1,
               'post_status' => 'publish',
             );
             
             $faq_posts = get_posts( $args );
             
             if ( !empty($faq_posts) ){
                foreach($faq_posts as $faq_post){
                    $meta_input['faq_'.$category_term->slug][] = $faq_post->ID;                    
                }
             }
          
          // services_tours, services_one-time-event
             $args = array(
               'post_type'   => BABE_Post_types::$service_post_type,
               'numberposts' => -1,
               'post_status' => 'publish',
             );
             
             $service_posts = get_posts( $args );
             
             if ( !empty($service_posts) ){
                foreach($service_posts as $service_post){
                    $meta_input['services_'.$category_term->slug][] = $service_post->ID;
                }
             }
          
          // categories_taxonomies
          if ( !empty($category_meta['categories_taxonomies']) ){
              foreach($category_meta['categories_taxonomies'] as $taxonomy_id){
                
                $taxonomy_slug = BABE_Post_types::$taxonomies_list[$taxonomy_id]['slug'];
                
                $terms = get_terms( array(
                  'taxonomy' => $taxonomy_slug,
                  'hide_empty' => false,
                ) );
                
                if ( !empty($terms) ){
                    foreach( $terms as $term ) {
                        $meta_input[$taxonomy_slug.'_'.$category_term->slug][] = $term->term_id;
                    }
                }
                
             }     
          }
          
          //// schedule
          if ( $category_title == 'Tour' ){
            
             $meta_input['duration_'.$category_term->slug] = array (
                  'd' => '0',
                  'h' => '2',
                  'i' => '0',
             );
             
             $meta_input['schedule'] = array (
                 1 => array (
                    0 => '12:00',
                 ),
                 3 => array (
                    0 => '12:00',
                    1 => '17:00',
                 ),
                 4 => array (
                    0 => '10:00',
                    1 => '12:00',
                    2 => '17:00',
                 ),
                 5 => array (
                    0 => '10:00',
                    1 => '12:00',
                 ),
                 6 => array (
                    0 => '12:00',
                    1 => '17:00',
                 ),
             );
            
          }
          
          //// guests
          if ( $category_title == 'Hotel'){
             $meta_input['guests'] = 4;
          }
          
          if ( $category_title == 'Hostel'){
             $meta_input['guests'] = 12;
          }
          
          //// start and end time
          if ( $category_title == 'One time event'){
              $meta_input['start_time_'.$category_term->slug] = '11:00';
              $meta_input['end_time_'.$category_term->slug] = '19:00';
          }
          
          ///// address
          if ( ($category_title == 'Tour' || $category_title == 'One time event') && $category_meta['categories_address'] ){
       
              $meta_input['address_'.$category_term->slug] = array(
                'address' => __('Bryce Canyon National Park, USA', 'ba-book-everything'),
                'latitude' => '37.59303769999999',
                'longitude' => '-112.18708950000001',
              );
              
              $meta_input['guests'] = 100;
            
          }
          
          //// stop_booking_before_
          if ( $category_title == 'Tour' ){
             $meta_input['stop_booking_before_'.$category_term->slug] = 2;
          }
          
          /////// mpoints
          if ($category_title == 'Tour' && BABE_Settings::$settings['mpoints_active']){
            
             $args = array(
               'post_type'   => BABE_Post_types::$mpoints_post_type,
               'numberposts' => -1,
               'post_status' => 'publish',
             );
             
             $mpoints_posts = get_posts( $args );
             $mpoints_arr = array();
             
             if ( !empty($mpoints_posts) ){
                foreach($mpoints_posts as $mpoints_post){
                    $mpoints_arr[] = array(
                       'place' => $mpoints_post->ID,
                       'time_shift' => array (
                         'h' => '2',
                         'i' => '0',
                       ),
                    );                    
                }
                
                $meta_input['meeting_place_'.$category_term->slug] = 'point';
                $meta_input['meeting_points_'.$category_term->slug] = $mpoints_arr;
             }
            
          }
          
          $i++;
          //// create post
          $post_id = wp_insert_post(array (
            'post_type' => BABE_Post_types::$booking_obj_post_type,
            'post_title' => $category_title.' '.__('example', 'ba-book-everything'),
            'post_content' => 'Lorem ipsum dolor sit amet, utinam munere antiopam vel ad. Qui eros iusto te. Nec ad feugiat honestatis. Quo illum detraxit an. Ius eius quodsi molestiae at, nostrum definitiones his cu. Discere referrentur mea id, an pri novum possim deterruisset. Eum oratio reprehendunt cu. Nec te quem assum postea.',
            'post_status' => 'publish',
            'post_author'   => 1,
            'menu_order' => $i,
            'meta_input' => $meta_input,
          ) );
          
          $created_post_ids[$post_id] = $post_id;
          
          $category_id = $term_category_id<10 ? '0'.$term_category_id : $term_category_id;
          $generated_code = apply_filters('cmb2_booking_obj_generated_code', $category_id.'-'.$post_id, $category_id, $post_id);
          
          //// update meta based on post ID
          
          wp_set_object_terms( $post_id, array( $term_category_id ), BABE_Post_types::$categories_tax, false );
          if ( !empty($category_meta['categories_taxonomies']) ){
              foreach($category_meta['categories_taxonomies'] as $taxonomy_id){
                
                $taxonomy_slug = BABE_Post_types::$taxonomies_list[$taxonomy_id]['slug'];
                
                $terms = get_terms( array(
                  'taxonomy' => $taxonomy_slug,
                  'hide_empty' => false,
                ) );
                
                if ( !empty($terms) ){
                    foreach( $terms as $term ) {
                        wp_set_object_terms( $post_id, array( $term->term_id ), $taxonomy_slug, true );
                    }
                }
                
             }     
          }
                    
          update_post_meta($post_id, 'code_'.$category_term->slug, $generated_code);
          
          $price_arr = array( 0 => (float)49);

	        if ( $category_title == 'Bike' ){
		        $price_arr[0] = 10;
	        }
          
          if ( $rules['ages'] ){
            
            $ages = BABE_Post_types::get_ages_arr();
            $i = 1;
            foreach ($ages as $age_arr){
              $price_arr[$age_arr['age_id']] = $i <= 2 ? (float)($price_arr[0] - $i * 10) : (float)0;
              $i++;
            }
            
            unset($price_arr[0]);
            
          }
          
          $days_arr = BABE_Calendar_functions::get_week_days_arr();
          $rate_days_arr = array();
          foreach ($days_arr as $day_num => $day_title){
            $rate_days_arr[$day_num] = $day_num;            
          }
          
          //// create and save rates  
          $rate_arr = array(
             'post_id' => $post_id,
             'cat_slug' => $category_term->slug,
             'apply_days' => $rate_days_arr,
             'start_days' => $rate_days_arr,
             '_price_general' => $price_arr,
             '_price_from' => '',
             '_prices_conditional' => array(),
             '_rate_min_booking' => '',
             '_rate_max_booking' => '',
             '_rate_title' => __('General', 'ba-book-everything'),
             '_rate_date_from' => '',
             '_rate_date_to' => '',
          );
          
          BABE_Prices::save_rate($rate_arr);
          
          BABE_CMB2_admin::update_booking_obj_post($post_id, [], (object)array());
            
        }
        
        // related_items
        foreach ($created_post_ids as $created_post_id){
            $related_arr = $created_post_ids;
            if ( ($key = array_search($created_post_id, $related_arr)) !== false) {
                unset($related_arr[$key]);
            }
            $values = array_values($related_arr);
            
            update_post_meta($created_post_id, 'related_items', $values);
        }
        
        return $created_post_ids;
        
    }                                              
    
//////////////////////////////    
    
}

BABE_Install::init();
