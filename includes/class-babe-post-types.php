<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Post_types Class.
 * 
 * @class 		BABE_Post_types
 * @version		1.3.19
 * @author 		Booking Algorithms
 */

class BABE_Post_types {
    
    // variables to use
    static $booking_obj_post_type = 'to_book';
    static $faq_post_type = 'faq';
    static $service_post_type = 'service';
    static $order_post_type = 'order';
    static $mpoints_post_type = 'places';
    static $coupon_post_type = 'coupon';
    static $fee_post_type = 'fee';

    static $table_category_deactivate_schedule;
    
    public static $search_filter_sort_by_args = [];
    
    // service type names
    static $service_type_names = [];
    
    static $categories_tax = 'categories';
    static $ages_tax = 'ages';
    static $taxonomies_list;  // array of custom taxonomies names and slugs
    // will store in this taxonomy all new custom taxonomies for booking objects 
    static $taxonomies_list_tax = 'taxonomies_list';
    
    static $attr_tax_pref = 'ba_';
    
    static $get_posts_count = 0;
    static $get_posts_pages = 0;
    
    //// cache
    
    private static $ages_arr = [];
    
    private static $ages_arr_ordered_by_id = [];
    
    private static $categories_arr = [];
    
    private static $post = [];
    
    private static $post_discount = [];
    
    private static $post_meta = [];

    private static $post_price_from = [];

    private static $post_related = [];

    private static $post_services = [];

    private static $post_fees = [];

    private static $post_taxes = [];

    private static $posts = [];

    private static $posts_count = [];

    private static $posts_pages = [];
    
//////////////////////////////
    /**
	 * Hook in tabs
	 */
    public static function init() {

        global $wpdb;
        self::$table_category_deactivate_schedule = $wpdb->prefix.'babe_category_deactivate_schedule';

		add_action( 'init', array( __CLASS__, 'register_post_types' ), 0 );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 0 );
        add_filter('babe_sanitize_'.BABE_Settings::$option_name, array( __CLASS__, 'update_booking_obj_posts_slug'), 10);
        
        add_filter('babe_sanitize_'.BABE_Settings::$option_name, array( __CLASS__, 'update_mpoints'), 10);
        
        add_action( 'before_delete_post', array( __CLASS__, 'delete_booking_obj_post'));

        add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts_taxonomies'), 10, 1 );
        
        self::$service_type_names = self::get_service_type_names();
        
        self::$search_filter_sort_by_args = self::get_search_filter_sort_by_args();  
	}
    
////////////////////////
     /**
	 * Action on delete post
     * 
     * @param int $booking_obj_id
     * @return
	 */
     public static function delete_booking_obj_post($booking_obj_id){
        
        $post_type = get_post_type( $booking_obj_id );
        if( $post_type == BABE_Post_types::$booking_obj_post_type ) {
            
            BABE_Prices::delete_rates_by_booking_obj_id($booking_obj_id);
            BABE_Prices::delete_discounts_by_booking_obj_id($booking_obj_id);
            BABE_Calendar_functions::delete_av_cal_by_booking_obj_id($booking_obj_id);
        }
        
        return;
    }     
    
///////////////////////////////////////
    /**
	 * Get service type names.
     * @return array
	 */
    public static function get_service_type_names(){ 
    
    return array(
            'booking' => __( 'per Booking', 'ba-book-everything' ),
            'person' => __( 'per Person', 'ba-book-everything' ),
            'day' => __( 'per Day', 'ba-book-everything' ),
            'night' => __( 'per Night', 'ba-book-everything' ),
            'person_day' => __( 'per Person/day', 'ba-book-everything' ),
            'person_night' => __( 'per Person/night', 'ba-book-everything' ),
         );          
    }
    
///////////////////////////////////////
    /**
	 * Get sort by args for serach results filter.
     * @return array
	 */
    public static function get_search_filter_sort_by_args(){
    
    return apply_filters('babe_search_filter_sort_by_args', array(
              'title_asc' => __( 'Title A-Z', 'ba-book-everything' ),
              'title_desc' => __( 'Title Z-A', 'ba-book-everything' ),
              'price_asc' => __( 'Price from low', 'ba-book-everything' ),
              'price_desc' => __( 'Price from high', 'ba-book-everything' ),
              'rating_asc' => __( 'Rating from low', 'ba-book-everything' ),
              'rating_desc' => __( 'Rating from high', 'ba-book-everything' ),
              'avdatefrom_asc' => __( 'Availability date from nearest', 'ba-book-everything' ),
              'avdatefrom_desc' => __( 'Availability date from farthest', 'ba-book-everything' ),
            ));
    }
    
///////////////////////////////////////
    /**
	 * Translate search form args to get posts args.
     * @param array $args - search form args
     * @return array
	 */
    public static function search_filter_to_get_posts_args($args = array()){
    
       if (isset($args['search_results_sort_by'])){
          switch ($args['search_results_sort_by']){
	          case 'price_desc':
		          $args['sort']    = 'price_from';
		          $args['sort_by'] = 'DESC';
		          break;
	          case 'price_asc':
		          $args['sort']    = 'price_from';
		          $args['sort_by'] = 'ASC';
		          break;
	          case 'title_desc':
		          $args['sort']    = 'post_title';
		          $args['sort_by'] = 'DESC';
		          break;
	          case 'title_asc':
		          $args['sort']    = 'post_title';
		          $args['sort_by'] = 'ASC';
		          break;
	          case 'rating_desc':
		          $args['sort']    = 'rating';
		          $args['sort_by'] = 'DESC';
		          break;
	          case 'rating_asc':
		          $args['sort']    = 'rating';
		          $args['sort_by'] = 'ASC';
		          break;
              case 'avdatefrom_asc':
                  $args['sort']    = 'av_date_from';
                  $args['sort_by'] = 'ASC';
                  break;
              case 'avdatefrom_desc':
                  $args['sort']    = 'av_date_from';
                  $args['sort_by'] = 'DESC';
                  break;

	          default:
		          $args['sort']    = 'post_title';
		          $args['sort_by'] = 'ASC';
		          break;
          }
        }
            
        return apply_filters('babe_search_filter_to_get_posts_args', $args);
    }

//////////////////////////////
    /**
     * Get all BABE post types.
     *
     * @return array
     */
    public static function get_babe_post_types(){

        $post_types = [
            self::$booking_obj_post_type,
            self::$coupon_post_type,
            self::$faq_post_type,
            self::$fee_post_type,
            self::$mpoints_post_type,
            self::$order_post_type,
            self::$service_post_type,
        ];

        return apply_filters('babe_get_all_post_types', $post_types);
    }

//////////////////////////////
    /**
	 * Register post types.
	 */
    public static function register_post_types() {
        
    $labels = array(
		'name'               => BABE_Settings::$settings['booking_obj_post_name_general'],
		'singular_name'      => BABE_Settings::$settings['booking_obj_post_name'],
		'menu_name'          => BABE_Settings::$settings['booking_obj_menu_name'],
		'name_admin_bar'     => BABE_Settings::$settings['booking_obj_post_name'],
		'add_new'            => __( 'Add ', 'ba-book-everything' ).BABE_Settings::$settings['booking_obj_post_name'],
		'add_new_item'       => __( 'Add New ', 'ba-book-everything' ).BABE_Settings::$settings['booking_obj_post_name'],
		'new_item'           => __( 'New ', 'ba-book-everything' ).BABE_Settings::$settings['booking_obj_post_name'],
		'edit_item'          => __( 'Edit ', 'ba-book-everything' ).BABE_Settings::$settings['booking_obj_post_name'],
		'view_item'          => __( 'View ', 'ba-book-everything' ).BABE_Settings::$settings['booking_obj_post_name'],
		'all_items'          => __( 'All ', 'ba-book-everything' ).BABE_Settings::$settings['booking_obj_post_name_general'],
		'search_items'       => __( 'Search ', 'ba-book-everything' ).BABE_Settings::$settings['booking_obj_post_name'],
		'parent_item_colon'  => __( 'Parent ', 'ba-book-everything' ).BABE_Settings::$settings['booking_obj_post_name'].':',
		'not_found'          => sprintf(__( 'No %s found.', 'ba-book-everything' ), BABE_Settings::$settings['booking_obj_post_name_general']),
		'not_found_in_trash' => sprintf(__( 'No %s found in Trash.', 'ba-book-everything' ), BABE_Settings::$settings['booking_obj_post_name_general'])
	);    

// Set other options for Custom Post Type

	$args = array(
		'description'         => BABE_Settings::$settings['booking_obj_post_name_general'],
		'labels'              => $labels,
		// Features this CPT supports
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt', 'comments', 'author' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
        'menu_position'        => 33,
        'menu_icon'           => 'dashicons-clipboard',
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
        'rewrite' => array(
                'slug' => BABE_Settings::$settings['booking_obj_post_slug']
            ),
	);
    
    if (BABE_Settings::$settings['booking_obj_gutenberg']){
        $args['show_in_rest'] = true;
    }

        $args = array_merge( $args, self::get_post_capabilities(self::$booking_obj_post_type) );

	// Registering our Post Type
	register_post_type( self::$booking_obj_post_type, $args );

    $labels = array(
		'name'                => _x( 'FAQ', 'Post Type General Name', 'ba-book-everything' ),
		'singular_name'       => _x( 'FAQ', 'Post Type Singular Name', 'ba-book-everything' ),
        'menu_name'          => _x( 'FAQ', 'admin menu', 'ba-book-everything' ),
		'name_admin_bar'     => _x( 'FAQ', 'add new on admin bar', 'ba-book-everything' ),
		'add_new'            => _x( 'Add FAQ', 'booking object', 'ba-book-everything' ),
        'add_new_item'        => __( 'Add New FAQ', 'ba-book-everything' ),
        'new_item'           => __( 'New FAQ', 'ba-book-everything' ),
		'edit_item'          => __( 'Edit FAQ', 'ba-book-everything' ),
		'view_item'          => __( 'View FAQ', 'ba-book-everything' ),
		'all_items'          => __( 'All FAQs', 'ba-book-everything' ),
		'search_items'       => __( 'Search FAQ', 'ba-book-everything' ),
		'parent_item_colon'  => __( 'Parent FAQ:', 'ba-book-everything' ),
		'not_found'          => __( 'Not found.', 'ba-book-everything' ),
		'not_found_in_trash' => __( 'Not found in Trash.', 'ba-book-everything' ),
		'update_item'         => __( 'Update FAQ', 'ba-book-everything' ),
	);

// Set other options for Custom Post Type

	$args = array(
		'description'         => __( 'FAQ', 'ba-book-everything' ),
		'labels'              => $labels,
		// Features this CPT supports
		'supports'            => array( 'title', 'editor', 'page-attributes', 'author' ),
		'hierarchical'        => true,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
        'menu_position'        => 34,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
	);

        $args = array_merge( $args, self::get_post_capabilities(self::$faq_post_type) );

	// Registering your Custom Post Type
	register_post_type( self::$faq_post_type, $args );
    
    $labels = array(
		'name'                => _x( 'Services', 'Post Type General Name', 'ba-book-everything' ),
		'singular_name'       => _x( 'Service', 'Post Type Singular Name', 'ba-book-everything' ),
        'menu_name'          => _x( 'Services', 'admin menu', 'ba-book-everything' ),
		'name_admin_bar'     => _x( 'Services', 'add new on admin bar', 'ba-book-everything' ),
		'add_new'            => _x( 'Add Service', 'booking object', 'ba-book-everything' ),
        'add_new_item'        => __( 'Add New Service', 'ba-book-everything' ),
        'new_item'           => __( 'New Service', 'ba-book-everything' ),
		'edit_item'          => __( 'Edit Service', 'ba-book-everything' ),
		'view_item'          => __( 'View Service', 'ba-book-everything' ),
		'all_items'          => __( 'All Services', 'ba-book-everything' ),
		'search_items'       => __( 'Search Service', 'ba-book-everything' ),
		'parent_item_colon'  => __( 'Parent Service:', 'ba-book-everything' ),
		'not_found'          => __( 'Not found.', 'ba-book-everything' ),
		'not_found_in_trash' => __( 'Not found in Trash.', 'ba-book-everything' ),
		'update_item'         => __( 'Update Service', 'ba-book-everything' ),
	);

// Set other options for Custom Post Type

	$args = array(
		'description'         => __( 'Services', 'ba-book-everything' ),
		'labels'              => $labels,
		// Features this CPT supports
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'author' ),
		'hierarchical'        => true,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
        'menu_position'        => 35,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
        'rewrite' => array(
                'slug' => 'services',
            ),
        'show_in_rest' => true,
	);

        $args = array_merge( $args, self::get_post_capabilities(self::$service_post_type) );

	// Registering your Custom Post Type
	register_post_type( self::$service_post_type, $args );
    
    $labels = array(
		'name'                => _x( 'Orders', 'Post Type General Name', 'ba-book-everything' ),
		'singular_name'       => _x( 'Order', 'Post Type Singular Name', 'ba-book-everything' ),
        'menu_name'          => _x( 'Orders', 'admin menu', 'ba-book-everything' ),
		'name_admin_bar'     => _x( 'Orders', 'add new on admin bar', 'ba-book-everything' ),
		'add_new'            => _x( 'Add Order', 'booking object', 'ba-book-everything' ),
        'add_new_item'        => __( 'Add New Order', 'ba-book-everything' ),
        'new_item'           => __( 'New Order', 'ba-book-everything' ),
		'edit_item'          => __( 'Edit Order', 'ba-book-everything' ),
		'view_item'          => __( 'View Order', 'ba-book-everything' ),
		'all_items'          => __( 'All Orders', 'ba-book-everything' ),
		'search_items'       => __( 'Search Order', 'ba-book-everything' ),
		'parent_item_colon'  => __( 'Parent Order:', 'ba-book-everything' ),
		'not_found'          => __( 'Not found.', 'ba-book-everything' ),
		'not_found_in_trash' => __( 'Not found in Trash.', 'ba-book-everything' ),
		'update_item'         => __( 'Update Order', 'ba-book-everything' ),
	);

