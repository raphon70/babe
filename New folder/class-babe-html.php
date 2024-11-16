<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_html Class.
 * 
 * @class 		BABE_html
 * @version		1.3.9
 * @author 		Booking Algorithms
 */

class BABE_html {
    
    // variables to use
    public static $nonce_title = 'babe-nonce';
    
    //// cache
    
    private static $order_items = array();
    
    private static $order_customer_details = array();
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {
        
        add_filter( 'the_content', array( __CLASS__, 'post_content'), 10, 1 );
        add_filter( 'the_content', array( __CLASS__, 'pages_content'), 10, 1 );
        add_filter( 'the_content', array( __CLASS__, 'page_search_result'), 10, 1 );
        
        add_action( 'init', array(__CLASS__, 'action_init'), 10);
        
        add_filter( 'babe_search_result_description', array( __CLASS__, 'search_result_description_price'), 10, 2);
        
        add_filter( 'babe_post_content', array( __CLASS__, 'babe_post_content'), 10, 3);
        add_filter( 'babe_post_content_before_tabs', array( __CLASS__, 'babe_post_content_block_slider'), 20, 3);
        add_filter( 'babe_post_content_before_tabs', array( __CLASS__, 'babe_post_content_item_code'), 30, 3);
        add_filter( 'babe_post_content_before_tabs', array( __CLASS__, 'babe_post_content_star_rating'), 40, 3);
        add_filter( 'babe_post_content_before_tabs', array( __CLASS__, 'babe_post_content_price_from'), 60, 3);
        add_filter( 'babe_post_content_before_tabs', array( __CLASS__, 'babe_post_content_event_date'), 50, 3);
        add_filter( 'babe_post_content_tabs', array( __CLASS__, 'babe_post_content_tabs'), 10, 3);
        add_filter( 'babe_post_content_after_tabs', array( __CLASS__, 'babe_post_content_block_related'), 20, 3);
        
        add_filter('babe_checkout_field_required', array( __CLASS__, 'checkout_field_required'), 10, 2);
        
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );
        
        add_filter( 'script_loader_tag', array( __CLASS__, 'async_defer_scripts'), 20, 3);
        
        add_action( 'wp_ajax_get_meeting_points', array( __CLASS__, 'ajax_get_meeting_points'));
        add_action( 'wp_ajax_nopriv_get_meeting_points', array( __CLASS__, 'ajax_get_meeting_points'));
        
        add_action( 'wp_ajax_get_times_guests', array( __CLASS__, 'ajax_get_times_guests'));
        add_action( 'wp_ajax_nopriv_get_times_guests', array( __CLASS__, 'ajax_get_times_guests'));

        add_action( 'wp_ajax_get_services', array( __CLASS__, 'ajax_get_services'));
        add_action( 'wp_ajax_nopriv_get_services', array( __CLASS__, 'ajax_get_services'));

        add_action( 'wp_ajax_get_booking_times', array( __CLASS__, 'ajax_get_booking_times'));
        add_action( 'wp_ajax_nopriv_get_booking_times', array( __CLASS__, 'ajax_get_booking_times'));
        
        add_action( 'wp_ajax_booking_calculate', array( __CLASS__, 'ajax_booking_calculate'));
        add_action( 'wp_ajax_nopriv_booking_calculate', array( __CLASS__, 'ajax_booking_calculate'));
        
        // apply coupon to order
        add_action( 'wp_ajax_apply_coupon_to_order', array( __CLASS__, 'ajax_apply_coupon_to_order'));
        add_action( 'wp_ajax_nopriv_apply_coupon_to_order', array( __CLASS__, 'ajax_apply_coupon_to_order'));
        add_action( 'wp_ajax_remove_coupon_from_order', array( __CLASS__, 'ajax_remove_coupon_from_order'));
        add_action( 'wp_ajax_nopriv_remove_coupon_from_order', array( __CLASS__, 'ajax_remove_coupon_from_order'));

        // ajax_checkout_payment_method_changed
        add_action( 'wp_ajax_checkout_payment_method_changed', array( __CLASS__, 'ajax_checkout_payment_method_changed'));
        add_action( 'wp_ajax_nopriv_checkout_payment_method_changed', array( __CLASS__, 'ajax_checkout_payment_method_changed'));
        
        // add coupon field
        add_filter('babe_checkout_after_order_items', array( __CLASS__, 'coupon_field_to_checkout_form' ), 10, 2);
        add_filter('babe_order_items_html', array( __CLASS__, 'coupon_field_to_order_items' ), 10, 4);

        //extended guest fields
        add_filter('babe_checkout_after_contact_fields', array( __CLASS__, 'extended_guest_fields' ), 10, 2);

        //add billing address fields
        add_filter('babe_checkout_after_contact_fields', array( __CLASS__, 'add_address_fields' ), 20, 2);
	}  
    
//////////////////////////////
    /**
	 * Hook in init
	 */
    public static function action_init() {

        if ( wp_doing_ajax() ){
            $current_language = BABE_Functions::get_current_language();
            if ( isset($_GET['lang']) && $current_language != $_GET['lang'] ){
                do_action( 'wpml_switch_language', substr( sanitize_text_field($_GET['lang']), 0, 5) );
            }
        }
       
       if (function_exists('qtranxf_useCurrentLanguageIfNotFoundShowAvailable')){
          // qTranslate-x priority fix
          remove_filter('the_content', 'qtranxf_useCurrentLanguageIfNotFoundShowAvailable', 100);
          add_filter('the_content', 'qtranxf_useCurrentLanguageIfNotFoundShowAvailable', 0);
        }
    }        
    
