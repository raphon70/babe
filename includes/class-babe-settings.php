<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

BABE_Settings::init();

/**
 * BABE_Settings Class.
 * Get general settings
 * @class 		BABE_Settings
 * @version		1.2.7
 * @author 		Booking Algorithms
 */

class BABE_Settings {
    
    //// option name
    static $option_name = 'babe_settings';
    
    static $google_api_key = '';
    
    //// option menu slug
    static $option_menu_slug = 'babe-settings';
    
    //// marker urls
    static $markers_urls = array(
         1 => 'css/img/pointer_1.png',
         2 => 'css/img/pointer_2.png',
         3 => 'css/img/pointer_3.png',
         4 => 'css/img/pointer_4.png',
         5 => 'css/img/pointer_5.png',
         6 => 'css/img/pointer_6.png',
         7 => 'css/img/pointer_7.png',
         8 => 'css/img/pointer_c_1.png',
    );
    
    /////unitegallery
    public static $unitegallery = [];
    
    ///// general settings array
    public static $settings = [];
    
///////////////////////////////////////    

    public static function init() {

        $current_lang = BABE_Functions::get_current_language();

        ////// switch option name
        self::$option_name = 'babe_settings'.'_'.$current_lang;
        
        self::$settings = wp_parse_args( get_option(self::$option_name), self::get_default_settings() );
        
        self::$google_api_key = self::$settings['google_api'] ?: '';

        if ( empty(self::$settings['google_map_marker']) ){
            self::$settings['google_map_marker'] = 1;
        }
        
        if (self::$settings['view_only_uploaded_images']){
            add_action( 'pre_get_posts', array( __CLASS__, 'show_unattached_media_only') );
            add_filter( 'ajax_query_attachments_args', array( __CLASS__, 'show_current_user_attachments'), 10, 1 );
        }

        add_action( 'init', array( __CLASS__, 'init_rating_criteria') );
        
        add_action( 'init', array( __CLASS__, 'init_unitegallery_settings') );
	}

    ////////////////////////////////////
    /**
     * Get settings
     *
     * @return array
     */
    public static function get_settings(){

        return apply_filters( 'babe_get_settings', self::$settings );
    }