// Set other options for Custom Post Type

	$args = array(
		'description'         => __( 'Order', 'ba-book-everything' ),
		'labels'              => $labels,
		// Features this CPT supports
		'supports'            => array( 'love', 'author' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
        'menu_position'        => 35,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'post',
	);

        $args = array_merge( $args, self::get_post_capabilities(self::$order_post_type) );
    
	register_post_type( self::$order_post_type, $args );
    ////////////////////////

        $labels = array(
            'name'                => _x( 'Fee', 'Post Type General Name', 'ba-book-everything' ),
            'singular_name'       => _x( 'Fee', 'Post Type Singular Name', 'ba-book-everything' ),
            'menu_name'          => _x( 'Fees', 'admin menu', 'ba-book-everything' ),
            'name_admin_bar'     => _x( 'Fee', 'add new on admin bar', 'ba-book-everything' ),
            'add_new'            => _x( 'Add Fee', 'booking object', 'ba-book-everything' ),
            'add_new_item'        => __( 'Add New Fee', 'ba-book-everything' ),
            'new_item'           => __( 'New Fee', 'ba-book-everything' ),
            'edit_item'          => __( 'Edit Fee', 'ba-book-everything' ),
            'view_item'          => __( 'View Fee', 'ba-book-everything' ),
            'all_items'          => __( 'All Fees', 'ba-book-everything' ),
            'search_items'       => __( 'Search Fee', 'ba-book-everything' ),
            'parent_item_colon'  => __( 'Parent Fee:', 'ba-book-everything' ),
            'not_found'          => __( 'Not found.', 'ba-book-everything' ),
            'not_found_in_trash' => __( 'Not found in Trash.', 'ba-book-everything' ),
            'update_item'         => __( 'Update Fee', 'ba-book-everything' ),
        );

// Set other options for Custom Post Type

        $args = array(
            'description'         => __( 'Fee', 'ba-book-everything' ),
            'labels'              => $labels,
            // Features this CPT supports
            'supports'            => array( 'title', 'editor', 'page-attributes', 'author' ),
            'hierarchical'        => true,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'        => 34,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => true,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
            'show_in_rest' => false,
        );

        $args = array_merge( $args, self::get_post_capabilities(self::$fee_post_type) );

        // Registering your Custom Post Type
        register_post_type( self::$fee_post_type, $args );

        //////////////

    if (BABE_Coupons::coupons_active()){
    
    $labels = array(
		'name'                => _x( 'Coupons', 'Post Type General Name', 'ba-book-everything' ),
		'singular_name'       => _x( 'Coupon', 'Post Type Singular Name', 'ba-book-everything' ),
        'menu_name'          => _x( 'Coupons', 'admin menu', 'ba-book-everything' ),
		'name_admin_bar'     => _x( 'Coupon', 'add new on admin bar', 'ba-book-everything' ),
		'add_new'            => _x( 'Add Coupon', 'booking object', 'ba-book-everything' ),
        'add_new_item'        => __( 'Add New Coupon', 'ba-book-everything' ),
        'new_item'           => __( 'New Coupon', 'ba-book-everything' ),
		'edit_item'          => __( 'Edit Coupon', 'ba-book-everything' ),
		'view_item'          => __( 'View Coupon', 'ba-book-everything' ),
		'all_items'          => __( 'All Coupons', 'ba-book-everything' ),
		'search_items'       => __( 'Search Coupon', 'ba-book-everything' ),
		'parent_item_colon'  => __( 'Parent Coupon:', 'ba-book-everything' ),
		'not_found'          => __( 'Not found.', 'ba-book-everything' ),
		'not_found_in_trash' => __( 'Not found in Trash.', 'ba-book-everything' ),
		'update_item'         => __( 'Update Coupon', 'ba-book-everything' ),
	);

// Set other options for Custom Post Type

	$args = array(
		'description'         => __( 'Coupon', 'ba-book-everything' ),
		'labels'              => $labels,
		// Features this CPT supports
		'supports'            => array( 'love', 'author' ),
		'hierarchical'        => true,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
        'menu_position'        => 34,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'post',
	);

        $args = array_merge( $args, self::get_post_capabilities(self::$coupon_post_type) );

	// Registering your Custom Post Type
	register_post_type( self::$coupon_post_type, $args );
    
    }
    
    ////////////////////////////
    
    if (BABE_Settings::$settings['mpoints_active']){
        
        $labels = array(
		'name'                => _x( 'Places', 'Post Type General Name', 'ba-book-everything' ),
		'singular_name'       => _x( 'Place', 'Post Type Singular Name', 'ba-book-everything' ),
        'menu_name'          => _x( 'Places', 'admin menu', 'ba-book-everything' ),
		'name_admin_bar'     => _x( 'Places', 'add new on admin bar', 'ba-book-everything' ),
		'add_new'            => _x( 'Add Place', 'booking object', 'ba-book-everything' ),
        'add_new_item'        => __( 'Add New Place', 'ba-book-everything' ),
        'new_item'           => __( 'New Place', 'ba-book-everything' ),
		'edit_item'          => __( 'Edit Place', 'ba-book-everything' ),
		'view_item'          => __( 'View Place', 'ba-book-everything' ),
		'all_items'          => __( 'All Places', 'ba-book-everything' ),
		'search_items'       => __( 'Search Place', 'ba-book-everything' ),
		'parent_item_colon'  => __( 'Parent Place:', 'ba-book-everything' ),
		'not_found'          => __( 'Not found.', 'ba-book-everything' ),
		'not_found_in_trash' => __( 'Not found in Trash.', 'ba-book-everything' ),
		'update_item'         => __( 'Update Place', 'ba-book-everything' ),
	);

// Set other options for Custom Post Type

	$args = array(
		'description'         => __( 'Place', 'ba-book-everything' ),
		'labels'              => $labels,
		// Features this CPT supports
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'author' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
        'menu_position'        => 35,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
        'show_in_rest' => true,
	);

        $args = array_merge( $args, self::get_post_capabilities(self::$mpoints_post_type) );

	// Registering your Custom Post Type
	register_post_type( self::$mpoints_post_type, $args );
        
    } ////////////////
    
    if (get_option('_babe_posts_need_rewrite_rules')){
        update_option('_babe_posts_need_rewrite_rules', 0);
        flush_rewrite_rules();
    }
    
    }

    /////////////////////////////
    /**
     * Get post capabilities
     *
     * @param string $post_type
     * @return array
     */
    public static function get_post_capabilities($post_type){

        $typeSingle = $post_type;
        $typePlural = $post_type.'s';

        $caps = [
            'capabilities'  => [
                // Mapping capabilities for CPT
                'edit_post'               => 'edit_'. $typeSingle,
                'read_post'               => 'read_'. $typeSingle,
                'delete_post'             => 'delete_'. $typeSingle,
                'edit_posts'              => 'edit_'. $typePlural,
                'edit_others_posts'       => 'edit_others_'. $typePlural,
                'publish_posts'           => 'publish_'. $typePlural,
                'read_private_posts'      => 'read_private_'. $typePlural,
                'delete_posts'            => 'delete_'. $typePlural,
                'delete_private_posts'    => 'delete_private_'. $typePlural,
                'delete_published_posts'  => 'delete_published_'. $typePlural,
                'delete_others_posts'     => 'delete_others_'. $typePlural,
                'edit_private_posts'      => 'edit_private_'. $typePlural,
                'edit_published_posts'    => 'edit_published_'. $typePlural,
            ],
            'map_meta_cap'       => 'true'
        ];

        return $caps;
    }

/////////////////////////////
     /**
	 * Change booking_obj post slug for all posts in DB.
     * @param array $new_input
     * @return array
	 */ 
     public static function update_booking_obj_posts_slug($new_input){ 
         if (BABE_Settings::$settings['booking_obj_post_slug'] != $new_input['booking_obj_post_slug']){
            update_option('_babe_posts_need_rewrite_rules', 1);
         }   
        
        return $new_input;
    }
    
/////////////////////////////
     /**
	 * Change booking_obj post slug for all posts in DB.
     * @param array $new_input
     * @return array
	 */ 
     public static function update_mpoints($new_input){ 
         if (BABE_Settings::$settings['mpoints_active'] != $new_input['mpoints_active']){
            update_option('_babe_posts_need_rewrite_rules', 1);
         }   
        
        return $new_input;
    }        
    