//////////////////////////////
    /**
	 * Enqueue assets.
	 */
    public static function wp_enqueue_scripts() {

        global $post;
        
        wp_enqueue_script( 'babe-select2-js', plugins_url( "js/select2.full.min.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
        
        ////////validation js////////
        wp_enqueue_script( 'babe-validate-js', plugins_url( "js/jquery.validate.min.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
        
        wp_enqueue_script( 'babe-util-js', plugins_url( "js/util.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
        wp_enqueue_script( 'babe-modal-js', plugins_url( "js/babe-modal.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
        wp_enqueue_script( 'babe-modal-adv-js', plugins_url( "js/modal.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );

        wp_enqueue_script('moment');

        if ( BABE_Settings::$settings['google_map_active'] ){
            ///// add google API
            $script = '(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
					key:"' . BABE_Settings::$google_api_key . '",
					v:"weekly",
					libraries:"places,marker,routes"
				});';

            wp_add_inline_script( 'moment', $script );
        }

        wp_enqueue_script( 'babe-daterangepicker-js', plugins_url( "js/daterangepicker.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );

	    /// ion.rangeSlider for price bar in search form
	    wp_enqueue_script( 'babe-ion-rangeslider-js', plugins_url( "js/ion.rangeSlider/ion.rangeSlider.min.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
	    wp_enqueue_style( 'babe-ion-rangeslider-css', plugins_url( "js/ion.rangeSlider/ion.rangeSlider.min.css", BABE_PLUGIN ), [], BABE_VERSION);

     if ( empty(BABE_Settings::$settings['use_minimized_js']) ){
        
        wp_enqueue_script( 'babe-js', plugins_url( "js/babe-scripts.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
        
     } else {
        
        wp_enqueue_script( 'babe-js', plugins_url( "js/babe-scripts.min.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
     }
     
     wp_enqueue_style( 'babe-daterangepicker-style', plugins_url( "css/daterangepicker.css", BABE_PLUGIN ), [], BABE_VERSION);
     
     wp_enqueue_style( 'babe-select2-style', plugins_url( "css/select2.min.css", BABE_PLUGIN ), [], BABE_VERSION);

     /////// i18n for validation ////////////
     if (function_exists( 'qtranxf_getLanguage' )){
        $lang = qtranxf_getLanguage();
    } else {
        $lang = substr( get_locale(), 0, 2 );
    }
    $local_validate_langs = array('ar' => 'ar', 'az' => 'az', 'bg' => 'bg', 'bn' => 'bn_BD','ca' => 'ca', 'cs' => 'cs', 'da' => 'da', 'de' => 'de', 'el' => 'el', 'es' => 'es', 'et' => 'et', 'eu' => 'eu', 'fa' => 'fa', 'fi' => 'fi', 'fr' => 'fr', 'ge' => 'ge', 'gl' => 'gl', 'he' => 'he', 'hr' => 'hr', 'hu' => 'hu', 'hy' => 'hy_AM', 'id' => 'id', 'is' => 'is', 'it' => 'it', 'ja' => 'ja', 'ka' => 'ka', 'kk' => 'kk', 'ko' => 'ko', 'lt' => 'lt', 'lv' => 'lv', 'mk' => 'mk', 'my' => 'my', 'nl' => 'nl', 'no' => 'no', 'pl' => 'pl', 'pt' => 'pt_PT', 'ro' => 'ro', 'ru' => 'ru', 'sd' => 'sd', 'si' => 'si', 'sk' => 'sk', 'sl' => 'sl', 'sr' => 'sr', 'sv' => 'sv', 'th' => 'th', 'tj' => 'tj', 'tr' => 'tr', 'uk' => 'uk', 'ur' => 'ur', 'vi' => 'vi', 'zh' => 'zh');
     
     if( isset($local_validate_langs[$lang]) ){

         wp_enqueue_script(
             'babe-validate-local-js',
             plugins_url( "js/localization/messages_".$local_validate_langs[$lang].".min.js", BABE_PLUGIN ),
             array('jquery'),
             BABE_VERSION,
             true
         );
     }
     ///////////////
     
     wp_enqueue_style( 'babe-modal-style', plugins_url( "css/babe-modal.css", BABE_PLUGIN ), [], BABE_VERSION);
     
     if (is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type){

         /////////////unitegallery/////////////
         if (!BABE_Settings::$settings['unitegallery_remove']){
             wp_enqueue_style( 'babe-unitegallery-styles', plugins_url('js/unitegallery/css/unite-gallery.css', BABE_PLUGIN ), [], BABE_VERSION);
             wp_enqueue_style( 'babe-unitegallery-default-styles', plugins_url('js/unitegallery/themes/default/ug-theme-default.css', BABE_PLUGIN ), array('babe-unitegallery-styles'), BABE_VERSION);

             wp_enqueue_style( 'babe-unitegallery-mediaelementplayer-styles', plugins_url('js/unitegallery/css/mediaelementplayer.min.css', BABE_PLUGIN ), [], BABE_VERSION);

             wp_enqueue_script('wp-mediaelement');

             wp_enqueue_script( 'babe-unitegallery-js', plugins_url('js/unitegallery/js/unitegallery.js', BABE_PLUGIN ), array('jquery'), BABE_VERSION, true);

             $unitegallery_theme = BABE_Settings::$unitegallery['gallery_theme'];

             wp_enqueue_script( 'babe-unitegallery-'.$unitegallery_theme.'-js', plugins_url('js/unitegallery/themes/'.$unitegallery_theme.'/ug-theme-'.$unitegallery_theme.'.js', BABE_PLUGIN ), array('babe-unitegallery-js'), BABE_VERSION, true);
         }
         ///////////////////////////////////////

         $post_id = $post->ID;
         $av_cal = BABE_Calendar_functions::get_av_cal($post_id);
         $first_av_date = key($av_cal);
         $first_av_date_obj = $first_av_date ? new DateTime($first_av_date) : new DateTime();

         $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);
         $basic_booking_period = $rules_cat['rules']['basic_booking_period'] ?? '';
         $cal_first_click = !in_array($basic_booking_period, ['recurrent_custom', 'single_custom', 'hour',]);
         $cal_first_click = apply_filters( 'babe_localize_script_calendar_clicks', $cal_first_click, $post_id, $rules_cat);
            
     } else {
         $av_cal = array();
         $first_av_date_obj = new DateTime();
         $cal_first_click = false;
         $basic_booking_period = '';
     }  ///// end if (is_single() && $post->post_type == BABE_Post_types::$booking_obj_post_type)

        $date_from = isset($_GET['date_from']) && BABE_Calendar_functions::isValidDate($_GET['date_from'], BABE_Settings::$settings['date_format']) ? $_GET['date_from'] : null;
        $date_to = isset($_GET['date_to']) && BABE_Calendar_functions::isValidDate($_GET['date_to'], BABE_Settings::$settings['date_format']) ? $_GET['date_to'] : null;

        $time_from = isset($_GET['time_from']) && BABE_Calendar_functions::isValidTime($_GET['time_from'], 'H:i') ? $_GET['time_from'] : null;
        $time_to = isset($_GET['time_to']) && BABE_Calendar_functions::isValidTime($_GET['time_to'], 'H:i') ? $_GET['time_to'] : null;
        $guests = isset($_GET['guests']) ? array_map('absint', $_GET['guests']) : array();
        if ( !empty($guests) ){
            $main_age_id = BABE_Post_types::get_main_age_id();
            if ( empty($guests[0]) ){
                $guests[0] = array_sum($guests);
            } elseif ( count($guests) === 1 ){
                $guests[$main_age_id] = $guests[0];
            }
        }

        if ($date_from !== null){
            if ( $date_to === null) {
                $date_to = $date_from;
            }
            if ( empty($av_cal) || empty($av_cal[BABE_Calendar_functions::date_to_sql($date_from)]['start_day'])){
                $date_from = null;
                $date_to = null;
            }
        }

        if (
            BABE_Settings::$settings['prefill_date_in_booking_form']
            && $first_av_date_obj !== null
            && $date_from === null
        ){
            $date_from = $first_av_date_obj->format(BABE_Settings::$settings['date_format']);
            $date_to = $date_from;
        }
     
        $travel_mode_html = '
               <div id="travel_mode_panel_modal">
                <label for="travel_mode_modal">'.__('Mode of Travel: ', 'ba-book-everything').'</label>
                <select id="travel_mode_modal" name="travel_mode_modal">
                <option value="WALKING">'.__('Walking', 'ba-book-everything').'</option>
                <option value="DRIVING">'.__('Driving', 'ba-book-everything').'</option>
                <option value="BICYCLING">'.__('Bicycling', 'ba-book-everything').'</option>
                </select>
               </div>
               
                <input class="address-autocomplete" name="autocomplete" placeholder="'.__( 'Enter your address', 'ba-book-everything' ).'" type="text" />
                ';

        $max_av_cal_date_obj = new DateTime();
        $max_av_cal_date_obj->modify('+'. absint(BABE_Settings::$settings['av_calendar_max_months']) . ' months');
        $max_av_cal_date = $max_av_cal_date_obj->format(BABE_Settings::$settings['date_format']);
        $min_av_cal_date = $first_av_date_obj->format(BABE_Settings::$settings['date_format']);

        $current_language = BABE_Functions::get_current_language();
        $current_language = apply_filters( 'wpml_current_language', $current_language );

        ////// localize babe-js
        wp_localize_script( 'babe-js', 'babe_lst', array(
                'ajax_url' => admin_url( 'admin-ajax.php?lang='.$current_language ),
                'date_format' => BABE_Settings::$settings['date_format'] === 'd/m/Y' ? 'dd/mm/yy' : 'mm/dd/yy',
                'drp_date_format' => BABE_Settings::$settings['date_format'] === 'd/m/Y' ? 'DD/MM/YYYY' : 'MM/DD/YYYY',
                'date_from' => $date_from,
                'date_to' => $date_to,
                'time_from' => $time_from,
                'time_to' => $time_to,
                'guests' => $guests,
                'nonce' => wp_create_nonce(self::$nonce_title),
                'av_cal' => $av_cal,
                'min_av_cal_date' => $min_av_cal_date,
                'max_av_cal_date' => $max_av_cal_date,
                'basic_booking_period' => $basic_booking_period,
                'cal_first_click' => $cal_first_click,
                'google_map_active' => (int) BABE_Settings::$settings['google_map_active'],
                'start_lat' => BABE_Settings::$settings['google_map_start_lat'],
                'start_lng' => BABE_Settings::$settings['google_map_start_lng'],
                'start_zoom' => BABE_Settings::$settings['google_map_zoom'],
                'marker_icon' => plugins_url( BABE_Settings::$markers_urls[BABE_Settings::$settings['google_map_marker']], BABE_PLUGIN ),
                'travel_mode_html' => $travel_mode_html,
                'messages' => [
                    'fill_in_all_data' => __('Please fill in all the data.', 'ba-book-everything'),
                    'minimum_guests_is' => _x('Minimum number of guests: ', 'booking form', 'ba-book-everything'),
                    'maximum_guests_is' => _x('Maximum number of guests: ', 'booking form', 'ba-book-everything'),
                    'select2select' => __('Select...', 'ba-book-everything'),
                ],
                'unitegallery_args' => BABE_Settings::$unitegallery,
                'daterangepickerLocale' => [
                    "applyLabel" => _x('Apply', 'daterangepicker', 'ba-book-everything'),
                    "cancelLabel" => _x('Cancel', 'daterangepicker', 'ba-book-everything'),
                    "fromLabel" => _x('From', 'daterangepicker', 'ba-book-everything'),
                    "toLabel" => _x('To', 'daterangepicker', 'ba-book-everything'),
                    "customRangeLabel" => _x('Custom', 'daterangepicker', 'ba-book-everything'),
                    "weekLabel" => _x('W', 'daterangepicker', 'ba-book-everything'),
                    "daysOfWeek" => BABE_Calendar_functions::get_week_days_arr_2_from_su(),
                    "monthNames" => BABE_Calendar_functions::get_months_arr(),
                    "firstDay" => BABE_Calendar_functions::$week_sunday ? 1 : 0,
                ],
                'states' => BABE_Locales::states(),
            )
        );
     
        ///// load datepicker
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-style', plugins_url( "css/jquery-ui.min.css", BABE_PLUGIN ), array(), BABE_VERSION, 'all');
        wp_localize_jquery_ui_datepicker();
        ///
        wp_enqueue_style('babe-fontawesome', plugins_url( "fonts/fontawesome-free/css/all.min.css", BABE_PLUGIN ), array(), BABE_VERSION, 'all');
      
        //// load plugin general styles
        if ( empty(BABE_Settings::$settings['use_minimized_css']) ){
            wp_enqueue_style( 'babe-style', plugins_url( "css/babe-style.css", BABE_PLUGIN ), [], BABE_VERSION);
        } else {
            wp_enqueue_style( 'babe-style', plugins_url( "css/babe-style.min.css", BABE_PLUGIN ), [], BABE_VERSION);
        }
     }

    /**
     * Add billing address fields to the checkout form
     *
     * @param string $output
     * @param array $args
     * @return string
     */
     public static function add_address_fields( $output, $args ){

         if ( !BABE_Settings::$settings['checkout_add_billing_address'] ){
             return $output;
         }

         $countries = BABE_Locales::countries();
         $states = BABE_Locales::states();

         $country_options = '<option value>'.__( 'Select...', 'ba-book-everything' ).'</option>';

         $country = !empty($args['meta']['billing_address']['country']) ? $args['meta']['billing_address']['country'] : '';
         $state = !empty($args['meta']['billing_address']['state']) ? $args['meta']['billing_address']['state'] : '';
         $city = !empty($args['meta']['billing_address']['city']) ? $args['meta']['billing_address']['city'] : '';
         $address = !empty($args['meta']['billing_address']['address']) ? $args['meta']['billing_address']['address'] : '';

         foreach( $countries as $country_code => $country_name){
             $country_options .= '<option value="'.esc_attr($country_code).'" '.selected($country,$country_code,false).'>'.esc_html($country_name).'</option>';
         }

         $state_options = '<option value>---</option>';
         $state_options_arr = $country && !empty($states[$country]) ? $states[$country] : [];

         foreach( $state_options_arr as $state_code => $state_name){
             $state_options .= '<option value="'.esc_attr($state_code).'" '.selected($state,$state_code,false).'>'.esc_html($state_name).'</option>';
         }

         $output .= '<h3>'. __( 'Billing address', 'ba-book-everything' ) .'</h3>';

         $output .= '<div class="address_fields_group input_group">
                        <div class="checkout-form-block checkout_select_block">
                          <div class="checkout_select_title">
				           <div class="checkout_select_wrapper checkout_form_input_field_content">
				               <label class="checkout_form_input_label">' . __( 'Country', 'ba-book-everything' ) . '</label>
							   <select class="checkout_select_field select2" name="billing_address[country]" id="billing_address_country" required="required">'.$country_options.'</select>
						   </div>
						  </div>
				        </div>
				        
                        <div class="checkout-form-block checkout_select_block">
                          <div class="checkout_select_title">
				           <div class="checkout_select_wrapper checkout_form_input_field_content">
				               <label class="checkout_form_input_label">' . __( 'State', 'ba-book-everything' ) . '</label>
							   <select class="checkout_select_field select2_state" name="billing_address[state]" id="billing_address_state">'.$state_options.'</select>
						   </div>
						  </div>
				        </div>
				        
				        <div class="checkout-form-block">
				           <div class="checkout_form_input_field '.(!empty($city) ? 'checkout_form_input_field_content' : '').'">
				               <label class="checkout_form_input_label">' . __( 'City', 'ba-book-everything' ) . '</label>
							   <input type="text" class="checkout_input_field" name="billing_address[city]" id="billing_address_city" value="'.esc_attr($city).'" required="required">
				               <div class="checkout_form_input_underline"><span class="checkout_form_input_ripple"></span></div>
						   </div>
				        </div>
				        <div class="checkout-form-block">
				           <div class="checkout_form_input_field '.(!empty($address) ? 'checkout_form_input_field_content' : '').'">
				               <label class="checkout_form_input_label">' . __( 'Address', 'ba-book-everything' ) . '</label>
							   <input type="text" class="checkout_input_field" name="billing_address[address]" id="billing_address_address" value="'.esc_attr($address).'" required="required">
				               <div class="checkout_form_input_underline"><span class="checkout_form_input_ripple"></span></div>
						   </div>
				        </div>
				      
				      </div>';

         return $output;
     }

//////////////////////////////
    /**
     * Add extra guest details to the checkout form
     *
     * @param string $output
     * @param array $args
     * @return string
     */
     public static function extended_guest_fields( $output, $args ){

    	if( empty($args['order_id']) ){
		    return $output;
	    }

         $order_items_arr = BABE_Order::get_order_items($args['order_id']);

         foreach ($order_items_arr as $order){

             $ages_arr = BABE_Post_types::get_post_ages( $order['booking_obj_id'] );

             $total_number_of_guests = array_sum($order['meta']['guests']);

             $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($order['booking_obj_id']);

             if ( empty($rules_cat['category_meta']['categories_other_guests']) || $total_number_of_guests < 2 ) {
                 continue;
             }

             $required = !empty($rules_cat['category_meta']['categories_other_guests_mandatory']) ? ' required="required"' : '';

             $extra_guests = !empty($args['meta']['extra_guests']) ? $args['meta']['extra_guests'] : [];

             for ($i = 0; $i <= ($total_number_of_guests - 2); $i++){

                 $age_group = isset($extra_guests[$i]['age_group']) ? (int)$extra_guests[$i]['age_group'] : 0;
                 $age_group_value = '';

                 $age_dropdown = '';
                 if ( count($ages_arr) > 1 ){
                     foreach ( $ages_arr as $age ){
                         $age_group_value = (int)$age['age_id'] === $age_group ? $age['description'] : $age_group_value;
                         $term_item_selected = (int)$age['age_id'] === $age_group ? 'term_item_selected' : '';
                         $age_dropdown .= '<li class="term_item '.$term_item_selected.'" data-value="' . $age['description'] . '" data-id="' . $age['age_id'] . '" >' . $age['description'] . '</li>';
                     }
                 }

                 $first_name = !empty($extra_guests[$i]['first_name']) ? $extra_guests[$i]['first_name'] : '';
                 $last_name = !empty($extra_guests[$i]['last_name']) ? $extra_guests[$i]['last_name'] : '';

                 $output .= '<h4>'. sprintf(__( 'Extra guest %s', 'ba-book-everything' ), ($i + 2) ) .'</h4>';
                 $output .= '<div class="additional_fields_group input_group">';
                 $output .= '<div class="checkout-form-block">
				           <div class="checkout_form_input_field '.(!empty($first_name) ? 'checkout_form_input_field_content' : '').'">
				               <label class="checkout_form_input_label">' . __( 'First name', 'ba-book-everything' ) . '</label>
							   <input type="text" class="checkout_input_field" name="extra_guests[' . $i .'][first_name]" id="guest' . $i .'_first_name" value="'.esc_attr($first_name).'"'. $required . '>
				               <div class="checkout_form_input_underline"><span class="checkout_form_input_ripple"></span></div>
						   </div>
				        </div>
				        <div class="checkout-form-block">
				           <div class="checkout_form_input_field '.(!empty($last_name) ? 'checkout_form_input_field_content' : '').'">
				               <label class="checkout_form_input_label">' . __( 'Last name', 'ba-book-everything' ) . '</label>
							   <input type="text" class="checkout_input_field" name="extra_guests[' . $i .'][last_name]" id="guest' . $i .'_last_name" value="'.esc_attr($last_name).'"'. $required . '>
				               <div class="checkout_form_input_underline"><span class="checkout_form_input_ripple"></span></div>
						   </div>
				        </div>
				        ';

                 if ($age_dropdown){

                     $output .= '<div class="select_guests_block input_select_field">
							<div class="input_select_title add_ids_title">
                                 <div class="input_select_wrapper checkout_form_input_field_content">
                                 <label class="checkout_form_input_label" for="age_list">' . __( 'Age group', 'ba-book-everything' ) . '</label>
                                    <input type="text" id="age_group' . $i .'" class="input_select_input age_group" name="extra_guests_title[' . $i .'][age_group]" value="'.esc_attr($age_group_value).'"'. $required . '>
                                    <input type="hidden" class="input_select_input_value" name="extra_guests[' . $i .'][age_group]" value="'.esc_attr($age_group).'">
                                    <div class="checkout_form_input_underline"><span class="checkout_form_input_ripple"></span></div>
                                    <ul class="input_select_list">'
                         . $age_dropdown .
                         '</ul>
                                    <i class="fas fa-chevron-down"></i>
                                 </div>  
                            </div>
						</div>';
                 }

                 $output .= '</div>';
             }
         }

	    return $output;
     }

//////////////////////////////////////////////
    
    /**
     * Loads scripts as async or defer to improve site perfomance.
     */
    public static function async_defer_scripts($tag, $handle, $src) {
   
         $scripts = array(
            'babe-modal-js' => 1,
         );
   
         if (isset($scripts[$handle])) {
            return str_replace(' src', ' async="async" src', $tag);
         }
     
         return $tag;
    } 
            
////////////////////////////
    /**
	 * Add content to booking_obj page.
     * @param string $content
     * @return string
	 */
    public static function post_content($content){
        global $post;
        $output = $content;

        if (is_single() && in_the_loop() && is_main_query()){
          if ($post->post_type == BABE_Post_types::$booking_obj_post_type){  
            
            $babe_post = BABE_Post_types::get_post($post->ID);
            if (!empty($babe_post)){
              remove_filter( 'the_content', array( __CLASS__, 'post_content'), 10 );
              $output = apply_filters( 'babe_post_content', $content, $post->ID, $babe_post);
            }
          }
          
          if ($post->post_type == BABE_Post_types::$mpoints_post_type){            
            remove_filter( 'the_content', array( __CLASS__, 'post_content'), 10 );
            $output = $content;
            
            $address_arr['address'] = get_post_meta($post->ID, 'address', true);
            $address_arr['ID'] = $post->ID;
            
            $output .= self::block_address_map_with_direction($address_arr);
          }             
        }
        
        return $output; 
    }

////////////////////////////
    /*
	 * Add slider to booking_obj page.
     *
     * @param string $content
     * @param int $post_id
     * @param array $post - BABE post
     * @return string
	 */
    public static function babe_post_content_block_slider($content, $post_id, $post){

        return $content.self::block_slider($post);

    }

////////////////////////////
    /*
	 * Add item code to booking_obj page.
     *
     * @param string $content
     * @param int $post_id
     * @param array $post - BABE post
     * @return string
	 */
    public static function babe_post_content_item_code($content, $post_id, $post){

        $content .= isset($post['code']) && $post['code'] ? '<div class="item_code">'.__( 'Item Code:', 'ba-book-everything' ).' <strong><span itemprop="productID">'.$post['code'].'</span></strong></div>' : '';

        return $content;

    }

////////////////////////////
    /*
	 * Add star rating to booking_obj page.
     *
     * @param string $content
     * @param int $post_id
     * @param array $post - BABE post
     * @return string
	 */
    public static function babe_post_content_star_rating($content, $post_id, $post){

        return $content.BABE_Rating::post_stars_rendering($post_id);

    }

////////////////////////////
    /*
	 * Add "price from" to booking_obj page.
     *
     * @param string $content
     * @param int $post_id
     * @param array $post - BABE post
     * @return string
	 */
    public static function babe_post_content_price_from($content, $post_id, $post){

        $content .= self::block_price_from($post);

        $content .= apply_filters( 'babe_post_content_after_price', '', $content, $post_id, $post);

        return $content;

    }

////////////////////////////
    /*
	 * Add event date to booking_obj page.
     *
     * @param string $content
     * @param int $post_id
     * @param array $post - BABE post
     * @return string
	 */
    public static function babe_post_content_event_date($content, $post_id, $post){

        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

        if ($rules_cat['rules']['basic_booking_period'] == 'single_custom'){

            $date_from_obj = new DateTime( BABE_Calendar_functions::date_to_sql($post['start_date']).' '.$post['start_time']);
            $date_to_obj = new DateTime( BABE_Calendar_functions::date_to_sql($post['end_date']).' '.$post['end_time']);

            $dates = $date_from_obj->format(get_option('date_format').' '.get_option('time_format')).' - ';

            if ( $date_from_obj->format('Y-m-d') != $date_to_obj->format('Y-m-d') ){
                $dates .= $date_to_obj->format(get_option('date_format')).' ';
            }

            $dates .= $date_to_obj->format( get_option('time_format') );

            $content .= '<div class="single_event_dates">'.__( 'When:', 'ba-book-everything' ).' <span class="single_event_dates_value">'.$dates.'</span></div>';

        }

        return $content;

    }

////////////////////////////
    /*
	 * Add main content tabs to booking_obj page.
     *
     * @param string $content
     * @param int $post_id
     * @param array $post - BABE post
     * @return string
	 */
    public static function babe_post_content_tabs($content, $post_id, $post){

        $output = '';

        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

        $tab_titles = [];
        $tab_content = [];
        $tab_slugs = [];
        $tab_fa_classes = [];

        $tab_titles[] = __( 'Overview', 'ba-book-everything' );
        $tab_content[] = $content; // put main post content to the first tab
        $tab_slugs[] = 'content';
        $tab_fa_classes[] = 'far fa-eye';

        if( !BABE_Settings::$settings['av_calendar_remove'] && $rules_cat['rules']['basic_booking_period'] != 'single_custom' ){

            $calendar_html = self::block_calendar($post);
            if ( $calendar_html ){
                $tab_titles[] = __( 'Calendar & Prices', 'ba-book-everything' );
                $tab_content[] = $calendar_html;
                $tab_slugs[] = 'calendar';
                $tab_fa_classes[] = 'far fa-calendar-alt';
            }
        }

        $block_steps = self::block_steps($post);

        if ($block_steps){
            $tab_titles[] = __( 'Details', 'ba-book-everything' );
            $tab_content[] = $block_steps;
            $tab_slugs[] = 'steps';
            $tab_fa_classes[] = 'fas fa-info-circle';
        }

        if( !BABE_Settings::$settings['google_map_remove'] && BABE_Settings::$settings['google_map_active'] && isset($rules_cat['category_meta']['categories_gmap_active']) && $rules_cat['category_meta']['categories_gmap_active'] ){

            $block_meeting_points = self::block_meeting_points($post);
            $block_meeting_points_title = __( 'Meeting points', 'ba-book-everything' );
            if ( !$block_meeting_points ){
                if (isset($post['address']['address']) && isset($post['address']['latitude']) && isset($post['address']['longitude']) && $post['address']['latitude'] && $post['address']['longitude']){
                    $block_meeting_points = self::block_address_map($post);
                    $block_meeting_points_title = __( 'Map', 'ba-book-everything' );
                } else {
                    $block_meeting_points_title = '';
                }
            }

            if ($block_meeting_points_title){
                $tab_titles[] = $block_meeting_points_title;
                $tab_content[] = $block_meeting_points;
                $tab_slugs[] = 'location';
                $tab_fa_classes[] = 'fas fa-map-marker-alt';
            }
        }

        $block_services = !BABE_Settings::$settings['services_to_booking_form'] ? self::block_services($post) : '';
        $services_title = isset($rules_cat['category_meta']['categories_services_title']) && !empty($rules_cat['category_meta']['categories_services_title']) ? $rules_cat['category_meta']['categories_services_title'] : __( 'Services', 'ba-book-everything' );

        if ($block_services){
            $tab_titles[] = $services_title;
            $tab_content[] = $block_services;
            $tab_slugs[] = 'services';
            $tab_fa_classes[] = 'fas fa-dollar-sign';
        }

        //////////// custom sections

        if ( !empty($post['custom_section'][0]['title']) ){
            foreach ($post['custom_section'] as $ind => $custom_section){

                if ($custom_section['title']){
                    $tab_titles[] = $custom_section['title'];
                    $tab_content[] = '<div class="block_custom_section">
                    '. do_shortcode( $custom_section['content'] ) .'
                     </div>';
                    $tab_slugs[] = 'custom_section_'.$ind;
                    $tab_fa_classes[] = $custom_section['fa_class'];
                }
            }
        }

        //////////////////////////

        $block_faq = self::block_faqs($post);
        $faq_title = isset($rules_cat['category_meta']['categories_faq_title']) && !empty($rules_cat['category_meta']['categories_faq_title']) ? $rules_cat['category_meta']['categories_faq_title'] : __( 'Questions & Answers', 'ba-book-everything' );

        if ($block_faq){
            $tab_titles[] = $faq_title;
            $tab_content[] = $block_faq;
            $tab_slugs[] = 'faq';
            $tab_fa_classes[] = 'fas fa-question';
        }

        if ( !empty(BABE_Settings::$settings['reviews_in_tabs']) ){

            $show_reviews = comments_open() || get_comments_number();
            $show_reviews = apply_filters( 'babe_post_content_show_reviews', $show_reviews, $post_id, $post);

            if ( $show_reviews ){

                $reviews_comment_template = !empty(BABE_Settings::$settings['reviews_comment_template']) ? BABE_Settings::$settings['reviews_comment_template'] : '';

                $reviews_comment_template = apply_filters( 'babe_post_content_reviews_comment_template', $reviews_comment_template, $post_id, $post);

                ob_start();
                comments_template($reviews_comment_template);
                $block_reviews = ob_get_clean();

                $tab_titles[] = __( 'Reviews', 'ba-book-everything' );
                $tab_content[] = $block_reviews;
                $tab_slugs[] = 'reviews';
                $tab_fa_classes[] = 'fas fa-star';
            }
        }

        ////////////////////////////////////////////////

        $tab_titles = apply_filters( 'babe_post_content_tab_titles', $tab_titles, $post_id, $post);
        $tab_content = apply_filters( 'babe_post_content_tab_content', $tab_content, $post_id, $post);
        $tab_slugs = apply_filters( 'babe_post_content_tab_slugs', $tab_slugs, $post_id, $post);
        $tab_fa_classes = apply_filters( 'babe_post_content_tab_fa_classes', $tab_fa_classes, $post_id, $post);

        if ( !empty(BABE_Settings::$settings['content_in_tabs']) && !empty($tab_titles) ){

            // generate tab html
            $output .= '<div class="babe_tab_title_group tabs_group babe_post_content_tab_titles">';
            foreach ( $tab_titles as $ind => $tab_title){
                $add_class = !$ind ? ' tab_active' : '';
                $add_icon = !empty($tab_fa_classes[$ind]) ? '<i class="'.esc_attr($tab_fa_classes[$ind]).'"></i> ' : '';
                $output .= '<span class="babe_post_content_tab_title tab_title'.$add_class.'" data-method="'.$tab_slugs[$ind].'">'.$add_icon.$tab_title.'</span>';
            }

            $output .= '</div>';
            foreach ( $tab_content as $ind => $tab_content_item){
                $add_class = !$ind ? ' tab_active' : '';
                $output .= '<div class="babe_post_content_tab_content tab_content'.$add_class.'" data-method="'.$tab_slugs[$ind].'">'.$tab_content_item.'</div>';
            }
        } else {

            $tab_titles[0] = '';
            // generate general html
            foreach ( $tab_titles as $ind => $tab_title){

                $add_icon = '';

                if ( strpos($tab_slugs[$ind], 'custom_section') === 0 && !empty($tab_fa_classes[$ind]) ){
                    $add_icon = '<i class="'.esc_attr($tab_fa_classes[$ind]).'"></i> ';
                }

                if ( $ind ){
                    $output .= '<h3 class="babe_post_content_title">'. $add_icon . $tab_title.'</h3>';
                }

                $output .= $tab_content[$ind];

                    // backward compatibility
                // will be deprecated in v 2
                $output .= apply_filters( 'babe_post_content_after_'.$tab_slugs[$ind], '', $content, $post_id, $post);
            }
        }

        return $output;

    }

////////////////////////////
    /*
	 * Add related items to booking_obj page.
     *
     * @param string $content
     * @param int $post_id
     * @param array $post - BABE post
     * @return string
	 */
    public static function babe_post_content_block_related($content, $post_id, $post){

        $block_related = self::block_related($post);
        $block_related_title = apply_filters( 'babe_post_content_related_title', __( 'You may like', 'ba-book-everything' ));
        $content .= $block_related ? '<h3 class="babe_post_content_title">'.esc_html($block_related_title).'</h3>'.$block_related : '';

        return $content;

    }

////////////////////////////
    /*
	 * Add our template content to booking_obj page.
     * @param string $content
     * @param int $post_id
     * @param array $post - BABE post
     * @return string
	 */
    public static function babe_post_content($content, $post_id, $post){
        
        $output = '';

        $output .= apply_filters( 'babe_post_content_before_tabs', '', $post_id, $post);

        $output .= apply_filters( 'babe_post_content_tabs', $content, $post_id, $post);

        $output .= apply_filters( 'babe_post_content_after_tabs', '', $post_id, $post);
        
        return $output; 
    }
    
///////////////////////////        
    /**
	 * Add price from to booking_obj page.
     * @param array $post - BABE post array
     * @return string
	 */
    public static function block_price_from($post){
        $output = '';

        if (
            !isset($post['discount_price_from'])
            || !isset($post['price_from'])
            || !isset($post['discount_date_to'])
            || !isset($post['discount'])
        ){
            $prices = BABE_Post_types::get_post_price_from($post['ID']);
        } else {
            $prices = $post;
        }

        if (!empty($prices)){

            $save = '';

            if ($prices['discount_price_from'] < $prices['price_from']){

                $date_to_obj = new DateTime($prices['discount_date_to']);

                $save = '<span class="item_info_price_from_discount">'.BABE_Currency::get_currency_price($prices['price_from']).'</span><span class="item_info_price_from_save">'.__( 'Save ', 'ba-book-everything' ).$prices['discount'].'%</span>
            <div class="item_info_discount_expired"><i class="far fa-clock"></i> '.__( 'Offer Ends: ', 'ba-book-everything' ).$date_to_obj->format(get_option('date_format')).'</div>
            ';
            }

            $output .= '<div class="item_info_price">
       <label>'. sprintf( BABE_Settings::get_option('price_from_label') , BABE_Currency::get_currency() ).'</label>
       <span class="item_info_price_from">'.BABE_Currency::get_currency_price($prices['discount_price_from']).'</span>'.$save.'
       '.self::get_post_schema($post).'
    </div>';

        }
    
        return $output; 
    }

///////////////////////////
    /**
     * Get post Product schema markup array
     *
     * @param array $post - BABE post array
     * @return string
     */
    public static function get_post_schema($post){

        $output = '';

        $prices = BABE_Post_types::get_post_price_from($post['ID']);

        if (!empty($prices)){

            if (function_exists( 'qtranxf_getLanguage' )){
                $lang = qtranxf_getLanguage();
            } else {
                $lang = substr( get_locale(), 0, 2 );
            }

            $productID = BABE_Post_types::get_post_category($post['ID'])->slug.'_'.$lang.'_'.$post['ID'];

            $description = get_post_meta( $post['ID'], '_yoast_wpseo_metadesc', true);
            $description = $description ? $description : BABE_Post_types::get_post_excerpt($post);

            /// \u00a0 character fix
            $description = str_replace( chr( 194 ) . chr( 160 ), ' ', $description );

            $post_meta = BABE_Post_types::get_post_meta($post['ID']);

            $schema = [
                '@context' => 'http://schema.org/',
                '@type' => 'Product',
                'name' => esc_attr( $post['post_title'] ),
                'url' => esc_url( get_permalink($post['ID']) ),
                'description' => esc_js( strip_tags($description) ),
                'productID' => $productID,
                'sku' => $productID,
                'mpn' => $productID,
                'brand' => [
                    '@type' => "Organization",
                    'name' => esc_attr( get_bloginfo( 'name' ) ),
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => esc_attr( $prices['discount_price_from'] ),
                    'priceCurrency' => esc_attr( BABE_Currency::get_currency() ),
                    'availability' => 'http://schema.org/InStock',
                    'validFrom' => BABE_Calendar_functions::date_to_sql($post_meta['start_date']),
                    'priceValidUntil' => BABE_Calendar_functions::date_to_sql($post_meta['end_date']),
                    'url' => esc_url( get_permalink($post['ID']) ),
                ],
            ];

            $featured_image = get_the_post_thumbnail_url($post['ID']);
            if( !empty($featured_image) ){
                $schema['image'] = esc_url($featured_image);
            }

            $total_rating = BABE_Rating::get_post_total_rating($post['ID']);
            $total_votes = BABE_Rating::get_post_total_votes($post['ID']);

            if ( $total_rating && $post['comment_count'] ){

                $comments = get_comments( array( 'post_id' => $post['ID'] ) );

                $i = 0;

                foreach ( $comments as $comment ) {

                    $rating = BABE_Rating::get_comment_total_rating($comment->comment_ID);

                    $schema['review'][] = [
                        '@type' => 'Review',
                        'author' => esc_attr($comment->comment_author),
                        'datePublished' => esc_attr($comment->comment_date),
                        'reviewBody' => esc_attr( sanitize_text_field( $comment->comment_content ) ),
                        'name' => esc_attr__('Worth a purchase', 'ba-book-everything'),
                        'reviewRating' => [
                            '@type' => 'Rating',
                            'ratingValue' => round($rating, 2),
                        ],
                    ];

                    $i++;

                    if ( $i >= 7 ){
                        break;
                    }
                }

                $schema['aggregateRating'] = [
                    '@type' => 'AggregateRating',
                    'ratingValue' => round($total_rating, 2),
                    'reviewCount' => $total_votes,
                ];

            }

            $schema = apply_filters( 'babe_post_schema_markup', $schema, $post);

            $output = '<script type="application/ld+json">'.json_encode($schema).'</script>';

        }

        return $output;
    }
    
///////////////////////////        
    /**
	 * Add unitegallery to booking_obj page.
     * @param array $post - we're looking for $post['images'] array here
     * @return string
	 */
    public static function block_slider($post){
    
    $output = '';
    
    $files = isset($post['images']) ? (array)$post['images'] : array();

    if(!BABE_Settings::$settings['unitegallery_remove'] && !empty($files)){
        
      $thumbnail = apply_filters('babe_slider_img_thumbnail', 'thumbnail');
      $full = apply_filters('babe_slider_img_full', 'full');     
      // Loop through them and output an image
      foreach ( $files as $file ) {
        if (is_array($file) && isset($file['image_id']) && $file['image_id']){
        $image_full_arr = wp_get_attachment_image_src( $file['image_id'], $full );
        $image_thumb_arr = wp_get_attachment_image_src( $file['image_id'], $thumbnail );
        $description = !empty($file['description']) ? ' data-description="'.esc_attr($file['description']).'"' : '' ;
        
        $output .= '
        <img src="'.$image_thumb_arr[0].'" data-image="'.$image_full_arr[0].'"'.$description.'/>
        ';
        }
      } //// end foreach
      
      if ($output){
        
         $unitegallery_settings = BABE_Settings::$unitegallery;
         $js_arr = array();
         
         foreach($unitegallery_settings as $key => $value){
            
            if ($value === null){
                $value_str = 'null';
            } elseif ($value === true){
                $value_str = 'true';
            } elseif ($value === false){
                $value_str = 'false';
            } elseif (is_float($value) || is_int($value)){
                $value_str = $value;
            } else {
                $value_str = '"'.$value.'"';
            }
            
            $js_arr[] = $key.':'.$value_str;
         }
         $js = implode(', ', $js_arr);

         $add_class = !empty(BABE_Settings::$settings['content_in_tabs']) ? ' babe_slider_tabs_content' : '';

         $output = '
         <div class="babe_slider'.$add_class.'" id="unitegallery" style="display:none;">
             '.$output.'
         </div>
         ';
      }
    
    }
    
    return $output;
}
    
////////////////////////////
    /**
	 * Add our content to pages.
     * @param string $content
     * @return string
	 */
    public static function pages_content($content){
        global $post;
        $output = $content;
        
        if (is_singular() && in_the_loop() && is_main_query()){

        if ($post->ID === (int)BABE_Settings::$settings['services_page'] && isset($_GET['current_action']) && $_GET['current_action'] === 'to_services'){
            remove_filter( 'the_content', array( __CLASS__, 'pages_content'), 10 );
            $output = apply_filters( 'babe_services_content', $content);
        }
        
        if ($post->ID === (int)BABE_Settings::$settings['checkout_page'] && isset($_GET['current_action']) && $_GET['current_action'] === 'to_checkout'){
            remove_filter( 'the_content', array( __CLASS__, 'pages_content'), 10 );
            $output = apply_filters( 'babe_checkout_content', $content);
        }
        
        if ($post->ID === (int)BABE_Settings::$settings['confirmation_page'] && isset($_GET['current_action']) && $_GET['current_action'] === 'to_confirm'){
            remove_filter( 'the_content', array( __CLASS__, 'pages_content'), 10 );
            $output = apply_filters( 'babe_confirmation_content', $content);
        }
        
        if ($post->ID === (int)BABE_Settings::$settings['admin_confirmation_page'] && isset($_GET['current_action']) && $_GET['current_action'] === 'to_admin_confirm'){
            remove_filter( 'the_content', array( __CLASS__, 'pages_content'), 10 );
            $output = apply_filters( 'babe_admin_confirmation_content', $content);
        }
        
        if ($post->ID === (int)BABE_Settings::$settings['my_account_page']){
            remove_filter( 'the_content', array( __CLASS__, 'pages_content'), 10 );
            $output = apply_filters( 'babe_my_account_content', $content);
        }
        
        }
        
        return $output; 
    }    
    
///////////page_search_result///////////
    /**
	 * Add search result to page.
     * @param string $content
     * @return string
	 */
    public static function page_search_result($content){
        global $post;
        $output = $content;
        
        if (in_the_loop() && is_main_query()){
        
          $add_search_result_to_page = is_page() && $post->ID == BABE_Settings::$settings['search_result_page'];
          $add_search_result_to_page = apply_filters('babe_add_search_result_to_page', $add_search_result_to_page, $post);
        
          if ($add_search_result_to_page){
            remove_filter( 'the_content', array( __CLASS__, 'page_search_result'), 10 );
            $output .= self::get_search_result(BABE_Settings::$settings['results_view']);
            
            $output .= '<div id="babe_search_result_refresh">
               <i class="fas fa-spinner fa-spin fa-3x"></i>
            </div>';
            
            $output = apply_filters('babe_search_result_content', $output, $post);
          }
        } 
        
        return $output; 
    }

    /**
	 * Add price from and rating to search result items.
     * @param string $description
     * @param array $post
     * @return string
	 */
    public static function search_result_description_price($description, $post){
        $output = '';
        
        $output .= BABE_Rating::post_stars_rendering($post['ID']);
        
        $output .= self::block_price_from($post);
        
        $output .= $description;
    
        return $output; 
    }
    
////////////////////////////
    /**
	 * Get search result
     * @param string $view
     * @return string
	 */
    public static function get_search_result($view = 'full') {
        
        $output = '';
        
        $args = wp_parse_args( $_GET, array(
            'request_search_results' => '',
            'date_from' => '', //// d/m/Y or m/d/Y format
            'date_to' => '',
            'time_from' => '00:00',
            'time_to' => '23:59',
            'categories' => [], //// term_taxonomy_ids from categories
            'terms' => [], //// term_taxonomy_ids from custom taxonomies in $taxonomies_list
            'search_results_sort_by' => 'title_asc',
            'keyword' => '',
        ));

        if ( !$args['request_search_results'] ){
            return $output;
        }
        
        if (isset( $_GET['guests'] )){
            $guests = array_map('absint', $_GET['guests']);
            $args['guests'] = array_sum($guests);
        }

        // sanitize args
        foreach ($args as $arg_key => $arg_value){
            $args[sanitize_title($arg_key)] = is_array($arg_value) ? array_map('absint', $arg_value) : sanitize_text_field($arg_value);
        }

        ///// categories
        if ( !empty(BABE_Search_From::$search_form_tabs) && is_array(BABE_Search_From::$search_form_tabs) && isset($_GET['search_tab']) && isset(BABE_Search_From::$search_form_tabs[$_GET['search_tab']]) ){
            $args['categories'] = BABE_Search_From::$search_form_tabs[$_GET['search_tab']]['categories'];
        }
        
        $args = apply_filters('babe_search_result_args', $args);

        $args = BABE_Post_types::search_filter_to_get_posts_args($args);

        $posts = BABE_Post_types::get_posts($args);
        $posts_pages = BABE_Post_types::$get_posts_pages;

        foreach($posts as $post){
            $output .= self::get_post_preview_html($post, $view);
        } /// end foreach $posts

        if ($output){

            $sort_by_filter = self::input_select_field_with_order('sr_sort_by', '', BABE_Post_types::get_search_filter_sort_by_args(), $args['search_results_sort_by']);

            $inner_class = apply_filters('babe_search_result_inner_class', 'babe_search_results_inner babe_search_results_inner_'.$view);

            $output = '<div class="babe_search_results">
            <div class="babe_search_results_filters">
              '.$sort_by_filter.'
            </div>
            <div class="'.esc_attr($inner_class).'">
            '.$output.'
            </div>
            </div>';
        } else {
            $output = '<div class="babe_search_results">
            '.__( 'No results were found for your request', 'ba-book-everything' ).'
            </div>';
        }

        $output .= BABE_Functions::pager($posts_pages);

        $output = apply_filters('babe_search_result_html', $output, $posts, $posts_pages);
        
        return $output;
    }

////////////////////////////
    /**
     * Get post preview html
     * @param array $post BABE post array
     * @param string $view - full or grid
     * @return string
     */
    public static function get_post_preview_html($post, $view = '') {

        $view = !$view ? BABE_Settings::$settings['results_view'] : $view;

        $thumbnail = apply_filters('babe_search_result_img_thumbnail', 'ba-thumbnail-sq');
        $excerpt_length = apply_filters('babe_search_result_excerpt_length', 25);
        $excerpt_grid_length = apply_filters('babe_search_result_grid_excerpt_length', 13);

        $item_url = BABE_Functions::get_page_url_with_args($post['ID'], $_GET);

        $image_srcs = wp_get_attachment_image_src( get_post_thumbnail_id( $post['ID'] ), $thumbnail);
        $image = $image_srcs ? '<a href="'.$item_url.'"><img src="'.$image_srcs[0].'"></a>' : '';

        if ( !isset($post['price_from']) ){
            $post_price_from = BABE_Post_types::get_post_price_from($post['ID']);
            $post = array_merge($post, $post_price_from);
        }

        $price_from_with_taxes = ( $post['price_from'] * (100+$post['categories_add_taxes']*$post['categories_tax']) )/100;

        $price_old = $post['discount_price_from'] < $price_from_with_taxes ? '<span class="item_info_price_old">' . BABE_Currency::get_currency_price( $price_from_with_taxes ) . '</span>' : '';

        $discount = $post['discount'] ? '<div class="item_info_price_discount">-' . $post['discount'] . '%</div>' : '';

        $item_info_price = '';
        if ( !empty($post['discount_price_from']) ){
            $item_info_price = '<div class="item_info_price">
							<label>' . __( 'from', 'ba-book-everything' ) . '</label>
							' . $price_old . '
							<span class="item_info_price_new">' . BABE_Currency::get_currency_price( $post['discount_price_from'] ) . '</span>
                            ' . $discount . ' 
						</div>';
        }


        $output = $view == 'full' ? apply_filters('babe_search_result_view_full', '
          <div class="block_search_res">
            <div class="search_res_img">
            '.$image.'
            </div>
            <div class="search_res_text">
              <a href="'.$item_url.'"><h3 class="search_res_title">
              '. apply_filters('translate_text', $post['post_title']) .'
              </h3></a>
              <div class="search_res_description">
              '.apply_filters('babe_search_result_description', BABE_Post_types::get_post_excerpt($post, $excerpt_length), $post).'
              </div>
              <div class="search_res_actions">
              </div>
            </div>
           </div>
           ', $post, $image) : apply_filters('babe_search_result_view_grid', '
           <div class="babe_all_items_item block_search_res_grid">
				<div class="babe_all_items_item_inner">
					<div class="item_img related_item_img">
						'.$image.'
					</div>
					<div class="item_text related_item_text">
                        <div class="item_title related_item_title">
                            <a href="' . $item_url . '">' . apply_filters('translate_text', $post['post_title']) . '</a>
                            ' . BABE_Rating::post_stars_rendering( $post['ID'] ) . '
                        </div>
						' . $item_info_price . '
						
						<div class="item_description">
							' . apply_filters('babe_search_result_grid_description', BABE_Post_types::get_post_excerpt($post, $excerpt_grid_length), $post) . '
						</div>
					</div>
				</div>
			</div>
            ', $post, $image);

        return $output;
    }

/////////////get_search_form///////////////
    /**
	 * Get search form html.
     * @param string $title
     * @return string
	 */
    public static function get_search_form($title = ''){
        
        $output = BABE_Search_From::render_form($title);
        return $output;
   
   }                
    
////////////////////////////
    /**
	 * Add steps to booking_obj page.
     * @param array $post
     * @return string
	 */
    public static function block_steps($post){

        $output = '';
        $results = array();
        
        if (!empty($post) && isset($post['steps']) && !empty($post['steps'])){
           
              foreach($post['steps'] as $step){
                if (isset($step['attraction']) && isset($step['title'])){
                $step_block = '<div class="block_step">
                <div class="block_step_title collapse-title">
                <h4>'.apply_filters('translate_text', $step['title']).'</h4>
                <span><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="block_step_content collapse-body">
                ' . apply_filters('the_content', $step['attraction']) . '
                </div>
              </div>';
                $results[] = apply_filters('babe_post_step_block_html', $step_block, $step);
                }
              }
              
              $output .= '<div id="block_steps">
              '.implode('', $results).'
              </div>';
            
        } //// end if !empty($post['steps'])    
        
        return $output; 
    }

    public static function block_custom_section( array $post ){

        $output = '';
        $results = array();

        if ( empty($post['custom_section'][0]['title']) ){
            return $output;
        }

        foreach ($post['custom_section'] as $ind => $custom_section){

            if ( empty($custom_section['title']) ){
                continue;
            }

            $add_icon = !empty($custom_section['fa_class']) ? '<i class="'.esc_attr($custom_section['fa_class']).'"></i> ' : '';

            $block = '<h3 class="babe_post_content_title">'. $add_icon . $custom_section['title'] .'</h3>'
                . '<div class="block_custom_section">
                    '. do_shortcode( $custom_section['content'] ) .'
                     </div>';
            $results[] = apply_filters('babe_post_custom_section_block_html', $block, $custom_section);
        }

        $output .= implode('', $results);

        return $output;
    }
    
////////////////////////////
    /**
	 * Add FAQs to booking_obj page.
     * 
     * @param array $post - BABE post
     * @return string
	 */
    public static function block_faqs($post){

        $output = '';
        $results = array();
        
        if (!empty($post) && isset($post['faq']) && !empty($post['faq'])){
            
             $faqs_arr = BABE_Post_types::get_post_faq($post);
             
             if (!empty($faqs_arr)){
                
                $i = 0;
                
              foreach($faqs_arr as $faq){
                
                $add_class = '';
                /// add class 'block_active' if we need to open item by default
                //$add_class = !$i ? ' block_active' : '';
                $add_class = apply_filters('babe_post_faq_block_html_active_item', $add_class, $faq, $i);
                
                $qa_block = '<div class="block_faq accordion-block'.$add_class.'">
                <div class="block_faq_title accordion-title">
                <h4>'.apply_filters('translate_text', $faq['post_title']).'</h4>
                <span><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="block_faq_content accordion-body">
                '.apply_filters('the_content', $faq['post_content']).'
                </div>
              </div>';
                $results[] = apply_filters('babe_post_faq_block_html', $qa_block, $faq, $i);
                $i++;
              }
              
              $output .= '<div id="block_faqs">
              '.implode('', $results).'
              </div>';
                
             }
   
        } //// end if !empty($post['faq'])    
        
        return $output; 
    }
    
////////////////////////////
    /**
	 * Add related items to booking_obj page
     * 
     * @param array $post - BABE post
     * @return string
	 */
    public static function block_related($post) {
        
        $output = '';
        $results = array();
        
        if (!empty($post) && isset($post['related_items']) && !empty($post['related_items'])){
            
          $related_arr = BABE_Post_types::get_post_related($post);
          
          $thumbnail = apply_filters('babe_post_related_item_thumbnail', 'ba-thumbnail-sq');
          $excerpt_length = apply_filters('post_related_item_excerpt_length', 13);
        
          foreach($related_arr as $related_post){
            
            $item_url = BABE_Functions::get_page_url_with_args($related_post['ID'], $_GET);
            
            $image_srcs = wp_get_attachment_image_src( get_post_thumbnail_id( $related_post['ID'] ), $thumbnail);
            $image = $image_srcs ? '<a href="'.$item_url.'"><img src="'.$image_srcs[0].'"></a>' : '';
            
            $price_old = $related_post['discount_price_from'] < $related_post['price_from'] ? '<span class="item_info_price_old">' . BABE_Currency::get_currency_price( $related_post['price_from'] ) . '</span>' : '';
				
			$discount = $related_post['discount'] ? '<div class="item_info_price_discount">-' . $related_post['discount'] . '%</div>' : '';
            
            $results[] = apply_filters('babe_post_related_item_html', '
          <div class="babe_all_items_item block_related_item">
				<div class="babe_all_items_item_inner">
					<div class="item_img related_item_img">
						'.$image.'
					</div>
					<div class="item_text related_item_text">
                        <div class="item_title related_item_title">
                            <a href="' . $item_url . '">' . apply_filters('translate_text', $related_post['post_title']) . '</a>
                            ' . BABE_Rating::post_stars_rendering( $related_post['ID'] ) . '
                        </div>
						<div class="item_info_price">
							<label>' . __( 'from', 'ba-book-everything' ) . '</label>
							' . $price_old . '
							<span class="item_info_price_new">' . BABE_Currency::get_currency_price( $related_post['discount_price_from'] ) . '</span>
                            ' . $discount . ' 
						</div>
								
						<div class="item_description related_item_description">
							' . apply_filters('babe_post_related_item_description', BABE_Post_types::get_post_excerpt($related_post, $excerpt_length), $related_post) . '
						</div>
					</div>
				</div>
			</div>
           ', $related_post, $image);
           
          } /// end foreach $related_post
        
          if (!empty($results)){
            
             $output .= '<div id="block_related">
              <div class="babe_shortcode_block_inner">
              '.implode('', $results).'
              </div>
            </div>';
          }
        
          $output = apply_filters('babe_post_related_block_html', $output, $related_arr);
        
        }
        
        return $output;
    }    
    
///////////////////////////////   
/////ajax_get_meeting_points////    
    /**
	 * Get post meeting points as formatted string.
     * @return void
	 */
    public static function ajax_get_meeting_points() {
        $output = '';
        
        $results = array();
        
        if (
            isset($_POST['post_id'], $_POST['lat'], $_POST['lng'], $_POST['nonce'])
            && BABE_Post_types::is_post_booking_obj($_POST['post_id'])
            && wp_verify_nonce($_POST['nonce'], self::$nonce_title)
        ){
            
          $post_id = (int)$_POST['post_id'];
          $post = BABE_Post_types::get_post($post_id);
          $lat = (float)$_POST['lat'];
          $lng = (float)$_POST['lng'];
          
          if (isset($post['meeting_points'], $post['meeting_place']) && $post['meeting_place'] === 'point'){
            
           $meeting_points = BABE_Post_types::get_post_meeting_points($post);
           
           if (!empty($meeting_points)){
              foreach($meeting_points as $point_id => $meeting_point){      
                $distance = BABE_Functions::distance($meeting_point['lat'], $meeting_point['lng'], $lat, $lng);
                $distance = (int)($distance * 1000000);
                $results[$distance] = '<div class="meeting_point" data-point-id="'.$point_id.'">
                <div class="meeting_point_description">
                <h4>'.implode(', ', $meeting_point['times']).' '.$meeting_point['address'].'</h4>
                '.$meeting_point['description'].'
                </div>
                <button class="btn button add_destination" data-lat="'.$meeting_point['lat'].'" data-lng="'.$meeting_point['lng'].'" data-address="'.$meeting_point['address'].'" data-point-id="'.$point_id.'">'.__('Select address', 'ba-book-everything').'</button>
              </div>';
              }
              
              /// sort results
              if (!empty($results)){
                ksort($results, SORT_NUMERIC);
                $results = array_slice($results, 0, 5, true);
              }
              
              $output .= '<h4>'.__('Select meeting point', 'ba-book-everything').'<a href="#booking_form">'.__('Go to Booking form', 'ba-book-everything').'</a></h4>'.implode('', $results);
            
           } //// end if !empty($meeting_points)
            
          } //// end if !empty($post) 
        }
        echo $output;
        wp_die();
    }

////////////////////////////////    
/////ajax_booking_calculate////    
    /**
	 * Get booking price.
     * @return void
	 */
    public static function ajax_booking_calculate() {

        $output = '';

        if (
            !isset($_POST['post_id'], $_POST['date_from'], $_POST['nonce'])
            || !BABE_Post_types::is_post_booking_obj($_POST['post_id'])
            || !wp_verify_nonce($_POST['nonce'], self::$nonce_title)
        ){
            echo $output;
            wp_die();
        }

        $post_id = (int)$_POST['post_id'];

        $post_data = [];
        if ( isset($_POST['data']) ){
            parse_str($_POST['data'], $post_data);
        }
        $data_arr = BABE_Order::sanitize_booking_vars($post_data);

        $order_id = 0;
        $currency = BABE_Currency::get_currency();

        if ( isset($_POST['order_item_id']) ){
            $order_item_id = absint($_POST['order_item_id']);
            $order_id = BABE_Order::get_order_id_by_item_id( $order_item_id );

            if ( BABE_Users::current_user_can_edit_order($order_id) ){
                $currency = BABE_Order::get_order_currency($order_id);
            } else {
                $order_id = 0;
            }
        }

        if ($data_arr['date_from']){
            $price_arr = BABE_Prices::get_obj_total_price_arr($post_id, $data_arr['date_from'], $data_arr['guests'], $data_arr['date_to'], $data_arr['services'], $data_arr['fees'], $order_id);
            $price = BABE_Prices::get_obj_total_price($post_id, $price_arr);
            $output = BABE_Currency::get_currency_price($price['total_with_taxes'], $currency);

            $args = [
                'post_id' => $post_id,
                'date_from' => $data_arr['date_from'],
                'date_to' => $data_arr['date_to'],
                'guests' => $data_arr['guests'],
                'services' => $data_arr['services'],
                'data_arr' => $data_arr,
                'price_arr' => $price_arr,
                'price' => $price,
            ];

            $output = apply_filters('ajax_babe_booking_calculate_price', $output, $args);
        }
        
        echo $output;
        wp_die();
    }        

//////////////////////////////    
/////ajax_get_times_guests////    
    /**
	 * Get available times and guests select for booking form.
     * @return
	 */
    public static function ajax_get_times_guests() {

        $output = array(
            'av_guests' => 0,
            'time_lines' => '',
            'select_guests' => '',
            'services' => '',
        );

        if (
            !isset($_POST['post_id'], $_POST['date_from'], $_POST['date_to'], $_POST['nonce'])
            || !BABE_Post_types::is_post_booking_obj($_POST['post_id'])
            || !wp_verify_nonce($_POST['nonce'], self::$nonce_title)
        ){
            echo json_encode($output);
            wp_die();
        }

        $post_id = (int)$_POST['post_id'];

        $date_from = BABE_Calendar_functions::isValidDate($_POST['date_from'], BABE_Settings::$settings['date_format']) ? BABE_Calendar_functions::date_to_sql($_POST['date_from']) : ''; /// now in Y-m-d format
        $date_to = BABE_Calendar_functions::isValidDate($_POST['date_to'], BABE_Settings::$settings['date_format']) ? BABE_Calendar_functions::date_to_sql($_POST['date_to']) : ''; /// now in Y-m-d format

        if (!$date_from){
            echo json_encode($output);
            wp_die();
        }

        ///// get rules
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

        if ($rules_cat['rules']['basic_booking_period'] === 'recurrent_custom'){

            $av_times_arr = self::booking_form_av_times($post_id, $date_from);
            if(!empty($av_times_arr)){
                $output = $av_times_arr;
            } else {
                $output['av_guests'] = 0;
                $output['time_lines'] = '';
            }

        } else {
            //// or av_guests
            $output['av_guests'] = BABE_Calendar_functions::get_av_guests($post_id, $date_from, $date_to);
            $output['time_lines'] = '';
        }

        $guests = !empty( $_POST['guests'] ) && is_array( $_POST['guests'] ) ? array_map('absint', $_POST['guests']) : array();

        //// booking_form_select_guests
        $output['select_guests'] = self::booking_form_select_guests($post_id, $output['av_guests'], $date_from, $date_to, $guests);

        $babe_post = BABE_Post_types::get_post($post_id);

        $output['services'] = self::list_add_services($babe_post, [], $date_from, ($date_to ?: $date_from), $guests);
        
        echo json_encode($output);
        wp_die();
    }

    /**
     * Get available services for booking form.
     * @return void
     */
    public static function ajax_get_services() {

        $output = '';

        if (
            !isset($_POST['post_id'], $_POST['date_from'], $_POST['date_to'], $_POST['guests'], $_POST['nonce'])
            || !BABE_Post_types::is_post_booking_obj($_POST['post_id'])
            || !wp_verify_nonce($_POST['nonce'], self::$nonce_title)
        ){
            echo $output;
            wp_die();
        }

        $post_id = (int)$_POST['post_id'];

        $post_data = [];
        if ( isset($_POST['data']) ){
            parse_str($_POST['data'], $post_data);
        }
        $data_arr = BABE_Order::sanitize_booking_vars($post_data);

        $babe_post = BABE_Post_types::get_post($post_id);
        $output = self::list_add_services($babe_post, $data_arr['services'], $data_arr['date_from'], $data_arr['date_to'], $data_arr['guests']);

        echo $output;
        wp_die();
    }
    
/////ajax_get_booking_times////    
    /**
	 * Get available booking times select for booking form.
     * @return void
	 */
    public static function ajax_get_booking_times() {
        
        $output = [
            'booking_time_from' => '',
            'booking_time_to' => '',
        ];

        if (
            !isset(
                $_POST['post_id'],
                $_POST['date_from'],
                $_POST['start_time'],
                $_POST['from_to'],
                $_POST['nonce']
            )
            || !BABE_Post_types::is_post_booking_obj($_POST['post_id'])
            || !wp_verify_nonce($_POST['nonce'], self::$nonce_title)
        ){
            echo json_encode($output);
            wp_die();
        }

        $post_id = (int)$_POST['post_id'];
        $start_time = BABE_Calendar_functions::isValidTime($_POST['start_time'], 'H:i') ? $_POST['start_time'] : '00:00';

        $date_from = BABE_Calendar_functions::isValidDate($_POST['date_from'], BABE_Settings::$settings['date_format']) ? $_POST['date_from'] : ''; /// now in Y-m-d format

        $time_selected = isset($_POST['time_selected']) && BABE_Calendar_functions::isValidTime($_POST['time_selected'], 'H:i') ? $_POST['time_selected'] : false;

        $from_to = $_POST['from_to'] === 'to' ? 'to' : 'from';

        if ($date_from){
            ///// get rules
            $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

            if ($rules_cat['rules']['basic_booking_period'] === 'day'){

                $field_name = $from_to === 'from' ? 'booking_time_from' : 'booking_time_to';
                $time_select_arr = self::get_time_select_arr($date_from, $post_id, ($from_to === 'from'), $start_time);
                $output[$field_name] = self::input_select_field($field_name, '', $time_select_arr, $time_selected);

            } elseif ($rules_cat['rules']['basic_booking_period'] === 'hour'){

                $time_select_arr = self::get_time_select_arr($date_from, $post_id, true, '00:00', [], 60, true);

                $output['booking_time_from'] = self::input_select_cloud('booking_time_from', $time_select_arr);

                $time_select_arr = self::get_time_select_arr($date_from, $post_id, false, '00:00', [], 60, true);

                $output['booking_time_to'] = self::input_select_cloud('booking_time_to', $time_select_arr);
            }
        }
        
        echo json_encode($output);
        wp_die();
    }        
    
////////////////////////////

    /**
     * Create av times radio input.
     *
     * @param int $post_id
     * @param string $date_from - Y-m-d H:i:s, must be valid
     * @param array $order_item_args
     * @param bool $ignor_stop_booking
     * @return array
     * @throws Exception
     */
    public static function booking_form_av_times(
        $post_id,
        $date_from,
        $order_item_args = array(),
        $ignor_stop_booking = false
    ){
        
        $date_from_obj = new DateTime($date_from);
        $av_cal = BABE_Calendar_functions::get_av_cal($post_id, $date_from_obj->format('Y-m-d'), $date_from_obj->format('Y-m-d'), $order_item_args, $ignor_stop_booking);
        $max_guests = absint( get_post_meta( $post_id, 'guests', true) );
        
        if (isset($order_item_args['date_from'])){
            
            $test_date_from_obj = new DateTime($order_item_args['date_from']);
            $test_time = $test_date_from_obj->format('H:i');
            
        } else {
            
            $test_time = $date_from_obj->format('H:i');
            
        }

        $output = array(
            'time_lines' => '',
            'av_guests' => 0,
            'av_time_guests' => array(),
        );

        if ( !isset($av_cal[$date_from_obj->format('Y-m-d')]['times']) ){
            return $output;
        }

        $times = $av_cal[$date_from_obj->format('Y-m-d')]['times'];

        $guests_min = $av_cal[$date_from_obj->format('Y-m-d')]['min_booking_period'] ?: 0;

        $input_type = apply_filters(
            'babe_booking_form_av_times_input_type',
            'radio',
            $times,
            $post_id,
            $date_from,
            $order_item_args,
            $ignor_stop_booking
        );

        if ( $input_type === 'select' ){
            $output['time_lines'] .= '
              <select class="booking_time_line_select" name="booking_time" >';
        }

        $i = 0;

        foreach( $times as $time => $av_guests){

            if ( !$av_guests || $av_guests < $guests_min ){
                continue;
            }

            if (
                ( !$i && !isset($times[$test_time]) )
                || $test_time == $time
            ){
                $output['av_guests'] = $av_guests;
                $checked = ' checked="checked"';
            } else {
                $checked = '';
            }

            $output['av_time_guests'][$time] = $av_guests;
            $time_date_obj = new DateTime($time);

            $i++;

            if ( $input_type === 'select' ){

                if ($checked){
                    $checked = ' selected="selected"';
                }

                $output['time_lines'] .= '
              <option class="booking_time_option" value="'.$time.'" id="booking_time_'.$i.'" data-max-guests="'.$av_guests.'" data-max-select-guests="'.min($av_guests, BABE_Settings::$settings['max_guests_select']).'"'.$checked.'>'
                    . apply_filters(
                        'babe_booking_form_av_times_time_label',
                        $time_date_obj->format(get_option('time_format')),
                        $time_date_obj,
                        $post_id
                    )
                    .'
              </option>';

            } else {

                $output['time_lines'] .= '
              <span class="booking_time_line">
                <input type="radio" class="booking_time" name="booking_time" value="'.$time.'" id="booking_time_'.$i.'" data-max-guests="'.$av_guests.'" data-max-select-guests="'.min($av_guests, BABE_Settings::$settings['max_guests_select']).'"'.$checked.'>
                <label for="booking_time_'.$i.'">'
                    . apply_filters(
                        'babe_booking_form_av_times_time_label',
                        $time_date_obj->format(get_option('time_format')),
                        $time_date_obj,
                        $post_id
                    )
                    .'</label>
              </span>';
            }
        }

        if ( $input_type === 'select' ){
            $output['time_lines'] .= '
              </select>';
        }
        
        return apply_filters(
            'babe_booking_form_av_times',
            $output,
            $times,
            $post_id,
            $date_from,
            $order_item_args,
            $ignor_stop_booking
        );
        
    }    
    
////////////////////////////
    /**
	 * Create select guests input fields.
     * 
     * @param int $post_id
     * @param int $max_guests
     * @param string $date_from - Y-m-d H:i:s, must be valid
     * @param string $date_to - Y-m-d H:i:s, must be valid
     * @param array $selected_guests_arr - [$age_id => $guests_num]
     * 
     * @return string
	 */
    public static function booking_form_select_guests($post_id, $max_guests = 0, $date_from = '', $date_to ='', $selected_guests_arr = array()){
        
        $output = '';
        
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);
        
        $max_guests = !$max_guests ? absint( get_post_meta($post_id, 'guests', true) ) : absint($max_guests);

        if ( !$max_guests || !$date_from ){
            return $output;
        }

        $min_guests_string = (string)get_post_meta($post_id, 'min_guests', true);
        $min_guests = $min_guests_string === '0' ? 0 : max(absint( $min_guests_string ),1);

        $guests_max = min($max_guests, BABE_Settings::$settings['max_guests_select']);
        $guests_min = max(0, $min_guests);

        $ages = BABE_Post_types::get_ages_arr();
        $post_ages = BABE_Post_types::get_post_ages($post_id);
        $main_age_id = BABE_Post_types::get_main_age_id($rules_cat['rules']);

        if (empty($post_ages)){

            if (!$rules_cat['rules']['ages'] || empty($ages)){

                $post_ages[0] = array(
                    'age_id' => 0,
                    'name' => '',
                    'description' => '',
                    'menu_order' => 1,
                    'slug' => '',
                );

            } else {
                $post_ages = $ages;
            }
        }

        $av_cal = BABE_Calendar_functions::get_av_cal($post_id, $date_from, $date_to);

        if( empty($av_cal) ){
            return $output;
        }

        $av_cal_first_rec = reset($av_cal);
        $av_cal_ages = isset($av_cal_first_rec['price_general']) && is_array($av_cal_first_rec['price_general']) ? $av_cal_first_rec['price_general'] : array();

        if ( $rules_cat['rules']['basic_booking_period'] === 'recurrent_custom' ){
            $guests_min = $av_cal_first_rec['min_booking_period'] ?: $guests_min;
            $guests_max = $av_cal_first_rec['max_booking_period'] ? min($max_guests, $av_cal_first_rec['max_booking_period']) : $guests_max;
        }

        $number_of_ages = count($post_ages);

        foreach ($post_ages as $age_term){

            if ( !isset($av_cal_ages[$age_term['age_id']]) ){
                continue;
            }

            if (
                $rules_cat['rules']['basic_booking_period'] === 'recurrent_custom'
                || $rules_cat['rules']['booking_mode'] === 'tickets'
                || $rules_cat['rules']['booking_mode'] === 'places'
            ){
                $price_arr = BABE_Prices::get_obj_total_price_arr($post_id, $date_from, array( $age_term['age_id'] => max(1, $guests_min) ), $date_to, array());
                $price = BABE_Prices::get_obj_total_price($post_id, $price_arr);
                $price_total_with_taxes = $price['total_with_taxes']/max(1, $guests_min);
                $price_html = BABE_Currency::get_currency_price($price_total_with_taxes);

                $price_arr_max = BABE_Prices::get_obj_total_price_arr($post_id, $date_from, array( $age_term['age_id'] => $guests_max ), $date_to, array());
                $price_max = BABE_Prices::get_obj_total_price($post_id, $price_arr_max);
                $price_max_total_with_taxes = $price_max['total_with_taxes']/$guests_max;

                if ( (int)$price_max_total_with_taxes != (int)$price_total_with_taxes ){
                    $price_html = '<span class="currency_amount_group">' . BABE_Currency::get_currency_price($price_max_total_with_taxes) . ' - ' . $price_html . '</span>';
                }

            } else {
                $price_html = '';
            }

            $age_term['description'] = $age_term['description'] ? ' ('.$age_term['description'].')' : '';

            $field_title = $age_term['name'] || $price_html ? '<div class="input_select_title_value">'. $age_term['name'] . $age_term['description'] . $price_html . '</div>' : '';

            $selected_value = $selected_guests_arr[$age_term['age_id']] ?? 0;
            if (
                !$selected_value
                && (
                    $number_of_ages === 1
                )
            ){
                $selected_value = $guests_min;
            }

            $output .= '
						<div class="select_guests_block input_select_field">
							<div class="input_select_title">
                                 ' . $field_title . '
                                 <div class="input_select_wrapper">
                                    <input type="text" id="guests_'.$age_term['age_id'].'" class="input_select_input input_select_input_value select_guests" data-guests-min="'.$guests_min.'" data-guests-max="'.$guests_max.'" name="booking_guests['.$age_term['age_id'].']" data-age-id="'.$age_term['age_id'].'" value="'.$selected_value.'">
                                    <ul class="input_select_list">
                                    '.self::get_range_input_select_options(
                                        (
                                            $number_of_ages === 1
                                                ? $guests_min : 0
                                        ),
                                        $guests_max,
                                        1,
                                        $selected_value
                ).'
                                    </ul>
                                    <i class="fas fa-chevron-down"></i>
                                 </div>  
                            </div>
						</div>
					';

            $guests_max -= $selected_value;

        }  ////////////// end foreach $post_ages
        
        return $output;
        
    }    
    
////////////////////////////
    /**
	 * Add booking form to booking_obj page
     *
     * @param int $booking_obj_id
     * @return string
	 */
    public static function booking_form($booking_obj_id = 0){
        
        $booking_obj_id = absint($booking_obj_id);
        
        if ($booking_obj_id && BABE_Post_types::is_post_booking_obj($booking_obj_id)){
            $post = get_post($booking_obj_id);
        } else {
            global $post;
        }

        $output = '';

        if ($post->post_type !== BABE_Post_types::$booking_obj_post_type){
            return $output;
        }

        $post_id = $post->ID;

        $babe_post = BABE_Post_types::get_post($post_id);

        $action = isset($babe_post['services']) && !empty($babe_post['services']) && !BABE_Settings::$settings['services_to_booking_form'] ? 'to_services' : 'to_checkout';

        $action = apply_filters('babe_booking_form_action', $action, $babe_post);

        ///// get rules
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

        ////// check dates for one time event
        if ($rules_cat['rules']['basic_booking_period'] === 'single_custom'){
            $current_date_obj = BABE_Functions::datetime_local();
            $date_from_obj = new DateTime( BABE_Calendar_functions::date_to_sql($babe_post['start_date']).' '.$babe_post['start_time']);

            if ($current_date_obj >= $date_from_obj){
                return $output;
            }
        }

        ///// get av times
        $av_times = BABE_Post_types::get_post_av_times($babe_post);

        $modal_meeting_points = '';

        $i = 1;

        /////////date fields

        $date_from = isset($_GET['date_from']) && BABE_Calendar_functions::isValidDate($_GET['date_from'], BABE_Settings::$settings['date_format']) ? $_GET['date_from'] : '';
        $date_to = isset($_GET['date_to']) && BABE_Calendar_functions::isValidDate($_GET['date_to'], BABE_Settings::$settings['date_format']) ? $_GET['date_to'] : '';

        $av_cal = BABE_Calendar_functions::get_av_cal($post_id);

        if ($date_from){
            if (!$date_to) {
                $date_to = $date_from;
            }
            $av_cal = BABE_Calendar_functions::get_av_cal($post_id, BABE_Calendar_functions::date_to_sql($date_from). ' 00:00:00', BABE_Calendar_functions::date_to_sql($date_to).' 23:59:59');
            if ( empty($av_cal[BABE_Calendar_functions::date_to_sql($date_from)]['start_day']) ){
                $date_from = '';
                $date_to = '';
            }
        }

        $input_time_from = '';
        $input_time_to = '';

        if ($rules_cat['rules']['basic_booking_period'] === 'day'){
            $time_select_arr = self::get_time_select_arr($date_from, $post_id, true);
            $input_time_from = '<div id="booking_time_from_block" class="booking-time-block">
                   '.self::input_select_field('booking_time_from', '', $time_select_arr, ($_GET['time_from'] ?? false)).'
                   </div>';
            $time_select_arr = self::get_time_select_arr($date_to, $post_id, false, ($date_from == $date_to && isset($_GET['time_from']) ? $_GET['time_from'] : '00:00'));
            $input_time_to = '<div id="booking_time_to_block" class="booking-time-block">
                   '.self::input_select_field('booking_time_to', '', $time_select_arr, ($_GET['time_to'] ?? false)).'
                   </div>';
        }

        $input_fields = apply_filters('babe_booking_form_before_date_from', [], $babe_post, $av_times, $rules_cat);

        $input_fields['date_from'] = '';

        if ($rules_cat['rules']['basic_booking_period'] === 'single_custom'){

            if (isset($babe_post['start_time']) && $babe_post['start_time'] && isset($babe_post['end_time']) && $babe_post['end_time']):
                /////////////////

                $date_to_obj = new DateTime( BABE_Calendar_functions::date_to_sql($babe_post['end_date']).' '.$babe_post['end_time']);
                $dates = $date_from_obj->format(get_option('date_format').' '.get_option('time_format')).' - ';
                if ( $date_from_obj->format('Y-m-d') != $date_to_obj->format('Y-m-d') ){
                    $dates .= $date_to_obj->format(get_option('date_format')).' ';
                }
                $dates .= $date_to_obj->format( get_option('time_format') );

                $input_time_from = '<input type="hidden" name="booking_time_from" id="booking_time_from" value="'.$date_from_obj->format('H:i').'"><input type="hidden" name="booking_time_to" id="booking_time_to" value="'.$date_to_obj->format('H:i').'"><input type="hidden" id="booking_date_from" name="booking_date_from" value="'.$babe_post['start_date'].'" data-post-id="'.$post_id.'"><input type="hidden" id="booking_date_to" name="booking_date_to" value="'.$babe_post['end_date'].'">';

                $input_fields['date_from'] = '
            <div class="booking-form-block booking-date-block">
                <label class="booking_form_input_label"><span class="booking_form_step_num">'.$i.'</span><i class="far fa-calendar-alt"></i></label>
            <div class="booking-date">
                   '.$dates.'
                   '.$input_time_from.'
			</div>
            
            </div>';
                $i++;

                endif;

            } elseif ( $rules_cat['rules']['basic_booking_period'] === 'hour' ) {

	            $date_from = empty($date_from) ? date(BABE_Settings::$settings['date_format']) : $date_from;

	            $time_select_arr = self::get_time_select_arr($date_from, $post_id, true, '00:00', array(), 60, true);
	            $input_time_from = '<div id="booking_time_from_block" class="booking-hourly-time-cloud">
                   '.self::input_select_cloud('booking_time_from', $time_select_arr, (isset($_GET['time_from']) && BABE_Calendar_functions::isValidTime($_GET['time_from']) ? $_GET['time_from'] : false)).'
                   </div>';

		            $input_fields['date_from'] = '
            <div class="booking-form-block booking-date-block">
                <label class="booking_form_input_label"><span class="booking_form_step_num">'.$i.'</span>'.apply_filters('babe_booking_form_date_from_label', __('From:', 'ba-book-everything')).'</label>
            <div class="booking-date">
                   <i class="far fa-calendar-alt"></i>
				   <input type="text" class="booking_date" id="booking_date_from" name="booking_date_from" value="'.$date_from.'" placeholder="'.apply_filters('babe_booking_form_date_from_placeholder', '').'" data-post-id="'.$post_id.'" autocomplete="off">
			</div>
            </div>
            <div class="booking-form-block booking-time-block">
                <label class="booking_form_input_label"><span class="booking_form_step_num">'.$i.'</span>'.apply_filters('babe_booking_form_time_from_label', __('Time from:', 'ba-book-everything')).'</label>
            <div class="booking-date">
                   '.$input_time_from.'
			</div>
            </div>
            ';
		            $i++;

            } else {

            $input_fields['date_from'] = '
            <div class="booking-form-block booking-date-block">
                <label class="booking_form_input_label"><span class="booking_form_step_num">'.$i.'</span>'.apply_filters('babe_booking_form_date_from_label', __('From:', 'ba-book-everything')).'</label>
            <div class="booking-date">
                   <i class="far fa-calendar-alt"></i>
				   <input type="text" class="booking_date" id="booking_date_from" name="booking_date_from" value="'.$date_from.'" placeholder="'.apply_filters('babe_booking_form_date_from_placeholder', '').'" data-post-id="'.$post_id.'" autocomplete="off">
                   '.$input_time_from.'
			</div>
            
            </div>';
            $i++;

            }

            $input_fields = apply_filters('babe_booking_form_after_date_from', $input_fields, $babe_post, $av_times, $rules_cat);

        $input_fields['date_to'] = '';

            if ($rules_cat['rules']['basic_booking_period'] !== 'recurrent_custom' && $rules_cat['rules']['basic_booking_period'] !== 'single_custom' &&  $rules_cat['rules']['basic_booking_period'] !== 'hour'){
                $input_fields['date_to'] = '
                <div class="booking-form-block booking-date-block">
                <label class="booking_form_input_label"><span class="booking_form_step_num">'.$i.'</span>'.apply_filters('babe_booking_form_date_to_label', __('To:', 'ba-book-everything')).'</label>
            <div class="booking-date">
                   <i class="far fa-calendar-alt"></i>
				   <input type="text" class="booking_date" id="booking_date_to" name="booking_date_to" value="'.$date_to.'" placeholder="'.apply_filters('babe_booking_form_date_to_placeholder', '').'" data-post-id="'.$post_id.'" autocomplete="off">
                   '.$input_time_to.'
			</div>
            </div>';
                 $i++;

                 $input_fields = apply_filters('babe_booking_form_after_date_to', $input_fields, $babe_post, $av_times, $rules_cat);

            }  elseif ( $rules_cat['rules']['basic_booking_period'] === 'hour' ) {

	            $date_to = empty($date_to) ? $date_from : $date_to;

	            $input_time_to = '<div id="booking_time_to_block" class="booking-hourly-time-cloud"></div>';

	            $input_fields['date_to'] = '
            <div class="booking-form-block booking-time-block">
                <label class="booking_form_input_label"><span class="booking_form_step_num">'.$i.'</span>'.apply_filters('babe_booking_form_time_from_label', __('Time to:', 'ba-book-everything')).'</label>
                <div class="booking-date">
                   '.$input_time_to.'
                </div>
            </div>
            ';
	            $i++;
	            $input_fields = apply_filters('babe_booking_form_after_date_to', $input_fields, $babe_post, $av_times, $rules_cat);
            }

            $check_add_av_times = $rules_cat['rules']['basic_booking_period'] === 'recurrent_custom';
            $check_add_av_times = apply_filters('babe_booking_form_check_add_av_times', $check_add_av_times, $av_times, $babe_post, $rules_cat);

            ////////////Time fields///////////

        $input_fields['time'] = '';

        if ($check_add_av_times){

                //// get AV time spans by AJAX
                $input_fields['time'] = '<div class="booking-form-block booking-times-block">
                <label class="booking_form_input_label"><span class="booking_form_step_num">'.$i.'</span>'.apply_filters('babe_booking_form_time_label', __('Time:', 'ba-book-everything')).'</label>
                <div id="booking-times" class="booking-date-times">
			    </div>
                </div>';

            $i++;

            $input_fields = apply_filters('babe_booking_form_after_av_times', $input_fields, $babe_post, $av_times, $rules_cat);

        }
        //////////////Guests fields/////////

        $input_fields['guests'] = '';

        if (!isset($rules_cat['category_meta']['categories_remove_guests']) || !$rules_cat['category_meta']['categories_remove_guests']){

            $guests = isset( $_GET['guests'] ) && is_array( $_GET['guests'] ) ? $_GET['guests'] : array();

            if ( !empty($guests) && $date_from && $rules_cat['rules']['basic_booking_period'] !== 'recurrent_custom' ){

                $main_age_id = BABE_Post_types::get_main_age_id();
                if ( empty($guests[0]) ){
                    $guests[0] = array_sum($guests);
                } elseif ( count($guests) == 1 ){
                    $guests[$main_age_id] = $guests[0];
                }

                $date_from_sql = BABE_Calendar_functions::date_to_sql($date_from);

                $date_to_sql = $date_to ? BABE_Calendar_functions::date_to_sql($date_to) : $date_to;

                $av_guests = BABE_Calendar_functions::get_av_guests($post_id, $date_from_sql, $date_to_sql);

                $guests = array_map('absint', $guests);

                $guests_output = self::booking_form_select_guests($post_id, $av_guests, $date_from_sql, $date_to_sql, $guests);

            } else {

                $guests_output = __('please, select date first', 'ba-book-everything');

            }

            $guests_title = $rules_cat['rules']['booking_mode'] === 'tickets' ? __('Tickets:', 'ba-book-everything') : __('Guests:', 'ba-book-everything');

            $input_fields['guests'] = '
            <div class="booking-form-block booking-guests-block">
            <label class="booking_form_input_label"><span class="booking_form_step_num">'.$i.'</span>'.apply_filters('babe_booking_form_guests_label', $guests_title).'</label>
            <div id="booking-guests-result">
            '.$guests_output.'
            </div>
            </div>';
            $i++;

            $input_fields = apply_filters('babe_booking_form_after_guests', $input_fields, $babe_post, $av_times, $rules_cat);

        }

        ////////////Meeting points///////////

        $input_fields['meeting_points'] = '';

        if (BABE_Settings::$settings['mpoints_active'] && !empty($babe_post) && isset($babe_post['meeting_points']) && isset($babe_post['meeting_place']) && $babe_post['meeting_place'] === 'point'){

            $meeting_points = BABE_Post_types::get_post_meeting_points($babe_post);

            if (!empty($meeting_points)){
                $meeting_points_output = array();
                foreach($meeting_points as $point_id => $meeting_point){
                    $meeting_points_output[] = '<div class="booking_meeting_point_line">
                <input type="radio" class="booking_meeting_point" name="booking_meeting_point" value="'.$point_id.'" id="booking_meeting_point_'.$point_id.'" data-point-id="'.$point_id.'">
                <label for="booking_meeting_point_'.$point_id.'">'.implode(', ', $meeting_point['times']).' - <a href="'.$meeting_point['permalink'].'" target="_blank" open-mode="modal" data-obj-id="'.$point_id.'" data-lat="'.$meeting_point['lat'].'" data-lng="'.$meeting_point['lng'].'" data-address="'.$meeting_point['address'].'" >'.$meeting_point['address'].'</a></label>
              </div>';
                }

                $find_closes_loc_text = BABE_Settings::$settings['google_map_remove'] || !BABE_Settings::$settings['google_map_active'] || is_admin() ? '' : ' (' . '<a href="#block_meeting_points">'.__('find closest location', 'ba-book-everything').'</a>'.')';

                $input_fields['meeting_points'] = apply_filters('babe_booking_form_meeting_points', '<div class="booking-form-block booking-meeting-point">
              <label class="booking_form_input_label"><span class="booking_form_step_num">'.$i.'</span>'.__('Select meeting point', 'ba-book-everything'). $find_closes_loc_text .':</label>
              '.implode(' ', $meeting_points_output).'
              </div>', $meeting_points_output, $meeting_points, $babe_post, $i);
                $i++;

                $input_fields = apply_filters('babe_booking_form_after_meeting_points', $input_fields, $babe_post, $av_times, $rules_cat);

                if (!is_admin()){

                    $modal_meeting_points = '<div id="babe_overlay_container">
            <div id="block_address_map_with_direction" class="babe_overlay_inner">
              <span id="modal_close"><i class="fas fa-times"></i></span>
              
                <h3>'.__('Find a route from your location', 'ba-book-everything').'</h3>
              
                <div id="google_map_address_with_direction" data-obj-id="" data-lat="" data-lng="" data-address="">
                </div>
                
                <div id="route_to_buttons">
                    <button id="route_to_button_ok" data-point-id="" class="btn button route_to_point_button">'.__('Ok', 'ba-book-everything').'</button>
                </div>  

            </div>
          </div>
          <div id="babe_overlay"></div>';

                }

            } //// end if !empty($meeting_points)
        }

        /////////////////////////////////////

        $fees_html = self::list_fees($babe_post);

        $input_fields['fees'] = '';

        if ($fees_html){
            $input_fields['fees'] = '
              <div class="booking-form-block booking-fees-block">
               '.$fees_html.'
              </div>';
        }

        /////////////////////////////////////

        $input_fields['services'] = '';

        if (BABE_Settings::$settings['services_to_booking_form']){

            $services_html = self::list_add_services($babe_post);

            if ($services_html){
                $input_fields['services'] = '
              <div class="booking-form-block booking-services-block">
              '.$services_html.'
              </div>';
            }
        }

        ////////////////////////////////////

        $input_fields = apply_filters('babe_booking_form_input_fields', $input_fields, $babe_post, $av_times, $rules_cat);

        $after_hidden_fields = apply_filters('babe_booking_form_after_hidden_fields', '', $babe_post, $av_times, $rules_cat);

        if ( BABE_Settings::$settings['disable_guest_bookings'] ){

            $submit_button = '<div class="booking_form_login_required">'.apply_filters('babe_booking_form_login_required_text', __('Please login or register to continue your booking', 'ba-book-everything')).'</div>';

        } else {
            $submit_button = '<button class="btn button booking_form_submit" data-post-id="'.$post_id.'"><i class="fas fa-shopping-cart"></i> '.apply_filters('babe_booking_form_submit_button_label', __('Book Now', 'ba-book-everything')).'</button>';
        }

        $output .= '<form id="booking_form" name="booking_form" method="post" action="" data-post-id="'.$post_id.'" class="booking_form_type_'.$rules_cat['rules']['basic_booking_period'].'">
            
            <div class="input_group">
            
            '.implode('', $input_fields).'
            
            </div>
            
            <div id="total_group">
                <label class="booking_form_input_label">'.__('Total:', 'ba-book-everything').'</label>
                <div id="booking_form_total">
                </div>
            </div>
            
            <div id="error_group">
                <label class="booking_form_error_label">'.__('Please fill in all the data.', 'ba-book-everything').'</label>
            </div>
            
            <input type="hidden" name="booking_obj_id" value="'.$post_id.'">
            <input type="hidden" name="action" value="'.$action.'">
            '.$after_hidden_fields.'
            
            <div class="submit_group">
            '.$submit_button.'
            </div>
            
            </form>';

        /*<button class="btn button booking_form_calculate" data-post-id="'.$post_id.'"><i class="fas fa-calculator"></i>'.__('Calculate', 'ba-book-everything').'</button>*/

        if ( $rules_cat['rules']['booking_mode'] === 'request' ){

            $output = self::get_request_booking_form( $babe_post );

        } elseif ( empty($av_cal) ){

            $output = '';
        }

        $output = apply_filters(
            'babe_booking_form_html',
            $output,
            $babe_post,
            $input_fields,
            $after_hidden_fields
        );
        
        if ($output){
            $output = '
            <div id="booking_form_block">
            '.$output.'
              
            '.$modal_meeting_points.'
            </div>';
        }      
        
        return $output; 
    }

    public static function get_request_booking_form( array $babe_post ): string
    {
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id( $babe_post['ID'] );

        $input_fields = [];

        if ( $rules_cat['rules']['basic_booking_period'] !== 'single_custom' || empty($babe_post['start_date']) ){

            $input_fields['date_from'] = '
               <div class="request_booking_input">
                  <label for="request_booking_name">'.__('From:', 'ba-book-everything').'</label>
                  <div class="booking-date">
                  <i class="far fa-calendar-alt"></i>
                  <input type="text" class="booking_date" id="request_booking_date_from" name="request_booking_date_from" required autocomplete="off" value="">
                  </div>
               </div>';

            if ( $rules_cat['rules']['basic_booking_period'] !== 'recurrent_custom' ){

                $input_fields['date_to'] = '
               <div class="request_booking_input">
                  <label for="request_booking_name">'.__('To:', 'ba-book-everything').'</label>
                  <div class="booking-date">
                  <i class="far fa-calendar-alt"></i>
                  <input type="text" class="booking_date" id="request_booking_date_to" name="request_booking_date_to" required autocomplete="off" value="">
                  </div>
               </div>';
            }
        } else {

            $date_from_obj = new DateTime( BABE_Calendar_functions::date_to_sql($babe_post['start_date']).' '.$babe_post['start_time']);

            $input_fields['date_from'] = '
               <input type="hidden" id="request_booking_date_from" name="request_booking_date_from" value="'.$date_from_obj->format(BABE_Settings::$settings['date_format']).'">
               ';
        }

        $output = '<div id="request_booking_form_block">
<form id="request_booking_form" name="request_booking_form" method="post" action="">
            
            <div class="input_group">
               '. implode('', $input_fields).'
               <div class="request_booking_input">
                  <label for="request_booking_name">'.__('Your name', 'ba-book-everything').'</label>
                  <input type="text" name="request_booking_name" id="request_booking_name" required>
               </div>
            
               <div class="request_booking_input">
                  <label for="request_booking_email">'.__('Your email', 'ba-book-everything').'</label>
                  <input type="email" name="request_booking_email" id="request_booking_email" required>
               </div>
               
               <div class="request_booking_input">
                  <textarea name="request_booking_message" id="request_booking_message" placeholder="'.__('Message', 'ba-book-everything').'" required></textarea>
               </div>
               '.apply_filters('babe_request_booking_form_custom_inputs_html', '', $babe_post).'          
            </div>
            
            <input type="hidden" id="booking_obj_id" name="booking_obj_id" value="'.$babe_post['ID'].'">
            
            <div class="submit_group">
               
               <button class="btn button request_booking_form_submit"><i class="fas fa-envelope"></i> '.apply_filters('babe_booking_form_submit_button_label', __('Request a reservation', 'ba-book-everything')).'</button>
               
            </div>
            
            </form>
            </div>';

        return apply_filters('babe_request_booking_form_html', $output, $babe_post);
    }

	//////////////////////////////////////

	/**
	 * Adds select time field to the search form.
	 *
	 * @param string $field_name
	 * @param string $field_title
	 * @param array $values_arr
	 * @param string $selected_key
	 * @param boolean $required
	 *
	 * @return string
	 */
	public static function input_select_field_with_order(
        $field_name,
        $field_title,
        $values_arr,
        $selected_key,
        $required = false,
        $classes = '',
        $attr = ''
    ) {

		$output = '';

		if ( empty($values_arr) ){
			return $output;
		}
		if ( !empty($_GET["search_results_sort_by"]) ){
			$sort = in_array('asc', explode('_', $_GET["search_results_sort_by"])) ? 'asc' : 'desc';
		} else {
			$sort = 'asc';
		}

		$titles = array(
            'title_asc' => __( 'Title', 'ba-book-everything' ),
            'title_desc' => __( 'Title', 'ba-book-everything' ),
            'price_asc' => __( 'Price', 'ba-book-everything' ),
            'price_desc' => __( 'Price', 'ba-book-everything' ),
            'rating_asc' => __( 'Rating', 'ba-book-everything' ),
            'rating_desc' => __( 'Rating', 'ba-book-everything' ),
            'avdatefrom_asc' => __( 'Availability date', 'ba-book-everything' ),
            'avdatefrom_desc' => __( 'Availability date', 'ba-book-everything' ),
        );

		if (!isset($values_arr[$selected_key])){
			reset($values_arr);
			$selected_key = (string)key($values_arr);
			$order_field_parameter = 'title_' . (($sort === 'asc') ? '_desc': '_asc');
		} else {
			$order_field_parameter = explode('_', $selected_key)[0] . (($sort === 'asc') ?  '_desc' : '_asc');
		}

		foreach ($values_arr as $val_id => $value){

		    $values_arr[$val_id] = isset($titles[$val_id]) ? $titles[$val_id] : explode(' ', $value)[0];

			if (!in_array( $sort, explode('_', $val_id))){
				unset ($values_arr[$val_id]);
			}
		}

		foreach ($values_arr as $key => $value){
			$add_class = $key === $selected_key ? ' term_item_selected' : '';
			$output .= '<li class="term_item'.$add_class.'" data-id="'.$key.'" data-value="'.$value.'">'.$value.'</li>';
		}

		$output = '<ul class="input_select_list">
                '.$output.'
            </ul>';

		$add_required = $required ? 'required="required"' : '';

		$field_title = $field_title ? '<div class="input_select_title_value">' . $field_title . '</div>' : '';
		$field_name_label = $field_name.'_label';

		$field_name_class = $field_name;

		if (strpos($field_name, '[') !== false){

			$field_name_arr = explode('[', $field_name);
			$field_name_label = $field_name_arr[0].'_label['.$field_name_arr[1];
			$field_name_class = $field_name_arr[0];
		}
		switch (explode('_', $selected_key)[0]){
			case 'title':
			    $icon_name =  ($sort === 'asc') ? '<i class="fa fa-sort-alpha-down"></i>' :
                    '<i class="fa fa-sort-alpha-up"></i>' ;
			    break;
			case 'price':
            case 'avdatefrom':
			    $icon_name =  ($sort === 'asc') ? '<i class="fa fa-sort-numeric-down"></i>' :
                    '<i class="fa fa-sort-numeric-up"></i>';
			    break;
			default:
			    $icon_name =  ($sort === 'asc') ? '<i class="fa fa-sort-amount-down"></i>' :
                    '<i class="fa fa-sort-amount-up"></i>';
			    break;
		}

		$sort ='<div class="input_select_field input_select_field_'.esc_attr($field_name_class).' '.esc_attr($classes).'" data-name="'.$field_name.'" '.$attr.' tabindex="0">
							<div class="input_select_sort">'.$icon_name.'

                              <input type="hidden" class="input_select_input_value" name="'.$field_name.'" '.$required.' value="'.$order_field_parameter.'">
                             </div>	
						</div>
					';

		$output = $sort . '
						<div class="input_select_field input_select_field_'.esc_attr($field_name_class).' '.esc_attr($classes).'" data-name="'.$field_name.'" '.$attr.' tabindex="0">
							<div class="input_select_title">
                                ' . $field_title . '
                                <div class="input_select_wrapper">
                                  <input type="text" class="input_select_input" name="'.$field_name_label.'" '.$required.' value="'.(isset($values_arr[$selected_key]) ? $values_arr[$selected_key] : '').'">
                                  <input type="hidden" class="input_select_input_value" name="'.$field_name.'" '.$required.' value="'.$selected_key.'">
                                  ' . $output . '
                                  <i class="fas fa-chevron-down"></i>
                                </div>
                             </div>	
						</div>
					';

		return $output;
	}

	//////////////////////////////////////

	/**
	 * Adds select time field to the search form.
	 *
	 * @param string $field_name
	 * @param array $values_arr
     * @param string | boolean $selected_key
	 * @param boolean $required
     * @param string $classes
     * @param string $attr
	 *
	 * @return string
	 */
	public static function input_select_cloud(
        $field_name,
        $values_arr = [],
        $selected_key = false,
        $required = true,
        $classes = '',
        $attr = ''
    ) {

		$output = '';

		if ( empty($values_arr) ){
			return $output;
		}

        reset($values_arr);

		foreach ($values_arr as $key => $value){
			$add_class = $key === $selected_key ? ' term_item_selected' : '';
			$add_class .= $value ? ' term_item_available' : '';
			$output .= '<div class="cloud_term_item'.$add_class.'" data-id="'.$key.'" data-value="'.$key.'">'.$key.'</div>';
		}

		$add_required = $required ? 'required="required"' : '';

		$field_name_class = $field_name;

		if (strpos($field_name, '[') !== false){

			$field_name_arr = explode('[', $field_name);
			$field_name_class = $field_name_arr[0];
		}

		$output = '<div class="input_select_cloud input_select_field_'.esc_attr($field_name_class).' '.esc_attr($classes).'" data-name="'.$field_name.'" '.$attr.' tabindex="0">
            <input type="hidden" class="input_select_input" name="'.$field_name.'" '.$add_required.' value="'.$selected_key.'">
            ' . $output . '
			</div>';

		return $output;
	}

//////////////////////////////////////

        /**
		 * Adds select time field to the search form.
		 * 
		 * @param string $field_name
         * @param string $field_title
         * @param array $values_arr
         * @param string $selected_key
         * @param boolean $required
         * @param string $classes
         * @param string $attr
		 * 
		 * @return string
		 */
		public static function input_select_field( $field_name, $field_title = '', $values_arr = array(), $selected_key = false, $required = false, $classes = '', $attr = '' ) {
		  
            $output = '';
            
            if (empty($values_arr)){
                return $output;
            }
            
            if (!isset($values_arr[$selected_key])){
                reset($values_arr);
                $selected_key = key($values_arr);
            }
            
			foreach ($values_arr as $key => $value){
                $add_class = $key === $selected_key ? ' term_item_selected' : '';
                $output .= '<li class="term_item'.$add_class.'" data-id="'.$key.'" data-value="'.$value.'">'.$value.'</li>';
            }
            
            $output = '<ul class="input_select_list">
                '.$output.'
            </ul>';
            
            $add_required = $required ? 'required="required"' : '';
            
            $field_title = $field_title ? '<div class="input_select_title_value">' . $field_title . '</div>' : '';
            $field_name_label = $field_name.'_label';
            
            $field_name_class = $field_name;
            
            if (strpos($field_name, '[') !== false){
                
                $field_name_arr = explode('[', $field_name);
                $field_name_label = $field_name_arr[0].'_label['.$field_name_arr[1];
                $field_name_class = $field_name_arr[0];
            }		
			
            $output = '
						<div class="input_select_field input_select_field_'.esc_attr($field_name_class).' '.esc_attr($classes).'" data-name="'.$field_name.'" '.$attr.' tabindex="0">
							<div class="input_select_title">
                                ' . $field_title . '
                                <div class="input_select_wrapper">
                                  <input type="text" class="input_select_input" name="'.$field_name_label.'" '.$required.' value="'.(isset($values_arr[$selected_key]) ? $values_arr[$selected_key] : '').'">
                                  <input type="hidden" class="input_select_input_value" name="'.$field_name.'" '.$required.' value="'.$selected_key.'">
                                  ' . $output . '
                                  <i class="fas fa-chevron-down"></i>
                                </div>
                             </div>	
						</div>
					';			
			
			return $output;
		}
        
////////////////////////////
     /**
	 * Get range input select options.
     * 
     * @param int $start
     * @param int $end
     * @param int $step
     * @param int $selected_value
     * 
     * @return string
	 */ 
     public static function get_range_input_select_options($start, $end, $step = 1, $selected_value = ''){
     
        $output = '';
      
        for($i = $start; $i <= $end; $i += $step){
           $add_class = $i === $selected_value ? ' term_item_selected' : '';
           $output .= '<li class="term_item'.$add_class.'" data-id="'.$i.'" data-value="'.$i.'">'.$i.'</li>';
        }
      
        return $output;
      
     }        
        
////////////////////////////
    /**
	 * Time select array.
     * 
     * @param string $date in d/m/Y or m/d/Y format
     * @param int $post_id
     * @param boolean $is_from
     * @param string $start_time '00:00'
     * @param array $order_item_args
     * @param int $step
     * @param boolean $is_hourly
     * 
     * @return array
	 */
    public static function get_time_select_arr(
        $date,
        $post_id,
        $is_from = true,
        $start_time = '00:00',
        $order_item_args = [],
        $step = 60,
        $is_hourly = false
    ){

        $output = [];
        
        $date_from = BABE_Calendar_functions::date_to_sql($date);
        $date_from_obj = new DateTime($date_from);

        $start_time_obj = new DateTime( $date_from_obj->format('Y-m-d') . ' ' .$start_time);
        
        $av_cal = BABE_Calendar_functions::get_av_cal($post_id, $date_from, $date_from, $order_item_args);

        if ( empty($av_cal[$date_from_obj->format('Y-m-d')]['times']) ){
            return $output;
        }

        $times = $av_cal[$date_from_obj->format('Y-m-d')]['times'];

        $babe_post = BABE_Post_types::get_post($post_id);

        if ( $is_hourly ){

            if ( $is_from && !empty($babe_post['av_time_from']) ) {

                foreach ( $times as $time => $available ){
                    if ( !in_array($time, $babe_post['av_time_from']) ){
                        continue;
                    }
                    $output[$time] = $available;
                }
            } elseif ( !$is_from && !empty($babe_post['av_time_to']) ) {

                foreach ( $times as $time => $available ){
                    if ( !in_array($time, $babe_post['av_time_to']) ){
                        continue;
                    }
                    $output[$time] = $available;
                }
            } else {
                $output = $times;
            }

            return $output;
        }

        //=============================//

        if ($step <=0 || $step > 30){
            $step = 0;
        }

        $step = (int)floor($step/5);
        if ($step === 5) {
            $step = 6;
        }

        $min_arr = array(':00');

        if ($step){
            for ($i = ($step*5); $i < 60; $i = $i+$step*5){
                $min_arr[] = $i < 10 ?  ':0'.$i : ':'.$i;
            }
        }

        $added_times = false;
        $allow_time = false;
        end($times);
        $last_time = key($times);
        $times_count = 0;
        $time_before = '';

        if ( $is_from ){
            $start_time_obj = new DateTime( $date_from_obj->format('Y-m-d') . ' ' . $last_time );
        }

        for($i=0; $i <= 23; $i++){

            $i_text = $i < 10 ? '0'.$i : $i;

            foreach ($min_arr as $min_text){

                $current_time = $i_text.$min_text;
                $current_time_obj = new DateTime($date_from_obj->format('Y-m-d') . ' ' . $current_time);
                $check_time = $current_time;

                if ( $is_from ){
                    $check_time = $start_time_obj->format('H:i');
                }

                if( isset($times[$check_time]) && $current_time === $check_time ){

                    $allow_time = $times[$check_time]
                        && (
                            $is_from
                            || !$added_times
                            || $allow_time
                        );

                    if (
                        !$allow_time
                        && $times_count === 1
                        && $time_before
                    ){
                        unset($output[$time_before]);
                        $times_count = 0;
                        $time_before = '';
                    }
                }

                if (
                    $allow_time
                    && $is_from
                    && !empty($babe_post['av_time_from'])
                    && !in_array($current_time, $babe_post['av_time_from'])
                ){
                    continue;
                }

                if (
                    $allow_time
                    && !$is_from
                    && !empty($babe_post['av_time_to'])
                    && !in_array($current_time, $babe_post['av_time_to'])
                ){
                    continue;
                }

                if ( $allow_time && $current_time_obj >= $start_time_obj){

                    $output[$current_time] = $current_time;
                    $times_count++;
                    $time_before = $current_time;
                    $added_times = true;
                }
            } //// end foreach $min_arr
        } /// end for $i
    
        return $output;
    }                
    
////////////////////////////
    /**
	 * Add checkout form to page.
     * 
     * @param array $args
     * 
     * @return string
	 */
    public static function admin_order_confirm_page_content($args){

        $output = '';
        
        $args = wp_parse_args( $args, array(
            'order_id' => 0,
            'order_num' => '',
            'order_admin_hash' => '',
            'order_status' => '',
            'current_action' => '',
            'action_update' => '',
            'check_update' => '',
        ));
        
        if ('av_confirmation' != $args['order_status']){
            //// Order is confirmed or rejected
            
            $add_class = $args['order_status'] != 'not_available' ? 'confirm' : 'reject';
            
            $output .= '<h4 class="babe_message_order babe_message_order_'.$add_class.'">';
            
            $output .= $args['order_status'] != 'not_available' ? sprintf(__( 'Order #%s is confirmed.', 'ba-book-everything' ), $args['order_num']) : sprintf(__( 'Order #%s is rejected.', 'ba-book-everything' ), $args['order_num']);
            
            $output .= '</h4>';
            
        } else {
            $args['check_update'] = 1;
            
            unset($args['order_status']);
            
            $message = $args['action_update'] == 'confirm' ? __( 'Confirm Order #', 'ba-book-everything' ) : __( 'Reject Order #', 'ba-book-everything' );
            
            $message .= $args['order_num'];
            
            $url = BABE_Settings::get_admin_confirmation_page_url($args);
            
            //// Button as url with $message and check_update = 1
            $output .= '<div class="babe_admin_order_confirm">
              <a href="'.$url.'" class="babe_button_admin_order babe_button_admin_order_'.$args['action_update'].'">'.$message.'</a>
            </div>';
        }
        
        return $output; 
    }    
    
////////////////////////////
    /**
	 * Create order items html.
     * @param int $order_id
     * @return string
	 */
    public static function order_items($order_id){
        $output = '';
        
        if (isset(self::$order_items[$order_id])){
            return self::$order_items[$order_id];  
        }

        if ( !$order_id ){
            return $output;
        }

        $order_items_arr = BABE_Order::get_order_items($order_id);

        if ( empty($order_items_arr) ){
            return $output;
        }

        $ages_arr_ordered_by_id = BABE_Post_types::get_ages_arr_ordered_by_id();
        $thumbnail = apply_filters('babe_order_items_img_thumbnail', 'thumbnail');

        $currency = BABE_Order::get_order_currency($order_id);

        $output .= '<table class="table_order_items_details"><tbody>';

        $subtotal = 0;
        $taxes_amount = 0;
        $total = 0;
        $fees_amount = 0;
        $fees_html = '';
        $taxes_html = '';
        $payment_gateway_html = '';

        foreach($order_items_arr as $item_id => $item){

            $booking_obj_id = $item['booking_obj_id'];
            $post = BABE_Post_types::get_post($booking_obj_id);

            if ( empty($post) ){
                continue;
            }

            $title = '<a target="_blank" href="'.get_permalink($booking_obj_id).'">'.$item['order_item_name'].'</a>';

            $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($booking_obj_id);

            $total_item_prices = BABE_Prices::get_obj_total_price($booking_obj_id, $item['meta']['price_arr']);

            /// get post thumbnail
            $image_srcs = wp_get_attachment_image_src( get_post_thumbnail_id( $booking_obj_id ), $thumbnail);
            $image = $image_srcs ? '<a target="_blank" href="'.get_permalink($booking_obj_id).'"><img src="'.$image_srcs[0].'"></a>' : '';

            $output .= '<tr><td class="order_item_row_image">'.$image.'</td>';

            $output .= '<td class="order_item_row_details order_item_row_main_details">
                <table class="table_order_item_row_details"><tbody>
                
                <tr><td class="order_item_info order_item_info_title">
                '.$title.'
                </td></tr>';

            /// date from - duration/ date to
            $date_format = $rules_cat['rules']['basic_booking_period'] === 'day' || $rules_cat['rules']['basic_booking_period'] === 'single_custom' || $rules_cat['rules']['basic_booking_period'] === 'hour' ? get_option('date_format').' - '.get_option('time_format') : get_option('date_format');

            $date_from_obj = new DateTime($item['meta']['date_from']);
            $date_from_html = '
                  <span class="order_item_td_value">'.( date_i18n( $date_format, strtotime($date_from_obj->format('Y-m-d H:i')) ) ).'</span>';

            if ($rules_cat['rules']['basic_booking_period'] !== 'recurrent_custom'){
                $date_to_obj = new DateTime($item['meta']['date_to']);
                $date_from_html = '<span class="order_item_td_label">'.__( 'From:', 'ba-book-everything' ).'</span>'. $date_from_html . '<span class="order_item_td_label">'.__( 'To:', 'ba-book-everything' ).'</span>
                  <span class="order_item_td_value">'.( date_i18n( $date_format, strtotime($date_to_obj->format('Y-m-d H:i')) ) ).'</span>';
            } else {
                $date_from_html = '<span class="order_item_td_label">'.__( 'Date:', 'ba-book-everything' ).'</span>'. $date_from_html . '<span class="order_item_td_label">'.__( 'Time:', 'ba-book-everything' ).'</span>
                  <span class="order_item_td_value">'.$date_from_obj->format(get_option('time_format')).'</span>';
            }

            $date_from_html = '<tr><td class="order_item_info order_item_info_dates">
                  '.$date_from_html.'
                  </td></tr>';

            $output .= apply_filters('babe_order_items_date_from_html', $date_from_html, $post, $rules_cat, $date_from_obj, $item);

            $duration = BABE_Post_types::get_post_duration($post);
            $duration = $duration ? '
                  <tr><td class="order_item_info order_item_info_duration">
                  <span class="order_item_td_label">'.__( 'Duration:', 'ba-book-everything' ).'</span>
                  <span class="order_item_td_value">'.$duration.'</span>
                  </td></tr>' : '';

            $output .= apply_filters('babe_order_items_duration_html', $duration, $post, $rules_cat);

            /// Departure - meeting point
            $meeting_points_arr = BABE_Post_types::get_post_meeting_points($post);
            $meeting_point_id = isset($item['meta']['booking_meeting_point']) ? (int)$item['meta']['booking_meeting_point'] : '';

            if (!empty($meeting_points_arr) && $meeting_point_id && isset($meeting_points_arr[$meeting_point_id])){
                $mp_html = '
                  <tr><td class="order_item_info order_item_info_meeting_point">
                  <span class="order_item_td_label">'.__( 'Departure from:', 'ba-book-everything' ).'</span>
                  <span class="order_item_td_value"><a href="'.$meeting_points_arr[$meeting_point_id]['permalink'].'" target="_blank">'.$meeting_points_arr[$meeting_point_id]['address'].'</a></span>
                  </td></tr>';

                if (isset($meeting_points_arr[$meeting_point_id]['times'][$date_from_obj->format('H:i')])){
                    $date_tmp = new DateTime('2017-01-01 '.$meeting_points_arr[$meeting_point_id]['times'][$date_from_obj->format('H:i')]);
                    $departure_time = $date_tmp->format(get_option('time_format'));

                    $mp_html .= '
                  <tr><td class="order_item_info order_item_info_meeting_point_time">
                  <span class="order_item_td_label">'.__( 'Departure time:', 'ba-book-everything' ).'</span>
                  <span class="order_item_td_value">'.$departure_time.'</span>
                  </td></tr>';
                } //// end if $meeting_points_arr[$meeting_point_id]['times'][$date_from_obj->format('H:i')])

                $output .= apply_filters('babe_order_items_meeting_points', $mp_html, $post, $rules_cat, $item, $total_item_prices);

                $output = apply_filters('babe_order_items_after_meeting_points', $output, $post, $rules_cat, $item, $total_item_prices);

            } ///// end if !empty($meeting_points_arr)

            if (!isset($rules_cat['category_meta']['categories_remove_guests']) || !$rules_cat['category_meta']['categories_remove_guests']){
                /// Prices by age $item['meta']['price_arr']
                $label_guests = $rules_cat['rules']['booking_mode'] === 'tickets' ? __( 'Tickets:', 'ba-book-everything' ) : __( 'Guests:', 'ba-book-everything' );

                $guests_html = '
                  <tr><td class="order_item_info order_item_info_guests">
                  <span class="order_item_td_label">'.$label_guests.'</span>
                  <span class="order_item_td_value"><table class="order_item_age_prices"><tbody>';

                foreach($item['meta']['guests'] as $age_id => $guests_num){

                    if ($guests_num){

                        if ( isset($ages_arr_ordered_by_id[$age_id]) ){
                            $age_title = $ages_arr_ordered_by_id[$age_id]['name'];
                        } else {
                            $age_title = $guests_num > 1 ? __( 'persons', 'ba-book-everything' ) : __( 'person', 'ba-book-everything' );
                        }

                        $guests_price_html = isset($item['meta']['price_arr']['clear'][$age_id]) && $rules_cat['rules']['booking_mode'] !== 'object'
                            ? '
                      <td><span class="order_item_age_price">'.BABE_Currency::get_currency_price( $item['meta']['price_arr']['clear'][$age_id], $currency).'</span></td>' : '';

                        if (!$age_id){

                            $guests_html .= '
                        <tr>
                          <td class="order_item_age_guests"><span class="order_item_age_guests_num">'.$guests_num.' '.$age_title.'</span></td>
                        '.$guests_price_html.'
                        </tr>';

                        } else {

                            $guests_html .= '
                      <tr>
                        <td class="order_item_age_title">'.$age_title.'</td>
                        <td class="order_item_age_guests"><span class="order_item_age_guests_num">x'
                                .$guests_num
                                .( $guests_price_html ? '=' : '')
                                .'</span></td>
                      '.$guests_price_html.'
                      </tr>';
                        }
                    }
                }

                $guests_html .= '</tbody></table>
                  </span>
                </td></tr>';

                $output .= apply_filters('babe_order_items_guests', $guests_html, $post, $rules_cat, $item, $total_item_prices);

                $output = apply_filters('babe_order_items_after_guests', $output, $post, $rules_cat, $item, $total_item_prices);
            }

            $output = apply_filters('babe_order_items_after_main_rows', $output, $post, $rules_cat, $item, $total_item_prices);

            $output .= '
                </tbody></table>
                
                </td><td class="order_item_row_price">';

            /// Total item price
            $discount_note = $item['meta']['price_arr']['discount']
                ? '<tr><td class="order_item_discount_note">'.sprintf(__( 'Price Cut %d%% off applied', 'ba-book-everything' ), $item['meta']['price_arr']['discount']).'</td></tr>'
                : '';

            $guests_price = 0;
            foreach($item['meta']['guests'] as $age_id => $guests_num){
                if ( $guests_num && !empty($item['meta']['price_arr']['clear'][$age_id]) ){
                    $guests_price += $item['meta']['price_arr']['clear'][$age_id];
                    if( $rules_cat['rules']['booking_mode'] === 'object' ){
                        break;
                    }
                }
            }

            $output .= '
                <table class="table_order_item_total_price"><tbody>
                <tr><td class="order_item_total_price">'.BABE_Currency::get_currency_price($guests_price, $currency).'
                </td></tr>
                '.$discount_note.'
                </tbody></table>
                </td></tr>';

            $output = apply_filters('babe_order_items_before_services', $output, $post, $rules_cat, $item, $total_item_prices);

            /// Services
            if (!empty($item['meta']['price_arr']['services'])){

                foreach($item['meta']['price_arr']['services'] as $service_id => $service_prices){

                    /// get service meta
                    $service_meta = (array)get_post_meta($service_id);
                    foreach($service_meta as $key=>$val){
                        $service_meta[$key] = maybe_unserialize($val[0]);
                    }

                    $output .= '<tr><td class="order_item_row_image"></td>
               <td class="order_item_row_details">';

                    $price = 0;
                    $age_details = array();
                    foreach($service_prices['clear'] as $age_id => $age_price){

                        $guests_num = !empty($item['meta']['guests'][$age_id]) ? $item['meta']['guests'][$age_id] : 0;

                        $service_qty = $item['meta']['services'][$service_id][$age_id] ?? $guests_num;

                        $price += $age_price;

                        if ($service_qty){
                            $age_details[$age_id] = $age_id && isset($ages_arr_ordered_by_id[$age_id]) ? $ages_arr_ordered_by_id[$age_id]['name'] . ' x'.$service_qty : 'x'.$service_qty;
                            if ( !$age_price ){
                                $age_details[$age_id] .= ' ('.BABE_Currency::get_zero_price_display_value($currency) . ')';
                            } else {
                                $age_details[$age_id] .= ' ('.BABE_Currency::get_currency_price($age_price, $currency) . ')';
                            }
                        }
                    }

                    if (
                        !in_array($service_meta['service_type'], ['booking', 'day', 'night'])
                        && isset($item['meta']['services'][$service_id])
                    ){
                        foreach($item['meta']['services'][$service_id] as $age_id => $service_qty){
                            if ( isset($age_details[$age_id]) || !$service_qty ){
                                continue;
                            }
                            $age_details[$age_id] = $age_id && isset($ages_arr_ordered_by_id[$age_id]) ? $ages_arr_ordered_by_id[$age_id]['name'] . ' x'.$service_qty : 'x'.$service_qty;
                            $age_details[$age_id] .= ' ('.BABE_Currency::get_zero_price_display_value($currency) . ')';
                        }
                    }

                    $service_title = get_the_title($service_id);

                    $output .= '<span class="order_item_service_title">'.$service_title.'</span> <span class="order_item_service_guests">'.implode(', ', $age_details).'</span>';

                    $output .= '
               </td>
               <td class="order_item_row_price">
                <table class="table_order_item_total_price"><tbody>
                <tr><td class="order_item_total_price">'.BABE_Currency::get_currency_price($price, $currency).'
                </td></tr>
                </tbody></table>
               </td>
               </tr>';

                }

                $output = apply_filters('babe_order_items_after_services', $output, $post, $rules_cat, $item, $total_item_prices);
            }

            $subtotal += $total_item_prices['total'];
            $taxes_amount += $total_item_prices['total_item_with_taxes'] + array_sum($total_item_prices['total_services_with_taxes']) - $total_item_prices['total_item'] - array_sum($total_item_prices['total_services']);
            $total += $total_item_prices['total_with_taxes'];

            // $taxes_html
            $taxes_percents = (float)apply_filters('babe_html_order_items_post_tax', BABE_Post_types::get_post_tax($post['ID']), $post['ID']);
            if ($taxes_percents){
                $taxes_title = !empty($rules_cat['category_meta']['categories_tax_title']) ? $rules_cat['category_meta']['categories_tax_title'] : __( 'Taxes', 'ba-book-everything' );

                $taxes_html .= '
                <tr><td class="order_items_row_total order_items_taxes" colspan="2">
            <span class="order_items_row_total_label">'.$taxes_title.' '.$taxes_percents.'%:</span>
            </td>
            <td class="order_items_row_total_amount order_items_taxes">
            <span id="order_items_row_taxes_amount">'.BABE_Currency::get_currency_price($taxes_amount, $currency).'</span>
            </td></tr>';
            }

            // $fees_html
            if ( !empty($total_item_prices['total_fees']) ){

                $fees_amount += array_sum($total_item_prices['total_fees']);

                foreach($total_item_prices['total_fees'] as $fee_id => $fee_amount){

                    $fees_html .= '
            <tr>
            <td class="order_items_row_total order_items_row_subtotal" colspan="2">
            <span class="order_items_row_total_label">'.get_the_title($fee_id).':</span>
            </td>
            <td class="order_items_row_total_amount order_items_row_subtotal">
            <span class="order_items_row_fee_amount">'.BABE_Currency::get_currency_price($fee_amount, $currency).'</span>
            </td></tr>';
                }
            }

            // $payment_gateway_html
            if ( $total_item_prices['total_payment_gateway_fee'] ){

                $payment_gateway_html .= '
            <tr>
            <td class="order_items_row_total order_items_row_subtotal" colspan="2">
            <span class="order_items_row_total_label">'. BABE_Order::get_order_payment_gateway_fee_title($order_id) .' ('.BABE_Order::get_order_payment_gateway_fee_percents($order_id).'%):</span>
            </td>
            <td class="order_items_row_total_amount order_items_row_subtotal">
            <span class="order_items_row_gateway_fee_amount">'.BABE_Currency::get_currency_price($total_item_prices['total_payment_gateway_fee'], $currency).'</span>
            </td></tr>';

            }
        } /// end foreach $order_items_arr

        /// Total price

        $output .= '
            <tr>
            <td class="order_items_row_total order_items_row_subtotal" colspan="2">
            <span class="order_items_row_total_label">'.__( 'Subtotal:', 'ba-book-everything' ).'</span>
            </td>
            <td class="order_items_row_total_amount order_items_row_subtotal">
            <span id="order_items_row_subtotal_amount">'.BABE_Currency::get_currency_price($subtotal, $currency).'</span>
            </td></tr>';

        $output .= apply_filters('babe_order_items_taxes_html', $taxes_html, $order_id, $order_items_arr, $ages_arr_ordered_by_id);

        $output .= apply_filters('babe_order_items_fees_html', $fees_html, $order_id, $order_items_arr, $ages_arr_ordered_by_id);

        //// payment gateway fee
        $output .= apply_filters('babe_order_items_gateway_fee_html', $payment_gateway_html, $order_id, $order_items_arr, $ages_arr_ordered_by_id);
        ///

        $order_coupon_num = BABE_Order::get_order_coupon_num($order_id);
        $order_coupon_amount = BABE_Order::get_order_coupon_amount_applied($order_id);
        $total_with_coupon = BABE_Order::get_order_total_amount($order_id);
        if ($order_coupon_amount){
            $output .= '
             <tr><td class="order_items_row_total order_items_row_coupon" colspan="2">
            <span class="order_items_row_total_label">'.__( 'Discount coupon', 'ba-book-everything' ).' '.$order_coupon_num.':</span>
            </td>
            <td class="order_items_row_total_amount order_items_row_coupon">
            <span id="order_items_row_total_amount">-'.BABE_Currency::get_currency_price($order_coupon_amount, $currency).'</span>
            </td></tr>';
        }

        $output .= '
            <tr><td class="order_items_row_total" colspan="2">
            <span class="order_items_row_total_label">'.__( 'Total:', 'ba-book-everything' ).'</span>
            </td>
            <td class="order_items_row_total_amount">
            <span id="order_items_row_total_amount">'.BABE_Currency::get_currency_price($total, $currency).'</span>
            </td></tr>';

        $prepaid_received = BABE_Order::get_order_prepaid_received($order_id) - BABE_Order::get_order_refunded_amount($order_id);

        $output .= '
            <tr><td class="order_items_row_total order_items_row_paid" colspan="2">
            <span class="order_items_row_total_label">'.__( 'Amount Paid:', 'ba-book-everything' ).'</span>
            </td>
            <td class="order_items_row_total_amount order_items_row_paid">
            <span id="order_items_row_paid_amount">'.BABE_Currency::get_currency_price($prepaid_received, $currency).'</span>
            </td></tr>';

        $amount_to_pay = $total_with_coupon - $prepaid_received;

        $output .= '
            <tr><td class="order_items_row_total order_items_row_due" colspan="2">
            <span class="order_items_row_total_label">'.__( 'Amount Due:', 'ba-book-everything' ).'</span>
            </td>
            <td class="order_items_row_total_amount order_items_row_due">
            <span id="order_items_row_paid_amount">'.BABE_Currency::get_currency_price($amount_to_pay, $currency).'</span>
            </td></tr>';

        $output .= '</tbody></table>';

        $output = apply_filters('babe_order_items_html', $output, $order_id, $order_items_arr, $ages_arr_ordered_by_id);
        
        self::$order_items[$order_id] = $output;
        
        return $output;
    }
    
///////////////////////////////////////    
    /**
	 * Ajax apply coupon to the order
	 */
    public static function ajax_apply_coupon_to_order(){
        
        $output = 0;
        
        if (isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) && BABE_Coupons::coupons_active()){
           $args = wp_parse_args( $_POST, array(
            'order_id' => 0,
            'order_num' => '',
            'order_hash' => '',
            'coupon_num' => '',
           ));
           $order_id = absint($args['order_id']);
           $coupon_num = sanitize_text_field($args['coupon_num']);
           
           if (BABE_Order::is_order_valid($order_id, $args['order_num'], $args['order_hash'])){
               $output = (int)BABE_Coupons::apply_coupon_to_the_order($order_id, $coupon_num);
               BABE_Order::recalculate_order_total_amount($order_id);
           }  
        }
        
        echo $output;
        wp_die();                   
    }


    /**
     * Ajax remove coupon from the order
     */
    public static function ajax_remove_coupon_from_order(){

        $output = 0;

        if (isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) && BABE_Coupons::coupons_active()){
            $args = wp_parse_args( $_POST, array(
                'order_id' => 0,
                'order_num' => '',
                'order_hash' => '',
                'coupon_num' => '',
            ));
            $order_id = absint($args['order_id']);
            $coupon_num = sanitize_text_field($args['coupon_num']);

            if (BABE_Order::is_order_valid($order_id, $args['order_num'], $args['order_hash'])){
                $output = (int)BABE_Coupons::remove_coupon_from_the_order($order_id, $coupon_num);
                BABE_Order::recalculate_order_total_amount($order_id);
            }
        }

        echo $output;
        wp_die();
    }

///////////////////////////////////////
    /**
     * Ajax checkout payment method is changed
     */
    public static function ajax_checkout_payment_method_changed(){

        $output = [
            'order_items' => '',
            'amount_group' => '',
            'amount_changed' => 0,
        ];

        if (
            !isset($_POST['nonce'])
            || !wp_verify_nonce( $_POST['nonce'], self::$nonce_title )
            || empty($_POST['payment_method'])
        ){
            echo json_encode($output);
            wp_die();
        }

        $args = wp_parse_args( $_POST, array(
            'order_id' => 0,
            'order_num' => '',
            'order_hash' => '',
        ));

        $payment_method = sanitize_text_field($_POST['payment_method']);
        $all_payment_methods = BABE_Payments::get_payment_methods();

        $order_id = absint($args['order_id']);

        if ( !isset($all_payment_methods[$payment_method]) || !BABE_Order::is_order_valid($order_id, $args['order_num'], $args['order_hash']) ){
            echo json_encode($output);
            wp_die();
        }

        $old_total_amount = BABE_Order::get_order_total_amount($order_id);

        do_action('babe_checkout_payment_gateway_selected', $order_id, $payment_method);

        BABE_Order::recalculate_order_total_amount($order_id);

        $prepaid_amount = BABE_Order::get_order_prepaid_amount($order_id);
        $total_amount = BABE_Order::get_order_total_amount($order_id);
        $payment_model = BABE_Order::get_order_payment_model($order_id);

        $output['amount_group'] = self::checkout_form_element_amount_group($total_amount, $prepaid_amount, $payment_model, $order_id);
        $output['order_items'] = self::order_items($order_id);
        $output['amount_changed'] = $total_amount - $old_total_amount;

        echo json_encode($output);
        wp_die();
    }
    
////////////////$output, $order_id, $order_items_arr, $ages_arr    
     /**
	 * Output coupon field html with order items
     * 
     * @param string $content
     * @param int $order_id
     * @param array $order_items_arr
     * @param array $ages_arr
     * @return string
	 */
     public static function coupon_field_to_order_items($content, $order_id, $order_items_arr, $ages_arr){

         global $post;
        
        $output = $content;

         if ( !BABE_Coupons::coupons_active() ){
             return $output;
         }

         $coupon = BABE_Coupons::get_coupon_by_order_id($order_id);

         if ( !$coupon ){
             return $output;
         }

         //// render info block about applied coupon
         $order_currency = BABE_Order::get_order_currency($order_id);
         $coupon_num = BABE_Coupons::get_coupon_num($coupon->ID);
         $coupon_amount = BABE_Coupons::get_coupon_amount($coupon->ID);
         if ($coupon_amount["type"] === 'percent') {
             $val = $coupon_amount["value"].'%';
         } else {
             $val = BABE_Currency::get_currency_price( BABE_Prices::localize_price($coupon_amount["value"], $order_currency), $order_currency);
         }

         $remove_button = '';

         if ( !empty($post->ID) && $post->ID === (int)BABE_Settings::$settings['checkout_page'] ){
             $remove_button = '<span id="coupon_form_submit_loader"></span>
                <span id="remove_coupon_form_submit" class="btn button button-primary" data-coupon-num="'.esc_attr($coupon_num).'">'.__( 'Remove Coupon', 'ba-book-everything' ).'</span>';
         }

         $output .= '
            <div class="coupon-form-block-applied">
                <label class="coupon_form_input_label">
                '.sprintf(__( 'Coupon Code %s with amount %s was applied', 'ba-book-everything' ), $coupon_num, $val).'
                </label>
                '.$remove_button.'
            </div>';

         return $output;
    }
        
////////////////////////
     /**
	 * Output coupon field html with the checkout form
     * 
     * @param string $content
     * @param array $args
     * @return string
	 */
     public static function coupon_field_to_checkout_form($content, $args){
        
        $output = $content;
        $order_id = $args['order_id'];

         if ( !BABE_Coupons::coupons_active() ){
             return $output;
         }

         $coupon = BABE_Coupons::get_coupon_by_order_id($order_id);
         if ($coupon){
             return $output;
         }

         //// render input field with button Apply
         $output .= '
            <div class="coupon-form-block">
                <label class="coupon_form_input_label">'.__( 'Enter Coupon Code and get a discount!', 'ba-book-everything' ).'</label>
            <span class="coupon_form_input_field">
				   <input type="text" name="coupon_number" id="coupon_input_field" value="" placeholder="'.'" />
			</span>
            <span id="coupon_form_submit_loader"></span>
            <span id="coupon_form_submit" class="btn button button-primary">'.__( 'Apply Coupon', 'ba-book-everything' ).'</span>
            </div>';

         return $output;
    }    
    
////////////////////////////
    /**
	 * Create order customer details html.
     * @param int $order_id
     * @return string
	 */
    public static function order_customer_details($order_id){
        $output = $guests = '';
        
        if (isset(self::$order_customer_details[$order_id])){
            return self::$order_customer_details[$order_id];  
        }

        $order_meta = BABE_Order::get_order_customer_details($order_id);
        
        unset($order_meta['email_check']);
        
        $order_meta = apply_filters('babe_order_customer_details_fields', $order_meta, $order_id);
        
        $output .= '<table class="table_customer_details" cellpadding="0" cellspacing="0"><tbody>';

        foreach($order_meta as $field_name => $field_content){

            if ( $field_name === 'billing_address' ){

                $countries = BABE_Locales::countries();
                $states = BABE_Locales::states();
                $country = '';

                foreach ($field_content as $field_id => $field_value){

                    if ( empty($field_value) ){
                        continue;
                    }

                    if ( $field_id === 'country' && isset($countries[$field_value]) ){

                        $country = $field_value;
                        $field_value = $countries[$field_value];

                    } elseif ( $field_id === 'state' && isset($states[$country][$field_value]) ){
                        $field_value = $states[$country][$field_value];
                    }

                    $output .= '
	            <tr>
		            <td class="customer_field_label">'.self::checkout_field_label($field_id).'</td>
		            <td class="customer_field_content">'.$field_value.'</td>
	            </tr>
	            ';

                }

            } elseif ( $field_name === 'extra_guests' ){

        	    $ages_arr = BABE_Post_types::get_ages_arr_ordered_by_id();

        		foreach ($field_content as $id => $guest_data){

                    $field_content_arr = [];

                    foreach ($guest_data as $guest_data_key => $guest_data_value){

                        if ( $guest_data_key === 'age_group' && !empty($ages_arr[$guest_data_value]) ){
                            $guest_data_value = '('. $ages_arr[$guest_data_value]['name'] . ' ' . $ages_arr[$guest_data_value]['description'] . ')';
                        }
                        $field_content_arr[] = $guest_data_value;
                    }

                    if ( empty($field_content_arr) ){
                        continue;
                    }

                    $guests .= '
			            <tr>
				            <td class="customer_field_label">'.self::checkout_field_label('guest'). ' ' . ($id + 2)  . '</td>
				            <td class="customer_field_content">'.implode(' ', $field_content_arr).'</td>
			            </tr>
			            ';

		        }

	        } else {

		        $output .= '
	            <tr>
		            <td class="customer_field_label">'.self::checkout_field_label($field_name).'</td>
		            <td class="customer_field_content">'.$field_content.'</td>
	            </tr>
	            ';
	        }

        }
        
        $output .= $guests . '</tbody></table>';
        
        $output = apply_filters('babe_order_customer_details_html', $output, $order_id);
        
        self::$order_customer_details[$order_id] = $output;
        
        return $output;
    }            
    
////////checkout_field_required///
    /**
	 * Required tag for checkout fields.
     * @param string $required_tag
     * @param string $field_name
     * @return string
	 */
    public static function checkout_field_required($required_tag, $field_name){
        $output = $required_tag;
        $required_tag_string = 'required="required"';
        
        switch($field_name){
            case 'first_name':
            case 'last_name':
            case 'email':
            case 'email_check':
            case 'phone':
              $output = $required_tag_string;
              break;  
        }
        
        return $output;
    }        
    
////////checkout_field_label///
    /**
	 * Labels for checkout fields.
     * @param string $label
     * @param string $field_name
     * @return string
	 */
    public static function checkout_field_label($field_name){
        $output = '';
        
        switch($field_name){
            case 'first_name':
              $output = __('First name', 'ba-book-everything');
              break;
            case 'last_name':
              $output = __('Last name', 'ba-book-everything');
              break;
            case 'email':
              $output = __('Email', 'ba-book-everything');
              break;
            case 'email_check':
              $output = __('Re-type Email', 'ba-book-everything');
              break;
            case 'phone':
              $output = __('Contact Phone', 'ba-book-everything');
              break;
            case 'guest':
                $output = __('Guest', 'ba-book-everything');
                break;
            case 'extra_guest':
                $output = __('Extra guest', 'ba-book-everything');
                break;
	        case 'extra_guests':
		        $output = __('Extra guests', 'ba-book-everything');
		        break;
            case 'country':
                $output = __('Country', 'ba-book-everything');
                break;
            case 'state':
                $output = __('State', 'ba-book-everything');
                break;
            case 'city':
                $output = __('City', 'ba-book-everything');
                break;
            case 'address':
                $output = __('Address', 'ba-book-everything');
                break;
        }
        
        $output = apply_filters('babe_checkout_field_label', $output, $field_name);
        
        return $output;
    }    

/////////////////////////////////////

    /**
     * @param float $total_amount
     * @param float $prepaid_amount
     * @param string $payment_model
     * @return string
     */
    public static function checkout_form_element_amount_group($total_amount, $prepaid_amount, $payment_model, $order_id = 0){

        $currency = $order_id ? BABE_Order::get_order_currency($order_id) : '';
        $received_prepaid_amount = BABE_Order::get_order_prepaid_received($order_id);
        $payment_method = BABE_Order::get_order_payment_method($order_id);

        $output = '<div class="amount_group">
<span class="checkout_form_amount_label">'.__('Amount to Pay:', 'ba-book-everything').'</span>';

        if ( $payment_method !== 'cash' ){
            $output .= '
                <div class="checkout_form_pay_total">
                <input type="radio" name="payment[amount_to_pay]" id="order_amount_to_pay_deposit" value="deposit" checked="checked"><label for="order_amount_to_pay_deposit">'.BABE_Currency::get_currency_price($prepaid_amount, $currency).(
                $payment_model === 'deposit_full'
                && !$received_prepaid_amount
                    ? ' '.__('(deposit)', 'ba-book-everything') : ''
                ).'</label>
                </div>';
        }

        if (
            $payment_method === 'cash'
            || ($payment_model === 'deposit_full' && !$received_prepaid_amount)
        ){
            $output .= '
                <div class="checkout_form_pay_total">
                  <input type="radio" name="payment[amount_to_pay]" id="order_amount_to_pay_full" value="full" '.checked($payment_method, 'cash', false).'><label for="order_amount_to_pay_full">'.BABE_Currency::get_currency_price($total_amount, $currency).(
                $payment_method !== 'cash'
                    ? ' '.__('(full)', 'ba-book-everything') : ''
                ). '</label>
                </div>
                ';
        }

        $output .= '</div>';

        return apply_filters('babe_checkout_form_element_amount_group', $output, $total_amount, $prepaid_amount, $payment_model, $order_id);
   }

////////////////////////////
    /**
	 * Add checkout form to page.
     * @param array $args
     * @return string
	 */
    public static function checkout_form($args){

        $output = '';
        $input_fields = [];
        
        $args = wp_parse_args( $args, array(
            'order_id' => 0,
            'order_num' => '',
            'order_hash' => '',
            'total_amount' => 0,
            'prepaid_amount' => 0,
            'payment_model' => 'full',
            'order_currency' => '',
            'action' => 'to_pay', //to_pay or to_av_confirm
            'meta' => [],
        ));
        
        $args['meta'] = wp_parse_args( $args['meta'], array(
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'email_check' => '',
            'phone' => '',
        ));
        
        $order_id = $args['order_id'];
        $order_num = $args['order_num'];
        $order_hash = $args['order_hash'];
        $action = $args['action'];
        $total_amount = $args['total_amount'];
        $prepaid_amount = $args['prepaid_amount'];
        
        $payment_model = $args['payment_model'];
        $currency = $args['order_currency'] ?: BABE_Order::get_order_currency($order_id);
        
        $args['meta'] = apply_filters('babe_checkout_args', $args['meta'], $args);
        
        $output .= '<h2>'.sprintf(__('Order #%s', 'ba-book-everything'), $order_num).'</h2>';
        
        $output .= self::order_items($order_id);
        
        $output = apply_filters('babe_checkout_after_order_items', $output, $args);
            
            ///////// fields
            
            foreach($args['meta'] as $field_name => $field_content){

                if ( in_array( $field_name, ['extra_guests', 'billing_address'] ) ){
                    continue;
                }

                $add_content_class = $field_content ? 'checkout_form_input_field_content' : '';

                $input_fields[$field_name] = '
            <div class="checkout-form-block">
                
               <div class="checkout_form_input_field '.$add_content_class.'">
                   <label class="checkout_form_input_label">'.self::checkout_field_label($field_name).'</label>
				   <input type="text" class="checkout_input_field checkout_input_required" name="'.$field_name.'" id="'.$field_name.'" value="'.$field_content.'" '.apply_filters('babe_checkout_field_required', '', $field_name).'/>
                   <div class="checkout_form_input_underline"><span class="checkout_form_input_ripple"></span></div>
			   </div>
               
            </div>';
            }
            
            //////////////////////////////////
            
        $input_fields = apply_filters('babe_checkout_input_fields_arr', $input_fields, $args);
            
            //////////////////////////////////
            $payment_fields = '';
            if ( $action === 'to_pay' ){
                
                $payment_fields = self::checkout_form_element_amount_group($total_amount, $prepaid_amount, $payment_model, $order_id);

                $payment_methods_arr = BABE_Settings::get_active_payment_methods($order_id);

                $order_payment_method = BABE_Order::get_order_payment_method($order_id);
                 
                 $payment_titles = '';
                 $payment_details = '';
                 $input_fields_name = 'payment';

                foreach($payment_methods_arr as $method => $method_title){

                    $tab_start_active = $method == $order_payment_method ? ' tab_active' : '';

                    $payment_titles .= '<span class="payment_method_title payment_method_title_'
                        .$method
                        .' tab_title'
                        .$tab_start_active.'" data-method="'.$method.'">'
                        .apply_filters(
                            'babe_checkout_payment_title_'.$method,
                            $method_title,
                            $args,
                            $input_fields_name
                        ).'</span>';
                    $payment_details .= '<div class="payment_method_fields payment_method_fields_'
                        .$method
                        .' tab_content'
                        .$tab_start_active.'" data-method="'.$method.'">'
                        .apply_filters(
                            'babe_checkout_payment_fields_'.$method,
                            '',
                            $args,
                            $input_fields_name
                        ).'</div>';
                }
                
                $payment_fields .= '<h2>'.__('Payment Method', 'ba-book-everything').'</h2>
                <div class="payment_group tabs_group">
                <div class="payment_titles_group tabs_titles">
                '.$payment_titles.'
                </div>
                <div class="payment_fields_group">'
                .$payment_details.'
                 </div>
                 <input type="hidden" name="payment[payment_method]" value="'.$order_payment_method.'">
                 </div>
                 ';
                                 
            } else {
                
                $payment_details_text = sprintf(__('You could pay after the items availability confirmation! <br> Confirmation will be sent to your e-mail. <br> Amount to pay online will be %s.', 'ba-book-everything'), BABE_Currency::get_currency_price($prepaid_amount, $currency));
                $payment_details_text = apply_filters('babe_checkout_payment_details_before_av_check', $payment_details_text, BABE_Currency::get_currency_price($prepaid_amount, $currency), $args);
                
                $payment_fields .= '<h2>'.__('Payment details', 'ba-book-everything').'</h2>
                 <div class="payment_group payment_details_before_av_check">
                 '.$payment_details_text.'
                 </div>
                 ';
                
            }  ///// if $action
            
            $output .= '<h2>'.__('Contact information', 'ba-book-everything').'</h2>';
            
            $output .= '<form id="checkout_form" name="checkout_form" method="post" action="">
            
            '.apply_filters('babe_checkout_before_contact_fields', '', $args).'
            
            <div class="contact_fields_group input_group">
            
            '.implode('', $input_fields).'
            
            </div>
            
            '.apply_filters('babe_checkout_after_contact_fields', '', $args).'

            <input type="hidden" name="order_id" value="'.$order_id.'">
            <input type="hidden" name="order_num" value="'.$order_num.'">
            <input type="hidden" name="order_hash" value="'.$order_hash.'">
            <input type="hidden" name="action" value="'.$action.'">
            
            '.$payment_fields.'
            
            '.apply_filters('babe_checkout_after_payment_fields', '', $args). apply_filters('babe_checkout_terms_fields', '
            <div class="terms_group">
              <div class="checkout_form_terms_check">
              <input type="checkbox" name="payment[terms_check]" id="order_terms_check" value="agree" required="required"><label for="order_terms_check">'.__('I read and agree to the terms & conditions', 'ba-book-everything').'</label>
              </div>
              <div class="checkout_form_terms_details">
              '.BABE_Settings::get_terms_page_content().'
              </div>
            </div>
            ', $args) . apply_filters('babe_checkout_after_terms_fields', '', $args).'
            
            <div class="submit_group">
               
               <button class="btn button checkout_form_submit">'.__('Complete My Order', 'ba-book-everything').'</button>
               
            </div>
            
            </form>';

        $output .= '<div id="babe_search_result_refresh">
               <i class="fas fa-spinner fa-spin fa-3x"></i>
            </div>';
            
            $output = apply_filters('babe_checkout_form_html', $output, $args);

        
        if ($output){
            $output = '
            <div id="checkout_form_block">
            '.$output.'
            </div>';
        }      
        
        return $output; 
    }
    
////////////////////////////
    /**
	 * Add confirm content to page.
     * @param array $args
     * @return string
	 */
    public static function confirm_content($args){

        $output = '';
        
        $args = wp_parse_args( $args, array(
            'order_id' => 0,
            'order_num' => '',
            'order_hash' => '',
            'order_status' => '',
        ));
        
        $order_id = $args['order_id'];
        
        ///// messages for $args['order_status']
        if ($args['order_status'] && isset(BABE_Settings::$settings['message_'.$args['order_status']])){
          
            $output .= '
            <div class="babe_message_order babe_message_order_status_'.$args['order_status'].'">
               ' . BABE_Settings::$settings['message_'.$args['order_status']] . '
            </div>';
            
        }
        
        if ( $args['order_status'] == 'payment_expected' || $args['order_status'] == 'draft' ){

            $message = $args['order_status'] == 'payment_expected' ? __('Pay Now!', 'ba-book-everything') : __('Complete the booking', 'ba-book-everything');

            $output .= '<div class="babe_order_confirm">
              <a href="'.BABE_Order::get_order_payment_page($order_id).'" class="babe_button_order babe_button_order_to_pay">'.$message.'</a>
            </div>';
        }
        
        $output .= '<h4>'.sprintf(__('Order #%s', 'ba-book-everything'), $args['order_num']).'</h4>';
        
        $output .= self::order_items($order_id);
        
        $output .= self::order_customer_details($order_id);
        
        $output = apply_filters('babe_confirm_content_html', $output, $args);
        
        return $output; 
    }            
    
////////////////////////////
    /**
	 * Add calendar to booking_obj page.
     * @param array $post
     * @return string
	 */
    public static function block_calendar($post){

        $output = '';
        $post_id = isset($post['ID']) ? (int)$post['ID'] : 0;
        
        if ($post_id){
            
         $av_cal = BABE_Calendar_functions::get_av_cal($post_id);
         
         if (!empty($av_cal)){
            
            $date_now_obj = BABE_Functions::datetime_local();
            
            //$date_from = $date_now_obj->format("Y-m-d");
            //$date_to = date("Y-m-d", strtotime("+1 year +1 month"));
            ///// get rates
            //$rates = BABE_Prices::get_rates($post_id, $date_from, $date_to);

             ///// get rules
            $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

            //// get discount
            $discount_arr = BABE_Post_types::get_post_discount($post_id);

            $first_av_date = key($av_cal);
             $first_av_date_obj = new DateTime($first_av_date);

            ///// create calendar by month
            $date_current = $first_av_date_obj->format('Y-m-01');
            $date_obj_current = new DateTime($date_current);
            $date_end = clone($date_obj_current);
            $date_end->modify('+'. absint(BABE_Settings::$settings['av_calendar_max_months']) . ' months');
            $interval = new DateInterval('P1M');
            $daterange = new DatePeriod($date_obj_current, $interval ,$date_end);
            $month_html = '';
            $i = 0;
            foreach($daterange as $date_obj){
                $block_class = !$i ? 'cal-month-active' : '';
                $month_html .= self::get_calendar_month_html($date_obj->format('Y-m-01'), $av_cal, $discount_arr, $rules_cat, $block_class);
                $i++;
            }
            
            
            $output .= $month_html ? '<div id="av-cal">'.$month_html.'</div>' : '';
           } 
   
        } //// end if $post_id    
        
        return $output; 
    }
    
////////////////////////////
    /**
	 * Get calendar month html.
     * @param string $date - format Y-m-d
     * @param array $av_cal
     * @param array $discount_arr
     * @param array $rules_cat - rules with category meta
     * @param string $block_class
     * @return string
	 */
    public static function get_calendar_month_html($date, $av_cal, $discount_arr, $rules_cat, $block_class = ''){

        $output = '';
        
        $main_age_id = BABE_Post_types::get_main_age_id($rules_cat['rules']);
        $units_arr = BABE_Prices::get_rate_units($rules_cat['rules']);
        $units = $units_arr['units'];
        $unit = $units_arr['unit'];
        $ages_arr_ordered_by_id = BABE_Post_types::get_ages_arr_ordered_by_id();

        $currency = BABE_Currency::get_currency();

        $tax_am = (float)apply_filters('babe_html_calendar_month_html_post_tax', BABE_Post_types::get_post_tax($rules_cat['post_id']), $rules_cat['post_id'])/100;

            ///// create calendar by month
            $date_obj_default = new DateTime($date);
            
            $date_now_obj = BABE_Functions::datetime_local();
            
            $date_current = $date_now_obj->format('Y-m-d');
            
            $date_obj_current = new DateTime($date_current);

            $interval = new DateInterval('P1D');
            $date_obj_month_start = new DateTime($date_obj_default->format('Y-m-01'));
            $date_start = clone($date_obj_month_start);
            $date_end = clone($date_obj_month_start);
            
            if (BABE_Calendar_functions::$week_first_day != BABE_Calendar_functions::get_week_day_num($date_start)){
                $start_day_shift = BABE_Calendar_functions::get_week_day_num($date_start) - BABE_Calendar_functions::$week_first_day;
                $date_start->modify('-'.$start_day_shift.' days');
            }
            
            $date_end->modify('+1 month'); // last day will excluded in loop
            
            if (BABE_Calendar_functions::$week_first_day != BABE_Calendar_functions::get_week_day_num($date_end)){
                $end_day_shift = BABE_Calendar_functions::$week_last_day - BABE_Calendar_functions::get_week_day_num($date_end) + 1;
                $date_end->modify('+'.$end_day_shift.' days'); 
            }
            
            $daterange = new DatePeriod($date_start, $interval ,$date_end);
            $i = 0;
            $prev_date_obj = clone($date_start);
            
            foreach($daterange as $date_cal){ //// loop by date

                $date_cal_day_num = BABE_Calendar_functions::get_week_day_num($date_cal);
                ///////////////////////
                $add_cell_class = $date_cal->format('m') !== $date_obj_month_start->format('m') ?
                    ' cal-cell-empty' : '';

                $av_guest = 0;
                $av_guest_min = 0;
                $last_av_guests = 0;

                if ( !empty($av_cal[$date_cal->format('Y-m-d')]['times']) ){
                    $times = $av_cal[$date_cal->format('Y-m-d')]['times'];
                    end($times);
                    $last_time = key($times);
                    $av_guest = max($times);
                    $av_guest_min = min($times);
                    $last_av_guests = $times[$last_time];
                }

                $prev_av_guest = !empty($av_cal[$prev_date_obj->format('Y-m-d')]['times']) ?
                    max($av_cal[$prev_date_obj->format('Y-m-d')]['times']) : 0;
                
                if (
                ($rules_cat['rules']['basic_booking_period'] === 'night' && !$av_guest && $prev_av_guest)
                || 
                ($rules_cat['rules']['basic_booking_period'] === 'day' && $av_guest && $av_cal[$date_cal->format('Y-m-d')]['times']['00:00'] && !$last_av_guests)
                ){
                    
                    $add_cell_class .= ' cal-cell-disabled-first';
                     
                } elseif (
                   $rules_cat['rules']['basic_booking_period'] === 'day'
                   && $av_guest
                   && $av_cal[$date_cal->format('Y-m-d')]['times']['00:00']
                   && $last_av_guests
                   && !$av_guest_min
                ) { 
                    
                    $add_cell_class .= ' cal-cell-stop-date';
                    
                } elseif ( !$av_guest ) { 
                    
                    $add_cell_class .= ' cal-cell-disabled';
                    
                }
                
                if ( 
                ( $rules_cat['rules']['basic_booking_period'] === 'night' && $av_guest && !$prev_av_guest )
                || (
                    $rules_cat['rules']['basic_booking_period'] === 'day'
                    && $av_guest
                    && !$av_cal[$date_cal->format('Y-m-d')]['times']['00:00']
                )
                ){
                    $add_cell_class .= ' cal-cell-disabled-last';
                }
                
                if ( $date_obj_current > $date_cal || !$av_guest ){
                    
                    $output .= '<div class="cal-cell'.$add_cell_class.'" data-day="'.$date_cal->format('d').'" data-month="'.$date_cal->format('m').'" data-year="'.$date_cal->format('Y').'" data-date="'.$date_cal->format(BABE_Settings::$settings['date_format']).'" data-date-sql="'.$date_cal->format('Y-m-d').'">
                    <div class="cal-cell-date">'.$date_cal->format('j').'</div>
                    </div>';
                    
                } else {
                    //// get price from
                    $price_from = $av_cal[$date_cal->format('Y-m-d')]['price_from'];
                    $rate_current = $av_cal[$date_cal->format('Y-m-d')];
                    
                 if ( !empty($rate_current) ){
                    
                    if (isset($rate_current['start_days'][$date_cal_day_num])){
                        $add_cell_class .= ' cal-cell-start-day';
                    }

                     $discount = apply_filters('babe_get_calendar_month_html_discount', $discount_arr['discount'], $main_age_id, $rules_cat['post_id'], $discount_arr );
                     $discountMultiplier = (100 - $discount)/100;
                    
                    $price_from = $price_from*$discountMultiplier;
                    $price_from = $price_from + round($price_from * $tax_am, BABE_Currency::get_currency_precision());

                    $prices_table = '';

                     if ( !BABE_Settings::$settings['av_calendar_remove_hover_prices'] ){
                         //// get prices table from $rate_current
                         
                         foreach( $av_cal[$date_cal->format('Y-m-d')]['rates'] as $rate_details){

                             if ( (int)$rate_details['start_day'] === 0 ){
                                 continue;
                             }

                             $prices_table .= '<h4>'.$rate_details['rate_title'].'</h4>';

                             $prices_table .= '
                    <div class="rate_min_max">';
                             if ( $rate_details['min_booking_period'] ){
                                 $prices_table .= '
                        <span class="rate_details_label">'.__('Minimum booking:', 'ba-book-everything').'</span> <span class="rate_details_value">'.$rate_details['min_booking_period'].' '.$units.'</span>
                        ';
                             }
                             if ( $rate_details['max_booking_period'] ){
                                 $prices_table .= '
                        <span class="rate_details_label">'.__('Maximum booking:', 'ba-book-everything').'</span> <span class="rate_details_value">'.$rate_details['max_booking_period'].' '.$units.'</span>
                        ';
                             }
                             $prices_table .= '
                    </div>';

                             if ( !empty($rate_details['price_general']) ){
                                 $prices_table .= '
                    <div class="rate_price_general">
                      <span class="rate_details_label">'.__('General price:', 'ba-book-everything').'</span> ';

                                 $tmp_prices = array();
                                 foreach($rate_details['price_general'] as $age_id => $price){

                                     $discount = apply_filters('babe_get_calendar_month_html_discount', $discount_arr['discount'], $age_id, $rules_cat['post_id'], $discount_arr );
                                     $discountMultiplier = (100 - $discount)/100;

                                     $age_title = !$age_id || !isset($ages_arr_ordered_by_id[$age_id]) ? '' : $ages_arr_ordered_by_id[$age_id]['name'] . ' (' . $ages_arr_ordered_by_id[$age_id]['description'] . ')';
                                     $price = round($price*$discountMultiplier, BABE_Currency::get_currency_precision());
                                     $price += round($price * $tax_am, BABE_Currency::get_currency_precision());
                                     $price = BABE_Prices::localize_price($price, $currency);
                                     $menu_order = isset($ages_arr_ordered_by_id[$age_id]) ? $ages_arr_ordered_by_id[$age_id]['menu_order'] : 0;
                                     $tmp_prices[$menu_order] = '<span class="price_age_title">'. $age_title . '</span> <span class="price_age_value">'.BABE_Currency::get_currency_price($price).$unit.'</span>';
                                 }
                                 ksort($tmp_prices);
                                 $prices_table .= implode(' | ', $tmp_prices);
                                 $prices_table .= '
                    </div>
                    ';
                             }

                             if ( !empty($rate_details['prices_conditional']) ){
                                 $signs = BABE_Prices::get_conditional_signs();
                                 $prices_table .= '
                    <div class="rate_prices_conditional">
                      <h4 class="rate_details_label">'.__('Options', 'ba-book-everything').'</h4>
                      <ul class="rate_prices_conditional_details">';

                                 foreach($rate_details['prices_conditional'] as $price_conditional){

                                     $prices_table .= '<li class="conditional_price_block">';

                                     $tmp_output = '';

                                     if ( isset($price_conditional['conditional_guests_sign']) && isset($price_conditional['conditional_guests_number']) && isset($signs[$price_conditional['conditional_guests_sign']]) ){
                                         $tmp_output .= '<span class="prices_conditional_if">'.__('guests', 'ba-book-everything').' '.$signs[$price_conditional['conditional_guests_sign']].' '.$price_conditional['conditional_guests_number']. '</span> ';
                                     }

                                     if ( isset($price_conditional['conditional_units_sign']) && isset($price_conditional['conditional_units_number']) && isset($signs[$price_conditional['conditional_units_sign']]) ){
                                         $tmp_output .= $tmp_output ? '<span class="prices_conditional_if">'.__('AND', 'ba-book-everything'). '</span> ' : '';

                                         $tmp_output .= '<span class="prices_conditional_if">'.$units.' '.$signs[$price_conditional['conditional_units_sign']].' '.$price_conditional['conditional_units_number']. '</span>';
                                     }

                                     $prices_table .= $tmp_output.' <span class="prices_conditional_then">'.__('Price', 'ba-book-everything').'</span> ';

                                     $tmp_prices = array();
                                     foreach($price_conditional['conditional_price'] as $age_id => $price){

                                         $discount = apply_filters('babe_get_calendar_month_html_discount', $discount_arr['discount'], $age_id, $rules_cat['post_id'], $discount_arr );
                                         $discountMultiplier = (100 - $discount)/100;

                                         $age_title = !$age_id || !isset($ages_arr_ordered_by_id[$age_id]) ? '' : $ages_arr_ordered_by_id[$age_id]['name'] . ' (' . $ages_arr_ordered_by_id[$age_id]['description'] . ')';
                                         $price = round($price*$discountMultiplier, BABE_Currency::get_currency_precision());
                                         $price += round($price * $tax_am, BABE_Currency::get_currency_precision());
                                         $price = BABE_Prices::localize_price($price, $currency);
                                         $menu_order = isset($ages_arr_ordered_by_id[$age_id]) ? $ages_arr_ordered_by_id[$age_id]['menu_order'] : 0;
                                         $tmp_prices[$menu_order] = '<span class="price_age_title">'. $age_title . '</span> <span class="price_age_value">'.BABE_Currency::get_currency_price($price).$unit.'</span>';
                                     }
                                     ksort($tmp_prices);
                                     $prices_table .= implode(' | ', $tmp_prices);

                                     $prices_table .= '</li>';
                                 }

                                 $prices_table .= '
                      </ul>  
                    </div>
                    ';
                             }
                         }

                         if ( $prices_table ){
                             $prices_table = '
                  <div class="view-rate-details">
                    <div class="view-rate-details-item">' . $prices_table . '</div></div>';
                         }
                     }
                       
                    ////////////// make output   
                    
                    $output .= '<div class="cal-cell cal-cell-active'.$add_cell_class.'" data-day="'.$date_cal->format('d').'" data-month="'.$date_cal->format('m').'" data-year="'.$date_cal->format('Y').'" data-date="'.$date_cal->format(BABE_Settings::$settings['date_format']).'" data-date-sql="'.$date_cal->format('Y-m-d').'" data-min-booking="'.$rate_current['min_booking_period'].'" data-max-booking="'.$rate_current['max_booking_period'].'">
                    <div class="cal-cell-date">'.$date_cal->format('j').'</div>
                    <div class="cal-cell-pricefrom">'. apply_filters(
                        'babe_av_calendar_cell_pricefrom',
                        BABE_Currency::get_currency_price(
                            BABE_Prices::localize_price($price_from, $currency)
                        ),
                        $currency,
                        $av_cal,
                        $date_cal
                        ).'</div>
                    '.$prices_table.'
                    </div>';                 
                        
                  } else {
                    ///// disable date without rate
                    $output .= '<div class="cal-cell cal-cell-disabled'.$add_cell_class.'" data-day="'.$date_cal->format('d').'" data-month="'.$date_cal->format('m').'" data-year="'.$date_cal->format('Y').'" data-date="'.$date_cal->format(BABE_Settings::$settings['date_format']).'" data-date-sql="'.$date_cal->format('Y-m-d').'">
                    <div class="cal-cell-date">'.$date_cal->format('j').'</div>
                    </div>';
                    
                  }
                }
                $i++;
                $prev_date_obj = $date_cal;
                ///////////////////////
            }
            
            if ($output){
                
                $week_names = BABE_Calendar_functions::get_week_days_arr();
                $week_names_html = '';
                foreach($week_names as $week_name){
                    $week_names_html .= '<div class="cal-week-name">'.$week_name.'</div>';
                }
                
                $add_invisible_cell = $i == 35 ? '<div class="cal-cell cal-cell-invisible"></div>' : ($i == 28 ? '<div class="cal-cell cal-cell-invisible"></div><div class="cal-cell cal-cell-invisible"></div><div class="cal-cell cal-cell-invisible"></div><div class="cal-cell cal-cell-invisible"></div><div class="cal-cell cal-cell-invisible"></div><div class="cal-cell cal-cell-invisible"></div><div class="cal-cell cal-cell-invisible"></div><div class="cal-cell cal-cell-invisible"></div>' : '');
                
                $output = '
                <div class="cal-month-block '.$block_class.'" data-yearmonth="'.$date_obj_month_start->format('Y-m').'">
                <div class="cal-week-names">'.$week_names_html.'
                </div>
                <div class="cal-month-bar"><span class="cal-month-prev"><i class="fas fa-chevron-left" aria-hidden="true"></i></span>'.BABE_Calendar_functions::$months_names[$date_obj_month_start->format('F')].$date_obj_month_start->format(' Y').'<span class="cal-month-next"><i class="fas fa-chevron-right" aria-hidden="true"></i></span>
                </div>
                <div class="cal-dates-block">
                '.$output.$add_invisible_cell.'
                </div>
                </div>
                ';
                
            }    
        
        return $output; 
    }

////////////////////////////
    /**
     * Get fees list with checkboxes.
     *
     * @param array $post - BABE post
     * @param array $selected_arr
     *
     * @return string
     */
    public static function list_fees( $post, $selected_arr = [] ){

        $output = '';

        if ( empty($post['fees']) ){
            return $output;
        }

        $fees_arr = BABE_Post_types::get_post_fees($post);

        if ( empty($fees_arr) ){
            return $output;
        }

        $selected_ids = array_flip($selected_arr);

        $mandatory_fields = [];
        $optional_fields = [];

        foreach($fees_arr as $fee){

            $sr_block = '';

            $general_price = (float)$fee['price'];

            $is_percent = $fee['price_type'] == 'percent' ? true : false;

            $checked = isset($selected_ids[$fee['ID']]) ? ' checked="checked"' : '';

            $sr_block .= '<div class="list_service">
                    <div class="list_service_title">
                   ';

            if ( $general_price && !$fee['is_mandatory'] ){

                $sr_block .= '<label>
                      <input type="checkbox" name="booking_fees[]" value="'.$fee['ID'].'" '.$checked.'/>
                      '.__( 'Add ', 'ba-book-everything' ).'
                    </label>
                    ';
            }

            $sr_block .= '<h4>'.$fee['post_title'].'</h4>
                    </div>
                    <div class="list_service_prices">
                    <span class="service_price_line">';

            if ( $general_price ){
                $sr_block .= $is_percent ? $general_price.__( '%', 'ba-book-everything' ) : BABE_Currency::get_currency_price($general_price);
            } else {
                $sr_block .= BABE_Currency::get_zero_price_display_value();
            }

            $sr_block .= '</span>';

            $service_content = $fee['post_content'] ? '<div class="view-list-details">'. apply_filters( 'the_content', $fee['post_content']).'</div>' : '';

            $sr_block .= '</div>'.$service_content.'
                     </div>';

            if ( !$general_price || $fee['is_mandatory'] ){

                $mandatory_fields[ $fee['ID'] ] = apply_filters('babe_list_fees_block_html', $sr_block, $fee);
            } else {

                $optional_fields[ $fee['ID'] ] = apply_filters('babe_list_fees_block_html', $sr_block, $fee);
            }

        }

        $fee_fields = [
            'mandatory_fields' => $mandatory_fields,
            'optional_fields' => $optional_fields,
        ];

        $fee_fields = apply_filters('babe_list_fees_fields', $fee_fields, $post, $selected_arr );

        if ( !empty( $fee_fields['mandatory_fields'] ) ){
            $output .= '
              <label class="booking_form_input_label">'.apply_filters('babe_booking_form_included_fees_label', __('Mandatory fees', 'ba-book-everything')).'</label>
              <div id="list_fees_mandatory">
                '.implode('', $fee_fields['mandatory_fields']).'
              </div>
              ';
        }

        if ( !empty( $fee_fields['optional_fields'] ) ){
            $output .= '
              <label class="booking_form_input_label">'.apply_filters('babe_booking_form_optional_fees_label', __('Optional fees', 'ba-book-everything')).'</label>
              <div id="list_fees_optional">
                '.implode('', $fee_fields['optional_fields']).'
              </div>
              ';
        }

        $output = apply_filters('babe_list_fees_html', $output, $fee_fields, $post, $selected_arr );

        return $output;
    }

////////////////////////////

    /**
     * Get services list with checkboxes.
     *
     * @param array $post - BABE post
     * @param array $selected_services_arr
     * @param string $date_from - Y-m-d H:i
     * @param string $date_to - Y-m-d H:i
     * @param array $guests
     * @return string
     */
    public static function list_add_services($post, $selected_services_arr = [], $date_from = '', $date_to = '', $guests = []): string
    {

        $output = '';

        if ( empty($post['services']) ){
            return $output;
        }

        $services_arr = BABE_Post_types::get_post_services($post);

        $services_arr = apply_filters('babe_html_list_add_services_arr', $services_arr, $post);

        if ( empty($services_arr) ){
            return $output;
        }
        
        return self::list_add_services_render_html($post['ID'], 'list_services', $services_arr, $selected_services_arr, $date_from, $date_to, $guests );
    }

////////////////////////////

    /**
     * Render services list html with checkboxes
     *
     * @param int $post_id
     * @param string $html_id
     * @param array $services_arr
     * @param array $selected_services_arr
     * @param string $date_from - Y-m-d H:i
     * @param string $date_to - Y-m-d H:i
     * @param array $guests
     * @return string
     */
    public static function list_add_services_render_html($post_id, $html_id, $services_arr, $selected_services_arr, $date_from, $date_to, $guests): string
    {
        $output = '';

        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

        $babe_post = BABE_Post_types::get_post($post_id);
        $input_type = !empty($babe_post['service_selection_type'])
        && $babe_post['service_selection_type'] === 'radio' ? 'radio' : 'checkbox';

        $tax_am = (float)apply_filters('babe_html_list_add_services_post_tax', BABE_Post_types::get_post_tax($post_id), $post_id)/100;

        $post_ages = BABE_Post_types::get_post_ages($post_id);

        $ages = empty($post_ages) ? array(
            0 => array(
                'age_id' => 0,
                'name' => __( 'Price', 'ba-book-everything' ),
                'description' => '',
                'menu_order' => 1,
                'slug' => '',
            ) ) : $post_ages;

        $service_ages = [];
        foreach ( $ages as $age ){
            $service_ages[$age['age_id']] = 1;
        }

        $selected_services_arr = BABE_Functions::normalize_order_services( $selected_services_arr, $service_ages );

        $before_service_type = '';

        $mandatory_fields = [];
        $optional_fields = [];

        $av_cal = BABE_Calendar_functions::get_av_cal($post_id, $date_from, $date_to);
        $av_cal_first_rec = reset($av_cal);
        $av_cal_first_rec_rate = isset($av_cal_first_rec['rates']) ? reset($av_cal_first_rec['rates']) : [];
        $av_cal_ages = isset($av_cal_first_rec_rate['price_general']) && is_array($av_cal_first_rec_rate['price_general']) ? $av_cal_first_rec_rate['price_general'] : array();

        foreach($services_arr as $service){

            $sr_block = '';

            $general_price = (float)$service['prices'][0];
            $other_prices = $service['prices'];

            // check conditional prices
            if (
                !empty($service['conditional_prices'])
                && is_array($service['conditional_prices'])
                && !empty($date_from)
                && !empty($date_to)
                && !empty($guests)
            ){
                $begin = new DateTime( $date_from );
                $end = new DateTime( $date_to );
                if ( in_array($service['service_type'], ['day','person_day']) ){
                    $end->modify( '+1 day' );
                }
                $d_interval = date_diff($begin, $end);
                $days_total = (int)$d_interval->format('%a'); // total days or nights
                if ( !$days_total ){
                    $days_total = 1;
                }
                $guests_total = array_sum($guests);
                $other_prices_conditional = [];
                foreach( $service['prices'] as $age_id => $service_price ){
                    $conditional_price = BABE_Prices::calculate_rate_conditional_price( $service['conditional_prices'], $age_id, $guests_total, $days_total, 1 );
                    if ( $conditional_price !== false ){
                        $other_prices_conditional[$age_id] = (float)$conditional_price;
                    }
                }
                if ( !empty($other_prices_conditional) ){
                    $other_prices = $other_prices_conditional;
                    $general_price = (float)$other_prices[0];
                }
            }

            unset($other_prices[0]);

            if (empty($other_prices) && !$general_price){
                continue;
            }

            $is_other_prices = false;

            if ($before_service_type !== $service['service_type']){
                $before_service_type = $service['service_type'];
            }

            $is_percent = $before_service_type === 'booking' && $service['price_type'] === 'percent';

            $checked = isset($selected_services_arr[$service['ID']]) ? ' checked="checked"' : '';

            $sr_block .= '<div class="list_service">
                    <div class="list_service_title">';

            if ( !$service['is_mandatory'] ){

                $sr_block .= '<label>
                      <input type="'.esc_attr($input_type).'" name="booking_services[]" value="'.$service['ID'].'" '.$checked.'/>
                      '.__( 'Add ', 'ba-book-everything' ).'
                    </label>';
            }

            $sr_block .= '<h4>'.$service['post_title'].'</h4>
                    </div>
                    <div class="list_service_prices">
                    ';

            if ($rules_cat['rules']['ages'] && is_array($other_prices)){
                foreach ($other_prices as $test_age => $test_price){
                    $is_other_prices = $test_age && $test_price !== '' ? true : $is_other_prices;
                }
            }

            if ( !in_array($before_service_type, array('booking', 'day', 'night')) ){

                foreach($ages as $age){

                    if ( !empty($av_cal_ages) && empty($av_cal_ages[$age['age_id']])){
                        continue;
                    }

                    $service_select_quantity = self::get_service_quantity_select_html($service, $selected_services_arr, $age['age_id']);

                    if (!$is_percent){

                        $price_value = $is_other_prices && $age['age_id'] && isset($other_prices[$age['age_id']]) ? $other_prices[$age['age_id']] : $general_price;

                        if ($price_value !== ''){

                            $price_value = (float)$price_value;

                            $price_value_html = $price_value == 0 ? BABE_Currency::get_zero_price_display_value() : BABE_Currency::get_currency_price($price_value + round($price_value * $tax_am, BABE_Currency::get_currency_precision()));

                            $price_label = '<label>'.apply_filters('translate_text', $age['name']).':</label>';

                            $sr_block .= '<span class="service_price_line">'.$price_label.$price_value_html.$service_select_quantity.'</span>';
                        }

                    } else {

                        if ( !$is_other_prices ){

                            $sr_block .= '<span class="service_price_line"><label>'.apply_filters('translate_text', $age['name']).':</label>'.$general_price.' '.__( '%/booking ', 'ba-book-everything' ).$service_select_quantity.'</span>';

                        } elseif ( isset($other_prices[$age['age_id']]) && $other_prices[$age['age_id']] !== '' ){

                            $sr_block .= '<span class="service_price_line"><label>'.apply_filters('translate_text', $age['name']).':</label>'.$other_prices[$age['age_id']].' '.__( '%/booking ', 'ba-book-everything' ).$service_select_quantity.'</span>';

                        }
                    }
                }

            } else {
                $sr_block .= '<span class="service_price_line">';

                $service_select_quantity = self::get_service_quantity_select_html($service, $selected_services_arr);

                if (!$is_percent){

                    $sr_block .= $general_price == 0 ? BABE_Currency::get_zero_price_display_value() : BABE_Currency::get_currency_price($general_price + round($general_price * $tax_am, BABE_Currency::get_currency_precision()));

                } else {

                    $sr_block .= $general_price.' '.__( '%/booking ', 'ba-book-everything' );
                }

                $sr_block .= $service_select_quantity.'</span>';
            }

            $service_content = $service['post_content'] ? '<div class="view-list-details">'. apply_filters( 'the_content', $service['post_content']).'</div>' : '';

            $sr_block .= '</div>'.$service_content.'
                     </div>';

            if ( $service['is_mandatory'] ){

                $mandatory_fields[ $service['ID'] ] = apply_filters('babe_list_add_services_block_html', $sr_block, $service, $ages);

            } else {

                $optional_fields[ $service['ID'] ] = apply_filters('babe_list_add_services_block_html', $sr_block, $service, $ages);
            }

        }

        $service_fields = [
            'mandatory_fields' => $mandatory_fields,
            'optional_fields' => $optional_fields,
        ];

        $service_fields = apply_filters('babe_list_add_services_render_html_fields', $service_fields, compact('post_id', 'services_arr', 'selected_services_arr', 'date_from', 'date_to', 'guests', 'ages') );

        if ( !empty($service_fields['mandatory_fields']) ){
            $output .= '
              <label class="booking_form_input_label">'.apply_filters('babe_booking_form_mandatory_services_label', __('Mandatory services', 'ba-book-everything')).'</label>
               <div class="booking_services_inner">
               '.implode('', $service_fields['mandatory_fields']).'
               </div>';
        }

        if ( !empty($service_fields['optional_fields']) ){
            $output .= '
              <label class="booking_form_input_label">'.apply_filters('babe_booking_form_optional_services_label', __('Add Extra', 'ba-book-everything')).'</label>
               <div class="booking_services_inner">
                 <div id="'.esc_attr($html_id).'">
                 '.implode('', $service_fields['optional_fields']).'
                 </div>
               </div>';
        }

        $output = apply_filters('babe_list_add_services_render_html', $output, compact('service_fields', 'post_id', 'services_arr', 'selected_services_arr', 'date_from', 'date_to', 'guests', 'ages', 'html_id') );

        return $output;
    }

    /**
     * @param array $service
     * @param array $selected_services_arr
     * @param int $age_id
     * @return string
     */
    public static function get_service_quantity_select_html($service, $selected_services_arr = [], $age_id = 0): string
    {
        $output = '';
        if ( empty($service['allow_quantity']) || empty($service['max_quantity']) ){
            return $output;
        }

        $output = '<span class="service_quantity">
                      <label>'.apply_filters('translate_text', __( 'qty.', 'ba-book-everything' )).'</label>
                      <select class="select_service_quantity" name="booking_service_qty['.$service['ID'].']['.$age_id.']">';

        $start_from = $age_id ? 0 : 1;

        for( $i=$start_from; $i <= (int)$service['max_quantity']; $i++ ){
            $output  .= '<option value="'.$i.'" '.selected( ( isset($selected_services_arr[ (int)$service['ID']][$age_id]) ? (int)$selected_services_arr[$service['ID']][$age_id] : 0 ), $i, false).'>'.$i.'</option>';
        }

        $output  .= '</select>
</span>';

        return $output;
    }

////////////////////////////
    /**
	 * Add services to booking_obj page.
     * @param array $post
     * @return string
	 */
    public static function block_add_services($post){

        $output = '';
        $results = array();
        
        if (!empty($post) && isset($post['services']) && !empty($post['services'])){
            
             $services_arr = BABE_Post_types::get_post_services($post);
             
             if (!empty($services_arr)){
                
                $tax_am = (float)apply_filters('babe_html_block_add_services_post_tax', BABE_Post_types::get_post_tax($post['ID']), $post['ID'])/100;
                
                $ages = BABE_Post_types::get_ages_arr();
                
                $ages = empty($ages) ? array(0 => array('name' => __( 'Price', 'ba-book-everything' ), 'description' => '')) : $ages;
                
                $before_service_type = '';
                
                foreach($services_arr as $service){

                    $sr_block = '';
                    
                    if ($before_service_type != $service['service_type']){
                        $sr_block .= '<div class="block_service">
                        <h4 class="block_service_header">
                        '.__( 'Prices ', 'ba-book-everything' ).BABE_Post_types::$service_type_names[$service['service_type']].'
                        </h4>
                        </div>';
                        
                        $before_service_type = $service['service_type'];
                    }
                    
                    $is_percent = $before_service_type == 'booking' && $service['price_type'] == 'percent' ? true : false;                     
                    
                    $sr_block .= '<div class="block_service">
                    <div class="block_service_title">
                    <label>
                      <input type="checkbox" name="booking_services[]" value="'.$service['ID'].'"/>
                      '.__( 'Add ', 'ba-book-everything' ).'
                    </label>
                    <h4>'.$service['post_title'].'</h4>
                    </div>
                    <div class="block_service_prices">
                    ';
                    
                    $general_price = $service['prices'][0];
                    
                    $other_prices = $service['prices'];
                    unset($other_prices[0]);
                    $is_other_prices = false;
                    foreach ($other_prices as $test_age => $test_price){
                        $is_other_prices = $test_price !== '' ? true : $is_other_prices; 
                    }
                    
                    $service_ages = array_keys($service['prices']);
                    
                    if (!in_array($before_service_type, array('booking', 'day', 'night'))){
                 
                     foreach($ages as $age){
                        $sr_block .= '<span class="service_price_line">';
                        
                        if (!$is_percent){
                          $sr_block .= $is_other_prices ? (isset($other_prices[$age['age_id']]) && $other_prices[$age['age_id']] !== '' ? '<label>'.apply_filters('translate_text', $age['name']).':</label>'.BABE_Currency::get_currency_price($other_prices[$age['age_id']] + round($other_prices[$age['age_id']] * $tax_am, BABE_Currency::get_currency_precision())) : '') : '<label>'.apply_filters('translate_text', $age['name']).':</label>'.BABE_Currency::get_currency_price($general_price + round($general_price * $tax_am, BABE_Currency::get_currency_precision()));
                        } else {
                          $sr_block .= $is_other_prices ? (isset($other_prices[$age['age_id']]) && $other_prices[$age['age_id']] !== ''? '<label>'.apply_filters('translate_text', $age['name']).':</label>'.$other_prices[$age['age_id']].' '.__( '%/booking ', 'ba-book-everything' ) : '') : '<label>'.apply_filters('translate_text', $age['name']).':</label>'.$general_price.' '.__( '%/booking ', 'ba-book-everything' );
                        }
                        
                        $sr_block .= '</span>';
                     }
                     
                     } else {
                        $sr_block .= '<span class="service_price_line">';
                        
                        if (!$is_percent){
                          $sr_block .= BABE_Currency::get_currency_price($general_price + round($general_price * $tax_am, BABE_Currency::get_currency_precision()));
                        } else {
                          $sr_block .= $general_price.' '.__( '%/booking ', 'ba-book-everything' );
                        }
                        
                        $sr_block .= '</span>';
                     }     
                 
                     $sr_block .= '</div>
                     </div>';   
                    
                     $results[] = apply_filters('babe_add_service_block_html', $sr_block, $service, $ages);
                }
                
                if(!empty($results)){
                    
                    $services_header = '<div class="block_services_header_padding"></div>
                    <div class="block_services_header_names">';
                    
                    foreach($ages as $age){
                        $services_header .= '<span class="block_service_age_name">
                          '.apply_filters('translate_text', $age['name']).'
                        </span>';
                    }
                    
                    $services_header.= '</div>';
                    
              $hidden_fields = '';
              
              $post_arr = $_GET;
              
              unset($post_arr['action']);
              unset($post_arr['current_action']);
              
              //print_r($post_arr);
              
              foreach($post_arr as $key => $value){
                if ($key != 'booking_guests'){
                  $hidden_fields .= '
                  <input type="hidden" name="'.$key.'" value="'.$value.'">
                  ';
                } else {
                    foreach($value as $guest_id => $guest_num){
                        $hidden_fields .= '
                  <input type="hidden" name="booking_guests['.$guest_id.']" value="'.$guest_num.'">
                  ';
                    }
                }  
              }      
                
                $output .= '
              <form name="add_services" id="add_services" method="post" action="">  
              <div id="block_services">
                <div class="block_services_title"></div>
                <div class="block_services_header">
                '.$services_header.'
                </div>
                '.implode('', $results).'
              </div>
              '.$hidden_fields.'
              <input type="hidden" name="action" value="to_checkout">
              <button class="btn button services_form_submit">'.__('Continue', 'ba-book-everything').' <i class="fas fa-caret-right"></i></button>
              </form>';
                 }
                
             }   
        } //// end if !empty($post['services'])    
        
        return $output; 
    }
    
////////////////////////////
    /**
	 * Add services to booking_obj page.
     * @param array $post
     * @return string
	 */
    public static function block_services($post){

        $output = '';
        $results = array();
        
        if (!empty($post) && isset($post['services']) && !empty($post['services'])){
            
             $services_arr = BABE_Post_types::get_post_services($post);
             
             if (!empty($services_arr)){
                
                $tax_am = (float)apply_filters('babe_html_block_services_post_tax', BABE_Post_types::get_post_tax($post['ID']), $post['ID'])/100;
                
                $ages = BABE_Post_types::get_ages_arr();
                
                $ages = empty($ages) ? array(0 => array('name' => __( 'Price', 'ba-book-everything' ), 'description' => '')) : $ages;
                
                $i = 0;
                $before_service_type = '';
                
                foreach($services_arr as $service){

                    $sr_block = '';
                    
                    if ($before_service_type != $service['service_type']){
                        $sr_block .= '<div class="block_service">
                        <h4 class="block_service_header">
                        '.__( 'Prices ', 'ba-book-everything' ).BABE_Post_types::$service_type_names[$service['service_type']].'
                        </h4>
                        </div>';
                        
                        $before_service_type = $service['service_type'];
                    }
                    
                    $is_percent = $before_service_type == 'booking' && $service['price_type'] == 'percent' ? true : false;                     
                    
                    $sr_block .= '<div class="block_service">
                    <div class="block_service_title">
                    <h4>'.$service['post_title'].'</h4>
                    </div>
                    <div class="block_service_prices">
                    ';
                    
                    $general_price = (float)$service['prices'][0];
                    
                    $other_prices = $service['prices'];
                    
                    unset($other_prices[0]);
                    
                    $is_other_prices = false;
                    
                    if (is_array($other_prices)){
                      foreach ($other_prices as $test_age => $test_price){
                        $is_other_prices = $test_price !== '' ? true : $is_other_prices; 
                      }
                    }  
                    
                    if (!in_array($before_service_type, array('booking', 'day', 'night'))){
                 
                     foreach($ages as $age){
                        
                        $sr_block .= '<span class="service_price_line">';
                        
                        if (!$is_percent){
                            
                          $price_value = $is_other_prices && $age['age_id'] && isset($other_prices[$age['age_id']]) && $other_prices[$age['age_id']] !== '' ? (float)$other_prices[$age['age_id']] : $general_price;
                          
                          if ($price_value !== ''){
                            
                             $price_value_html = $price_value == 0 ? BABE_Currency::get_zero_price_display_value() : BABE_Currency::get_currency_price($price_value + round($price_value * $tax_am, BABE_Currency::get_currency_precision()));
                             
                             $sr_block .= '<label>'.apply_filters('translate_text', $age['name']).':</label>'.$price_value_html.'</span>';
                            
                          }
                          
                        } else {
                          $sr_block .= $is_other_prices ? (isset($other_prices[$age['age_id']]) && $other_prices[$age['age_id']] !== ''? '<label>'.apply_filters('translate_text', $age['name']).':</label>'.$other_prices[$age['age_id']].' '.__( '%/booking ', 'ba-book-everything' ) : '') : '<label>'.apply_filters('translate_text', $age['name']).':</label>'.$general_price.' '.__( '%/booking ', 'ba-book-everything' );
                        }
                        
                        $sr_block .= '</span>';
                     }
                     
                     } else {
                        $sr_block .= '<span class="service_price_line">';
                        
                        if (!$is_percent){
                            
                          $price_value_html = $general_price == 0 ? BABE_Currency::get_zero_price_display_value() : BABE_Currency::get_currency_price($general_price + round($general_price * $tax_am, BABE_Currency::get_currency_precision()));
                            
                          $sr_block .= $price_value_html;
                          
                        } else {
                          $sr_block .= $general_price.' '.__( '%/booking ', 'ba-book-everything' );
                        }
                        
                        $sr_block .= '</span>';
                     }     
                 
                     $sr_block .= '</div>
                     </div>';   
                    
                     $results[] = apply_filters('babe_post_service_block_html', $sr_block, $service, $ages);
                }
                
                if(!empty($results)){
                    
                    $services_header = '<div class="block_services_header_padding"></div>
                    <div class="block_services_header_names">';
                    
                    foreach($ages as $age){
                        $services_header .= '<span class="block_service_age_name">
                          '.apply_filters('translate_text', $age['name']).'
                        </span>';
                    }
                    
                    $services_header.= '</div>';
                
                $output .= '<div id="block_services">
                <div class="block_services_title"></div>
                <div class="block_services_header">
                '.$services_header.'
                </div>
                '.implode('', $results).'
              </div>';
                 }
                
             }   
        } //// end if !empty($post['services'])    
        
        return $output; 
    }                    
    
////////////////////////////
    /**
	 * Add meeting points to booking_obj page.
     * @param array $post
     * @return string
	 */
    public static function block_meeting_points($post){

        $output = '';
        $results = array();
        
       // $output .= print_r( $post, 1);
        
        if (!empty($post) && isset($post['meeting_points']) && isset($post['meeting_place']) && $post['meeting_place'] == 'point'){
            
        if (isset($post['address']['address']) && isset($post['address']['latitude']) && isset($post['address']['longitude'])){
            $address = apply_filters('translate_text', $post['address']['address']);
            $lat = $post['address']['latitude'];
            $lng = $post['address']['longitude'];
        } else {
            $address = '';
            $lat = '';
            $lng = '';
        }  
            
        $output .= '<div id="block_meeting_points">
            <h3>'.__('Find closest meeting point', 'ba-book-everything').'</h3>
            <div class="meeting-points">
              <div class="meeting_points_search">
              <input class="address-autocomplete" name="autocomplete" placeholder="'.__( 'Enter your address', 'ba-book-everything' ).'" type="text" />
              <button class="btn button find_points">'.__('Search', 'ba-book-everything').'</button>
              <div id="travel_mode_panel">
              <label for="travel_mode">'.__('Mode of Travel: ', 'ba-book-everything').'</label>
                <select id="travel_mode" name="travel_mode">
                <option value="WALKING">'.__('Walking', 'ba-book-everything').'</option>
                <option value="DRIVING">'.__('Driving', 'ba-book-everything').'</option>
                <option value="BICYCLING">'.__('Bicycling', 'ba-book-everything').'</option>
                </select>
               </div>
               
               </div>
               
              <div id="meeting_points_result"></div>
            </div>
            <div id="google_map_meeting_points" data-obj-id="'.$post['ID'].'" data-lat="'.$lat.'" data-lng="'.$lng.'" data-address="'.$address.'">
            </div>     
        </div>';    
            
        $meeting_points = BABE_Post_types::get_post_meeting_points($post);
           
           if (!empty($meeting_points)){
              foreach($meeting_points as $point_id => $meeting_point){      
                $results[] = '<div class="meeting_point_default" data-point-id="'.$point_id.'">
                <div class="meeting_point_description">
                <h4>'.implode(', ', $meeting_point['times']).' - <a href="'.$meeting_point['permalink'].'" target="_blank">'.$meeting_point['address'].'</a></h4>
                '.$meeting_point['description'].'
                </div>
              </div>';
              }
              
              $output .= '<div id="block_meeting_points_default">
              '.implode('', $results).'
              </div>';
            
           } //// end if !empty($meeting_points)
        
        }
        
        return $output; 
    }
    
////////////////////////////
    /**
	 * Add address map to booking_obj post.
     * @param array $post
     * @return string
	 */
    public static function block_address_map($post){

        $output = '';
        
        if (!empty($post) && isset($post['address']['address']) && isset($post['address']['latitude']) && isset($post['address']['longitude'])){
        
        $address = apply_filters('translate_text', $post['address']['address']);
        $lat = $post['address']['latitude'];
        $lng = $post['address']['longitude'];
        
        $output .= '<div id="block_address_map">
            <div id="google_map_address" data-obj-id="'.$post['ID'].'" data-lat="'.$lat.'" data-lng="'.$lng.'" data-address="'.$address.'">
            </div>     
        </div>';
        }
        
        return $output; 
    }
    
////////////////////////////
    /**
	 * Add address map with direction to booking_obj post.
     * @param array $post
     * @return string
	 */
    public static function block_address_map_with_direction($post){

        $output = '';
        
        if (!empty($post) && isset($post['address']['address']) && isset($post['address']['latitude']) && isset($post['address']['longitude'])){
        
        $address = apply_filters('translate_text', $post['address']['address']);
        $lat = $post['address']['latitude'];
        $lng = $post['address']['longitude'];
        
        $output .= '<div id="block_address_map_with_direction">
             <h3>'.__('Find a route from your location', 'ba-book-everything').'</h3>              
              
              <input class="address-autocomplete" name="autocomplete" placeholder="'.__( 'Enter your address', 'ba-book-everything' ).'" type="text" />
              
              <div id="travel_mode_panel">
                <label for="travel_mode">'.__('Mode of Travel: ', 'ba-book-everything').'</label>
                <select id="travel_mode" name="travel_mode">
                <option value="WALKING">'.__('Walking', 'ba-book-everything').'</option>
                <option value="DRIVING">'.__('Driving', 'ba-book-everything').'</option>
                <option value="BICYCLING">'.__('Bicycling', 'ba-book-everything').'</option>
                </select>
               </div>
               
            <div id="google_map_address_with_direction" data-obj-id="'.$post['ID'].'" data-lat="'.$lat.'" data-lng="'.$lng.'" data-address="'.$address.'">
            </div>    
        </div>
        
        ';
        }
        
        return $output; 
    }    

////////////////////////////
    
}

BABE_html::init();