    ////////////////////////////////////
    /**
     * Get default settings
     *
     * @return array
     */
    public static function get_default_settings(){

        return array(
            'date_format' => 'd/m/Y',
            'zero_price_display_value' => '0',
            'results_per_page' => 10,
            'results_without_av_check' => 0,
            'results_without_av_cal' => 0,
            'posts_per_taxonomy_page' => 12,
            'results_view' => 'grid',
            'booking_obj_post_slug' => 'to_book',
            'booking_obj_post_name' => __( 'Booking Object', 'ba-book-everything' ),
            'booking_obj_post_name_general' => __( 'Booking Objects', 'ba-book-everything' ),
            'booking_obj_menu_name' => __( 'BA Book Everything', 'ba-book-everything' ),
            'attr_tax_prefix'=> 'ba_',
            'booking_obj_gutenberg'=> 0,
            'content_in_tabs'=> 0,
            'reviews_in_tabs'=> 0,
            'reviews_comment_template'=> '',
            'mpoints_active'=> 0,
            'view_only_uploaded_images' => 0,
            'unitegallery_remove' => 0,
            'av_calendar_remove' => 0,
            'av_calendar_remove_hover_prices' => 0,
            'av_calendar_max_months' => 12,
            'google_map_remove' => 0,
            'services_to_booking_form' => 1,
            'prefill_date_in_booking_form' => 0,
            'google_map_active' => 0,
            'google_api' => '',
            'google_map_start_lat' => -33.8688,
            'google_map_start_lng' => 151.2195,
            'google_map_zoom' => 13,
            'google_map_marker' => 1,
            'max_guests_select' => 10,
            'my_account_disable' => 0,
            'search_result_page' => 0,
            'services_page' => 0,
            'checkout_page' => 0,
            'confirmation_page' => 0,
            'terms_page' => 0,
            'my_account_page' => 0,
            'admin_confirmation_page' => 0,
            'checkout_add_billing_address' => 0,
            'disable_guest_bookings' => 0,
            'use_minimized_css' => 0,
            'use_minimized_js' => 0,
            'order_availability_confirm' => 'auto',
            'order_payment_processing_waiting' => 30,
            'payment_methods' => array( 0 => 'cash'),
            'currency_place' => 'left',
            'currency' => 'USD',
            'base_country' => 'US',
            'base_state' => 'CA',
            'price_thousand_separator' => '',
            'price_decimal_separator' => '.',
            'price_decimals' => 2, // number of decimals after the decimal point
            'price_from_label' => _x( 'From %s', 'item price from label', 'ba-book-everything' ),
            'shop_email' => '',
            'message_av_confirmation' => __( "Your order is waiting for the availability confirmation, you will be notified by email when it's ready. Thank you!", 'ba-book-everything' ),
            'message_not_available' => __( 'Sorry, but your selected items are not available for selected dates/times. Please, search another dates/times or items and create new order.', 'ba-book-everything' ),
            'message_payment_deferred' => __( 'Your order is completed and received, and a confirmation email was sent to you. You will pay the full amount later. Thank you!', 'ba-book-everything' ),
            'message_payment_expected' => __( 'Your order is confirmed, but not completed. To complete your order, please, click the link below to make a payment.', 'ba-book-everything' ),
            'message_payment_processing' => __( 'Your order has been confirmed and your payment is being processed. Thank you!', 'ba-book-everything' ),
            'message_payment_received' => __( 'Your order is completed, your payment has been received, and a confirmation email was sent to you. Thank you!', 'ba-book-everything' ),
            'message_draft' => __( 'Your booking has not been paid or confirmed. Follow the link to complete your booking.', 'ba-book-everything' ),
            'email_logo' => '',
            'email_header_image' => '',
            'email_footer_message' => '',
            'email_footer_credit' => '',
            'email_admin_order_updated_subject' => __('Order #%s updated', 'ba-book-everything'),
            'email_admin_order_updated_title' => __('Order updated', 'ba-book-everything'),
            'email_admin_order_updated_message' => __('Order updated. Please, find details below.', 'ba-book-everything'),
            'email_admin_new_order_subject' => __('New order #%s', 'ba-book-everything'),
            'email_admin_new_order_title' => __('New order', 'ba-book-everything'),
            'email_admin_new_order_message' => __('You have new order. Please, find details below.', 'ba-book-everything'),

            'email_admin_request_booking_subject' => __('New booking request for %1$s', 'ba-book-everything'),
            'email_admin_request_booking_title' => __('New booking request', 'ba-book-everything'),
            'email_admin_request_booking_message' => __('You have new booking request. Please, find details below.', 'ba-book-everything'),

            'email_admin_new_order_av_confirm_subject' => __('Availability request', 'ba-book-everything'),
            'email_admin_new_order_av_confirm_title' => __('New Order is waiting for confirmation', 'ba-book-everything'),
            'email_admin_new_order_av_confirm_message' => __('Please, confirm or reject this Order.', 'ba-book-everything'),
            'email_new_order_av_confirm_subject' => __('Your order #%s', 'ba-book-everything'),
            'email_new_order_av_confirm_title' => __('New Order created', 'ba-book-everything'),
            'email_new_order_av_confirm_message' => __('Hello, %s

Thank you for booking! Your Order is waiting for availability confirmation. We will send you a confirmation letter as soon as possible.', 'ba-book-everything'),
            'email_order_updated_subject' => __('Your order #%s is updated', 'ba-book-everything'),
            'email_order_updated_title' => __('Your order has been updated', 'ba-book-everything'),
            'email_order_updated_message' => __('Hello, %1$s

Your order has been updated. Please, find details below.', 'ba-book-everything'),
            'email_new_order_subject' => __('Your order #%s', 'ba-book-everything'),
            'email_new_order_title' => __('Your order has been received', 'ba-book-everything'),
            'email_new_order_message' => __('Hello, %1$s

Thank you for booking! Your order has been received.', 'ba-book-everything'),
            'email_new_order_to_pay_subject' => __('Your order is waiting for payment', 'ba-book-everything'),
            'email_new_order_to_pay_title' => __('Your order is waiting for payment', 'ba-book-everything'),
            'email_new_order_to_pay_message' => __('Hello, %1$s

Your order is confirmed, but not completed. To complete your order, click the link below to make a payment. Amount to pay is %2$s.', 'ba-book-everything'),
            'email_order_rejected_subject' => __('Selected items are not available', 'ba-book-everything'),
            'email_order_rejected_title' => __('Selected items are not available', 'ba-book-everything'),
            'email_order_rejected_message' => __('Hello, %s

Sorry, but your selected items are not available for selected dates/times. You could search another dates/times or items and create new Order.', 'ba-book-everything'),
            'email_new_customer_created_subject' => __('Your account details', 'ba-book-everything'),
            'email_new_customer_created_title' => __('Your account details', 'ba-book-everything'),
            'email_new_customer_created_message' => __('Hello, %s

Thank you for booking with us! You could use this login/password to manage your bookings:', 'ba-book-everything'),
            'email_password_reseted_subject' => __('Your password has been reset', 'ba-book-everything'),
            'email_password_reseted_title' => __('Your password has been reset.', 'ba-book-everything'),
            'email_password_reseted_message' => __('Hello, %s

Your password has been reset. You could use this new password to manage your account:', 'ba-book-everything'),
            'email_admin_order_canceled_subject' => __('Order # %1$s was canceled', 'ba-book-everything'),
            'email_admin_order_canceled_title' => __('Order has been canceled', 'ba-book-everything'),
            'email_admin_order_canceled_message' => __('The order has been canceled:', 'ba-book-everything'),
            'email_order_canceled_subject' => __('Your order was canceled', 'ba-book-everything'),
            'email_order_canceled_title' => __('Your order has been canceled', 'ba-book-everything'),
            'email_order_canceled_message' => __('Hello, %1$s

Your order has been canceled:', 'ba-book-everything'),
            'email_color_font' => '#000000',
            'email_color_background' => '#EAECED',
            'email_color_title' => '#ff4800',
            'email_color_link' => '#039be5',
            'email_color_button' => '#ff4800',
            'email_color_button_yes' => '#9acd32',
            'email_color_button_no' => '#F64020',
            'voucher_left_message' => '',
            'voucher_right_message' => '',
            'voucher_footer_message' => '',
            'rating_stars_num' => 5,
            'rating_criteria' => array(),
            'use_extended_wp_import' => 1,
        );

    }
    
///////////////////////////////////////
    /**
	 * Show only current user attachments.
     * 
     * @param array $query
     * @return
	 */
    public static function show_current_user_attachments($query) {
        
        $user_id = get_current_user_id();
        if( $user_id ) {
            $query['author'] = $user_id;
        }
        
        return $query;
    }    
    
///////////////////////////////////////
    /**
	 * Show only current post attachments.
     * 
     * @param array $query
     * @return
	 */
    public static function show_current_post_attachments($query) {
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if( $post_id ) {
            $query['post_parent'] = $post_id;
        }
        
        return $query;
    }    

///////////////////////////////////////
    /**
	 * Only show unattached madia in media library.
     * 
     * @param object $wp_query_obj
     * @return void
	 */
    public static function show_unattached_media_only($wp_query_obj) {
        
        global $current_user, $pagenow;
        
        $is_attachment_request = ($wp_query_obj->get('post_type') === 'attachment');
        
        if( $pagenow === 'upload.php' && $is_attachment_request && $current_user instanceof WP_User){
            $wp_query_obj->set( 'post_parent', 0 );
        }
    }
    
///////////////////////////////////////
    /**
	 * Init rating criteria array.
     * @return void
	 */
    public static function init_rating_criteria() {
        
        $rating_criteria_arr = (array)self::$settings['rating_criteria'];
        
        $rating_criteria_arr = apply_filters('babe_init_rating_criteria', $rating_criteria_arr);
        
        if (empty($rating_criteria_arr)){
            $rating_criteria_arr['base'] = __('Single rating','ba-book-everything');
        }
        
        self::$settings['rating_criteria'] = $rating_criteria_arr;
    }    
    
///////////////////////////////////////
    /**
	 * Get rating criteria.
     * @return array
	 */
    public static function get_rating_criteria() {
        
        return self::$settings['rating_criteria'];
    }
    
///////////////////////////////////////
    /**
	 * Get rating stars num.
     * @return int
	 */
    public static function get_rating_stars_num() {
        
        return self::$settings['rating_stars_num'];
    }            
    
///////////////////////////////////////
    /**
	 * Get active payment methods.
     * @return array
	 */
    public static function get_active_payment_methods( ?int $order_id = null ) {
        
        $payment_methods_arr = [];

        foreach(self::$settings['payment_methods'] as $ind => $method){
            if (isset(BABE_Payments::$payment_methods[$method])){
                $payment_methods_arr[$method] = BABE_Payments::$payment_methods[$method];
            }
        }

        $keys = array_keys(BABE_Payments::$payment_methods);

        uksort($payment_methods_arr, function($key1, $key2) use ($keys) {
            return ((array_search($key1, $keys) > array_search($key2, $keys)) ? 1 : -1);
        });

        return apply_filters( 'babe_get_active_payment_methods', $payment_methods_arr, $order_id );
    }
    
///////////////////////////////////////
    /**
	 * Is payment method available.
     * @param string $method
     * @return boolean
	 */
    public static function is_payment_method_available($method) {
        
        return in_array($method, self::$settings['payment_methods']);
    }
    
///////////////////////////////////////
    /**
	 * Get my account page url.
     * @param array $args
     * @return string
	 */
    public static function get_my_account_page_url($args = array()) {
        
        return BABE_Functions::get_page_url_with_args(self::$settings['my_account_page'], $args);
    }            
    
///////////////////////////////////////
    /**
	 * Get search result page url.
     * @return string
	 */
    public static function get_search_result_page_url() {
        return self::$settings['search_result_page'] ? get_permalink(self::$settings['search_result_page']) : '';
    }
    
///////////////////////////////////////
    /**
	 * Get checkout page url.
     * @param array $args
     * @return string
	 */
    public static function get_checkout_page_url($args = array()) {

        return add_query_arg( $args, get_permalink(self::$settings['checkout_page']) );
    }
    
///////////////////////////////////////
    /**
	 * Get services page url.
     * @param array $args
     * @return string
	 */
    public static function get_services_page_url($args = array()) {
        
        return add_query_arg( $args, get_permalink(self::$settings['services_page']) );
    }    
    
///////////////////////////////////////
    /**
	 * Get confirmation page url.
     * @param array $args
     * @return string
	 */
    public static function get_confirmation_page_url($args = array()) {

        return add_query_arg( $args, get_permalink(self::$settings['confirmation_page']) );
    }
    
///////////////////////////////////////
    /**
	 * Get admin confirmation page url.
     * @param array $args
     * @return string
	 */
    public static function get_admin_confirmation_page_url($args = array()) {
        
        return add_query_arg( $args, get_permalink(self::$settings['admin_confirmation_page']) );
    }    
    
///////////////////////////////////////
    /**
	 * Get terms page content.
     * @return string
	 */
    public static function get_terms_page_content() {
        
        return BABE_Functions::get_page_content(self::$settings['terms_page']);
    }
    
///////////////////////////////////////

    /**
     * Get option by name.
     * @param string $setting_name
     * @param string $default
     * @return mixed
     */
    public static function get_option($setting_name, $default = '') {
        
        return self::$settings[$setting_name] ?? $default;
    }

///////////////////////////////////////
    /**
     * Get base country
     * @return string
     */
    public static function get_base_country() {

        return apply_filters( 'babe_base_country', self::$settings['base_country'] );
    }

    /**
     * Get base state
     * @return string
     */
    public static function get_base_state() {

        return apply_filters( 'babe_base_state', self::$settings['base_state'] );
    }

///////////////////////////////////////
    /**
	 * Init unitegallery settings array.
     * @return void
	 */
    public static function init_unitegallery_settings() {
        
        $unitegallery_settings = array(
           'gallery_theme' => "default",//theme options
           'theme_panel_position' => "bottom", //top, bottom, left, right - thumbs panel position
           'theme_hide_panel_under_width' =>  480,  //hide panel under certain browser width, if null, don't hide
           'gallery_width' => 900,       //gallery width  
           'gallery_height' => 500,       //gallery height
           'gallery_min_width' =>  320,      //gallery minimal width when resizing
           'gallery_min_height' =>  300,     //gallery minimal height when resizing
           'gallery_skin' => "default",      //default, alexis etc... - the global skin of the gallery. Will change all gallery items by default.
           'gallery_images_preload_type' => "minimal",  //all , minimal , visible - preload type of the images.
                //minimal - only image nabours will be loaded each time.
                //visible - visible thumbs images will be loaded each time.
                //all - load all the images first time.
           'gallery_autoplay' => true,      //true / false - begin slideshow autoplay on start
           'gallery_play_interval' =>  7000,    //play interval of the slideshow
           'gallery_pause_on_mouseover' =>  true,   //true,false - pause on mouseover when playing slideshow true/false
           'gallery_control_thumbs_mousewheel' => false, //true,false - enable / disable the mousewheel
           'gallery_control_keyboard' =>  true,    //true,false - enable / disble keyboard controls
           'gallery_carousel' => true,      //true,false - next button on last image goes to first image.
           'gallery_preserve_ratio' =>  true,    //true, false - preserver ratio when on window resize
           'gallery_debug_errors' => true,     //show error message when there is some error on the gallery area.
           'gallery_background_color' =>  "",    //set custom background color. If not set it will be taken from css.
           'slider_scale_mode' =>  "fill",     //fit =>  scale down and up the image to always fit the slider
                //down =>  scale down only, smaller images will be shown, don't enlarge images (scale up)
                //fill =>  fill the entire slider space by scaling, cropping and centering the image
           'slider_scale_mode_media' =>  "fill",   //fit, down, full scale mode on media items
           'slider_scale_mode_fullscreen' =>  "down",  //fit, down, full scale mode on fullscreen.
           'slider_item_padding_top' =>  0,     //padding top of the slider item
           'slider_item_padding_bottom' =>  0,    //padding bottom of the slider item
           'slider_item_padding_left' =>  0,    //padding left of the slider item
           'slider_item_padding_right' =>  0,    //padding right of the slider item
           'slider_transition' =>  "slide",     //fade, slide - the transition of the slide change
           'slider_transition_speed' => 1500,    //transition duration of slide change
           'slider_transition_easing' =>  "easeInOutQuad", //transition easing function of slide change
           'slider_control_swipe' => true,     //true,false - enable swiping control
           'slider_control_zoom' => true,     //true, false - enable zooming control
           'slider_zoom_max_ratio' =>  6,     //max zoom ratio
           'slider_loader_type' =>  1,      //shape of the loader (1-7)
           'slider_loader_color' => "white",    //color of the loader =>  (black , white)
           'slider_enable_bullets' =>  false,    //enable the bullets onslider element
           'slider_bullets_skin' =>  "",     //skin of the bullets, if empty inherit from gallery skin
           'slider_bullets_space_between' =>  -1,   //set the space between bullets. If -1 then will be set default space from the skins
           'slider_bullets_align_hor' => "center",   //left, center, right - bullets horizontal align
           'slider_bullets_align_vert' => "bottom",   //top, middle, bottom - bullets vertical algin
           'slider_bullets_offset_hor' => 0,    //bullets horizontal offset 
           'slider_bullets_offset_vert' => 10,    //bullets vertical offset
           'slider_enable_arrows' =>  true,     //enable arrows onslider element
           'slider_arrows_skin' =>  "",      //skin of the slider arrows, if empty inherit from gallery skin
           'slider_arrow_left_align_hor' => "left",     //left, center, right - left arrow horizonal align
           'slider_arrow_left_align_vert' => "middle",   //top, middle, bottom - left arrow vertical align
           'slider_arrow_left_offset_hor' => 20,     //left arrow horizontal offset
           'slider_arrow_left_offset_vert' => 0,     //left arrow vertical offset
           'slider_arrow_right_align_hor' => "right",    //left, center, right - right arrow horizontal algin
           'slider_arrow_right_align_vert' => "middle",  //top, middle, bottom - right arrow vertical align
           'slider_arrow_right_offset_hor' => 20,      //right arrow horizontal offset 
           'slider_arrow_right_offset_vert' => 0,      //right arrow vertical offset
           'slider_enable_progress_indicator' =>  false,   //enable progress indicator element
           'slider_progress_indicator_type' =>  "pie",   //pie, pie2, bar (if pie not supported, it will switch to bar automatically)
           'slider_progress_indicator_align_hor' => "left",  //left, center, right - progress indicator horizontal align
           'slider_progress_indicator_align_vert' => "top",  //top, middle, bottom - progress indicator vertical align
           'slider_progress_indicator_offset_hor' => 16,  //progress indicator horizontal offset 
           'slider_progress_indicator_offset_vert' => 36,  //progress indicator vertical offset
           'slider_progressbar_color' => "#ffffff",    //progress bar color
           'slider_progressbar_opacity' =>  0.6,    //progress bar opacity
           'slider_progressbar_line_width' =>  5,    //progress bar line width
           'slider_progresspie_type_fill' =>  false,   //false is stroke, true is fill - the progress pie type, stroke of fill
           'slider_progresspie_color1' =>  "#B5B5B5",    //the first color of the progress pie
           'slider_progresspie_color2' =>  "#E5E5E5",   //progress pie second color 
           'slider_progresspie_stroke_width' =>  6,    //progress pie stroke width 
           'slider_progresspie_width' =>  30,     //progess pie width
           'slider_progresspie_height' => 30,     //progress pie height
           'slider_enable_play_button' =>  true,    //true,false - enable play / pause button onslider element
           'slider_play_button_skin' =>  "",     //skin of the slider play button, if empty inherit from gallery skin
           'slider_play_button_align_hor' => "left",      //left, center, right - play button horizontal align
           'slider_play_button_align_vert' => "top",         //top, middle, bottom - play button vertical align
           'slider_play_button_offset_hor' => 40,          //play button horizontal offset 
           'slider_play_button_offset_vert' => 8,       //play button vertical offset
           'slider_enable_fullscreen_button' =>  true,   //true,false - enable fullscreen button onslider element
           'slider_fullscreen_button_skin' =>  "",    //skin of the slider fullscreen button, if empty inherit from gallery skin
           'slider_fullscreen_button_align_hor' => "left",   //left, center, right - fullscreen button horizonatal align
           'slider_fullscreen_button_align_vert' => "top",   //top, middle, bottom - fullscreen button vertical align
           'slider_fullscreen_button_offset_hor' => 11,      //fullscreen button horizontal offset 
           'slider_fullscreen_button_offset_vert' => 9,      //fullscreen button vertical offset
           'slider_enable_zoom_panel' =>  true,     //true,false - enable the zoom buttons, works together with zoom control.
           'slider_zoompanel_skin' =>  "",      //skin of the slider zoom panel, if empty inherit from gallery skin    
           'slider_zoompanel_align_hor' => "right",       //left, center, right - zoom panel horizontal align
           'slider_zoompanel_align_vert' => "top",         //top, middle, bottom - zoom panel vertical align
           'slider_zoompanel_offset_hor' => 12,           //zoom panel horizontal offset 
           'slider_zoompanel_offset_vert' => 10,          //zoom panel vertical offset
           'slider_controls_always_on' =>  true,       //true,false - controls are always on, false - show only on mouseover
           'slider_controls_appear_ontap' =>  true,    //true,false - appear controls on tap event on touch devices
           'slider_controls_appear_duration' =>  300,   //the duration of appearing controls
           'slider_videoplay_button_type' =>  "square",    //square, round - the videoplay button type, square or round 
           'slider_enable_text_panel' =>  false,    //true,false - enable the text panel
           'slider_textpanel_always_on' =>  true,    //true,false - text panel are always on, false - show only on mouseover
           'slider_textpanel_text_valign' => "middle",   //middle, top, bottom - text vertical align
           'slider_textpanel_padding_top' => 10,    //textpanel padding top 
           'slider_textpanel_padding_bottom' => 10,    //textpanel padding bottom
           'slider_textpanel_height' =>  null,     //textpanel height. if null it will be set dynamically
           'slider_textpanel_padding_title_description' =>  5, //the space between the title and the description
           'slider_textpanel_padding_right' =>  11,    //cut some space for text from right
           'slider_textpanel_padding_left' =>  11,    //cut some space for text from left
           'slider_textpanel_fade_duration' =>  200,   //the fade duration of textpanel appear
           'slider_textpanel_enable_title' =>  true,   //enable the title text
           'slider_textpanel_enable_description' =>  true,  //enable the description text
           'slider_textpanel_enable_bg' =>  true,    //enable the textpanel background
           'slider_textpanel_bg_color' => "#000000",   //textpanel background color
           'slider_textpanel_bg_opacity' =>  0.4,    //textpanel background opacity
           'slider_textpanel_title_color' => null,    //textpanel title color. if null - take from css
           'slider_textpanel_title_font_family' => null,  //textpanel title font family. if null - take from css
           'slider_textpanel_title_text_align' => null,   //textpanel title text align. if null - take from css
           'slider_textpanel_title_font_size' => null,   //textpanel title font size. if null - take from css
           'slider_textpanel_title_bold' => null,    //textpanel title bold. if null - take from css
           'slider_textpanel_desc_color' => null,    //textpanel description font color. if null - take from css
           'slider_textpanel_desc_font_family' => null,   //textpanel description font family. if null - take from css
           'slider_textpanel_desc_text_align' => null,   //textpanel description text align. if null - take from css
           'slider_textpanel_desc_font_size' => null,   //textpanel description font size. if null - take from css
           'slider_textpanel_desc_bold' => null,    //textpanel description bold. if null - take from css
           'thumb_width' => 88,        //thumb width
           'thumb_height' => 50,       //thumb height
           'thumb_fixed_size' => true,      //true,false - fixed/dynamic thumbnail width
           'thumb_border_effect' => true,     //true, false - specify if the thumb has border
           'thumb_border_width' =>  0,      //thumb border width
           'thumb_border_color' =>  "#000000",    //thumb border color
           'thumb_over_border_width' =>  0,     //thumb border width in mouseover state
           'thumb_over_border_color' =>  "#d9d9d9",   //thumb border color in mouseover state
           'thumb_selected_border_width' =>  1,    //thumb width in selected state
           'thumb_selected_border_color' =>  "#d9d9d9",  //thumb border color in selected state
           'thumb_round_corners_radius' => 0,    //thumb border radius
           'thumb_color_overlay_effect' =>  true,   //true,false - thumb color overlay effect, release the overlay on mouseover and selected states
           'thumb_overlay_color' =>  "#000000",    //thumb overlay color
           'thumb_overlay_opacity' =>  0.4,     //thumb overlay color opacity
           'thumb_overlay_reverse' => false,    //true,false - reverse the overlay, will be shown on selected state only
           'thumb_image_overlay_effect' =>  false,   //true,false - images overlay effect on normal state only
           'thumb_image_overlay_type' =>  "bw",    //bw , blur, sepia - the type of image effect overlay, black and white, sepia and blur.
           'thumb_transition_duration' =>  200,    //thumb effect transition duration
           'thumb_transition_easing' =>  "easeOutQuad",  //thumb effect transition easing
           'thumb_show_loader' => true,      //show thumb loader while loading the thumb
           'thumb_loader_type' => "dark",     //dark, light - thumb loader type
           'strippanel_padding_top' => 8,     //space from top of the panel
           'strippanel_padding_bottom' => 8,    //space from bottom of the panel
           'strippanel_padding_left' =>   0,    //space from left of the panel
           'strippanel_padding_right' =>  0,    //space from right of the panel
           'strippanel_enable_buttons' =>  false,   //enable buttons from the sides of the panel
           'strippanel_buttons_skin' =>  "",    //skin of the buttons, if empty inherit from gallery skin
           'strippanel_padding_buttons' =>  2,    //padding between the buttons and the panel
           'strippanel_buttons_role'  =>  "scroll_strip",   // scroll_strip, advance_item - the role of the side buttons
           'strippanel_enable_handle' =>  true,    //enable grid handle   
           'strippanel_handle_align' =>  "top",    //top, middle, bottom , left, right, center - close handle tip align on the handle bar according panel orientation
           'strippanel_handle_offset' =>  0,    //offset of handle bar according the valign
           'strippanel_handle_skin' =>  "",     //skin of the handle, if empty inherit from gallery skin
           'strippanel_background_color' => "",    //background color of the strip wrapper, if not set, it will be taken from the css
           'strip_thumbs_align' =>  "left",     //left, center, right, top, middle, bottom - the align of the thumbs when they smaller then the strip size.
           'strip_space_between_thumbs' => 6,    //space between thumbs
           'strip_thumb_touch_sensetivity' => 15,     //from 1-100, 1 - most sensetive, 100 - most unsensetive
           'strip_scroll_to_thumb_duration' => 500,   //duration of scrolling to thumb
           'strip_scroll_to_thumb_easing' => "easeOutCubic", //easing of scrolling to thumb animation
           'strip_control_avia' => true,     //avia control - move the strip according strip moseover position
           'strip_control_touch' => true,     //touch control - move the strip by dragging it
        );
        
        self::$unitegallery = apply_filters('babe_init_unitegallery_settings', $unitegallery_settings);       
        //// sanitize
        self::$unitegallery['gallery_theme'] = isset(self::$unitegallery['gallery_theme']) && in_array(self::$unitegallery['gallery_theme'], array('carousel', 'compact', 'default', 'grid', 'slider', 'tiles', 'tilesgrid', 'video')) ? self::$unitegallery['gallery_theme'] : 'compact';
    }
    
///////////////////////////////////////
}