//////////////////////////////
    /**
	 * Register taxonomies.
	 */
    public static function register_taxonomies() {

        $current_lang = BABE_Functions::get_current_language();

        $default_lang = BABE_Functions::get_default_language();
        
       //// categories 
        
       $labels = array(
			'name'              => __( 'Booking Categories', 'ba-book-everything' ),
			'singular_name'     => __( 'Category', 'ba-book-everything' ),
			'search_items'      => __( 'Search Categories', 'ba-book-everything' ),
			'all_items'         => __( 'All Categories', 'ba-book-everything' ),
			'parent_item'       => __( 'Parent Category', 'ba-book-everything' ),
			'parent_item_colon' => __( 'Parent Category:', 'ba-book-everything' ),
			'edit_item'         => __( 'Edit Category', 'ba-book-everything' ),
			'update_itm'        => __( 'Update Category', 'ba-book-everything' ),
			'add_new_item'      => __( 'Add New Category', 'ba-book-everything' ),
			'new_item_name'     => __( 'New Category', 'ba-book-everything' ),
			'menu_name'         => __( 'Booking Categories', 'ba-book-everything' ),
		);

		register_taxonomy( self::$categories_tax, self::$booking_obj_post_type, array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'query_var'         => self::$categories_tax,
			'public'            => true,
			'show_ui'           => true,
            'show_in_nav_menus'   => true,
			'show_admin_column' => true,
		) );
        
        //// list_taxonomy
        
        $labels = array(
			'name'              => __( 'Taxonomies', 'ba-book-everything' ),
			'singular_name'     => __( 'Taxonomy', 'ba-book-everything' ),
			'search_items'      => __( 'Search Taxonomies', 'ba-book-everything' ),
			'all_items'         => __( 'All Taxonomies', 'ba-book-everything' ),
			'parent_item'       => __( 'Parent Taxonomy', 'ba-book-everything' ),
			'parent_item_colon' => __( 'Parent Taxonomy:', 'ba-book-everything' ),
			'edit_item'         => __( 'Edit Taxonomy', 'ba-book-everything' ),
			'update_itm'        => __( 'Update Taxonomy', 'ba-book-everything' ),
			'add_new_item'      => __( 'Add New Taxonomy', 'ba-book-everything' ),
			'new_item_name'     => __( 'New Taxonomy', 'ba-book-everything' ),
			'menu_name'         => __( 'Taxonomies', 'ba-book-everything' ),
		);

		register_taxonomy( self::$taxonomies_list_tax, self::$booking_obj_post_type, array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'query_var'         => self::$taxonomies_list_tax,
			'public'            => true,
			'show_ui'           => true,
            'show_in_nav_menus'   => true,
			'show_admin_column' => false,
		) );
        
        //// ages 
        
       $labels = array(
			'name'              => __( 'Ages', 'ba-book-everything' ),
			'singular_name'     => __( 'Age', 'ba-book-everything' ),
			'search_items'      => __( 'Search Ages', 'ba-book-everything' ),
			'all_items'         => __( 'All Ages', 'ba-book-everything' ),
			'parent_item'       => __( 'Parent Age', 'ba-book-everything' ),
			'parent_item_colon' => __( 'Parent Age:', 'ba-book-everything' ),
			'edit_item'         => __( 'Edit Age', 'ba-book-everything' ),
			'update_itm'        => __( 'Update Age', 'ba-book-everything' ),
			'add_new_item'      => __( 'Add New Age', 'ba-book-everything' ),
			'new_item_name'     => __( 'New Age', 'ba-book-everything' ),
			'menu_name'         => __( 'Ages', 'ba-book-everything' ),
		);

		register_taxonomy( self::$ages_tax, self::$booking_obj_post_type, array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'query_var'         => self::$ages_tax,
			'public'            => true,
			'show_ui'           => true,
            'show_in_nav_menus'   => false,
			'show_admin_column' => false,
		) );

        if ( BABE_Functions::is_wpml_active() && $current_lang !== $default_lang ){
            // get/create terms in current language
            $terms = get_terms( array(
                'taxonomy' => self::$categories_tax,
                'hide_empty' => false
            ) );
            $current_lang = apply_filters( 'wpml_current_language', $current_lang );
            $term_ids = BABE_Functions::wpml_get_translated_term_ids( $terms, $current_lang, self::$categories_tax, true );
            $terms = get_terms( array(
                'taxonomy' => self::$ages_tax,
                'hide_empty' => false
            ) );
            $term_ids = BABE_Functions::wpml_get_translated_term_ids( $terms, $current_lang, self::$ages_tax, true );
        }

        self::$attr_tax_pref = BABE_Settings::get_option('attr_tax_prefix', self::$attr_tax_pref);

        self::init_taxonomies_list();
         
    }

//////////////////////////////

    /**
     * Register custom taxonomy
     * @param string $new_tax_slug
     * @param string $new_tax_name
     */
    public static function register_custom_taxonomy( $new_tax_slug, $new_tax_name ) {

        // create new taxonomy
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

        register_taxonomy( $new_tax_slug, self::$booking_obj_post_type, array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'query_var'         => $new_tax_slug,
            'public'            => true,
            'show_ui'           => true,
            'show_in_nav_menus'   => true,
            'show_admin_column' => false,
            'show_in_menu' => true,
            'show_in_rest' => true,
        ) );
    }
       
//////////////////////////////
    /**
	 * Init taxonomies list array.
	 */
    static function init_taxonomies_list() {

        $current_lang = BABE_Functions::get_current_language();
        $current_lang = apply_filters( 'wpml_current_language', $current_lang );

        $default_lang = BABE_Functions::get_default_language();

        if ( BABE_Functions::is_wpml_active() && $current_lang !== $default_lang ){
            do_action( 'wpml_switch_language', $default_lang );
        }

        $taxonomies = get_terms( array(
          'taxonomy' => self::$taxonomies_list_tax,
          'hide_empty' => false
        ) );

        if ( BABE_Functions::is_wpml_active() && $current_lang !== $default_lang ){
            do_action( 'wpml_switch_language', $current_lang );
        }

        if ( empty($taxonomies) ){
            self::$taxonomies_list = [];
            return;
        }

        $taxonomy_ids = [];

        if ( BABE_Functions::is_wpml_active() && $current_lang !== $default_lang ){
            // get/create the terms in current language
            $taxonomy_ids = BABE_Functions::wpml_get_translated_term_ids( $taxonomies, $current_lang, self::$taxonomies_list_tax, true );
        }

        $sync_options = [];

        foreach( $taxonomies as $ind => $tax_term ) {

            $subtaxonomy_default = self::$attr_tax_pref.$tax_term->slug;

            $term_id = $tax_term->term_id;
            $term_name = $tax_term->name;

            if ( !empty($taxonomy_ids[$ind]) ){
                $term_id = $taxonomy_ids[$ind];
                $translated_term = get_term_by('id', $term_id, self::$taxonomies_list_tax);
                $term_name = $translated_term->name;
            }

            self::register_custom_taxonomy( $subtaxonomy_default, apply_filters('translate_text', $term_name) );

            // get/create the terms in current language
            $subterms = get_terms( array(
                'taxonomy' => $subtaxonomy_default,
                'hide_empty' => false
            ) );
            $subtaxonomy_ids = BABE_Functions::wpml_get_translated_term_ids( $subterms, $current_lang, $subtaxonomy_default, true );

            self::$taxonomies_list[$tax_term->term_id] = array(
                'name' => apply_filters('translate_text', $term_name),
                'slug' => $subtaxonomy_default,
                'term_slug' => $tax_term->slug,
                'term_id' => $tax_term->term_id,
            );

            $term_vals = get_term_meta($term_id);
            foreach($term_vals as $key=>$val){
                self::$taxonomies_list[$tax_term->term_id][$key] = $val[0];
            }

            $sync_options[$subtaxonomy_default] = 2;
        }

        if ( BABE_Functions::is_wpml_active() ){
            /** @var WPML_Settings_Helper $settings_helper */
            $settings_helper = wpml_load_settings_helper();
            $settings_helper->update_taxonomy_sync_settings( $sync_options );
        }
    }

////////////////////////////
    /**
	 * Get taxonomy meta by custom taxonomy slug.
     * @param $slug - Custom taxonomy slug
     * @return array
	 */
    static function get_taxonomy_meta_by_slug($slug) {
          $term_id = 0;
          foreach( self::$taxonomies_list as $id => $term_arr ){
              if ($term_arr['slug'] == $slug){
                 $term_id = $id;
                 break;
              }
          }
          $output = $term_id ? self::get_term_meta($term_id) : array();
          return $output;    
    }

////////////////////////////
    /**
     * Get custom taxonomies
     *
     * @return array = array(
     *  term_id => array(
    'name' => {custom_taxonomy_translated_name},
    'slug' => {custom_taxonomy_slug},
    'term_slug' => {taxonomies_list_tax_term_slug},
    'term_id' => {taxonomies_list_tax_term_id},
    ) )
     */
    public static function get_custom_taxonomies(){

        return self::$taxonomies_list;
    }

////////////////////////////
    /**
	 * Get categories array.
     * @return array
	 */
    public static function get_categories_arr() {
        $output = array();
        
        if (!empty(self::$categories_arr)){
            return self::$categories_arr;  
        }
        
        $taxonomies = get_terms( array(
          'taxonomy' => self::$categories_tax,
          'hide_empty' => false
        ) );
        
        if ( !empty($taxonomies) ){
            foreach( $taxonomies as $tax_term ) {
               $output[$tax_term->term_id] = apply_filters('translate_text', $tax_term->name);  
            }
        }
        
        self::$categories_arr = $output;
        
        return $output;       
    }

    public static function get_category_exclude_dates( int $category_id ): array
    {
        global $wpdb;

        $query = "SELECT * 
        FROM ".self::$table_category_deactivate_schedule."
        WHERE category_id = ".$category_id;

        return $wpdb->get_results($query, ARRAY_A);
    }

    public static function add_category_exclude_dates( int $category_id, DateTime $date_from, DateTime $date_to): void
    {
        global $wpdb;

        $wpdb->insert(
            self::$table_category_deactivate_schedule,
            array(
                'category_id' => $category_id,
                'deactivate_date_from' => $date_from->format('Y-m-d H:i:s'),
                'deactivate_date_to' => $date_to->format('Y-m-d H:i:s'),
            )
        );
    }

    public static function delete_category_exclude_dates( int $category_deactivate_schedule_id ): void
    {
        global $wpdb;

        $wpdb->query( $wpdb->prepare( 'DELETE FROM '.self::$table_category_deactivate_schedule.' WHERE id = %d', $category_deactivate_schedule_id) );
    }

////////////////////////////
    /**
	 * Get ages array.
     * @return array
	 */
    public static function get_ages_arr() {
        $output = array();
        
        if (!empty(self::$ages_arr)){
            return self::$ages_arr;
        }
        
        $ages = get_terms( array(
          'taxonomy' => self::$ages_tax,
          'hide_empty' => false,
          'order' => 'ASC',
          'orderby' => 'meta_value_num',
          'meta_key' => 'menu_order',
          'suppress_filters' => false,
        ) );
        
        if ( !empty($ages) ){            
            foreach( $ages as $age_term ) {
                $output[] = array(
                   'age_id' => (int)$age_term->term_id,
                   'name' => apply_filters('translate_text', $age_term->name),
                   'slug' => $age_term->slug,
                   'description' => apply_filters('translate_text', $age_term->description),
                   'menu_order' => (int)get_term_meta( (int)$age_term->term_id, 'menu_order', 1 ),
                ); 
            }
        }
        
        self::$ages_arr = $output;
        
        return $output;       
    }
    
////////////////////////////
    /**
	 * Get ages array ordered by id
     * @return array
	 */
    public static function get_ages_arr_ordered_by_id() {
        $output = array();
        
        if (!empty(self::$ages_arr_ordered_by_id)){
            return self::$ages_arr_ordered_by_id;  
        }
        
        $ages = self::get_ages_arr();
        
        if ( !empty($ages) ){            
            foreach( $ages as $age_term ) {
                $output[$age_term['age_id']] = $age_term; 
            }
        }
        
        ksort($output, SORT_NUMERIC);
                
        self::$ages_arr_ordered_by_id = $output;
        
        return $output;       
    }    
    
////////////////////////////
    /**
	 * Clear cached ages
     * 
     * @return
	 */
    public static function clear_cache_ages() {
        
        self::$ages_arr = array();
        self::$ages_arr_ordered_by_id = array();
        
        return;       
    }    
    
////////////////////////////
    /**
	 * Get ages array.
     * @return array
	 */
    public static function get_post_ages($booking_obj_id) {
        
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($booking_obj_id);
        
        return !$rules_cat['rules']['ages'] ? array() : self::get_ages_arr();
    }        
    
////////////////////////////
    /**
	 * Get main age id by rule.
     * @param array $rule
     * @return int
	 */
    public static function get_main_age_id($rule = array()) {
        $output = 0;

         if ( (isset($rule['ages']) && $rule['ages']) || empty($rule) ){
              $ages = self::get_ages_arr();
              if (!empty($ages)){
                 $output = $ages[0]['age_id'];
              }
         }          
        
        return $output;       
    }    
        
////////////////////////////
    /**
	 * Get category meta.
     * @param $term_id int
     * @return array
	 */
    public static function get_term_meta($term_id) {

        $output = [];
        $term_vals = get_term_meta($term_id);

        foreach($term_vals as $key=>$val){
            $output[$key] = maybe_unserialize($val[0]);
        }
        
        return $output;       
    }
////////////////////////////
    /**
	 * Check if the taxonomy is from core taxonomies.
     * @param $slug string - taxonomy slug
     * @return boolean
	 */
    public static function is_core_taxonomy($slug) {
        
        return $slug == self::$categories_tax || $slug == self::$taxonomies_list_tax || $slug == self::$ages_tax;       
    
    }
    
////////////////////////////

    /**
     * Get posts min or max price from based on search request
     *
     * @param array $search_args
     * @param string $flag
     * @return float
     */
    public static function get_posts_range_price($search_args = array(), $flag = 'max') {

        $args = array();

        if ( isset( $search_args['guests'] ) && is_array($search_args['guests']) ){
            $guests = array_map('absint', $search_args['guests']);
            $args['guests'] = array_sum($guests);
        }

        // sanitize args
        foreach ($search_args as $arg_key => $arg_value){
            $args[sanitize_title($arg_key)] = is_array($arg_value) ? array_map('absint', $arg_value) : sanitize_text_field($arg_value);
        }

        $args['paged'] = 1;
        $args['posts_per_page'] = 1;
        $args['sort'] = 'price_from';
        $args['sort_by'] = $flag === 'max' ? 'DESC' : 'ASC';
        $args['return_total_count'] = 0;

        unset($args['min_price']);
        unset($args['max_price']);
        
        $posts = self::get_posts($args);
        // prices already localizied

        return !empty($posts[0]['discount_price_from']) ? $posts[0]['discount_price_from'] : 0;

    }

///////////////////////////////
    /**
     * Filter post args for front taxonomy queries
     *
     * @param WP_Query $query
     */
    public static function pre_get_posts_taxonomies($query){

        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        foreach( BABE_Post_types::$taxonomies_list as $taxonomy){
            if ( is_tax( $taxonomy['slug'] ) ) {
                $query->set( 'posts_per_page', BABE_Settings::$settings['posts_per_taxonomy_page'] );
                $query->set( 'post_type', BABE_Post_types::$booking_obj_post_type );
                return;
            }
        }
    }

////////////////////////////
    /**
	 * Get posts - main search query.
     * @param array $args_array
     * @return array
	 */
    public static function get_posts($args_array = []) {
        global $wpdb;
        
        $args = wp_parse_args( $args_array, array(
            'date_from' => '', //// d/m/Y or m/d/Y format
            'date_to' => '',
            'time_from' => '00:00',
            'time_to' => '',
            'categories' => [], //// term_taxonomy_ids from booking categories
            'terms' => [], //// term_taxonomy_ids from custom taxonomies in $taxonomies_list
            'posts_per_page' => BABE_Settings::$settings['results_per_page'],
            'guests' => 1,
            'sort' => 'price_from', /// price_from, rating, post_title, av_date_from
            'sort_by' => 'ASC',
            'min_price' => '',
            'max_price' => '',
            'post__in' => [],
            'keyword' => '',
            'return_total_count' => 1,
            'paged' => (get_query_var('paged') ? absint(get_query_var('paged')) : 1),
            'post_author' => 0,
            'without_av_check' => 0,
            'group_results_by_date' => 0,
            'not_scheduled' => 0,
            'without_av_cal' => BABE_Settings::$settings['results_without_av_cal'],
        ));

        ////// try to found in cache

        // create hash of the query args
        $args_hash = hash('sha256', json_encode($args, 512));

        if (
            isset( self::$posts[$args_hash] )
            && isset( self::$posts_count[$args_hash] )
            && isset( self::$posts_pages[$args_hash] )
        ){
            self::$get_posts_count = self::$posts_count[$args_hash];
            self::$get_posts_pages = self::$posts_pages[$args_hash];
            return self::$posts[$args_hash];
        }
        
        ////// sanitize args

        $guests = max(1, (int)$args['guests']);
        
        if ( !$args['date_from'] ){
            $guests = 0;
        }
        
        $date_now_obj = BABE_Functions::datetime_local();
        $date_to_obj_alt = clone $date_now_obj;
        $date_to_obj_alt->modify("+1 year");
        
        $date_from = BABE_Calendar_functions::isValidDate($args['date_from'], BABE_Settings::$settings['date_format']) ? BABE_Calendar_functions::date_to_sql($args['date_from']) : $date_now_obj->format("Y-m-d");
        
        $date_to = BABE_Calendar_functions::isValidDate($args['date_to'], BABE_Settings::$settings['date_format']) ? BABE_Calendar_functions::date_to_sql($args['date_to']) : $date_to_obj_alt->format("Y-m-d");
        
        if(BABE_Calendar_functions::isValidTime($args['time_from'], 'H:i')){
          $date_from .= ' '.$args['time_from'];
        }

        if( $args['time_to'] && BABE_Calendar_functions::isValidTime($args['time_to'], 'H:i') ){
            $date_to .= ' '.$args['time_to'] . ':59';
        } else {
            $date_to .= ' 23:59:59';
        }
        
        $date_from_obj = new DateTime($date_from);
        $date_to_obj = new DateTime($date_to);

        if ( $date_from_obj < $date_now_obj ){
            $date_from_obj = clone $date_now_obj;
            $date_from = $date_from_obj->format('Y-m-d H:i:s');
        }
        
        ////// filter rates
        $rate_clauses = '';

        $rate_date_from = new DateTime( $date_from_obj->format('Y-m-d 00:00:00') );
        $rate_date_to = new DateTime( $date_to_obj->format('Y-m-d 23:59:59') );
        if ($date_from_obj == $date_to_obj){
            $rate_date_to->modify('+1 day');
        }
        $d_interval = date_diff($rate_date_from, $rate_date_to);
        $days_total = $d_interval->format('%a'); // total days
        // if < 7 days check the apply days
        if ($days_total < 7){
            $tmp_clauses_arr = [];
            $interval = new DateInterval('P1D');
            $daterange = new DatePeriod($rate_date_from, $interval, $rate_date_to);
            foreach($daterange as $date){
                $date_cal_day_num = BABE_Calendar_functions::get_week_day_num($date);
                $tmp_clauses_arr[] = "LOCATE('i:".$date_cal_day_num.";', t_rate.apply_days) > 0";
            }
            $rate_clauses .= " AND ( ".implode(' OR ', $tmp_clauses_arr)." )";
        }
        //////
        
        $datetime_from = $date_from_obj->format('Y-m-d H:i:s');
        $datetime_to = $date_to_obj->format('Y-m-d H:i:s');
        
        $date_now = $date_now_obj->format('Y-m-d H:i:s');

        $posts_per_page = (int)$args['posts_per_page'];
        $posts_per_page = $posts_per_page < 1 ? -1 : $posts_per_page;
        $offset = max(0, (int)$args['paged'] -1)*$posts_per_page;
        
        $limit_clauses = $posts_per_page == -1 ? "" : "
        LIMIT ".$offset.", ".$posts_per_page."   
";
        switch($args['sort']){
            case 'post_title':
                $sort = 'posts.post_title';
                break;
            case 'rating':
                $sort = 'pmr.meta_value';
                break;
            case 'av_date_from':
                $sort = BABE_Settings::$settings['results_without_av_check']
                    && empty($args_array['date_from']) ?
                    'discount_price_from' : 'av_date_from';
                break;
            case 'price_from':
            default:
                $sort = 'discount_price_from';
                break;
        }

        $sort = apply_filters('babe_get_posts_sort_arg', $sort, $args);

        $sort_by = $args['sort_by'] === 'ASC' ? ' ASC' : ' DESC';
        $order_by = ' ORDER BY '.$sort.$sort_by;

        if ( $args['sort'] !== 'av_date_from' ){
            $order_by .= $args['group_results_by_date'] && empty($args['without_av_cal']) ? ", av_cal.cal_date ASC" : "";
        } elseif ( $args['group_results_by_date'] && empty($args['without_av_cal']) ){
            $order_by = " ORDER BY av_cal.cal_date".$sort_by;
        }

        $order_by .= ", t_rate.booking_obj_id ASC, t_rate.rate_order ASC, t_rate.price_from ASC, t_rate.date_from DESC, t_rate.date_to DESC";

        $group_by = "";
        //GROUP BY posts.ID
        if ( !$args['group_results_by_date'] ){
            $group_by = " GROUP BY posts.ID";
        }

        $terms_filter = '';
        
        if( !empty($args['terms']) ){

            $args['terms'] = array_map( 'intval', $args['terms'] );
            $args['terms'] = array_unique($args['terms']);

            $in_terms = [];

            foreach($args['terms'] as $key => $term_id){

                $term = get_term($term_id);

                if ( !$term instanceof WP_Term){
                    continue;
                }

                $in_terms[] = $term_id;

                $term_children = get_term_children( $term_id, $term->taxonomy );
                if ( !empty($term_children) && !$term_children instanceof WP_Error ){
                    $in_terms = array_merge( $in_terms, $term_children );
                }
            }

            if ( !empty($in_terms) ){

                foreach( $in_terms as $in_term ){
                    $terms_filter .= "
                AND EXISTS (
                  SELECT object_id
                  FROM $wpdb->term_relationships
                  WHERE object_id = posts.ID 
                  AND term_taxonomy_id = ".$in_term."
                  ) 
                ";
                }
            }
        }

        $terms_filter = apply_filters('babe_get_posts_search_query_terms_filter', $terms_filter, $args);
        
        if( !empty($args['categories']) && is_array($args['categories']) ){
           $args['categories'] = array_map( 'intval', $args['categories'] );
           $categories_filter = " AND ct.term_taxonomy_id IN (".implode(", ", $args['categories']).")";
        } else {
           $categories_filter = ''; 
        }
        
        if(!empty($args['post__in'])){
           $args['post__in'] = array_map( 'intval', $args['post__in'] );
           $post__in_filter = " AND posts.ID IN (".implode(", ", $args['post__in']).")";
        } else {
           $post__in_filter = ''; 
        }

        if( !empty($args['post_author']) ){
            $post_author = " AND posts.post_author = ".(int)$args['post_author'];
        } elseif ( !empty($args['author']) ) {
            $post_author = " AND posts.post_author = ".(int)$args['author'];
        } else {
            $post_author = '';
        }

        /////////Check availability//////////////

        $av_join = (
            BABE_Settings::$settings['results_without_av_check']
            && empty($args_array['date_from'])
        )
            || !empty($args['without_av_check']) ? 'LEFT' : 'INNER';

        $av_where = 'WHERE in_schedule=1';

        if (
            !BABE_Settings::$settings['results_without_av_check']
            && empty($args['without_av_check'])
        ){
            $av_where .= " AND date_from >= '".$date_from."' AND date_from <= '".$date_to."'";
        } else {
            $av_where = "";
        }

        $group_results_by_date = $args['group_results_by_date'] ? 1 : 0;

        $av_cal_clauses = "
        # get av cal
        ".$av_join." JOIN 
        (
        SELECT booking_obj_id AS obj_id, DATE_FORMAT(date_from, '%Y-%m-%d') AS cal_date, MIN(date_from) AS av_date_from, ".$group_results_by_date." as group_results_by_date
        FROM ".BABE_Calendar_functions::$table_av_cal."
        ".$av_where."
        ".($args['group_results_by_date'] ? "GROUP BY cal_date, booking_obj_id" : "GROUP BY booking_obj_id" )."
        ORDER BY date_from ASC
        ) av_cal ON av_cal.obj_id = posts.ID";

        if ( !empty($args['without_av_cal']) ){
            $av_cal_clauses = "";
        }

        $av_cal_exclude_filter = "
           AND NOT EXISTS (
              SELECT booking_obj_id
              FROM ".BABE_Calendar_functions::$table_av_cal."               
              WHERE booking_obj_id = posts.ID 
              AND in_schedule=1 
              AND date_from > '".$date_from."' 
              AND date_from <= '".$date_to."'
              AND av_guests < ".$guests."
              AND rules.basic_booking_period != 'recurrent_custom' 
              AND rules.basic_booking_period != 'single_custom' 
              AND rules.basic_booking_period != 'night'
              LIMIT 1
           )           
           AND NOT EXISTS (
              SELECT booking_obj_id
              FROM ".BABE_Calendar_functions::$table_av_cal."
              WHERE booking_obj_id = posts.ID 
              AND in_schedule=1 
              AND date_from >= '".$date_from."' 
              AND date_from < '".$date_to."'
              AND av_guests < ".$guests."
              AND rules.basic_booking_period = 'night'
              LIMIT 1
           ) 
           AND (
              ( 
              rules.basic_booking_period != 'recurrent_custom' 
              AND rules.basic_booking_period != 'single_custom'
              )
              OR (
               (
               rules.basic_booking_period = 'recurrent_custom' 
               OR rules.basic_booking_period = 'single_custom'
               )
               AND EXISTS (
                  SELECT booking_obj_id
                  FROM ".BABE_Calendar_functions::$table_av_cal."
                  WHERE booking_obj_id = posts.ID 
                  AND in_schedule=1 
                  AND date_from >= '".$datetime_from."' 
                  AND date_from <= '".$datetime_to."'
                  AND av_guests > ".$guests." 
                  LIMIT 1
                  ) 
              )
           )
           ";

        if (
            (
                empty($args_array['date_from'])
                && BABE_Settings::$settings['results_without_av_check']
            )
            || !empty($args['without_av_check'])
        ){
            $av_cal_exclude_filter = "";

        } else {

            $av_cal_clauses .= "
        LEFT JOIN 
        (
        SELECT category_id AS category_id_to_exclude
        FROM ".self::$table_category_deactivate_schedule."
        WHERE deactivate_date_from <= '".$date_from."' AND deactivate_date_to >= '".$date_to."'
        GROUP BY category_id
        ) cds ON cds.category_id_to_exclude = ctt.term_id";

            $av_cal_exclude_filter .= "
           AND cds.category_id_to_exclude IS NULL";
        }

        if ( $args['not_scheduled'] ){

            $av_cal_clauses = "# get av cal
        LEFT JOIN 
        (
        SELECT booking_obj_id AS obj_id, DATE_FORMAT(date_from, '%Y-%m-%d') AS cal_date, MIN(date_from) AS av_date_from, ".$group_results_by_date." as group_results_by_date
        FROM ".BABE_Calendar_functions::$table_av_cal."
        WHERE date_from >= '".$date_from."' AND date_from <= '".$date_to."'
        GROUP BY booking_obj_id
        ) av_cal ON av_cal.obj_id = posts.ID";

            $av_cal_exclude_filter = "
           AND av_cal.obj_id IS NULL";

            $group_by = " GROUP BY posts.ID";
        }

        ////////////////////

        $discount_price_from_calculation = "(t_rate.price_from*(100-COALESCE(pmd.discount, 0))*(100+COALESCE(tm2.meta_value, 0)*COALESCE(tm3.meta_value, 0)))/(100*100)";
        
        $min_price_filter = $args['min_price'] ?  "
            AND ".$discount_price_from_calculation." >= ".absint($args['min_price']) : "";
        
        $max_price_filter = $args['max_price'] ? "
            AND ".$discount_price_from_calculation." <= ".(int)$args['max_price'] : "";

        $keyword = sanitize_text_field($args['keyword']);

        $keyword_filter = $keyword ? "
            AND (posts.post_title LIKE '%".esc_sql($keyword)."%' OR posts.post_content LIKE '%".esc_sql($keyword)."%')" : '';

        $lang_filter = '';

        if ( BABE_Functions::is_wpml_active() ){
            $current_lang = apply_filters( 'wpml_current_language', get_locale() );

            $lang_filter = "INNER JOIN
        (
        SELECT element_type AS lang_element_type, element_id AS lang_element_id, language_code
        FROM ".$wpdb->prefix."icl_translations
        WHERE language_code = '".esc_attr($current_lang)."' AND element_type = 'post_".BABE_Post_types::$booking_obj_post_type."'
        ) icl_tr ON posts.ID = icl_tr.lang_element_id
        ";
        }

        //// create query
        
        $query = "SELECT posts.*, 
        rules.*,
        ".$discount_price_from_calculation." AS discount_price_from,
        pm.guests AS guests,
        ctt.slug AS category_slug,
        ctt.term_id AS category_id,
        CAST(tm2.meta_value AS UNSIGNED) AS categories_add_taxes,
        CAST(tm3.meta_value AS UNSIGNED) AS categories_tax,
        CAST(pmr.meta_value AS DECIMAL(3,2)) AS rating,
        items_number,
        pmd.discount, pmd.discount_date_from, pmd.discount_date_to,
        t_rate.rate_id, t_rate.rate_title, t_rate.date_from AS rate_date_from, t_rate.date_to AS rate_date_to, t_rate.apply_days, t_rate.start_days, t_rate.min_booking_period, t_rate.max_booking_period, t_rate.price_from, t_rate.price_general, t_rate.prices_conditional, t_rate.rate_order
        FROM ".$wpdb->posts." posts 
        
        #every our post assigned to terms from booking categories and other taxonomies
        INNER JOIN ".$wpdb->term_relationships." tr ON posts.ID = tr.object_id 
        
        #we need only our post type with categories
        INNER JOIN ".$wpdb->term_taxonomy." ct ON ct.term_taxonomy_id = tr.term_taxonomy_id 
          AND ct.taxonomy = '".self::$categories_tax."'".$categories_filter."
        
        #get category slug
        INNER JOIN ".$wpdb->terms." ctt ON ctt.term_id = ct.term_id
        
        ".$lang_filter."
        
        # get rates
        ".$av_join." JOIN ".BABE_Prices::$table_rate." t_rate ON posts.ID = t_rate.booking_obj_id 
        AND ( t_rate.date_from <= '".$date_to."' OR t_rate.date_from IS NULL ) 
        AND ( t_rate.date_to >= '".$date_from."' OR t_rate.date_to IS NULL )"
            .$rate_clauses.
            "
        #get max guests        
        INNER JOIN 
        (
        SELECT CAST(meta_value AS DECIMAL) AS guests, post_id AS pm_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'guests'
        ) pm ON ( posts.ID = pm.pm_post_id AND pm.guests >= ".$guests." )
        
        #add categories_booking_rule meta
        LEFT JOIN ".$wpdb->termmeta." tm ON tr.term_taxonomy_id = tm.term_id AND tm.meta_key = 'categories_booking_rule'
        
        # get rule
        LEFT JOIN ".BABE_Booking_Rules::$table_booking_rules." rules ON rules.rule_id = tm.meta_value
        
        #get items number
        LEFT JOIN 
        (
        SELECT GREATEST(CAST(COALESCE(meta_value, 1) AS DECIMAL), 1) AS items_number, post_id AS pmt_post_id, meta_key AS pmt_meta_key
        FROM ".$wpdb->postmeta."
        ) pmt ON ( posts.ID = pmt.pmt_post_id AND pmt.pmt_meta_key = CONCAT('items_number_', ctt.slug) )
        
        ".$av_cal_clauses."
        
        #add categories_add_taxes meta
        LEFT JOIN ".$wpdb->termmeta." tm2 ON tr.term_taxonomy_id = tm2.term_id AND tm2.meta_key = 'categories_add_taxes'
        
        #add categories_tax meta
        LEFT JOIN ".$wpdb->termmeta." tm3 ON tr.term_taxonomy_id = tm3.term_id AND tm3.meta_key = 'categories_tax'
        
        #get rating
        LEFT JOIN ".$wpdb->postmeta." pmr ON posts.ID = pmr.post_id AND pmr.meta_key = '_rating' 
        
        #get discount
        LEFT JOIN 
        (
        SELECT discount, date_from AS discount_date_from, date_to AS discount_date_to, booking_obj_id AS discount_obj_id
        FROM ".BABE_Prices::$table_discount."
        WHERE date_from <= '".$date_now."' AND date_to >= '".$date_now."'
        ) pmd ON posts.ID = pmd.discount_obj_id
        
        WHERE posts.post_type = '".self::$booking_obj_post_type."'
           AND posts.post_status = 'publish'
           ".$post__in_filter.$post_author.$min_price_filter.$max_price_filter . $keyword_filter . $terms_filter . $av_cal_exclude_filter. $group_by;

        $query = apply_filters('babe_get_posts_search_query', $query, $args);

        // run main query
        $output = $wpdb->get_results($query.$order_by.$limit_clauses, ARRAY_A);

        if ( !empty($output) ){

            $currency = BABE_Currency::get_currency();

            foreach ($output as $ind => $item) {

                if ( empty($output[$ind]['price_from']) ){
                    continue;
                }

                $output[$ind]['price_from'] = BABE_Prices::localize_price( $output[$ind]['price_from'], $currency );
                $output[$ind]['discount_price_from'] = BABE_Prices::localize_price( $output[$ind]['discount_price_from'], $currency );
            }
        }

        self::$get_posts_count = count($output);

        if ( $args['return_total_count'] ){
            // run count query
            self::$get_posts_count = $wpdb->get_var("SELECT COUNT(discount_price_from) AS total_count FROM (".$query.") AS a");
        }

        self::$get_posts_pages = $posts_per_page == -1 ? 1 : ceil(self::$get_posts_count/$posts_per_page);

        /// create cache
        self::$posts_count[$args_hash] = self::$get_posts_count;
        self::$posts_pages[$args_hash] = self::$get_posts_pages;
        self::$posts[$args_hash] = $output;
        
        return $output;
    } 
    
/////is_post_booking_obj////    
    /**
	 * Is the post type a booking obj post type?
     * @param int $post_id
     * @return boolean
	 */
    public static function is_post_booking_obj($post_id){
        
        $post_id = absint($post_id); 
        
        return self::$booking_obj_post_type == get_post_type($post_id) ? true : false;
    }
    
/////is_post_order////    
    /**
	 * Is the post type an order post type?
     * @param int $post_id
     * @return boolean
	 */
    public static function is_post_order($post_id){
        
        $post_id = absint($post_id); 
        
        return self::$order_post_type == get_post_type($post_id) ? true : false;
    }    
    
/////////////////////    
    /**
	 * Get post services.
     * @param array $post
     * @return array
	 */
    public static function get_post_services($post) {
        global $wpdb;
        
        $output = [];

        if (isset(self::$post_services[$post['ID']])){
            return self::$post_services[$post['ID']];
        }

        if ( empty($post['ID']) || empty($post['services']) ){
            return $output;
        }
            
        $ids = array_map( 'intval', (array)$post['services']);

        ////
        $query = "SELECT * 
        FROM ".$wpdb->posts." posts
        
        LEFT JOIN #get is_mandatory
        (
        SELECT CAST(meta_value AS UNSIGNED) AS is_mandatory, post_id AS pmm_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'is_mandatory'
        ) pmm ON posts.ID = pmm.pmm_post_id
        
        LEFT JOIN #get service_type
        (
        SELECT meta_value AS service_type, post_id AS pms_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'service_type'
        ) pms ON posts.ID = pms.pms_post_id
        
        LEFT JOIN #get price_type
        (
        SELECT meta_value AS price_type, post_id AS pm_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'price_type'
        ) pm ON posts.ID = pm.pm_post_id
        
        LEFT JOIN #get prices
        (
        SELECT meta_value AS prices, post_id AS pmp_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'prices'
        ) pmp ON posts.ID = pmp.pmp_post_id
        
        LEFT JOIN #get conditional prices
        (
        SELECT meta_value AS conditional_prices, post_id AS pmc_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'conditional_prices'
        ) pmc ON posts.ID = pmc.pmc_post_id
        
        LEFT JOIN #get allow_quantity
        (
        SELECT meta_value AS allow_quantity, post_id AS pmq_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'allow_quantity'
        ) pmq ON posts.ID = pmq.pmq_post_id
        
        LEFT JOIN #get max_quantity
        (
        SELECT meta_value AS max_quantity, post_id AS pmqm_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'max_quantity'
        ) pmqm ON posts.ID = pmqm.pmqm_post_id
        
        WHERE (
          posts.post_status = 'publish'
          AND
           posts.post_type = '".self::$service_post_type."'
          AND posts.ID IN (".implode(", ", $ids).")
        )
        
        GROUP BY posts.ID
        ORDER BY service_type ASC  
";
        /////

        $results = $wpdb->get_results($query, ARRAY_A);
        $currency = BABE_Currency::get_currency();
        foreach($results as $i => $result){
            $output[$i] = $result;
            $output[$i]['prices'] = maybe_unserialize($result['prices']);
            if (!isset($output[$i]['prices'][0])){
                $output[$i]['prices'][0] = 0;
            }
            $output[$i]['conditional_prices'] = maybe_unserialize($result['conditional_prices']);

            if ( $output[$i]['price_type'] !== 'percent' ){
                foreach( $output[$i]['prices'] as $age_id => $age_price){
                    if ( $age_price === '' ){
                        continue;
                    }
                    $output[$i]['prices'][$age_id] = BABE_Prices::localize_price((float)$age_price, $currency);
                }
            }

            unset($output[$i]['pmp_post_id']);
            unset($output[$i]['pm_post_id']);
            unset($output[$i]['pms_post_id']);
            unset($output[$i]['pmm_post_id']);
            unset($output[$i]['pmc_post_id']);
        }

        self::$post_services[$post['ID']] = $output;

        return $output;
    }

/////////////////////
    /**
     * Get post fees.
     *
     * @param array $post
     *
     * @return array
     */
    public static function get_post_fees($post) {
        global $wpdb;

        $output = [];

        if (isset(self::$post_fees[$post['ID']])){
            return self::$post_fees[$post['ID']];
        }

        if ( empty($post['ID']) || empty($post['fees']) ){
            return $output;
        }

        $ids = (array)$post['fees'];

        $ids = array_map( 'intval', $ids );
        ////
        $query = "SELECT * 
        FROM ".$wpdb->posts." posts
        
        LEFT JOIN #get is_mandatory
        (
        SELECT CAST(meta_value AS UNSIGNED) AS is_mandatory, post_id AS pms_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'is_mandatory'
        ) pms ON posts.ID = pms.pms_post_id
        
        LEFT JOIN #get price_type
        (
        SELECT meta_value AS price_type, post_id AS pm_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'price_type'
        ) pm ON posts.ID = pm.pm_post_id
        
        LEFT JOIN #get price
        (
        SELECT meta_value AS price, post_id AS pmp_post_id 
        FROM ".$wpdb->postmeta."
        WHERE meta_key = 'price'
        ) pmp ON posts.ID = pmp.pmp_post_id
        
        WHERE (
          (posts.post_status = 'publish'
          AND
           posts.post_type = '".self::$fee_post_type."')
          AND posts.ID IN (".implode(", ", $ids).")
        )
        
        GROUP BY posts.ID
        ORDER BY is_mandatory DESC  
";
        /////

        $results = $wpdb->get_results($query, ARRAY_A);

        if ( empty($results) ){
            self::$post_fees[$post['ID']] = $output;
            return $output;
        }

        $currency = BABE_Currency::get_currency();

        foreach($results as $i => $result){
            $output[$i] = $result;

            if ( $output[$i]['price_type'] !== 'percent' ){
                $output[$i]['price'] = BABE_Prices::localize_price((float)$output[$i]['price'], $currency);
            }

            unset($output[$i]['pmp_post_id']);
            unset($output[$i]['pm_post_id']);
            unset($output[$i]['pms_post_id']);
        }

        self::$post_fees[$post['ID']] = $output;

        return $output;
    }

/////////////////////
    /**
     * Get post mandatory fee ids.
     *
     * @param array $post
     *
     * @return array
     */
    public static function get_post_mandatory_fee_ids($post) {

        $output = [];

        $fees = self::get_post_fees($post);

        if ( empty($fees) ){
            return $output;
        }

        foreach($fees as $i => $fee){
            if ( $fee['is_mandatory'] ){
                $output[] = $fee['ID'];
            }
        }

        return $output;

    }

/////////////////////
    /**
     * Get post mandatory service ids.
     *
     * @param array $post
     *
     * @return array
     */
    public static function get_post_mandatory_service_ids($post) {

        $output = [];

        $services = self::get_post_services($post);

        if ( empty($services) ){
            return $output;
        }

        foreach($services as $i => $service){
            if ( $service['is_mandatory'] ){
                $output[] = $service['ID'];
            }
        }

        return $output;
    }

/////////////////////
    /**
     * Get post taxes value
     *
     * @param int $post_id
     *
     * @return float
     */
    public static function get_post_tax($post_id) {

        $post_id = (int)$post_id;

        if ( $post_id <= 0 ){
            return 0;
        }

        if ( isset(self::$post_taxes[$post_id]) ){
            return self::$post_taxes[$post_id];
        }

        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

        $tax_value = !empty($rules_cat['category_meta']['categories_add_taxes']) && !empty($rules_cat['category_meta']['categories_tax']) ? (float)$rules_cat['category_meta']['categories_tax'] : 0;

        self::$post_taxes[$post_id] = $tax_value;

        return $tax_value;
    }
    
//////////////////////////////
    /**
	 * Get post FAQs.
     * @param array $post
     * @return array
	 */
    public static function get_post_faq($post) {
        
        $output = array();
          
        if (!empty($post) && isset($post['ID']) && isset($post['faq']) && !empty($post['faq'])){
            
          $ids = (array)$post['faq'];
            
          if (!empty($ids)){
             $ids = array_map( 'intval', $ids );
                
             $args = array(
                'post_type'   => self::$faq_post_type,
                'numberposts' => -1,
                'post__in' => $ids,
                'post_status' => 'publish',
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'suppress_filters' => false,
             );
             
             $args = apply_filters('babe_get_post_faq_args', $args, $post);
             
             $posts_obj = get_posts( $args );
             $output = json_decode(json_encode($posts_obj), true);
          } //// end if !empty $ids   
       } /// end if !empty $post      
             
       return $output;
    
    }
    
//////////////////////////////
    /**
	 * Get post related items.
     * 
     * @param array $post
     * @return array
	 */
    public static function get_post_related($post) {
        
        $output = [];

        if (isset(self::$post_related[$post['ID']])){
            return self::$post_related[$post['ID']];
        }
          
        if (!empty($post) && isset($post['ID']) && isset($post['related_items']) && !empty($post['related_items'])){
            
          $ids = (array)$post['related_items'];
            
          if (!empty($ids)){
             $ids = array_map( 'intval', $ids );
                
             $args = array(
                'post__in' => $ids,
             );
             
             $args = apply_filters('babe_get_post_related', $args, $post);
             
             $output = self::get_posts( $args );
          } //// end if !empty $ids   
       } /// end if !empty $post

        self::$post_related[$post['ID']] = $output;
             
       return $output;
    
    }             
    
/////////////////////    
    /**
	 * Get post meeting points.
     * @param array $post
     * @return array
	 */
    public static function get_post_meeting_points($post) {
        $output = array();
          
          if (!empty($post) && isset($post['meeting_points']) && isset($post['meeting_place']) && $post['meeting_place'] == 'point'){
           
           foreach($post['meeting_points'] as $meeting_point){
            
              $place_id = $meeting_point['place'];
              $address_arr = get_post_meta($place_id, 'address', true);
              $description = get_post_meta($place_id, 'description', true);
              $lat = isset($address_arr['latitude']) ? $address_arr['latitude'] : 0;
              $lng = isset($address_arr['longitude']) ? $address_arr['longitude'] : 0;
              $address = isset($address_arr['address']) ? apply_filters('translate_text', $address_arr['address']) : '';
              
              $times = array();
              if (!empty($post['schedule'])){
                 foreach($post['schedule'] as $schedule){
                    foreach($schedule as $time){
                      $date_tmp = new DateTime('2017-01-01 '.$time);
                      $modify_string = '';
                      $modify_string .= isset($meeting_point['time_shift']['h']) ? '-'. (int)$meeting_point['time_shift']['h'] .' hour ' : '';
                      $modify_string .= isset($meeting_point['time_shift']['i']) ? '-'. (int)$meeting_point['time_shift']['i'] .' minute' : '';
                      if ($modify_string){
                          $date_tmp->modify($modify_string);
                      }
                      $times[$time] = $date_tmp->format(get_option('time_format'));
                    }
                 }
              }
              $times = array_unique($times);
              
              $output[$place_id] = array(
                 'title' => get_the_title($place_id),
                 'address' => $address,
                 'lat' => $lat,
                 'lng' => $lng,
                 'description' => $description,
                 'times' => $times,
                 'permalink' => get_permalink($place_id),
                 'point_id' => $place_id,
              );
           } //// end foreach $post['meeting_points']
            
          } //// end if !empty($post) 
        
        return $output;
    }
    
////////////////////////////
    /**
	 * Get post available times.
     * @param array $post
     * @return array
	 */
    public static function get_post_av_times($post) {
        $output = array();
        
        if (!empty($post) && isset($post['schedule']) && !empty($post['schedule'])){            
            foreach($post['schedule'] as $day_num => $time_arr){
                foreach($time_arr as $time){
                    $output[] = $time;
                }
            }
            
            $output = array_unique($output);
            sort($output);
        }
        
        return $output;
    }        
     
////////////////////////////
    /**
	 * Get post available days.
     * @param array $post
     * @return array
	 */
    public static function get_post_av_days($post) {
        $output = array();
        
        //$post = self::get_post($post_id);
        if (!empty($post) && isset($post['schedule']) && !empty($post['schedule'])){            
            $week_days = BABE_Calendar_functions::get_week_days_arr();
            foreach($post['schedule'] as $day_num => $time_arr){
                $output[$day_num] = $week_days[$day_num];
            }
        }
        
        return $output;
    }
    
////////////////////////////
    /**
	 * Get post available days.
     * @param array $post
     * @return string
	 */
    public static function get_post_av_days_str($post) {
        $week_days = self::get_post_av_days($post);
        $week_days_string = implode(', ', $week_days);  
        return count($week_days) === 7 ? __('Daily', 'ba-book-everything') : $week_days_string;
    }
    
////////////////////////////
    /**
	 * Get post duration.
     * @param array $post
     * @return string
	 */
    public static function get_post_duration($post) {
        $output = '';
        
        $arr = array();
        
        if (!empty($post) && isset($post['duration']) && !empty($post['duration'])){            
            if ( !empty($post['duration']['d']) ) {
                $arr[] = $post['duration']['d']
                    .' '
                    . (
                        (int)$post['duration']['d'] === 1
                            ? __('day', 'ba-book-everything')
                            : __('days', 'ba-book-everything')
                    );
            }
            if ( !empty($post['duration']['h']) ) {
                $arr[] = $post['duration']['h']
                    .' '
                    . (
                    (int)$post['duration']['h'] === 1
                        ? __('hour', 'ba-book-everything')
                        : __('hours', 'ba-book-everything')
                    );
            }
            if ( !empty($post['duration']['i']) ) {
                $arr[] = $post['duration']['i']
                    .' '
                    . (
                    (int)$post['duration']['i'] === 1
                        ? __('minute', 'ba-book-everything')
                        : __('minutes', 'ba-book-everything')
                    );
            }
            $output .= implode(' ', $arr);
        }
        
        return $output;
    }

    ////////////////////////
    /**
     * Get post last available booking time for the current date
     * @param int $post_id
     * @return string
     */
    public static function get_post_last_av_booking_time($post_id) {

        $default = '00:00';

        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

        if ( empty($rules_cat['category_slug']) ){
            return $default;
        }

        $last_av_booking_time = get_post_meta( $post_id, 'last_av_booking_time_'.$rules_cat['category_slug'], true);

        return $last_av_booking_time && BABE_Calendar_functions::isValidTime($last_av_booking_time, 'H:i') ? $last_av_booking_time : $default;
    }

    ////////////////////////////
    /**
	 * Get post items number
     * 
     * @param int|array post ID or post array
     * @return int
	 */
    public static function get_post_items_number($post): int
    {
        $output = 1;
        
        if (is_numeric($post)){
            $post = self::get_post($post);
        }

        if ( empty($post) || !is_array($post) ){
            return $output;
        }

        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post['ID']);

        $output = $rules_cat['rules']['booking_mode'] === 'object'
        && isset($post['items_number'])
        && absint($post['items_number'])
            ? absint($post['items_number']) : 1;

        return $output;
    }                     

    ////////////////////////////
    /**
	 * Get post with all meta.
     * @param int $post_id
     * @return array
	 */
    public static function get_post($post_id) {
        global $wpdb;
        
        $output = array();
        
        $post_id = absint($post_id);
        
        if (isset(self::$post[$post_id])){
            return self::$post[$post_id];  
        }

        if ($post_id <= 0){
            return $output;
        }

        $query = "SELECT * 
        FROM ".$wpdb->posts." posts
        WHERE (
          (posts.ID = ".$post_id."
          AND
           posts.post_status = 'publish')
        )";

        $post = $wpdb->get_results($query, ARRAY_A);

        if ( empty($post) ){
            return $output;
        }

        $output = $post[0];
        $meta = self::get_post_meta($post_id);

        if ( !empty($meta) ){
            $output = $output + $meta;
        }
        
        self::$post[$post_id] = $output;
        
        return $output;
    }

    ////////////////////////////
    /**
	 * Get all post meta.
     * @param int $post_id
     * @return array
	 */
    public static function get_post_meta($post_id) {
        global $wpdb;
        
        $output = array();
        
        $post_id = absint($post_id);
        
        if ( isset(self::$post_meta[$post_id]) ){
            return self::$post_meta[$post_id];  
        }

        if ($post_id <= 0){
            return $output;
        }

        $meta = array();

        $term = self::get_post_category($post_id);

        if ( !empty($term) ){

            $slug = '_'.$term->slug;

            $query = "SELECT * 
          FROM ".$wpdb->postmeta." pm
          WHERE pm.post_id = ".$post_id."
          ORDER BY pm.meta_key ASC";

            $result = $wpdb->get_results($query, ARRAY_A);
            foreach($result as $row){
                if(substr($row['meta_key'], 0, 1) !== '_' && substr($row['meta_key'], 0, 4) !== 'tmp_'){
                    $meta_key = str_replace($slug, '', $row['meta_key']);
                    $meta[$meta_key] = maybe_unserialize($row['meta_value']);
                }
            }
            $output = $meta;

        }
        
        self::$post_meta[$post_id] = $output;
        
        return $output;
    }
    
////////////////////////////
    /**
	 * Get post discount.
     * @param int $post_id
     * @return array
	 */
    public static function get_post_discount($post_id) {
        global $wpdb;
        
        if (isset(self::$post_discount[$post_id])){
            return self::$post_discount[$post_id];  
        }
        
        $date_now_obj = BABE_Functions::datetime_local();
        $date_now = $date_now_obj->format('Y-m-d H:i:s');
        
        /// get discount
        $discount_db = $wpdb->get_row("SELECT discount, date_from AS discount_date_from, date_to AS discount_date_to FROM ".BABE_Prices::$table_discount." WHERE booking_obj_id = '".$post_id."' AND date_from <= '".$date_now."' AND date_to >= '".$date_now."'", ARRAY_A);
        
        $output = !empty($discount_db) ? $discount_db
            : array(
                'discount' => 0,
                'discount_date_from' => $date_now,
                'discount_date_to' => $date_now,
            );

        $output = apply_filters('babe_post_discount', $output, $post_id );
        
        self::$post_discount[$post_id] = $output;

        return $output;
    }        
    
////////////////////////////
    /**
	 * Get post price from
     * @param int $post_id
     * @return array
	 */
    public static function get_post_price_from($post_id) {
        global $wpdb;

        if (isset(self::$post_price_from[$post_id])){
            return self::$post_price_from[$post_id];
        }

        $date_now_obj = BABE_Functions::datetime_local();
        $date_now = $date_now_obj->format('Y-m-d H:i:s');
        
        $date_to_obj = new DateTime($date_now_obj->format('Y-m-d'));
        $date_to_obj->modify("+18 months");
        
        $datetime_from = $date_now_obj->format('Y-m-d 00:00:00');
        $datetime_to = $date_to_obj->format('Y-m-d H:i:s');
        
        $query = "SELECT *, (t_rate.price_from*(100-COALESCE(pmd.discount, 0))*(100+COALESCE(tm2.categories_add_taxes, 0)*COALESCE(tm3.categories_tax, 0)))/(100*100) AS discount_price_from
        FROM ".BABE_Prices::$table_rate." t_rate
        
        INNER JOIN #every our post assigned to terms from categories and other taxonomies
        (
        SELECT object_id AS tr_object_id, term_taxonomy_id AS tr_term_taxonomy_id
        FROM ".$wpdb->term_relationships."
        ) tr ON t_rate.booking_obj_id = tr.tr_object_id  
        
        INNER JOIN #we need only our post type with categories
        (
        SELECT term_taxonomy_id AS ct_term_taxonomy_id, term_id AS ct_term_id
        FROM ".$wpdb->term_taxonomy."
        WHERE taxonomy = '".self::$categories_tax."'
        ) ct ON ct.ct_term_taxonomy_id = tr.tr_term_taxonomy_id
        
        LEFT JOIN #add categories_add_taxes meta
        (
        SELECT CAST(meta_value AS UNSIGNED) AS categories_add_taxes, term_id AS tm2_term_id
        FROM ".$wpdb->termmeta."
        WHERE meta_key = 'categories_add_taxes'
        ) tm2 ON tr.tr_term_taxonomy_id = tm2.tm2_term_id
        
        LEFT JOIN #add categories_tax meta
        (
        SELECT CAST(meta_value AS UNSIGNED) AS categories_tax, term_id AS tm3_term_id
        FROM ".$wpdb->termmeta."
        WHERE meta_key = 'categories_tax'
        ) tm3 ON tr.tr_term_taxonomy_id = tm3.tm3_term_id
        
        LEFT JOIN #get discount
        (
        SELECT discount, date_from AS discount_date_from, date_to AS discount_date_to, booking_obj_id AS discount_obj_id
        FROM ".BABE_Prices::$table_discount."
        WHERE date_from <= '".$date_now."' AND date_to >= '".$date_now."'
        ) pmd ON t_rate.booking_obj_id = pmd.discount_obj_id
        
        WHERE (
          t_rate.booking_obj_id = ".(int)$post_id."
          AND ( t_rate.date_to >= '".$datetime_from."' OR t_rate.date_to IS NULL )
          AND ( t_rate.date_from <= '".$datetime_to."' OR t_rate.date_from IS NULL )
        )
        
        ORDER BY t_rate.rate_order ASC, t_rate.price_from ASC, t_rate.date_from DESC, t_rate.date_to DESC, discount_price_from ASC
        LIMIT 0, 1   
";     
        $results = $wpdb->get_results($query, ARRAY_A);

        self::$post_price_from[$post_id] = !empty($results) ? array(
            'price_from' => BABE_Prices::localize_price( $results[0]['price_from'], BABE_Currency::get_currency() ),
            'categories_add_taxes' => $results[0]['categories_add_taxes'],
            'categories_tax' => $results[0]['categories_tax'],
            'discount' => isset($results[0]['discount']) ? $results[0]['discount'] : 0,
            'discount_price_from' => BABE_Prices::localize_price( $results[0]['discount_price_from'], BABE_Currency::get_currency() ),
            'discount_date_from' => isset($results[0]['discount_date_from']) ? $results[0]['discount_date_from'] : '',
            'discount_date_to' => isset($results[0]['discount_date_to']) ? $results[0]['discount_date_to'] : '',
        ) : [];
        
        return self::$post_price_from[$post_id];
    }    
    
////////////////////////////
    /**
	 * Get post category.
     * @param int $post_id
     * @return WP_Term | string
	 */
    public static function get_post_category($post_id) {

        $categories_arr = get_the_terms($post_id, self::$categories_tax);
        if (!empty($categories_arr) && !is_wp_error($categories_arr)){
           return $categories_arr[0];   
        }  
        
        return '';
    }        

////////////////////////////
    /**
	 * Get post terms from all custom taxonomies or one taxonomy
     * @param int $post_id
     * @param string $taxonomy_slug
     * @return array
	 */
    public static function get_post_terms($post_id, $taxonomy_slug = '') {
        
        $output = array();
        
        $taxonomies_list = self::$taxonomies_list;
        
        $rules = BABE_Booking_Rules::get_rule_by_obj_id($post_id);
        
        if (!empty($rules) && isset($rules['category_meta']['categories_taxonomies'])){
        foreach($rules['category_meta']['categories_taxonomies'] as $taxonomy_id){
            $taxonomy = $taxonomies_list[$taxonomy_id]; 
            // slug, select_mode, frontend_style, frontend_class, gmap_active
            if (!$taxonomy_slug || ($taxonomy_slug && $taxonomy_slug == $taxonomy['slug'])){
            
            $terms = get_the_terms($post_id, $taxonomy['slug']);
            if (!empty($terms) && !is_wp_error($terms)){
               $output[$taxonomy['slug']] = $taxonomy;
               foreach($terms as $term){
                   $output[$taxonomy['slug']]['terms'][$term->term_id] = array(
                    'term_id' => $term->term_id,
                    'term_taxonomy_id' => $term->term_taxonomy_id,
                    'name' => apply_filters('translate_text', $term->name),
                    'slug' => $term->slug,
                    'description' => apply_filters('translate_text', $term->description),
                    'parent' => $term->parent,
                    'count' => $term->count,
                   );
                   $term_vals = get_term_meta($term->term_id);
                   foreach($term_vals as $key=>$val){
                    $output[$taxonomy['slug']]['terms'][$term->term_id][$key] = $val[0];
                   }
                   $output[$taxonomy['slug']]['terms'][$term->term_id]['image_id'] = get_term_meta($term->term_id, 'image_id', 1);
               } 
            } else {
                $output[$taxonomy['slug']] = array();
            }
            
            } //// end if $taxonomy_slug
        }
        }     
        
        return !empty($output) && $taxonomy_slug ? $output[$taxonomy_slug] : $output;
    }
    
////////////////////////////
    /**
	 * Get terms hierarchy with active last level children
     * @param array $args
     * @param array $value - selected term ids
     * @return string
	 */
    public static function get_terms_children_hierarchy(
        $args,
        $value = array(),
        $insert_in_dropdown_container = true)
    {
        $output = '';

        // make sure we specify each part of the value we need.
        $args = wp_parse_args( $args, array(
            'taxonomy' => '',
            'parent_term_id' => 0,
            'level' => 0,
            'view' => '', // 'select', 'multicheck' or 'list'
            'add_count' => '',
            'option_all' => '',
            'option_all_value' => 0,
            'option_all_title' => '',
            'id' => '',
            'class' => '',
            'class_item' => 'term_item',
            'class_item_selected' => 'term_item_selected',
            'name' => '',
            'prefix_char' => ' ',
            'term_id_name' => 'term_id',
            'data-conditional-id' => '',
            'data-conditional-value' => '',
        ) );

        if ( !$args['taxonomy'] ){
            return $output;
        }

        $args['view'] = in_array($args['view'], ['select', 'multicheck', 'list']) ? $args['view'] : 'list';
        $args['name'] = !$args['name'] ? $args['taxonomy'].'_terms' : $args['name'];
        $args['id'] = $args['id'] ?: $args['name'];
        $args['term_id_name'] = $args['term_id_name'] === 'term_id' ? $args['term_id_name'] : 'term_taxonomy_id';

        $conditional_attr = $args['data-conditional-id'] && $args['data-conditional-value'] ? ' data-conditional-id="'.$args['data-conditional-id'].'" data-conditional-value="'.$args['data-conditional-value'].'"' : '';

        $terms_all = get_terms( array(
            'taxonomy' => $args['taxonomy'],
            'hide_empty' => false,
            'parent' => $args['parent_term_id']
        ) );

        $prefix = substr($args['prefix_char'].$args['prefix_char'].$args['prefix_char'].$args['prefix_char'].$args['prefix_char'].$args['prefix_char'].$args['prefix_char'], 0, $args['level']*mb_strlen($args['prefix_char']));

        foreach ( $terms_all as $term ) {
            $add_term_count = $args['add_count'] ? ' (' . absint( $term->count ) . ')' : '';

            $new_args = $args;
            $new_args['level'] = $args['level'] + 1;
            $new_args['parent_term_id'] = $term->term_id;

            $in_array = in_array($term->{$args['term_id_name']}, (array)$value);
            $class = $in_array ? $args['class_item'].' '.$args['class_item_selected'] : $args['class_item'];
            $class .= ' '.$args['class_item'].'_level_'.$args['level'];

            $children = self::get_terms_children_hierarchy($new_args, $value);
            $all_level_terms_selectable = (bool)get_term_meta($term->term_id, 'all_level_terms_selectable', true);

            $disable_current_item = $children && !$all_level_terms_selectable;

            $term_name = esc_attr( apply_filters('translate_text', $term->name) );

            $term_name = apply_filters('babe_get_terms_hierarchy_term_name', $term_name, $term->term_id, $args);

            if($args['view'] === 'select'){

                $selected = $in_array ? ' selected' : '';
                $disabled = $disable_current_item ? ' disabled' : '';
                $class = $disable_current_item ? $class.' term_item_disabled' : $class;

                $output .= '<option value="'.$term->{$args['term_id_name']}.'" class="'.$class.'"'.$selected.$disabled.'>'.$prefix.$term_name.$add_term_count.'</option>';
                $output .= $children;

            } elseif ($args['view'] === 'multicheck'){

                $checked = $in_array ? ' checked="checked"' : '';
                $class = !$disable_current_item ? $class.' term_item_checkbox' : $class;

                $output .= '<div class="'.$class.'" data-id="'.$term->term_id.'" data-parent-id="'.$term->parent.'">'.$prefix;
                $output .= !$disable_current_item ? '<input type="checkbox" id="'.$args['name'].'_'.$term->{$args['term_id_name']}.'" name="'.$args['name'].'['.$term->{$args['term_id_name']}.']" value="'.$term->{$args['term_id_name']}.'"'.$checked.$conditional_attr.'><label for="'.$args['name'].'_'.$term->{$args['term_id_name']}.'">' : '';
                $output .= $term_name.$add_term_count;
                $output .= !$disable_current_item ? '</label></div>' : '</div>';

                $output .= $children;
            } else {

                $class = $disable_current_item ? 'term_item_disabled' : $args['class_item'];
                $class = $in_array ? $class.' '.$args['class_item_selected'] : $class;
                $class .= ' '.$args['class_item'].'_level_'.$args['level'];

                $output .= '<li class="'.$class.'" data-id="'.$term->{$args['term_id_name']}.'">'.$prefix.$term_name.$add_term_count.$children.'</li>';

            }
        } /// end foreach $terms_all

        if ($output && $args['view'] === 'list') {
            $add_id = $args['level'] ? '_'.$args['parent_term_id'] : '';

            if ($args['level'] === 0){

                $pre_option = $args['option_all'] && $args['option_all_title'] ? '<li class="'.$args['class_item'].' term_item_all" data-id="'.$args['option_all_value'].'">'.$args['option_all_title'].'</li>' : '';

                $output = '<ul id="'.$args['id'].$add_id.'" class="'.$args['class'].'" data-parent="'.$args['parent_term_id'].'">
        '.$pre_option.$output.'
        </ul>';

            } else {
                $output = '
           <ul id="'.$args['id'].$add_id.'" class="'.$args['class'].'_'.$args['level'].'" >
             '.$output.'
           </ul>
           ';
            }

        } elseif ($output && $args['level'] === 0){
            if($args['view'] === 'select'){

                $pre_option = $args['option_all'] && $args['option_all_title'] ? '<option value="'.$args['option_all_value'].'" class="'.$args['class_item'].'">'.$args['option_all_title'].'</option>' : '';

                $output = '<select id="'.$args['id'].'" name="'.$args['name'].'" class="'.$args['class'].'"'.$conditional_attr.'>
            '.$pre_option.$output.'
            </select>';

            } elseif ($args['view'] === 'multicheck' && $insert_in_dropdown_container){
                $output = ' <div id="'.$args['id'].'" class="'.$args['class'].'"> '.$output.' </div>';
            } else {
                $output = '<div id="'.$args['id'].'" class="'.$args['class'].'">
            '.$output.'
            </div>';
            }
        }

        return $output;
    }
    
///////////////////////////
    /**
	 * Get excerpt from post content
     * @return string
	 */
    public static function get_post_excerpt($post, $length = 25){

    $the_excerpt = !empty($post['post_excerpt']) ? $post['post_excerpt'] : $post['post_content']; //Gets post_content to be used as a basis for the excerpt
    $excerpt_length = $length; //Sets excerpt length by word count
    $the_excerpt = strip_tags(strip_shortcodes($the_excerpt), '<p><ul><li><strong><a>'); //Strips tags and images
    $words = explode(' ', $the_excerpt, $excerpt_length + 1);

    if(count($words) > $excerpt_length) :
        array_pop($words);
       // array_push($words, '…');
        $the_excerpt = implode(' ', $words).'...';
    endif;

    $the_excerpt = BABE_Functions::closetags($the_excerpt);

    return $the_excerpt;
    
    }        
            
////////////////////////////
    
}

BABE_Post_types::init();
