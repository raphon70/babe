<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_CMB2_admin Class.
 * 
 * @class 		BABE_CMB2_admin
 * @version		1.3.0
 * @author 		Booking Algorithms
 */

class BABE_CMB2_admin {
    
    private static $nonce_title = 'cmb2-tpl-nonce';

    private static $posts_options = [];
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {       
        add_action( 'cmb2_admin_init', array( __CLASS__, 'categories_metabox' ), 10, 1);
        add_action( 'cmb2_admin_init', array( __CLASS__, 'ages_metabox' ), 10, 1);
        add_action( 'cmb2_admin_init', array( __CLASS__, 'taxonomies_metabox' ), 10, 1);
        add_action( 'cmb2_admin_init', array( __CLASS__, 'taxonomies_list_metabox' ), 10, 1);
        
        add_action( 'cmb2_admin_init', array( __CLASS__, 'booking_obj_metabox' ), 10, 1);
        add_action( 'cmb2_init', array( __CLASS__, 'booking_obj_metabox' ), 10, 1);
        add_action( 'cmb2_booking_obj_after_select_category', array( __CLASS__, 'booking_obj_after_select_category' ), 10, 2);
        add_action( 'cmb2_booking_obj_before_av_dates', array( __CLASS__, 'booking_obj_before_av_dates' ), 10, 2);
        
        add_action( 'cmb2_admin_init', array( __CLASS__, 'faq_metabox' ), 10, 1);
        add_action( 'cmb2_init', array( __CLASS__, 'faq_metabox' ), 10, 1);

        add_action( 'cmb2_admin_init', array( __CLASS__, 'fee_metabox' ), 10, 1);
        add_action( 'cmb2_init', array( __CLASS__, 'fee_metabox' ), 10, 1);
        
        add_action( 'cmb2_admin_init', array( __CLASS__, 'service_metabox' ), 10, 1);
        add_action( 'cmb2_init', array( __CLASS__, 'service_metabox' ), 10, 1);
        add_action( 'cmb2_service_before_service_type', array( __CLASS__, 'service_before_service_type_front' ), 10, 2);
        
        add_action( 'cmb2_save_field', array( __CLASS__, 'cmb2_save_field'), 10, 4 );
        add_filter( 'cmb2_can_save', array( __CLASS__, 'cmb2_can_save'), 10, 2 );
        
        add_action( 'cmb2_admin_init', array( __CLASS__, 'order_metabox' ), 10, 1);
        add_action( 'cmb2_admin_init', array( __CLASS__, 'order_items_metabox' ), 20, 1);
        add_action( 'cmb2_admin_init', array( __CLASS__, 'order_customer_metabox' ), 30, 1);
        add_action( 'cmb2_admin_init', array( __CLASS__, 'order_customer_extra_guests_metabox' ), 40, 1);
        
        add_action( 'cmb2_admin_init', array( __CLASS__, 'mpoints_metabox' ), 10, 1);
        
        add_action( 'wp_ajax_save_schedule', array( __CLASS__, 'ajax_save_schedule'));
        add_action( 'wp_ajax_generate_coupon_number', array( __CLASS__, 'ajax_generate_coupon_number_admin'));

        add_action( 'wp_insert_post', array( __CLASS__, 'update_tmp_post_data'), 10, 3 );
        add_action( 'cmb2_save_post_fields_booking_obj_metabox', array( __CLASS__, 'update_booking_obj_post'), 10, 3 );

        add_action( 'wp_after_insert_post', array( __CLASS__, 'wpml_clone_post_meta_fields'), 10, 4 );
        
        add_action( 'cmb2_admin_init', array( __CLASS__, 'coupon_metabox' ), 10, 1);
        add_filter( 'cmb2_render_coupon_usage', array( __CLASS__, 'cmb2_coupon_usage'), 10, 5 );
        add_filter( 'cmb2_render_coupon_num', array( __CLASS__, 'cmb2_coupon_num'), 10, 5 );
        add_filter( 'cmb2_render_coupon_amount', array( __CLASS__, 'cmb2_coupon_amount'), 10, 5 );
        add_filter( 'cmb2_render_category_disable_dates', array( __CLASS__, 'cmb2_category_disable_dates'), 10, 5 );

        add_action( 'wp_ajax_add_category_exclude_dates', array( __CLASS__, 'ajax_add_category_exclude_dates'));
        add_action( 'wp_ajax_delete_category_exclude_dates', array( __CLASS__, 'ajax_delete_category_exclude_dates'));
        add_action( 'wp_ajax_recalculate_rating', array( __CLASS__, 'ajax_recalculate_rating'));

        add_filter( 'cmb2_render_price_details', array( __CLASS__, 'cmb2_price_details'), 10, 5 );
        add_filter( 'cmb2_render_object_code', array( __CLASS__, 'cmb2_object_code'), 10, 5 );
        add_filter( 'cmb2_render_duration', array( __CLASS__, 'cmb2_duration'), 10, 5 );
        add_filter( 'cmb2_render_time_shift', array( __CLASS__, 'cmb2_time_shift'), 10, 5 );
        add_filter( 'cmb2_render_schedule', array( __CLASS__, 'cmb2_schedule'), 10, 5 );
        add_filter( 'cmb2_render_recalculate_rating', array( __CLASS__, 'cmb2_recalculate_rating'), 10, 5 );

        add_filter( 'cmb2_render_service_prices', array( __CLASS__, 'cmb2_service_prices'), 10, 5 );
        add_filter( 'cmb2_sanitize_service_prices', array( __CLASS__, 'cmb2_sanitize_service_prices'), 10, 5 );
        add_filter( 'cmb2_render_pw_select', array( __CLASS__, 'cmb2_pw_select' ), 10, 5 );
        add_filter( 'cmb2_render_pw_multiselect', array( __CLASS__, 'cmb2_pw_multiselect' ), 10, 5 );
        add_filter( 'cmb2_sanitize_pw_multiselect', array( __CLASS__, 'cmb2_sanitize_pw_multiselect' ), 10, 4 );
        
        add_filter( 'cmb2_render_address', array( __CLASS__, 'cmb2_address'), 10, 5 );
        add_filter( 'cmb2_render_mpoints_select', array( __CLASS__, 'cmb2_mpoints_select'), 10, 5 );
        add_filter( 'cmb2_render_text_title', array( __CLASS__, 'cmb2_text_title'), 10, 5 );
        add_filter( 'cmb2_render_tax_children_multicheck', array( __CLASS__, 'cmb2_tax_children_multicheck'), 10, 5 );
        add_filter( 'cmb2_sanitize_tax_children_multicheck', array( __CLASS__, 'cmb2_sanitize_tax_children_multicheck'), 10, 5 );

        add_filter( 'cmb2_render_tax_radio', array( __CLASS__, 'cmb2_tax_radio'), 10, 5 );
        add_filter( 'cmb2_sanitize_tax_radio', array( __CLASS__, 'cmb2_sanitize_tax_radio'), 10, 5 );
        
        add_filter( 'cmb2_render_discount', array( __CLASS__, 'cmb2_discount'), 10, 5 );
        add_filter( 'cmb2_sanitize_discount', array( __CLASS__, 'cmb2_sanitize_discount'), 10, 5 );
        
        add_filter( 'cmb2_render_order_items', array( __CLASS__, 'cmb2_order_items'), 10, 5 );
        
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
        
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueued_assets' ) );

        add_action( 'mtphr_post_duplicator_created', array( __CLASS__, 'mtphr_post_duplicator_created' ), 10, 3 );
                
	}
//////////////////////////////
    /**
	 * Enqueue assets.
	 */
    public static function wp_enqueued_assets() {
        
        global $post;
        
      if (isset($_GET['inner_page']) && $_GET['inner_page'] === 'edit-post' && isset($_GET['edit_post_id']) && $_GET['edit_post_id'] && BABE_Users::current_user_can_edit_post($_GET['edit_post_id'])){

          wp_enqueue_script( 'babe-sweetalert', plugins_url( 'js/sweetalert/sweetalert2.all.min.js', BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
        
     wp_enqueue_script( 'babe-cmb2-js', plugins_url( "js/admin/babe-admin-cmb2.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
     wp_localize_script( 'babe-cmb2-js', 'babe_cmb2_lst', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'date_format' => BABE_Settings::$settings['date_format'] === 'd/m/Y' ? 'dd/mm/yy' : 'mm/dd/yy',
            'nonce' => wp_create_nonce(self::$nonce_title),
            'start_lat' => BABE_Settings::$settings['google_map_start_lat'],
            'start_lng' => BABE_Settings::$settings['google_map_start_lng'],
            'start_zoom' => BABE_Settings::$settings['google_map_zoom'],
             'messages' => [
                 'are_you_sure' => _x('Are you sure?', 'admin modal messages', 'ba-book-everything'),
                 'ok' => _x('Ok', 'admin modal messages', 'ba-book-everything'),
                 'cancel' => _x('Cancel', 'admin modal messages', 'ba-book-everything'),
                 'oops' => _x('Oops...', 'admin modal messages', 'ba-book-everything'),
                 'something_wrong' => _x('Something went wrong!', 'admin modal messages', 'ba-book-everything'),
                 'page_will_be_reloaded' => _x('The page will be reloaded', 'admin modal messages', 'ba-book-everything'),
                 'done' => _x('Done!', 'admin modal messages', 'ba-book-everything'),
                 'save' => _x('Save', 'admin modal messages', 'ba-book-everything'),
                 'delete' => _x('Delete', 'admin modal messages', 'ba-book-everything'),
                 'add_rule' => __('Add rule', 'ba-book-everything'),
                 'update_rule' => __('Update rule', 'ba-book-everything'),
             ],
         )
     );
     } 
      
     }
     
//////////////////////////////
    /**
	 * Enqueue assets admin.
	 */
    public static function admin_enqueue_scripts() {
        
     global $current_screen;   
        
     if (isset($_GET['post_type']) && isset($_GET['taxonomy']) && $_GET['post_type'] == BABE_Post_types::$booking_obj_post_type && !empty($current_screen) && $current_screen->base === 'edit-tags'){
     wp_enqueue_style( 'babe-admin-categories-style', plugins_url( "css/admin/babe-admin-categories.css", BABE_PLUGIN ), array(), BABE_VERSION);
      }
      
      wp_enqueue_script( 'babe-util-js', plugins_url( "js/util.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
     
     wp_enqueue_script( 'babe-modal-adv-js', plugins_url( "js/modal.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );

        wp_enqueue_script( 'babe-sweetalert', plugins_url( 'js/sweetalert/sweetalert2.all.min.js', BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
     
     wp_enqueue_style( 'babe-modal-style', plugins_url( "css/babe-modal.css", BABE_PLUGIN ), array(), BABE_VERSION);
      
     wp_enqueue_script( 'babe-admin-select2-js', plugins_url( "js/select2.full.min.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );

        if ( BABE_Settings::$settings['google_map_active'] ){
            ///// add google API
            $script = '(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
					key:"' . BABE_Settings::$google_api_key . '",
					v:"weekly",
					libraries:"'.apply_filters('babe_admin_google_map_libraries', 'places,marker,routes').'"
				});';

            wp_add_inline_script( 'babe-admin-select2-js', $script );
        }
      
     wp_enqueue_script( 'babe-admin-cmb2-js', plugins_url( "js/admin/babe-admin-cmb2.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );
     wp_localize_script( 'babe-admin-cmb2-js', 'babe_cmb2_lst', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'date_format' => BABE_Settings::$settings['date_format'] === 'd/m/Y' ? 'dd/mm/yy' : 'mm/dd/yy',
            'nonce' => wp_create_nonce(self::$nonce_title),
            'start_lat' => BABE_Settings::$settings['google_map_start_lat'],
            'start_lng' => BABE_Settings::$settings['google_map_start_lng'],
            'start_zoom' => BABE_Settings::$settings['google_map_zoom'],
             'messages' => [
                 'are_you_sure' => _x('Are you sure?', 'admin modal messages', 'ba-book-everything'),
                 'ok' => _x('Ok', 'admin modal messages', 'ba-book-everything'),
                 'cancel' => _x('Cancel', 'admin modal messages', 'ba-book-everything'),
                 'oops' => _x('Oops...', 'admin modal messages', 'ba-book-everything'),
                 'something_wrong' => _x('Something went wrong!', 'admin modal messages', 'ba-book-everything'),
                 'page_will_be_reloaded' => _x('The page will be reloaded', 'admin modal messages', 'ba-book-everything'),
                 'done' => _x('Done!', 'admin modal messages', 'ba-book-everything'),
                 'save' => _x('Save', 'admin modal messages', 'ba-book-everything'),
                 'delete' => _x('Delete', 'admin modal messages', 'ba-book-everything'),
                 'add_rule' => __('Add rule', 'ba-book-everything'),
                 'update_rule' => __('Update rule', 'ba-book-everything'),
             ],
             'select2select' => __('Select...', 'ba-book-everything'),
             'states' => BABE_Locales::states(),
         )
     );
     
     wp_enqueue_style( 'babe-admin-select2-style', plugins_url( "css/select2.min.css", BABE_PLUGIN ), array(), BABE_VERSION);
     
     wp_enqueue_script('jquery-ui-datepicker');
     wp_enqueue_style('jquery-ui-admin-style', plugins_url( "css/jquery-ui.min.css", BABE_PLUGIN ), array(), BABE_VERSION, 'all');
     
     wp_enqueue_style('babe-fontawesome', plugins_url( "fonts/fontawesome-free/css/all.min.css", BABE_PLUGIN ), array(), BABE_VERSION);
     
      wp_enqueue_style( 'babe-admin-cmb2-style', plugins_url( "css/admin/babe-admin-cmb2.css", BABE_PLUGIN ), array(), BABE_VERSION);
      
     }
     
///////////////////////////////////////    
    /**
	 * Ajax generate unique coupon number from admin
	 */
    public static function ajax_generate_coupon_number_admin(){
        
        $output = '';
        
        if ( isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ){
            $output = BABE_Coupons::generate_coupon_num();
        }
        
        echo $output;
        wp_die();                   
    }      
     
//////////////////////////////////////
     /**
	 * Register coupon usage field.
	 */ 
     public static function cmb2_coupon_usage($field, $value, $object_id, $object_type, $field_type){
     
      $output = '';
      
      $output .= '<div class="coupon-usage">
       '.BABE_Coupons::get_coupon_usage($object_id).'
      </div>';
    
      echo $output;
     }

    /**
     * Register coupon num field.
     */
    public static function cmb2_coupon_num($field, $value, $object_id, $object_type, $field_type){

        $output = '';

        $output .= '<div class="coupon-num">
       '. get_post_meta($object_id, '_coupon_num', true) .'
      </div>';

        echo $output;
    }

    /**
     * Register coupon amount field.
     */
    public static function cmb2_coupon_amount($field, $value, $object_id, $object_type, $field_type){

        $output = '';

        $output .= '<div class="coupon-amount">
       '. get_post_meta($object_id, '_coupon_amount_applied', true) .'
      </div>';

        echo $output;
    }
     
///////////////////////////////////////
    /**
	 * Register coupon extra fields.
	 */
    public static function coupon_metabox() {
      $prefix = '_coupon_';
      
      ////////////main metabox////////////////

      $cmb = new_cmb2_box( array(
        'id'            => 'coupon_metabox',
        'title'         => __( '&nbsp;', 'ba-book-everything' ),
        'object_types'  => array( BABE_Post_types::$coupon_post_type, ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
      ) );
      
      $cmb->add_field( array(
         'name' => __( 'Coupon Code (unique)', 'ba-book-everything' ),
         'id'   => $prefix . 'number',
         'type'    => 'text',
         'default' => BABE_Coupons::generate_coupon_num(),         
         'attributes'  => array(
           'required'    => 'required',
           'class' => 'medium-text',
         ),
         'after_field' => ' <span id="coupon_generate_num" class="button button-primary button-large">'.__( 'Generate', 'ba-book-everything' ).'</span><span id="coupon_generate_num_loader"></span>',
      ) );

	    $cmb->add_field( array(
		    'name'       => __( 'Percent, ', 'ba-book-everything' ) .'%',
		    'id'         => $prefix . 'percent',
		    'type'       => 'text',
		    'description' => __( 'Will be used by default if both amount and percent fields filled.', 'ba-book-everything' ),
		    'attributes' => array(
			    'type' => 'number',
			    'min' => '1',
			    'pattern' => '[0-9]*',
			    'class' => 'medium-text',
		    ),
	    ) );

      $cmb->add_field( array(
      'name'       => __( 'Amount, ', 'ba-book-everything' ).BABE_Currency::get_currency_symbol(),
      'id'         => $prefix . 'amount',
      'type'       => 'text',
      'attributes' => array(
           'pattern' => '[0-9\.]*',
           //'required'    => 'required',
           'class' => 'medium-text',
          ),
      ) );
      
      $cmb->add_field( array(
         'name' => __( 'Status', 'ba-book-everything' ),
         'id'   => $prefix . 'status',
         'type'    => 'select',
         'show_option_none' => false,
         'options' => BABE_Coupons::$coupon_statuses,
      ) );

        $cmb->add_field( array(
            'name' => __( 'Number of uses', 'ba-book-everything' ),
            'id'   => $prefix . 'coupon_usage_view',
            'type' => 'coupon_usage',
        ) );

        $cmb->add_field( array(
            'name' => __( 'Limit on the number of uses (0 for unlimited)', 'ba-book-everything' ),
            'id'   => $prefix . 'usage_limit',
            'type' => 'text',
            'default' => 1,
            'attributes' => array(
                'type' => 'number',
                'min' => '0',
                'pattern' => '[0-9]*',
            ),
        ) );

        $related_items_options = self::get_posts_options(
            BABE_Post_types::$booking_obj_post_type,
            $cmb
        );

        $cmb->add_field( array(
            'name'       => __( 'Apply only to selected items', 'ba-book-everything' ),
            'desc' => '',
            'id'         => $prefix . 'booking_items',
            'type' => 'pw_multiselect',
            'options' => $related_items_options,
        ) );

        $all_categories = BABE_Post_types::get_categories_arr();

        $cmb->add_field( array(
            'name'       => __( 'Apply only to items with booking category', 'ba-book-everything' ),
            'desc' => '',
            'id'         => $prefix . 'booking_category',
            'type' => 'pw_multiselect',
            'options' => $all_categories,
        ) );

        $cmb->add_field( array(
          'name' => __( 'Expiration date', 'ba-book-everything' ),
          'id'   => $prefix . 'end_date',
          'type' => 'text',
          'default' => BABE_Coupons::get_coupon_default_expiration_date(),
          'classes' => array( 'av_dates' ),
       ) );
    }               
     
/////////////cmb2_service_prices//////
     /**
	 * Register service prices fields.
	 */ 
     public static function cmb2_service_prices($field, $value, $object_id, $object_type, $field_type ){
     
      $output = '';
      
      $output .= '<div class="service_prices_block" data-obj-id="'.$object_id.'">
      
      <div class="service_price_item">
         <label for="'.$field_type->_id( '_0' ).'">'.__( 'General, ', 'ba-book-everything' ).BABE_Currency::get_currency_symbol().' | %</label>
      '.$field_type->input( array(
			'name'  => $field_type->_name( '[0]' ),
			'id'    => $field_type->_id( '_0' ),
			'value' => isset($value[0]) ? $value[0] : 0,
            'class' => 'medium-text',
            'required'    => 'required',
		) ).'
       </div>';
      
      $ages = BABE_Post_types::get_ages_arr();
      
      if (!empty($ages)){
        
        $output .= '<h4>'.__( 'price by age (used instead of the general):', 'ba-book-everything' ).'</h4>';
        
      foreach ($ages as $age_arr){
        
        $output .= '<div class="service_price_item">
         <label for="'.$field_type->_id( '_'.$age_arr['age_id'] ).'">'.$age_arr['name'] . ' (' . $age_arr['description'] . '), '.BABE_Currency::get_currency_symbol().' | %</label>
      '.$field_type->input( array(
			'name'  => $field_type->_name( '['.$age_arr['age_id'].']' ),
			'id'    => $field_type->_id( '_'.$age_arr['age_id'] ),
			'value' => isset($value[$age_arr['age_id']]) ? $value[$age_arr['age_id']] : '',
            'class' => 'medium-text',
		) ).'
       </div>';
      }
      }

      $output .= BABE_Prices::get_service_conditional_price_fields( $object_id );
      
      $output .= ' 
       </div>';
    
      echo $output;
     }

    /**
     * Sanitize discount fields.
     */
    public static function cmb2_sanitize_service_prices( $override_value, $value, $object_id, $field_args, $sanitizer_object ) {

        $ages_ordered_by_id = BABE_Post_types::get_ages_arr_ordered_by_id();

        if ( !empty($_POST['rate_conditional_prices']) ){

            $post_conditional_prices = str_replace('\"', '"', sanitize_text_field($_POST['rate_conditional_prices']) );

            try {

                $result = [];
                $accept_fields = [
                    'order',
                    'conditional_guests_sign',
                    'conditional_guests_number',
                    'conditional_price',
                    'conditional_units_sign',
                    'conditional_units_number',
                ];

                $conditional_prices_arr = json_decode( $post_conditional_prices, true );
                foreach ($conditional_prices_arr as $ind => $conditional_prices){

                    if ( empty($conditional_prices) || !is_array($conditional_prices) ){
                        continue;
                    }

                    foreach( $conditional_prices as $key => $conditional_value){
                        if ( !in_array($key, $accept_fields) ){
                            continue;
                        }
                        if ( $key !== 'conditional_price' && !is_array($conditional_value) ){
                            $result[$ind][$key] = abs( (int)$conditional_value );
                        }
                        if ( $key === 'conditional_price' && is_array($conditional_value) ){
                            foreach($conditional_value as $age_id => $age_price){

                                if ( is_array($age_price) ){
                                    continue;
                                }
                                $age_id = abs((int)$age_id);
                                if ( $age_id > 0 && !isset($ages_ordered_by_id[$age_id]) ){
                                    continue;
                                }

                                $result[$ind][$key][$age_id] = (float)$age_price;
                            }
                        }
                    }
                }
                update_post_meta($object_id, 'conditional_prices', $result);

            } catch ( Exception $e ){
                return $value;
            }
        }

        return $value;
    }
     
//////////////////////////////////////
     /**
	 * Register booking_obj price_details fields.
	 */ 
     public static function cmb2_price_details($field, $value, $object_id, $object_type, $field_type){
     
      $output = '<div id="prices-block" data-obj-id="'.$object_id.'">
      </div>
      <div id="prices-form" data-obj-id="'.$object_id.'">
      </div>';
    
      echo $output;
     }

     public static function cmb2_recalculate_rating($field, $value, $object_id, $object_type, $field_type){

      $output = '<div>
        <button id="recalculate_rating" class="button-secondary" data-obj-id="'.$object_id.'">'
          . __('Recalculate rating', 'ba-book-everything')
          . ' <i class="fas fa-sync-alt"></i>'
          .'</button>
        &nbsp;&nbsp;<span id="recalculate_rating_spinner"></span>
      </div>';

      echo $output;
     }
  
//////////////////////////////////////
     /**
	 * Register booking_obj duration fields.
	 */ 
     public static function cmb2_duration($field, $value, $object_id, $object_type, $field_type ){
     
      $output = '';
      
      // make sure we specify each part of the value we need.
	  $value = wp_parse_args( $value, array(
		'd' => '',
		'h' => '',
		'i' => '',
	  ) );
      
      $output .= '<div class="duration_block" data-obj-id="'.$object_id.'">
      <div class="duration_select"><label for="'.$field_type->_id( '_d' ).'">'.__( 'Days', 'ba-book-everything' ).'</label>
      '.$field_type->select( array(
			'name'  => $field_type->_name( '[d]' ),
			'id'    => $field_type->_id( '_d' ),
			'options' => BABE_Functions::get_range_select_options(0, 30, 1, $value['d']),
			'desc'  => '',
		) ).'
       </div>
       
       <div class="duration_select"><label for="'.$field_type->_id( '_h' ).'">'.__( 'Hours', 'ba-book-everything' ).'</label>
      '.$field_type->select( array(
			'name'  => $field_type->_name( '[h]' ),
			'id'    => $field_type->_id( '_h' ),
			'options' => BABE_Functions::get_range_select_options(0, 23, 1, $value['h']),
			'desc'  => '',
		) ).'
       </div>
       
       <div class="duration_select"><label for="'.$field_type->_id( '_i' ).'">'.__( 'Minutes', 'ba-book-everything' ).'</label>
      '.$field_type->select( array(
			'name'  => $field_type->_name( '[i]' ),
			'id'    => $field_type->_id( '_i' ),
			'options' => BABE_Functions::get_range_select_options(0, 55, 5, $value['i']),
			'desc'  => '',
		) ).'
       </div>
        
       </div>';
    
      echo $output;
     }

    /**
     * Render select2 box field
     */
    public static function cmb2_pw_select( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {

        echo $field_type_object->select( array(
            'name'  => $field_type_object->_name(),
            'id'    => $field_type_object->_id(),
            'class'            => 'pw_select2 pw_select',
            'desc'             => $field_type_object->_desc( true ),
            'options'          => '<option></option>' . $field_type_object->concat_items(),
            'data-placeholder' => $field->args( 'attributes', 'placeholder' ) ? $field->args( 'attributes', 'placeholder' ) : $field->args( 'description' ),
        ) );
    }

    /**
     * Render multi-value select input field
     */
    public static function cmb2_pw_multiselect( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {

        $a = $field_type_object->parse_args( 'pw_multiselect', array(
            'multiple'         => 'multiple',
            'style'            => 'width: 99%',
            'class'            => 'pw_select2 pw_multiselect',
            'name'             => $field_type_object->_name() . '[]',
            'id'               => $field_type_object->_id(),
            'desc'             => $field_type_object->_desc( true ),
            'options'          => self::get_pw_multiselect_options( $field_escaped_value, $field_type_object ),
            'data-placeholder' => $field->args( 'attributes', 'placeholder' ) ? $field->args( 'attributes', 'placeholder' ) : $field->args( 'description' ),
        ) );

        $attrs = $field_type_object->concat_attrs( $a, array( 'desc', 'options' ) );
        echo sprintf( '<select%s>%s</select>%s', $attrs, $a['options'], $a['desc'] );
    }

    /**
     * Return the list of options, with selected options at the top preserving their order.
     * This also handles the removal of selected options which no longer exist in the options array.
     */
    public static function get_pw_multiselect_options( $field_escaped_value, $field_type_object ) {
        $options = (array) $field_type_object->field->options();

        // If we have selected items, we need to preserve their order
        if ( ! empty( $field_escaped_value ) ) {
            $options = self::sort_array_by_array( $options, $field_escaped_value );
        }

        $selected_items = '';
        $other_items = '';

        foreach ( $options as $option_value => $option_label ) {

            // Clone args & modify for just this item
            $option = array(
                'value' => $option_value,
                'label' => $option_label,
            );

            // Split options into those which are selected and the rest
            if ( in_array( $option_value, (array) $field_escaped_value ) ) {
                $option['checked'] = true;
                $selected_items .= $field_type_object->select_option( $option );
            } else {
                $other_items .= $field_type_object->select_option( $option );
            }
        }

        return $selected_items . $other_items;
    }

    /**
     * Sort an array by the keys of another array
     */
    public static function sort_array_by_array( array $array, array $orderArray ) {
        $ordered = array();
        foreach ( $orderArray as $key ) {
            if ( array_key_exists( $key, $array ) ) {
                $ordered[ $key ] = $array[ $key ];
                unset( $array[ $key ] );
            }
        }
        return $ordered + $array;
    }

    /**
     * Handle sanitization for repeatable fields
     */
    public static function cmb2_sanitize_pw_multiselect( $check, $meta_value, $object_id, $field_args ) {
        if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
            return $check;
        }
        foreach ( $meta_value as $key => $val ) {
            $meta_value[$key] = array_map( 'sanitize_text_field', $val );
        }
        return $meta_value;
    }

//////////////////////////////////////
     /**
	 * Register booking_obj time_shift fields.
	 */ 
     public static function cmb2_time_shift($field, $value, $object_id, $object_type, $field_type ){
     
      $output = '';
      
      // make sure we specify each part of the value we need.
	  $value = wp_parse_args( $value, array(
		'h' => '',
		'i' => '',
	  ) );
      
      $output .= '<div class="time_shift_block" data-obj-id="'.$object_id.'">
 
       <div class="time_shift_select"> - 
      '.$field_type->select( array(
			'name'  => $field_type->_name( '[h]' ),
			'id'    => $field_type->_id( '_h' ),
			'options' => BABE_Functions::get_range_select_options(0, 23, 1, $value['h']),
			'desc'  => '',
		) ).'
       <label for="'.$field_type->_id( '_h' ).'">'.__( 'Hours', 'ba-book-everything' ).'</label> 
       </div>
       
       <div class="time_shift_select"> - 
      '.$field_type->select( array(
			'name'  => $field_type->_name( '[i]' ),
			'id'    => $field_type->_id( '_i' ),
			'options' => BABE_Functions::get_range_select_options(0, 55, 5, $value['i']),
			'desc'  => '',
		) ).'
       <label for="'.$field_type->_id( '_i' ).'">'.__( 'Minutes', 'ba-book-everything' ).'</label> 
       </div>
        
       </div>';
    
      echo $output;
     }
     
///////////tax_children_multicheck////////     
     /**
	 * Register booking_obj tax_children_multicheck fields.
	 */ 
     public static function cmb2_tax_children_multicheck($field, $value, $object_id, $object_type, $field_type ){
     
      $output = '';
      
      $selected_arr = array();
      
      $post_terms_ids = wp_get_post_terms($object_id, $field->args['taxonomy'], array("fields" => "ids"));
      if (!empty($post_terms_ids)){
        foreach($post_terms_ids as $term_id){
          $selected_arr[] = $term_id;
        }
      }
      
      $output .= BABE_Post_types::get_terms_children_hierarchy(array(
		'taxonomy' => $field->args['taxonomy'],
        'view' => 'multicheck', // 'select', 'multicheck' or 'list'
        'id' => $field_type->_id(),
        'name' => $field_type->_name(),
        'data-conditional-id' => isset($field->args['attributes']['data-conditional-id']) ? $field->args['attributes']['data-conditional-id'] : '',
        'data-conditional-value' => isset($field->args['attributes']['data-conditional-value']) ? $field->args['attributes']['data-conditional-value'] : '',
	  ), $selected_arr);
    
      echo $output;
     }     
     
///////////cmb2_sanitize_discount////////     
     /**
	 * Sanitize discount fields.
	 */ 
     public static function cmb2_sanitize_discount( $override_value, $value, $object_id, $field_args, $sanitizer_object ) {
        global $wpdb;
        
        /// get discount
        $discount_db = $wpdb->get_row("SELECT * FROM ".BABE_Prices::$table_discount." WHERE booking_obj_id = '".$object_id."'", ARRAY_A);
        $update_id = isset($discount_db['id']) ? $discount_db['id'] : 0;
        
        if(!empty($value) && $value['discount'] && $value['date_from'] && $value['date_to'] && BABE_Calendar_functions::isValidDate($value['date_from'], BABE_Settings::$settings['date_format']) && BABE_Calendar_functions::isValidDate($value['date_to'], BABE_Settings::$settings['date_format'])){
            $discount = (float)$value['discount'];
            $date_from = BABE_Calendar_functions::date_to_sql($value['date_from']);
            $date_to = BABE_Calendar_functions::date_to_sql($value['date_to']);
            $date_from_obj = new DateTime($date_from);
            $date_to_obj = new DateTime($date_to);
            
            if($update_id){
                $wpdb->update(
                   BABE_Prices::$table_discount,
                     array(
                       'date_from' => $date_from_obj->format('Y-m-d H:i:s'),
                       'date_to' => $date_to_obj->format('Y-m-d H:i:s'),
                       'discount' => $discount,
                     ),
                     array(
                       'booking_obj_id' => $object_id,
                     ),
                     array( 
                      '%s',	// value1
                      '%s',	// value2
                      '%d'	// value3
                     ), 
                     array( '%d' )
               );
            } else {
               $wpdb->insert(
                   BABE_Prices::$table_discount,
                     array(
                       'booking_obj_id' => $object_id,
                       'date_from' => $date_from_obj->format('Y-m-d H:i:s'),
                       'date_to' => $date_to_obj->format('Y-m-d H:i:s'),
                       'discount' => $discount,
                     )
               );
            }
            
        } elseif ($update_id) {
            $wpdb->query( $wpdb->prepare( 'DELETE FROM '.BABE_Prices::$table_discount.' WHERE booking_obj_id = %d', $object_id) );
        }

	  return $value;
     }     

///////////tax_children_multicheck////////     
     /**
	 * Sanitize tax children multichek fields.
	 */ 
     public static function cmb2_sanitize_tax_children_multicheck( $override_value, $value, $object_id, $field_args, $sanitizer_object ) {
        
        $taxonomy = isset($field_args['taxonomy']) ? $field_args['taxonomy'] : '';
        
        if ($taxonomy){
            
           $category = $field_args['render_row_cb'][0]->data_to_save['categories'];
           
           if ($field_args['id'] == $taxonomy.'_'.$category){
           
             if(empty($value)){
               $value = array();
             }
             
             $term_ids = array_map( 'intval', $value );
             $term_ids = array_unique( $term_ids );
             wp_set_object_terms( $object_id, $term_ids, $taxonomy, false );
        
           }
        }
	  return $value;
     }

     //////////////////////////////////////

    /**
     * Register booking_obj tax_radio fields.
     */
    public static function cmb2_tax_radio($field, $value, $object_id, $object_type, $field_type ){

        $output = '';

        $selected_arr = array();

        $post_terms_ids = wp_get_post_terms($object_id, $field->args['taxonomy'], array("fields" => "ids"));
        if (!empty($post_terms_ids)){
            foreach($post_terms_ids as $term_id){
                $selected_arr[] = $term_id;
            }
        }

        $terms = get_terms( array(
            'taxonomy' => $field->args['taxonomy'],
            'hide_empty' => false,
        ) );

        $conditional_attr = isset($field->args['attributes']['data-conditional-id']) && isset($field->args['attributes']['data-conditional-value']) ? ' data-conditional-id="'.$field->args['attributes']['data-conditional-id'].'" data-conditional-value="'.$field->args['attributes']['data-conditional-value'].'"' : '';

        $output .= '<ul class="cmb2-radio-list cmb2-list">
<li><input type="radio" class="cmb2-option" name="'.$field_type->_name().'" id="'.$field_type->_name().'" value="0"  '.$conditional_attr.' '.checked( true, empty($selected_arr), false ).'> <label for="'.$field_type->_name().'">'.__('None', 'ba-book-everything').'</label></li>';

        foreach ( $terms as $term ){

            $term_name = esc_attr( apply_filters('translate_text', $term->name) );

            $output .= '<li><input type="radio" class="cmb2-option" name="'.$field_type->_name().'" id="'.$field_type->_name().$term->term_id.'" value="'.$term->term_id.'"'.$conditional_attr.' '.checked( true, in_array($term->term_id, $selected_arr), false ).'> <label for="'.$field_type->_name().$term->term_id.'">'.$term_name.'</label></li>';

        }

        $output .= '</ul>';

        echo $output;
    }

    /**
     * Sanitize taxonomy_radio fields.
     */
    public static function cmb2_sanitize_tax_radio( $override_value, $value, $object_id, $field_args, $sanitizer_object ) {

        $taxonomy = isset($field_args['taxonomy']) ? $field_args['taxonomy'] : '';

        if ($taxonomy){
            $category = $field_args['render_row_cb'][0]->data_to_save['categories'];
            if ( $field_args['id'] == $taxonomy.'_'.$category ){
                wp_set_object_terms( $object_id, [(int)$value], $taxonomy, false );
                return (int)$value;
            }
            return '';
        }

        return $value;
    }
     
////////////cmb2_object_code///////////          
     /**
	 * Register booking_obj text_title fields.
	 */ 
     public static function cmb2_object_code($field, $value, $object_id, $object_type, $field_type ){
     
      $output = '';
      
      $category_id = $field->args['category_id'] ? (int)$field->args['category_id'] : 0;
      
      $category_id = $category_id<10 ? '0'.$category_id : $category_id;
      
      $generated_code = apply_filters('cmb2_booking_obj_generated_code', $category_id.'-'.$object_id, $category_id, $object_id); 
      
      $value = !$value ? $generated_code : $value;
      
      $output .= $field_type->input( array(
			'name'  => $field_type->_name(),
			'id'    => $field_type->_id(),
			'value' => $value,
          //  'class' => 'regular-text',
		//	'desc'  => '<p class="cmb2-metabox-description">'.$field->args['desc'].'</p>',
            'data-conditional-id' => $field->args['attributes']['data-conditional-id'],
            'data-conditional-value' => $field->args['attributes']['data-conditional-value'],
		) );
    
      echo $output;
     }
     
////////cmb2_before_row_divider//////     

     /**
	 * CMB2 row header
     * @param  object $field_args Current field args
     * @param  object $field      Current field object
	 */ 
     public static function cmb2_before_row_header($field_args, $field){
     
      $output = '';
      
      $title = isset($field_args['row_title']) ? $field_args['row_title'] : '';
      $data_id = isset($field_args['attributes']['data-conditional-id']) ? ' data-conditional-id="'.$field_args['attributes']['data-conditional-id'].'"': '';
      $data_value = isset($field_args['attributes']['data-conditional-value']) ? ' data-conditional-value="'.$field_args['attributes']['data-conditional-value'].'"': '';
      
      $output .= '
      <div class="cmb-row cmb-type-row-header">
      <div class="cmb2-before-row-header"'.$data_id.$data_value.' name="__row_title_'.$field_args['id'].'">'.$title.'</div>
      </div>';
    
      echo $output;
     } 
               
//////////////////////////////////////
     /**
	 * Register booking_obj text_title fields.
	 */ 
     public static function cmb2_text_title($field, $value, $object_id, $object_type, $field_type ){
     
      $output = '';
      
      $output .= $field_type->input( array(
			'name'  => $field_type->_name(),
			'id'    => $field_type->_id(),
			'value' => $value,
            'class' => 'regular-text q_translatable',
			'desc'  => '',
		) );
    
      echo $output;
     } 
     
//////////////////////////////////////
     /**
	 * Register booking_obj discount fields.
	 */ 
     public static function cmb2_discount($field, $value, $object_id, $object_type, $field_type ){
     
      $output = '';
      
      // make sure we specify each part of the value we need.
	  $value = wp_parse_args( $value, array(
		'discount' => '',
		'date_from' => '',
		'date_to' => '',
	  ) );
      
      $output .= '<div class="discount-block" data-obj-id="'.$object_id.'">
      <div class="discount"><label for="'.$field_type->_id( '_discount' ).'">'.__( 'Discount, %', 'ba-book-everything' ).'</label>
      '.$field_type->input( array(
			'name'  => $field_type->_name( '[discount]' ),
			'id'    => $field_type->_id( '_discount' ),
			'value' => $value['discount'],
            'class' => 'cmb2-text-small',
            'type' => 'number',
            'min' => '0',
            'max' => '99',
            'pattern' => '[0-9]*',
            'desc'  => '',
		) ).'
       </div>
       
       <div class="discount_date"><label for="'.$field_type->_id( '_date_from' ).'">'.__( 'Date from', 'ba-book-everything' ).'</label>
      '.$field_type->input( array(
			'name'  => $field_type->_name( '[date_from]' ),
			'id'    => $field_type->_id( '_date_from' ),
			'value' => $value['date_from'],
            'class' => 'cmb2-text-small date_input',
			'desc'  => '',
		) ).'
       </div>
       
       <div class="discount_date"><label for="'.$field_type->_id( '_date_to' ).'">'.__( 'Date to', 'ba-book-everything' ).'</label>
      '.$field_type->input( array(
			'name'  => $field_type->_name( '[date_to]' ),
			'id'    => $field_type->_id( '_date_to' ),
			'value' => $value['date_to'],
            'class' => 'cmb2-text-small date_input',
			'desc'  => '',
		) ).'
       </div>
        
       </div>';
    
      echo $output;
     }
     
//////////////////////////////
    /**
	 * Get mpoints option list.
     * @return array
	 */
    public static function cmb2_mpoints_select($field, $value, $object_id, $object_type, $field_type){
       
       $output = '';
       
       $args = array(
        'post_type'   => BABE_Post_types::$mpoints_post_type,
        'numberposts' => -1,
        'post_parent' => 0,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
        'suppress_filters' => false,
       );
       
       $children_args = $args;
       
       $posts = get_posts( $args );
       
       if ( $posts ) {
        
        $output .= '<select class="cmb2_mpoints_select" name="'.$field_type->_name().'" id="'.$field_type->_id().'" data-conditional-id="'.$field->args['attributes']['data-conditional-id'].'" data-conditional-value="'.$field->args['attributes']['data-conditional-value'].'">';
        
        foreach ( $posts as $post ) {
            
          $children_args['post_parent'] = $post->ID;
          $children = get_children( $children_args );
          $disabled = !empty($children) ? ' disabled' : '';   
          $output .= '<option class="" value="'.$post->ID.'"'.$disabled.' '.selected( $value, $post->ID, false ).' data-parent="0">'.$post->post_title.'</option>';
          
          if (!empty($children)){
              foreach($children as $child_post){
                 $output .= '<option class="" value="'.$child_post->ID.'" '.selected( $value, $child_post->ID, false ).' data-parent="'.$post->ID.'"> - '.$child_post->post_title.'</option>';
              }
          }
          
        }
        
        $output .= '</select>';
        
       }
       
       echo $output;
       
       return;
    }              
      
//////////////////////////////////////
     /**
	 * Register booking_obj address fields.
	 */ 
     public static function cmb2_address($field, $value, $object_id, $object_type, $field_type ){
     
      $output = '';
      
      // make sure we specify each part of the value we need.
	  $value = wp_parse_args( $value, array(
		'address' => '',
		'latitude' => '',
		'longitude' => '',
	  ) );
      
      $output .= '<div class="address_block" data-obj-id="'.$object_id.'">
      <div class="address_address"><label for="'.$field_type->_id( '_address' ).'">'.__( 'Address', 'ba-book-everything' ).'</label>
      '.$field_type->input( array(
			'name'  => $field_type->_name( '[address]' ),
			'id'    => $field_type->_id( '_address' ),
			'value' => $value['address'],
            'class' => 'regular-text q_translatable',
			'desc'  => '',
		) ).'
       </div>
       
       <div class="address_latitude"><label for="'.$field_type->_id( '_latitude' ).'">'.__( 'Latitude', 'ba-book-everything' ).'</label>
      '.$field_type->input( array(
			'name'  => $field_type->_name( '[latitude]' ),
			'id'    => $field_type->_id( '_latitude' ),
			'value' => $value['latitude'],
			'desc'  => '',
		) ).'
       </div>
       
       <div class="address_longitude"><label for="'.$field_type->_id( '_longitude' ).'">'.__( 'Longitude', 'ba-book-everything' ).'</label>
      '.$field_type->input( array(
			'name'  => $field_type->_name( '[longitude]' ),
			'id'    => $field_type->_id( '_longitude' ),
			'value' => $value['longitude'],
			'desc'  => '',
		) ).'
       </div>
       
       <div class="address_from_google">
          <button class="btn button get_from_google">'.__('Get data from Google API', 'ba-book-everything').'</button>
          <div class="google_map_get">
            <div class="locationField">
              <input class="autocomplete" name="autocomplete" placeholder="'.__( 'Enter address here', 'ba-book-everything' ).'" type="text" />
              <button class="btn button save_from_google">'.__('Get address', 'ba-book-everything').'</button>
            </div>
            <div class="google_map">
            </div>
          </div>
       </div>
        
       </div>';
    
      echo $output;
     }      
     
//////////////////////////////////////
     /**
	 * Register booking_obj schedule fields.
	 */ 
     public static function cmb2_schedule($field, $value, $object_id, $object_type, $field_type ){
     
      $output = '<div id="schedule_block" data-obj-id="'.$object_id.'">
      <div class="schedule_details">
      ';
      
      $schedule = get_post_meta( $object_id, 'schedule', true );
      
      //// get week days
      $days_arr = BABE_Calendar_functions::get_week_days_arr();
      
      //// foreach weekday show timespans
      foreach ($days_arr as $day_num => $day_title){
        $output .= '<div class="schedule_day" data-day-num="'.$day_num.'">
                   <h4>'.$day_title.':</h4>
                   ';
                   
        if (!empty($schedule) && isset($schedule[$day_num])){
            foreach($schedule[$day_num] as $time){
                $time_date_obj = new DateTime($time);

                $output .= '<span class="schedule_time">'.$time_date_obj->format(get_option('time_format')).'<input type="hidden" class="schedule_time_'.$day_num.'" name="_schedule_'.$day_num.'[]" value="'.$time.'"><i class="fas fa-times"></i></span>';
            }
        }           
        
        $output .= '</div>';
      }
      
      $output .= '</div>'; //// end schedule_details
      
      $output .= '<div class="schedule_form">';
      
      //// form to add time span
      $output .= '
      <div class="schedule_form_item">
      <label for="schedule_form_day">'.__( 'Day of the week', 'ba-book-everything' ).':</label>
      <select id="schedule_form_day" class="schedule_form_select" name="schedule_form_day">';
      foreach ($days_arr as $day_num => $day_title){
          $output .= '<option value="'. $day_num .'">'. $day_title .'</option>';
      }
      $output .= '
      </select>
      </div>';

      $hour_select = '';

         for($i = 0; $i <= 23; $i += 1){
             $time_date_obj = new DateTime($i . ':00');
             $time_title = $time_date_obj->format(get_option('time_format'));
             $time_title = str_replace(':00', '', $time_title);

             $hour_select .= '<option value="'. $i .'" '. selected( $value, $i, false ) .'>'. $time_title .'</option>';
         }
      
      $output .= '
      <div class="schedule_form_item">
      <label for="schedule_form_hour">'.__( 'Hour', 'ba-book-everything' ).':</label>
      <select id="schedule_form_hour" class="schedule_form_select" name="schedule_form_hour">
      '.$hour_select.'
      </select>
      </div>';
      
      $output .= '
      <div class="schedule_form_item">
      <label for="schedule_form_minute">'.__( 'Minutes', 'ba-book-everything' ).':</label>
      <select id="schedule_form_minute" class="schedule_form_select" name="schedule_form_minute">
      '.BABE_Functions::get_range_select_options(0,55,5).'
      </select>
      </div>';
      
      $output .= '<div class="schedule_form_item">
      <button class="btn button" id="add_schedule">'.__('Add time to schedule', 'ba-book-everything').'</button>
      </div>';
      
      $output .= '</div>'; //// end schedule_form
      $output .= '<div class="schedule_form_save"><button class="btn button button-primary" id="save_schedule">'.__('Save schedule', 'ba-book-everything').'</button>
      <div id="save_schedule_spinner"></div>
      </div>';
      $output .= '</div>'; //// end schedule_block
      
      echo $output;
     }
      
///////////////////////////////////////    
    /**
	 * Save schedule.
	 */
    public static function ajax_save_schedule(){
        $output = '';
        
        if (
                isset($_POST['obj_id'])
                && (int)$_POST['obj_id'] > 0
                && isset($_POST['start_date'])
                && BABE_Calendar_functions::isValidDate($_POST['start_date'], BABE_Settings::$settings['date_format'])
                && isset($_POST['end_date'])
                && BABE_Calendar_functions::isValidDate($_POST['end_date'], BABE_Settings::$settings['date_format'])
                && isset($_POST['cyclic_start_every'])
                && isset($_POST['cyclic_av'])
                && isset($_POST['schedule'])
                && isset($_POST['nonce'])
                && wp_verify_nonce( $_POST['nonce'], self::$nonce_title )
                && BABE_Users::current_user_can_edit_post($_POST['obj_id'])
        ){
            
          $booking_obj_id = (int)$_POST['obj_id'];
          $schedule = empty($_POST['schedule']) ? array() : $_POST['schedule'];
          
          foreach ($schedule as $ind => $day_arr){
            sort($schedule[$ind]);
          }
          
          update_post_meta($booking_obj_id, 'schedule', $schedule);
          //// update av calendar
          BABE_Calendar_functions::update_av_cal($booking_obj_id, $_POST['start_date'], $_POST['end_date'], $schedule, absint($_POST['cyclic_start_every']), absint($_POST['cyclic_av']));          
          $output = 1;
        }
        
        echo $output;
        wp_die();                   
    }               
     
////////booking_obj_after_select_category/////

    /**
     * Register booking_obj after_select_category fields.
     * @param CMB2 $cmb
     * @param string $prefix
     */
    public static function booking_obj_after_select_category($cmb, $prefix) {
        
        /// Conditional magic starts here...
        
        /// get all categories
        
        $all_categories = BABE_Post_types::get_categories_arr();
        
        /// foreach category

        foreach ($all_categories as $category_id => $category_name){

            $category = get_term_by( 'id', $category_id, BABE_Post_types::$categories_tax );

            /// get category meta

            $category_meta = BABE_Post_types::get_term_meta($category_id);
            //Array ( [categories_week] => Array ( [0] => 1 [1] => 2 [2] => 3 [3] => 4 [4] => 5 [5] => 6 [6] => 7 ) [categories_booking_rule] => 0 [categories_gmap_active] => 1 [categories_reviews_active] => 0 [categories_add_taxes] => 0 [categories_taxonomies] => Array ( [0] => 8 ) [categories_step_by_step] => 0 [categories_address] => 0 )

            if ( empty($category_meta['categories_booking_rule']) ){
                continue;
            }

            $rule_id = $category_meta['categories_booking_rule'];
            $rules = BABE_Booking_Rules::get_rule($rule_id);

            if( empty($rules) ){
                continue;
            }

            if ( $rules['booking_mode'] === 'object' ){

                $cmb->add_field( array(
                    'name'       => __( 'Number of items', 'ba-book-everything' ),
                    'desc'       => __( 'Number of the same rooms, cars, etc.', 'ba-book-everything' ),
                    'id'         => $prefix . 'items_number_'.$category->slug,
                    'type'       => 'text',
                    'default'    => 1,
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                        'type' => 'number',
                        'min' => '1',
                        'pattern' => '[0-9]*',
                        'class' => 'cmb2-text-small',
                    ),
                ) );
            }

            if(apply_filters('cmb2_booking_obj_add_obj_code', true, $cmb, $prefix, $category, $category_id, $rules)){
                //object id for front-end, generated
                $cmb->add_field( array(
                    'name'       => __( 'Object code (unique)', 'ba-book-everything' ),
                    'desc'       => __( 'any unique character set for identification', 'ba-book-everything' ),
                    'id'         => $prefix . 'code_'.$category->slug,
                    'type'       => 'object_code',
                    'category_id' => $category_id,
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                    ),
                ) );
            }

            if($rules['stop_booking_before']){

                $cmb->add_field( array(
                    'name'       => __( 'Stop booking .. hours before the start (optional)', 'ba-book-everything' ),
                    'desc'       => '',
                    'id'         => $prefix . 'stop_booking_before_'.$category->slug,
                    'type'       => 'text',
                    'default'    => $rules['stop_booking_before'],
                    'category_id' => $category_id,
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                        'type' => 'number',
                        'min' => '0',
                        'pattern' => '[0-9]*',
                        'class' => 'cmb2-text-small',
                    ),
                ) );

            }

            // $rules['basic_booking_period']
            if ($rules['basic_booking_period'] === 'single_custom'){
                $cmb->add_field( array(
                    'name' => __( 'Start Time', 'ba-book-everything' ),
                    'desc' => '',
                    'id'   => $prefix . 'start_time_'.$category->slug,
                    'type' => 'text_time',
                    'time_format' => get_option('time_format'),
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                    ),
                ) );

                $cmb->add_field( array(
                    'name' => __( 'End Time', 'ba-book-everything' ),
                    'desc' => '',
                    'id'   => $prefix . 'end_time_'.$category->slug,
                    'type' => 'text_time',
                    'time_format' => get_option('time_format'),
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                    ),
                ) );
            } /// end if single_custom

            if ($rules['basic_booking_period'] === 'recurrent_custom'){

                //// add duration
                $cmb->add_field( array(
                    'name' => __( 'Duration', 'ba-book-everything' ),
                    'desc' => '',
                    'id'   => $prefix . 'duration_'.$category->slug,
                    'type' => 'duration',
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                    ),
                    'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
                    'row_title' => __( 'Schedule section', 'ba-book-everything' ),
                ) );

                //// add schedule
                $cmb->add_field( array(
                    'id'   => 'schedule_conditions_'.$category->slug,
                    'type' => 'hidden',
                ) );
            }

            if ( in_array($rules['basic_booking_period'], [
                'day',
                'hour',
            ]) ){

                $time_options = [];
                for($i=0; $i <= 23; $i++){
                    $i_text = $i < 10 ? '0'. (string)$i : (string)$i;
                    $i_text .= ':00';
                    $time_options[$i_text] = $i_text;
                }

                $cmb->add_field( array(
                    'name' => __( 'Available time from', 'ba-book-everything' ),
                    'desc' => __( 'Leave blank to allow any time', 'ba-book-everything' ),
                    'id'   => $prefix . 'av_time_from_'.$category->slug,
                    'type' => 'pw_multiselect',
                    'options' => $time_options,
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                    ),
                    'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
                    'row_title' => __( 'Business time', 'ba-book-everything' ),
                ) );

                $cmb->add_field( array(
                    'name' => __( 'Available time to', 'ba-book-everything' ),
                    'desc' => __( 'Leave blank to allow any time', 'ba-book-everything' ),
                    'id'   => $prefix . 'av_time_to_'.$category->slug,
                    'type' => 'pw_multiselect',
                    'options' => $time_options,
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                    ),
                ) );
            }

        } //// end foreach $all_categories
        
        //// add schedule
            $cmb->add_field( array(
              'name' => __( 'Schedule', 'ba-book-everything' ),
              'desc' => '',
              'id'   => $prefix . 'schedule_group',
              'type' => 'schedule',
             ) );
        
    }
    
//////////////////////////////////////
     /**
	 * Register order details fields.
	 */ 
     public static function cmb2_order_items($field, $value, $object_id, $object_type, $field_type){
     
      $output = '';
      
      $output .= BABE_html::order_items($object_id); 
    
      echo $output;
     }    
    
/////////order_metabox/////////////              
///////////////////////////////////////
    /**
	 * Register order extra fields.
	 */
    public static function order_metabox() {
      $prefix = '_';
      
      $cmb = new_cmb2_box( array(
        'id'            => 'order_metabox',
        'title'         => __( '&nbsp;', 'ba-book-everything' ),
        'object_types'  => array( BABE_Post_types::$order_post_type, ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
      ) );
      
      $cmb->add_field( array(
         'name' => __( 'Order Number (unique)', 'ba-book-everything' ),
         'id'   => $prefix . 'order_number',
         'type'    => 'text',
         'default' => BABE_Order::generate_order_num(),
      ) );

      $currency = '';

        if ( empty( $cmb->object_id() ) ){
            $order_statuses['draft'] = BABE_Order::$order_statuses['draft'];

        } else {

            $order_id = $cmb->object_id();
            $currency = BABE_Order::get_order_currency( $order_id );

            $current_status = BABE_Order::get_order_status( $order_id );
            if ( !$current_status ){
                $order_statuses['draft'] = BABE_Order::$order_statuses['draft'];
            } else {
                $order_statuses[$current_status] = BABE_Order::$order_statuses[$current_status] ?? $current_status;
            }

            if ( $current_status && !empty(BABE_Order::$order_status_router[$current_status]) ){

                foreach( BABE_Order::$order_status_router[$current_status] as $new_status => $status_data ){

                    if ( apply_filters('babe_order_status_available_to_select', true, $current_status, $new_status, $order_id) ) {
                        $order_statuses[$new_status] = BABE_Order::$order_statuses[$new_status];
                    }
                }
            }
        }
      
      $cmb->add_field( array(
         'name' => __( 'Status', 'ba-book-everything' ),
         'id'   => $prefix . 'status',
         'type'    => 'select',
         'show_option_none' => false,
         'options' => $order_statuses,
         'default' => 'draft',
      ) );
      
      $cmb->add_field( array(
         'name' => __( 'Total amount, ', 'ba-book-everything' ).BABE_Currency::get_currency_symbol($currency),
         'id'   => $prefix . 'total_amount',
         'type'    => 'text',
         'default' => 0,
      ) );

        if ( BABE_Coupons::coupons_active() ){

            $cmb->add_field( array(
                'name' => __( 'Coupon number', 'ba-book-everything' ),
                'id'   => '_coupon_num_update',
                'type' => 'text',
            ) );

            $cmb->add_field( array(
                'name' => __( 'Applied coupon number', 'ba-book-everything' ),
                'id'   => '_coupon_num_view',
                'type' => 'coupon_num',
            ) );

            $cmb->add_field( array(
                'name' => __( 'Applied coupon amount, ', 'ba-book-everything' ).BABE_Currency::get_currency_symbol($currency),
                'id'   => '_coupon_amount_applied_view',
                'type' => 'coupon_amount',
            ) );
        }
      
      $cmb->add_field( array(
         'name' => __( 'Expected Prepaid amount, ', 'ba-book-everything' ).BABE_Currency::get_currency_symbol($currency),
         'id'   => $prefix . 'prepaid_amount',
         'type'    => 'text',
         'default' => 0,
         'after_field' => ' <span id="get_the_rest_amount" class="button button-secondary button-large">'.__( 'Get the rest amount', 'ba-book-everything' ).'</span>',
      ) );
      
      $cmb->add_field( array(
         'name' => __( 'Prepaid received, ', 'ba-book-everything' ).BABE_Currency::get_currency_symbol($currency),
         'id'   => $prefix . 'prepaid_received',
         'type'    => 'text',
         'default' => 0,
      ) );
      
      
    }
    
///////////////////////////////////////
    /**
	 * Register order extra fields.
	 */
    public static function order_items_metabox() {
      $prefix = '_';
      
      ////// Order details//////////
      
      $cmb = new_cmb2_box( array(
        'id'            => 'order_items_metabox',
        'title'         => __( 'Order items', 'ba-book-everything' ),
        'object_types'  => array( BABE_Post_types::$order_post_type, ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
      ) );
      
      $cmb->add_field( array(
         'name' => __( 'Order items', 'ba-book-everything' ),
         'id'   => $prefix . 'order_items',
         'type'    => 'order_items',
      ) );
      
    }
    
///////////////////////////////////////
    /**
	 * Register order extra fields.
	 */
    public static function order_customer_metabox() {
      $prefix = '_';
      
      ////// Customer details//////////
      
      $cmb = new_cmb2_box( array(
        'id'            => 'order_customer_metabox',
        'title'         => __( 'Customer contacts', 'ba-book-everything' ),
        'object_types'  => array( BABE_Post_types::$order_post_type, ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
      ) );
      
      $order_id = 0;
       if ( isset( $_REQUEST['post'] ) ) {
        $order_id = $cmb->object_id( absint( $_REQUEST['post'] ) );
       } elseif ( isset( $_REQUEST['post_ID'] ) ) {
        $order_id = $cmb->object_id( absint( $_REQUEST['post_ID'] ) );
       }
      
      $customer_details_arr = BABE_Order::get_order_customer_details($order_id);
      
      $customer_details_arr_texts = apply_filters('babe_cmb2_order_customer_fields_arr', $customer_details_arr, $order_id);
      
      foreach($customer_details_arr_texts as $field_name => $field_value){

          if ($field_name == 'email_check' || $field_name == 'extra_guests' || $field_name == 'billing_address'){
              continue;
          }

          $cmb2_field_args = [
              'name' => BABE_html::checkout_field_label($field_name),
              'id'   =>  $field_name,
              'type'    => 'text',
          ];

          $cmb2_field_args = apply_filters('babe_cmb2_order_customer_field_args', $cmb2_field_args, $field_name, $order_id);

          if ( $cmb2_field_args['type'] === 'textarea' ){
              $cmb2_field_args['type'] = 'textarea_small';
          }

          $cmb->add_field( $cmb2_field_args );
      }

      if ( isset(
              $customer_details_arr_texts['billing_address']['country'],
              $customer_details_arr_texts['billing_address']['state'],
              $customer_details_arr_texts['billing_address']['city'],
              $customer_details_arr_texts['billing_address']['address']
           )
      ){
          $countries = BABE_Locales::countries();
          $states = BABE_Locales::states();
          $country = !empty($customer_details_arr_texts['billing_address']['country']) ? $customer_details_arr_texts['billing_address']['country'] : '';

          $cmb->add_field( array(
              'name' => BABE_html::checkout_field_label('country'),
              'id'   => 'billing_address[country]',
              'type'    => 'select',
              'show_option_none' => true,
              'options' => $countries,
              'default' => isset($customer_details_arr_texts['billing_address']['country']) ? $customer_details_arr_texts['billing_address']['country'] : '',
          ) );

          $state_options = !empty($states[$country]) ? $states[$country] : [];

          $cmb->add_field( array(
              'name' => BABE_html::checkout_field_label('state'),
              'id'   => 'billing_address[state]',
              'type'    => 'select',
              'show_option_none' => true,
              'options' => $state_options,
              'default' => isset($customer_details_arr_texts['billing_address']['state']) ? $customer_details_arr_texts['billing_address']['state'] : '',
          ) );

          $cmb->add_field( array(
              'name' => BABE_html::checkout_field_label('city'),
              'id'   => 'billing_address[city]',
              'type'    => 'text',
              'default' => isset($customer_details_arr_texts['billing_address']['city']) ? $customer_details_arr_texts['billing_address']['city'] : '',
          ) );

          $cmb->add_field( array(
              'name' => BABE_html::checkout_field_label('address'),
              'id'   => 'billing_address[address]',
              'type'    => 'text',
              'default' => isset($customer_details_arr_texts['billing_address']['address']) ? $customer_details_arr_texts['billing_address']['address'] : '',
          ) );

      }
      
      do_action('babe_cmb2_order_customer_fields', $cmb, $customer_details_arr, $order_id);
      
    }

///////////////////////////////////////
    /**
     * Register order extra guests fields.
     */
    public static function order_customer_extra_guests_metabox() {

        ////// Extra guest details//////////

        $cmb = new_cmb2_box( array(
            'id'            => 'order_customer_extra_guests_metabox',
            'title'         => __( 'Extra guest contacts', 'ba-book-everything' ),
            'object_types'  => array( BABE_Post_types::$order_post_type, ), // Post type
            'context'       => 'normal',
            'priority'      => 'high',
            'show_names'    => true, // Show field names on the left
            // 'cmb_styles' => false, // false to disable the CMB stylesheet
            // 'closed'     => true, // Keep the metabox closed by default
        ) );

        $order_id = $cmb->object_id();

        $order_items = BABE_Order::get_order_items($order_id);

        if ( empty($order_items) ){
            return;
        }

        $order = reset($order_items);
        $ages_arr = BABE_Post_types::get_post_ages($order['booking_obj_id']);

        $total_number_of_guests = array_sum($order['meta']['guests']);

        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($order['booking_obj_id']);

        if ( empty($rules_cat['category_meta']['categories_other_guests']) || $total_number_of_guests < 2 ) {
            return;
        }

        $custom_field_id = $cmb->add_field( array(
            'id'          => 'extra_guests',
            'type'        => 'group',
            'options'     => array(
                'group_title'   => __( 'Extra guest', 'ba-book-everything' ).' {#}', // since version 1.1.4, {#} gets replaced by row number
                'add_button'    => __( 'Add guest data', 'ba-book-everything' ),
                'remove_button' => __( 'Remove guest data', 'ba-book-everything' ),
                'sortable'      => true, // beta
            ),
        ) );

        $customer_meta = apply_filters('babe_order_extra_guest_fields', [
            'first_name' => '',
            'last_name' => '',
            'age_group' => '',
        ]);

        foreach ($customer_meta as $customer_meta_key => $customer_meta_desc){

            if ( $customer_meta_key == 'age_group' && !empty($ages_arr) ){

                $age_options = [];

                foreach ( $ages_arr as $age ){
                    $age_options[$age['age_id']] = $age['name'] . ' ' . $age['description'];
                }

                $cmb->add_group_field( $custom_field_id, [
                    'name'       => BABE_html::checkout_field_label($customer_meta_key),
                    'id'         => $customer_meta_key,
                    'type'       => 'select',
                    'options' => $age_options,
                ] );

            } else {

                $cmb->add_group_field( $custom_field_id, [
                    'name'       => BABE_html::checkout_field_label($customer_meta_key),
                    'id'         => $customer_meta_key,
                    'type'       => 'text',
                ] );
            }
        }

        do_action('babe_cmb2_order_customer_extra_guest_fields', $cmb, $order_id);

    }

/////////faq_metabox/////////////              
///////////////////////////////////////
    /**
	 * Register faq extra fields.
	 */
    public static function faq_metabox() {
      
      $prefix = '';
      
      $cmb = new_cmb2_box( array(
        'id'            => 'faq_metabox',
        'title'         => __( '&nbsp;', 'ba-book-everything' ),
        'object_types'  => array( BABE_Post_types::$faq_post_type, ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
      ) );
        
     if (!is_admin()){
      
     do_action('babe_cmb2_faq_metabox_not_admin_before', $cmb, $prefix); 
      
      $cmb->add_field( array(
    'name' => __( 'Title', 'ba-book-everything' ),
    'id'   => 'tmp_post_title',
    'type' => 'text',
    'attributes'  => array(
         'required'    => 'required',
         'placeholder' => __( 'Enter title here', 'ba-book-everything' ),
         ),
    ) );
    
    $cmb->add_field( array(
    'name' => __( 'Content', 'ba-book-everything' ),
    'id'   => 'tmp_post_content',
    'type' => 'wysiwyg',
    'options' => array(
        'textarea_rows' => get_option('default_post_edit_rows', 7),
    ),
    ) );
    
    do_action('babe_cmb2_faq_metabox_not_admin_after', $cmb, $prefix);
    
    } else {
        do_action('babe_cmb2_faq_metabox_admin', $cmb, $prefix);
    }
          
    }          
    
/////////service_metabox/////////////              
///////////////////////////////////////
    /**
	 * Register service extra fields.
	 */
    public static function service_metabox() {
      $prefix = '';
      
      $cmb = new_cmb2_box( array(
        'id'            => 'service_metabox',
        'title'         => __( '&nbsp;', 'ba-book-everything' ),
        'object_types'  => array( BABE_Post_types::$service_post_type, ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
      ) );
      
      do_action('cmb2_service_before_service_type', $cmb, $prefix);
      
      $cmb->add_field( array(
         'name' => __( 'Service type', 'ba-book-everything' ),
         'id'   => $prefix . 'service_type',
         'type'    => 'radio',
         'options' => BABE_Post_types::$service_type_names,
         'default' => 'booking',
      ) );
      
      $cmb->add_field( array(
         'name' => __( 'Price type', 'ba-book-everything' ),
         'id'   => $prefix . 'price_type',
         'type'    => 'radio_inline',
         'options' => array(
            'amount' => __( 'Amount', 'ba-book-everything' ),
            'percent' => __( 'Percentages from booking (only for per Booking service type)', 'ba-book-everything' ),
         ),
         'default' => 'amount',
      ) );      
      
      $cmb->add_field( array(
         'name' => __( 'Prices', 'ba-book-everything' ),
         'id'   => $prefix . 'prices',
         'type' => 'service_prices',
      ) );

        $cmb->add_field( array(
            'name' => __( 'Is mandatory?', 'ba-book-everything' ),
            'id'   => $prefix . 'is_mandatory',
            'type'    => 'radio_inline',
            'options' => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        $cmb->add_field( array(
            'name' => __( 'Allow to select quantity?', 'ba-book-everything' ),
            'id'   => $prefix . 'allow_quantity',
            'type'    => 'radio_inline',
            'options' => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        //maximum quantity
        $cmb->add_field( array(
            'name'       => __( 'Maximum quantity', 'ba-book-everything' ),
            // 'desc'       => __( 'field description (optional)', 'ba-book-everything' ),
            'id'         => $prefix . 'max_quantity',
            'type'       => 'text',
            'attributes' => array(
                'type' => 'number',
                'min' => '1',
                'pattern' => '[0-9]*',
            ),
            'default' => 1,
        ) );

        do_action('cmb2_service_after_fields', $cmb, $prefix);
      
    }

/////////fee_metabox/////////////
///////////////////////////////////////
    /**
     * Register fee extra fields.
     */
    public static function fee_metabox() {
        $prefix = '';

        $cmb = new_cmb2_box( array(
            'id'            => 'fee_metabox',
            'title'         => __( '&nbsp;', 'ba-book-everything' ),
            'object_types'  => array( BABE_Post_types::$fee_post_type, ), // Post type
            'context'       => 'normal',
            'priority'      => 'high',
            'show_names'    => true,
        ) );

        if ( !is_admin() ){

            do_action('babe_cmb2_fee_metabox_not_admin_before', $cmb, $prefix);

            $cmb->add_field( array(
                'name' => __( 'Title', 'ba-book-everything' ),
                'id'   => 'tmp_post_title',
                'type' => 'text',
                'attributes'  => array(
                    'required'    => 'required',
                    'placeholder' => __( 'Enter title here', 'ba-book-everything' ),
                ),
            ) );

            $cmb->add_field( array(
                'name' => __( 'Content', 'ba-book-everything' ),
                'id'   => 'tmp_post_content',
                'type' => 'wysiwyg',
                'options' => array(
                    'textarea_rows' => get_option('default_post_edit_rows', 7),
                ),
            ) );
        }

        do_action('cmb2_fee_before_fields', $cmb, $prefix);

        $cmb->add_field( array(
            'name' => __( 'Price type', 'ba-book-everything' ),
            'id'   => $prefix . 'price_type',
            'type'    => 'radio_inline',
            'options' => array(
                'amount' => __( 'Amount', 'ba-book-everything' ),
                'percent' => __( 'Percentage', 'ba-book-everything' ),
            ),
            'default' => 'amount',
        ) );

        $cmb->add_field( array(
            'name' => __( 'Price', 'ba-book-everything' ).', '.BABE_Currency::get_currency_symbol().' | %',
            'id'   => $prefix . 'price',
            'type' => 'text',
        ) );

        $cmb->add_field( array(
            'name' => __( 'Is mandatory?', 'ba-book-everything' ),
            'id'   => $prefix . 'is_mandatory',
            'type'    => 'radio_inline',
            'options' => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        do_action('cmb2_fee_after_fields', $cmb, $prefix);

    }

/////////mpoints_metabox/////////////              
///////////////////////////////////////
    /**
	 * Register service extra fields.
	 */
    public static function mpoints_metabox() {
      $prefix = '';
      
      $cmb = new_cmb2_box( array(
        'id'            => 'mpoints_metabox',
        'title'         => __( '&nbsp;', 'ba-book-everything' ),
        'object_types'  => array( BABE_Post_types::$mpoints_post_type, ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
      ) );
      
      $cmb->add_field( array(
                'name'       => __( 'Address', 'ba-book-everything' ),
                'id'         => 'address',
                'type'       => 'address',
                'desc'       => __( 'Street, etc.', 'ba-book-everything' ),
      ) );
      
      $cmb->add_field( array(
                'name'       => __( 'Address description (optional)', 'ba-book-everything' ),
                'id'         => 'description',
                'type'       => 'textarea_small',
                'desc'       => '',
      ) );
      
    }
    
////////booking_obj_before_av_dates////    
     /**
	 * Register booking_obj before_av_dates fields.
	 */
    public static function booking_obj_before_av_dates($cmb, $prefix) {
        
        if (!is_admin()){
            
    //$object_id = isset($_GET['edit_post_id']) ? absint($_GET['edit_post_id']) : $cmb->object_id();        
            //// front end extra fields
    $cmb->add_field( array(
    'name' => __( 'Title', 'ba-book-everything' ),
    'id'   => 'tmp_post_title',
    'type' => 'text',
    'attributes'  => array(
         'required'    => 'required',
         'placeholder' => __( 'Enter title here', 'ba-book-everything' ),
         ),
    ) );
    
    $cmb->add_field( array(
    'name' => __( 'Content', 'ba-book-everything' ),
    'id'   => 'tmp_post_content',
    'type' => 'wysiwyg',
    'options' => array(
        'textarea_rows' => get_option('default_post_edit_rows', 7),
    ),
    ) );
    
    $cmb->add_field( array(
	'name'    => __( 'Featured image', 'ba-book-everything' ),
	'id'      => 'tmp_featured_image',
	'type'    => 'file',
	'options' => array(
		'url' => false, // Hide the text input for the url
	),
	'text'    => array(
		'add_upload_file_text' => 'Add image' // Change upload button text. Default: "Add or Upload File"
	),
	'query_args' => array(
	  'type' => array(
		'image/gif',
		'image/jpeg',
		'image/png',
	  ),
	),
	'preview_size' => array( 70, 70 ), // Image size to use when previewing in the admin.
    ) );  
            
          $group_images_id = $cmb->add_field( array(
                 'id'          => $prefix . 'images',
                 'type'        => 'group',
                 'description' => __( 'Slideshow Images', 'ba-book-everything' ),
                 // 'repeatable'  => false, // use false if you want non-repeatable group
                 'options'     => array(
                   'group_title'   => __( 'Slideshow Image', 'ba-book-everything' ).' {#}', // since version 1.1.4, {#} gets replaced by row number
                   'add_button'    => __( 'Add slideshow image', 'ba-book-everything' ),
                   'remove_button' => __( 'Remove image', 'ba-book-everything' ),
                   'sortable'      => true, // beta
                 ),
                ) );
    
    $cmb->add_group_field( $group_images_id, array(
                'name'       => __( 'Image file', 'ba-book-everything' ),
                'id'         => 'image',
                'type'       => 'file',
                'query_args' => array(
                   'type' => array(
                      'image/gif',
                      'image/jpeg',
                      'image/png',
                   ),
                ),
               // 'desc'       => __( 'image URL', 'ba-book-everything' ),
                'preview_size' => array( 70, 70 ),
                'options' => array(
                  'url' => false, // Hide the text input for the url
                ),
                ) );
                
    $cmb->add_group_field( $group_images_id, array(
                'name'       => __( 'Image description (optional)', 'ba-book-everything' ),
                'id'         => 'description',
                'type'       => 'textarea_small',
                ) );  
            
        }
        
        return;
    }
    
////////service_before_service_type_front////    
     /**
	 * Register service before_service_type fields.
	 */
    public static function service_before_service_type_front($cmb, $prefix) {
        
        if (!is_admin()){
            //// front end extra fields
    $cmb->add_field( array(
    'name' => __( 'Title', 'ba-book-everything' ),
    'id'   => 'tmp_post_title',
    'type' => 'text',
    'attributes'  => array(
         'required'    => 'required',
         'placeholder' => __( 'Enter title here', 'ba-book-everything' ),
         ),
    ) );
    
    $cmb->add_field( array(
    'name' => __( 'Content', 'ba-book-everything' ),
    'id'   => 'tmp_post_content',
    'type' => 'wysiwyg',
    'options' => array(
        'textarea_rows' => get_option('default_post_edit_rows', 7),
    ),
    ) );
            
        }
        
        return;
    }
     
///////////cmb2_save_field////////     
     /**
		 * Hooks after save field action.
		 *
		 * @since 2.2.0
		 *
		 * @param string            $field_id the current field id paramater.
		 * @param bool              $updated  Whether the metadata update action occurred.
		 * @param string            $action   Action performed. Could be "repeatable", "updated", or "removed".
		 * @param CMB2_Field object $field_obj    This field object
		 */ 
     public static function cmb2_save_field( $field_id, $updated, $action, $field_obj ) {

         if ( !$updated ){
             return;
         }

         $object_id = $field_obj->object_id;

         if ( is_admin() && $field_id === '_coupon_num_update' && BABE_Coupons::coupons_active() ){

             $coupon_num_update = get_post_meta($object_id, '_coupon_num_update', true);
             $coupon_num = get_post_meta($object_id, '_coupon_num', true);

             if ( $coupon_num_update !== $coupon_num ){
                 BABE_Coupons::remove_coupon_from_the_order($object_id, $coupon_num);
                 BABE_Order::recalculate_order_total_amount($object_id);

                 if ( !empty($coupon_num_update) ){
                     BABE_Coupons::apply_coupon_to_the_order($object_id, $coupon_num_update);
                     BABE_Order::recalculate_order_total_amount($object_id);
                 }
             }
         }

         if ( is_admin() ){
             return;
         }

         if ($field_id === 'tmp_post_title'){

             $title = get_post_meta($object_id, 'tmp_post_title', true);
             if ($title){
                 $post_args = array(
                     'ID'           => $object_id,
                     'post_title'   => $title,
                     'post_name' => sanitize_title($title),
                     'post_status' => 'publish',
                 );
                 wp_update_post( $post_args );
             } else {
                 $title = get_the_title($object_id);
                 update_post_meta($object_id, 'tmp_post_title', $title);
             }

             return;
         }
         if ($field_id === 'tmp_post_content'){

             $content = get_post_meta($object_id, 'tmp_post_content', true);
             $post_args = array(
                 'ID'           => $object_id,
                 'post_content'   => $content,
             );
             wp_update_post( $post_args );

             return;
         }
         if ($field_id === 'tmp_featured_image_id'){

             $image_id = get_post_meta($object_id, 'tmp_featured_image_id', true);
             if ($image_id){
                 set_post_thumbnail( $object_id, $image_id );
             } else {
                 delete_post_meta( $object_id, '_thumbnail_id' );
             }
         }
         
     }
     
////////cmb2_can_save//////////////////
    /**
		 * Filter to determine if metabox is allowed to save.
		 *
		 * @param bool   $can_save Whether the current metabox can save.
		 * @param object $cmb      The CMB2 instance
		 */
     public static function cmb2_can_save($can_save, $cmb){
        
        $object_id = $cmb->object_id();
        
        if ($can_save && $object_id && BABE_Users::current_user_can_edit_post($object_id)){
           return true;
        }
        
        return $can_save;
     }         
                   
///////////////////////////////////////
    /**
	 * Register booking_obj extra fields.
	 */
    public static function booking_obj_metabox() {

        $prefix = '';
      
      ///////////sidebar metabox/////////
      
      $cmb = new_cmb2_box( array(
        'id'            => 'booking_obj_metabox_side',
        'title'         => __( 'Slideshow Images', 'ba-book-everything' ),
        'object_types'  => array( BABE_Post_types::$booking_obj_post_type, ), // Post type
        'context'       => 'side',
        'priority'      => 'low',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
      ) );
      
      $group_images_id = $cmb->add_field( array(
                 'id'          => $prefix . 'images',
                 'type'        => 'group',
                // 'description' => __( 'Slideshow Images', 'ba-book-everything' ),
                 // 'repeatable'  => false, // use false if you want non-repeatable group
                 'options'     => array(
                   'group_title'   => __( 'Image', 'ba-book-everything' ).' {#}', // since version 1.1.4, {#} gets replaced by row number
                   'add_button'    => __( 'Add slideshow image', 'ba-book-everything' ),
                   'remove_button' => __( 'Remove image', 'ba-book-everything' ),
                   'sortable'      => true, // beta
                 ),
                ) );
    
    $cmb->add_group_field( $group_images_id, array(
                'name'       => __( 'Image file', 'ba-book-everything' ),
                'id'         => 'image',
                'type'       => 'file',
               // 'desc'       => __( 'image URL', 'ba-book-everything' ),
                'preview_size' => array( 70, 70 ),
                'options' => array(
                  'url' => false, // Hide the text input for the url
                ),
                ) );
                
    $cmb->add_group_field( $group_images_id, array(
                'name'       => __( 'Image description (optional)', 'ba-book-everything' ),
                'id'         => 'description',
                'type'       => 'textarea_small',
                ) );
      
      ////////////main metabox////////////////

      $cmb = new_cmb2_box( array(
        'id'            => 'booking_obj_metabox',
        'title'         => __( '&nbsp;', 'ba-book-everything' ),
        'object_types'  => array( BABE_Post_types::$booking_obj_post_type, ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        // 'closed'     => true, // Keep the metabox closed by default
      ) );
      
      $post_id = 0;
      
      if ( isset( $_REQUEST['post'] ) ) {
        $post_id = $cmb->object_id( absint( $_REQUEST['post'] ) );
       } elseif ( isset( $_REQUEST['post_ID'] ) ) {
        $post_id = $cmb->object_id( absint( $_REQUEST['post_ID'] ) );
       }
      
    do_action('cmb2_booking_obj_before_av_dates', $cmb, $prefix);  
      
    $cmb->add_field( array(
    'name' => __( 'Available from Date', 'ba-book-everything' ),
    'id'   => $prefix . 'start_date',
    'type' => 'text',
    'attributes'  => array(
         'required'    => 'required',
         ),
     'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
     'row_title' => __( 'Basic settings', 'ba-book-everything' ),
    ) );

    $cmb->add_field( array(
    'name' => __( 'Available to Date', 'ba-book-everything' ),
    'id'   => $prefix . 'end_date',
    'type' => 'text',
    'attributes'  => array(
         'required'    => 'required',
         ),
    ) );
    
    $cmb->add_field( array(
    'name'       => __( 'Cyclic availability: start after every N days', 'ba-book-everything' ),
    'desc'       => __( 'Blank or 0 to skip this option', 'ba-book-everything' ),
    'id'         => $prefix . 'cyclic_start_every',
    'type'       => 'text',
    'attributes' => array(
        'type' => 'number',
        'min' => '0',
        'pattern' => '[0-9]*',
        'class' => 'cmb2-text-small',
        ),
    ) );
    
    $cmb->add_field( array(
    'name'       => __( 'Cyclic availability: available N days after start', 'ba-book-everything' ),
    'desc'       => __( 'Used if previous field value > 1', 'ba-book-everything' ),
    'id'         => $prefix . 'cyclic_av',
    'type'       => 'text',
    'attributes' => array(
        'type' => 'number',
        'min' => '0',
        'pattern' => '[0-9]*',
        'class' => 'cmb2-text-small',
          ),
    ) );

        $group_included_dates = $cmb->add_field( array(
            'id'          => $prefix . 'included_dates',
            'type'        => 'group',
            // 'description' => __( 'Slideshow Images', 'ba-book-everything' ),
            // 'repeatable'  => false, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'   => __( 'Included Date {#} (if entered other will be excluded)', 'ba-book-everything' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'    => __( 'Include more dates to the schedule', 'ba-book-everything' ),
                'remove_button' => __( 'Remove date', 'ba-book-everything' ),
                'sortable'      => true, // beta
            ),
        ) );

        $cmb->add_group_field( $group_included_dates, array(
            'name' => __( 'Date to include', 'ba-book-everything' ),
            'id'   => $prefix . 'included_date',
            'type' => 'text',
            'classes' => array( 'av_dates' ),
        ) );
    
    $group_excluded_dates = $cmb->add_field( array(
                 'id'          => $prefix . 'excluded_dates',
                 'type'        => 'group',
                // 'description' => __( 'Slideshow Images', 'ba-book-everything' ),
                 // 'repeatable'  => false, // use false if you want non-repeatable group
                 'options'     => array(
                   'group_title'   => __( 'Excluded Date', 'ba-book-everything' ).' {#}', // since version 1.1.4, {#} gets replaced by row number
                   'add_button'    => __( 'Exclude more dates from schedule', 'ba-book-everything' ),
                   'remove_button' => __( 'Remove date', 'ba-book-everything' ),
                   'sortable'      => true, // beta
                 ),
                ) );
                
    $cmb->add_group_field( $group_excluded_dates, array(
                'name' => __( 'Date to exclude', 'ba-book-everything' ),
                'id'   => $prefix . 'excluded_date',
                'type' => 'text',
                'classes' => array( 'av_dates' ),
    ) );
    
    do_action('cmb2_booking_obj_after_av_dates', $cmb, $prefix);

        //minimum number of guests
        $cmb->add_field( array(
            'name'       => __( 'Minimum number of Guests', 'ba-book-everything' ),
            'desc'       => __( 'used in the guest selection dropdown for main age, default 0', 'ba-book-everything' ),
            'id'         => $prefix . 'min_guests',
            'type'       => 'text',
            'default' => '1',
            'attributes' => array(
                'type' => 'number',
                'min' => '0',
                'pattern' => '[0-9]*',
                'class' => 'cmb2-text-small',
            ),
        ) );

        //maximum number of guests
        $cmb->add_field( array(
            'name'       => __( 'Maximum number of Guests', 'ba-book-everything' ),
            // 'desc'       => __( 'field description (optional)', 'ba-book-everything' ),
            'id'         => $prefix . 'guests',
            'type'       => 'text',
            'attributes' => array(
                'type' => 'number',
                'min' => '1',
                'pattern' => '[0-9]*',
                'required'    => 'required',
                'class' => 'cmb2-text-small',
            ),
        ) );
        
        $cmb->add_field( array(
                  'name'           => __( 'Select category to setup other post fields', 'ba-book-everything' ),
                  'id'             => $prefix . BABE_Post_types::$categories_tax,
                  'taxonomy'       => BABE_Post_types::$categories_tax, //Enter Taxonomy Slug
                  'type'           => 'taxonomy_select',
                  'show_option_none' => false,
                  'text'           => array(
                  'no_terms_text' => __( 'Sorry, no terms could be found.', 'ba-book-everything' )
                  ),
                  'remove_default' => true
                ) );
                
        do_action('cmb2_booking_obj_after_select_category', $cmb, $prefix);
        
        $cmb->add_field( array(
         'name' => __( 'Fixed Deposit amount, ', 'ba-book-everything' ).BABE_Currency::get_currency_symbol(),
         'id'   => $prefix . 'deposit_fixed',
         'type' => 'text',
         'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
         'row_title' => __( 'Prices', 'ba-book-everything' ),
            'attributes' => array(
                'class' => 'cmb2-text-small',
            ),
         ) );
        
        $cmb->add_field( array(
         'name' => __( 'Prices', 'ba-book-everything' ),
         'id'   => $prefix . 'prices',
         'type' => 'price_details',
         ) );
         
         //discount for promotions
         $cmb->add_field( array(
         'name'       => __( 'Discount (optional)', 'ba-book-everything' ),
          'desc'       => __( 'applies to all prices', 'ba-book-everything' ),
          'id'         => $prefix . 'discount',
          'type'       => 'discount',
         ) );

        $fees_options = self::get_posts_options(BABE_Post_types::$fee_post_type, $cmb);

        if (!empty($fees_options)){
            $cmb->add_field( array(
                'name'       => __( 'Fees', 'ba-book-everything' ),
                'id'         => $prefix . 'fees',
                'type'       => 'multicheck',
                'options' => $fees_options,
                'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
                'row_title' => __( 'Fees section', 'ba-book-everything' ),
            ) );
        }

        /// Conditional magic starts here...
        
        /// get all categories
        
        $all_categories = BABE_Post_types::get_categories_arr();
        
        /// foreach category
        
        foreach ($all_categories as $category_id => $category_name){

            $category = get_term_by( 'id', $category_id, BABE_Post_types::$categories_tax );

            if ( empty($category) ){
                continue;
            }

            $category_meta = BABE_Post_types::get_term_meta($category_id);
            //Array ( [categories_week] => Array ( [0] => 1 [1] => 2 [2] => 3 [3] => 4 [4] => 5 [5] => 6 [6] => 7 ) [categories_booking_rule] => 0 [categories_gmap_active] => 1 [categories_reviews_active] => 0 [categories_add_taxes] => 0 [categories_taxonomies] => Array ( [0] => 8 ) [categories_step_by_step] => 0 [categories_address] => 0 )

            if ( empty($category_meta['categories_booking_rule']) ){
                continue;
            }

            $rule_id = $category_meta['categories_booking_rule'];
            $rules = BABE_Booking_Rules::get_rule($rule_id);

            if( empty($rules) ){
                continue;
            }

            do_action('cmb2_booking_obj_after_prices', $cmb, $prefix, $category);

            if ( $rules['basic_booking_period'] === 'night' ){

                $select_options = [];

                for($i=0; $i <= 23; $i++){
                    $i_text = $i < 10 ? '0'.$i : $i;
                    $i_text .= ':00';
                    $select_options[$i_text] = $i_text;
                }

                $cmb->add_field( array(
                    'name'       => __( 'Last available booking time for the current date, H:i', 'ba-book-everything' ),
                    'id'         => $prefix . 'last_av_booking_time_'.$category->slug,
                    'type'       => 'select',
                    'options' => $select_options,
                    'default' => '00:00',
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                    ),
                    'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
                    'row_title' => __( 'Booking rules section', 'ba-book-everything' ),
                ) );
            }

            /// create metaboxes by meta
            if (isset($category_meta['categories_address']) && $category_meta['categories_address']){

                if(apply_filters('cmb2_booking_obj_add_address', true, $cmb, $prefix, $category, $category_meta, $rules)){

                    $cmb->add_field( array(
                        'name'       => __( 'Address', 'ba-book-everything' ),
                        'id'         => $prefix . 'address_'.$category->slug,
                        'type'       => 'address',
                        'desc'       => __( 'Street, etc.', 'ba-book-everything' ),
                        'attributes' => array(
                            'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                            'data-conditional-value' => $category->slug,
                        ),
                        'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
                        'row_title' => __( 'Address section', 'ba-book-everything' ),
                    ) );
                }

                $add_meeting_places = $rules['basic_booking_period'] === 'recurrent_custom' && BABE_Settings::$settings['mpoints_active'];

                if ( apply_filters('cmb2_booking_obj_add_meeting_places', $add_meeting_places, $cmb, $prefix, $category, $category_meta, $rules) ){

                    if( apply_filters('cmb2_booking_obj_add_select_meeting_place', true, $cmb, $prefix, $category, $category_meta, $rules) ){
                        $cmb->add_field( array(
                            'name'       => __( 'Meeting place', 'ba-book-everything' ),
                            'id'         => $prefix . 'meeting_place_'.$category->slug,
                            'type'    => 'radio_inline',
                            'options' => array(
                                'address' => __( 'Main address', 'ba-book-everything' ),
                                'point' => __( 'Meeting point', 'ba-book-everything' ),
                                'custom' => __( 'Customer address', 'ba-book-everything' ),
                            ),
                            'default' => 'point',
                            'attributes' => array(
                                'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                                'data-conditional-value' => $category->slug,
                            ),
                        ) );
                    }

                    do_action('cmb2_booking_obj_before_mpoints_group', $cmb, $prefix, $category, $category_meta, $rules);

                    $group_meeting_id = $cmb->add_field( array(
                        'id'          => $prefix . 'meeting_points_'.$category->slug,
                        'type'        => 'group',
                        'description' => __( 'Add meeting point', 'ba-book-everything' ),
                        // 'repeatable'  => false, // use false if you want non-repeatable group
                        'options'     => array(
                            'group_title'   => __( 'Meeting point', 'ba-book-everything' ).' {#}', // since version 1.1.4, {#} gets replaced by row number
                            'add_button'    => __( 'Add meeting point', 'ba-book-everything' ),
                            'remove_button' => __( 'Remove meeting point', 'ba-book-everything' ),
                            'sortable'      => true, // beta
                        ),
                    ) );

                    do_action('cmb2_booking_obj_before_mpoints_select', $group_meeting_id, $cmb, $prefix, $category);

                    $cmb2_place_field_args = array(
                        'name'       => __( 'Place', 'ba-book-everything' ),
                        'id'         => 'place',
                        'type'       => 'mpoints_select',
                        //  'classes' => array('babe_cmb2_select_2_row'),
                        'desc'       => '',
                        'attributes' => array(
                            'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                            'data-conditional-value' => $category->slug,
                        ),
                    );

                    $cmb2_place_field_args = apply_filters('cmb2_booking_obj_place_field_args', $cmb2_place_field_args, $cmb, $prefix, $category);

                    $cmb->add_group_field( $group_meeting_id, $cmb2_place_field_args );

                    $cmb2_time_shift_args = array(
                        'name' => __( 'Difference with the main time', 'ba-book-everything' ),
                        'desc' => '',
                        'id'   => 'time_shift',
                        'type' => 'time_shift',
                        'attributes' => array(
                            'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                            'data-conditional-value' => $category->slug,
                        ),
                    );

                    $cmb2_time_shift_args = apply_filters('cmb2_booking_obj_time_shift_field_args', $cmb2_time_shift_args, $cmb, $prefix, $category);

                    //// add time_shift
                    $cmb->add_group_field( $group_meeting_id, $cmb2_time_shift_args );

                }  //// end if recurrent_custom
            }

            do_action('cmb2_booking_obj_after_address', $cmb, $prefix, $category);

            if ( !empty($category_meta['categories_step_by_step']) ){
                //////////////////////////////////////////////
                //////////// Attractions ////////////////////

                $step_title = isset($category_meta['categories_step_title']) && $category_meta['categories_step_title'] ? $category_meta['categories_step_title'] : __( 'Step', 'ba-book-everything' );

                $group_field_id = $cmb->add_field( array(
                    'id'          => $prefix . 'steps_'.$category->slug,
                    'type'        => 'group',
                    'description' => sprintf(__( 'Add %s description', 'ba-book-everything' ), $step_title),
                    // 'repeatable'  => false, // use false if you want non-repeatable group
                    'options'     => array(
                        'group_title'   => $step_title.' {#}', // since version 1.1.4, {#} gets replaced by row number
                        'add_button'    => sprintf(__( 'Add %s', 'ba-book-everything' ), $step_title),
                        'remove_button' => sprintf(__( 'Remove %s', 'ba-book-everything' ), $step_title),
                        'sortable'      => true, // beta
                    ),
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                    ),
                    'before_group' => array( __CLASS__, 'cmb2_before_row_header'),
                    'row_title' => $step_title.__( ' section', 'ba-book-everything' ),
                ) );

                $cmb->add_group_field( $group_field_id, array(
                    'name'       => __( 'Title', 'ba-book-everything' ),
                    'id'         => 'title',
                    'type'       => 'text_title',
                    'attributes' => array(
                        'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                        'data-conditional-value' => $category->slug,
                    ),
                ) );

                $cmb->add_group_field( $group_field_id, array(
                    'name'    => __( 'Description', 'ba-book-everything' ),
                    'id'      => 'attraction',
                    'type'    => 'wysiwyg',
                    'options' => array(
                        'wpautop' => true,
                        'textarea_rows' => get_option('default_post_edit_rows', 7)
                    ),
                    'before' => '<div data-conditional-id="'.$prefix . BABE_Post_types::$categories_tax.'" data-conditional-value="'.$category->slug.'" name="__attraction">',
                    'after' => '</div>'
                ) );

                //////////////////////////////////////////////
            }

            /////other fields title
            $cmb->add_field( array(
                'name'       => '',
                'id'         => $prefix . 'rowtitle_'.$category->slug,
                'type'       => 'title',
                'classes' => 'cmb2-row-hidden',
                'attributes' => array(
                    'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                    'data-conditional-value' => $category->slug,
                ),
                'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
                'row_title' => __( 'Taxonomies and Other fields', 'ba-book-everything' ),
            ) );

            do_action('cmb2_booking_obj_after_steps', $cmb, $prefix, $category);

            if ( !empty($category_meta['categories_taxonomies']) ){

                foreach($category_meta['categories_taxonomies'] as $taxonomy_id){

                    if ( !isset(BABE_Post_types::$taxonomies_list[$taxonomy_id]['slug']) ){
                        continue;
                    }

                    $taxonomy_slug = BABE_Post_types::$taxonomies_list[$taxonomy_id]['slug'];
                    $taxonomy_meta = BABE_Post_types::get_taxonomy_meta_by_slug($taxonomy_slug);

                    if ( empty($taxonomy_meta['select_mode']) ){
                        continue;
                    }

                    switch ($taxonomy_meta['select_mode']){
                        case 'multi_checkbox':
                            $select_type = 'tax_children_multicheck';
                            break;
                        case 'multi_select':
                            $select_type = 'taxonomy_multicheck';
                            break;
                        case 'single_radio':
                            $select_type = 'tax_radio';
                            break;
                        case 'single_select':
                            $select_type = 'taxonomy_select';
                            break;
                    }

                    $cmb->add_field( array(
                        'name'           => BABE_Post_types::$taxonomies_list[$taxonomy_id]['name'],
                        'id'             => $prefix . BABE_Post_types::$taxonomies_list[$taxonomy_id]['slug'].'_'.$category->slug,
                        'taxonomy'       => BABE_Post_types::$taxonomies_list[$taxonomy_id]['slug'], //Enter Taxonomy Slug
                        'type'           => $select_type,
                        'text'           => array(
                            'no_terms_text' => __( 'Sorry, no terms could be found.', 'ba-book-everything' )
                        ),
                        'remove_default' => 'true',
                        'attributes' => array(
                            'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                            'data-conditional-value' => $category->slug,
                        ),
                    ) );
                }
            }

            do_action('cmb2_booking_obj_after_taxonomies', $cmb, $prefix, $category);

            if ( !empty($category_meta['categories_services']) ){

                $services_options = self::get_posts_options(BABE_Post_types::$service_post_type, $cmb);

                if ( !empty($services_options) ){

                    $cmb->add_field( array(
                        'name'       => __( 'Service selection method', 'ba-book-everything' ),
                        'id'         => $prefix . 'service_selection_type_'.$category->slug,
                        'type'       => 'radio_inline',
                        'options'          => array(
                            'checkbox' => __( 'Checkbox', 'ba-book-everything' ),
                            'radio' => __( 'Radio', 'ba-book-everything' ),
                        ),
                        'default' => 'checkbox',
                        'attributes' => array(
                            'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                            'data-conditional-value' => $category->slug,
                        ),
                        'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
                        'row_title' => __( 'Services section', 'ba-book-everything' ),
                    ) );

                    $cmb->add_field( array(
                        'name'       => __( 'Services', 'ba-book-everything' ),
                        'id'         => $prefix . 'services_'.$category->slug,
                        'type'       => 'multicheck',
                        'options' => $services_options,
                        'attributes' => array(
                            'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                            'data-conditional-value' => $category->slug,
                        ),
                    ) );
                }
            }

            if ( !empty($category_meta['categories_faq']) ){

                $faq_options = self::get_posts_options(BABE_Post_types::$faq_post_type, $cmb);

                if( !empty($faq_options) ){
                    $cmb->add_field( array(
                        'name'       => __( 'FAQ', 'ba-book-everything' ),
                        'id'         => $prefix . 'faq_'.$category->slug,
                        'type'       => 'multicheck',
                        'options' => $faq_options,
                        'attributes' => array(
                            'data-conditional-id'    => $prefix . BABE_Post_types::$categories_tax,
                            'data-conditional-value' => $category->slug,
                        ),
                        'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
                        'row_title' => __( 'Questions & Answers section', 'ba-book-everything' ),
                    ) );
                }
            }

        } //// end foreach $all_categories

        //////////// Custom sections ////////////////////

        $custom_field_title = __( 'Custom section', 'ba-book-everything' );

        $custom_field_id = $cmb->add_field( array(
            'id'          => $prefix . 'custom_section',
            'type'        => 'group',
            'options'     => array(
                'group_title'   => $custom_field_title.' {#}', // since version 1.1.4, {#} gets replaced by row number
                'add_button'    => sprintf(__( 'Add %s', 'ba-book-everything' ), $custom_field_title),
                'remove_button' => sprintf(__( 'Remove %s', 'ba-book-everything' ), $custom_field_title),
                'sortable'      => true, // beta
            ),
            'before_group' => array( __CLASS__, 'cmb2_before_row_header'),
            'row_title' => $custom_field_title,
        ) );

        $cmb->add_group_field( $custom_field_id, array(
            'name'       => __( 'Title', 'ba-book-everything' ),
            'id'         => 'title',
            'type'       => 'text_title',
        ) );

        $cmb->add_group_field( $custom_field_id, array(
            'name'       => __( 'Font Awesome 5 class (far fa-eye)', 'ba-book-everything' ),
            'id'         => 'fa_class',
            'type'       => 'text_title',
        ) );

        $cmb->add_group_field( $custom_field_id, array(
            'name'    => __( 'Content', 'ba-book-everything' ),
            'id'      => 'content',
            'type'    => 'wysiwyg',
            'options' => array(
                'wpautop' => true,
                'textarea_rows' => get_option('default_post_edit_rows', 7)
            ),
        ) );

        //////////////////////////////////////////////

        $related_items_options = self::get_posts_options(
            BABE_Post_types::$booking_obj_post_type,
            $cmb,
            [],
            [ 'post__not_in' => [$post_id] ]
        );

	    if (!empty($related_items_options)) {

            $cmb->add_field( array(
                'name'       => __( 'Related items', 'ba-book-everything' ),
                'desc' => '',
                'id'         => $prefix . 'related_items',
                'type' => 'pw_multiselect',
                'options' => $related_items_options,
                'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
                'row_title' => __( 'Related items', 'ba-book-everything' ),
            ) );
        }

        $cmb->add_field( array(
            'name'       => __( 'Recalculate Rating', 'ba-book-everything' ),
            'desc' => '',
            'id'   => $prefix . 'recalculate_rating',
            'type' => 'recalculate_rating',
            'before_row' => array( __CLASS__, 'cmb2_before_row_header'),
            'row_title' => __( 'Rating', 'ba-book-everything' ),
        ) );
       
       do_action('cmb2_booking_obj_after_all', $cmb, $prefix);
    }

    /////////////
    /**
     * Related items callback
     *
     * @param array $field_args
     * @param object $field
     */
    public static function cmb2_related_items_cb( $field_args, $field ) {
		$id          = $field->args( 'id' );
		$label       = $field->args( 'name' );
		$name        = $field->args( '_name' );
		$options     = $field->args["options"];
		$description = $field->args( 'desc' );
		$valls       = empty($field_args["display_cb"][0]->value)? array(): $field_args["display_cb"][0]->value;
		?>
		<div class="cmb-row cmb-type-row-header">
			<div class="cmb2-before-row-header" name="__row_title_related_items"><?php echo __( 'Related items', 'ba-book-everything' ); ?></div>
		</div>
		<div class="cmb-row cmb-type-multicheck-inline cmb2-id-related-items cmb-inline" data-fieldtype="multicheck_inline">
			<div class="cmb-th">
				<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
				<p class="cmb2-metabox-description"><?php echo $description; ?></p>
			</div>
			<div class="cmb-td">

            <?php
                $output =''; $i =1;
                foreach ($options as $cat_name => $cat_list){
                $output .= '<button type="button" class="related_collapsible">' . $cat_name . '</button> 
                            <div class="content">
                            <span class="button-secondary related_all_non">Select / Deselect All</span>
                                <ul class="cmb2-checkbox-list no-select-all cmb2-list "> <br/>';

                    foreach ($cat_list as $cat_id => $cat){

	                    $checked = '';
                        foreach ($valls as $active_checkbox){
                            if ((int)$active_checkbox == $cat_id) {
	                            $checked = ' checked="" ';
	                            break;
                            }
                        }
                        //$checked = (array_search($cat_id, $valls)) ? ' checked="" ': '';
                        $output .= '<li><input type="checkbox" class="cmb2-option" name="related_items[]" id="related_items'.$i.'" value="'.$cat_id.'" '. $checked . ' > 
                                        <label for="related_items'.$i.'">'.$cat .'</label></li>';
                        $i++;
                    }
                    $output .= '</ul></div>';

                }
                echo $output;
            ?>
			</div>
		</div>

		<?php
	}


///////////////////////////////////////
    /**
	 * Register ages extra fields.
	 */
    public static function ages_metabox() {
        $prefix = 'ages_';

	$cmb_term = new_cmb2_box( array(
		'id'               => $prefix . 'edit',
		'title'            => __( 'Ages Metabox', 'ba-book-everything' ), // Doesn't output for term boxes
		'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
		'taxonomies'       => array( BABE_Post_types::$ages_tax ), // Tells CMB2 which taxonomies should have these fields
		// 'new_term_section' => true, // Will display in the "Add New Category" section
	) );
    
    //// get all ages
    $ages_arr = BABE_Post_types::get_ages_arr();
    $num = count($ages_arr) + 1;
    
    $cmb_term->add_field( array(
    'name'       => __( 'Order (1, 2, etc.), *required', 'ba-book-everything' ),
    'desc'       => __( 'With the smallest number will be the main age, others will be sorted by ASC', 'ba-book-everything' ),
    'id'         => 'menu_order',
    'type'       => 'text',
    'attributes' => array(
           'type' => 'number',
           'min' => '1',
           'pattern' => '[0-9]*',
          ),
    'default'  => $num,
    ) );
    
   }             
    
///////////////////////////////////////
    /**
	 * Register categories extra fields.
	 */
    public static function categories_metabox() {
        $prefix = 'categories_';

        $cmb_term = new_cmb2_box( array(
            'id'               => $prefix . 'edit',
            'title'            => __( 'Categories Metabox', 'ba-book-everything' ), // Doesn't output for term boxes
            'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
            'taxonomies'       => array( BABE_Post_types::$categories_tax ), // Tells CMB2 which taxonomies should have these fields
            // 'new_term_section' => true, // Will display in the "Add New Category" section
        ) );

        $cmb_term->add_field( array(
            'name'    => __( 'Available days', 'ba-book-everything' ),
            'desc'    => __( 'Setup available days template', 'ba-book-everything' ),
            'id'      => $prefix . 'week',
            'type'    => 'multicheck_inline',
            'select_all_button' => false,
            'default' => array('0', '1', '2', '3', '4', '5', '6', '7'),
            'options_cb' => 'BABE_Calendar_functions::cmb2_get_week_options',
        ) );

        $cmb_term->add_field( array(
            'name'    => __( 'Excluded dates', 'ba-book-everything' ),
            'desc'    => __( 'Disable availability of all booking items for these dates', 'ba-book-everything' ),
            'id'      => $prefix . 'disable_dates',
            'type'    => 'category_disable_dates',
        ) );

        $cmb_term->add_field( array(
            'name'       => __( 'Booking rule', 'ba-book-everything' ),
            // 'desc'       => __( 'field description (optional)', 'ba-book-everything' ),
            'id'         => $prefix . 'booking_rule',
            'type'       => 'select',
            'options_cb' => array( __CLASS__ , 'get_booking_rules_options'),
            'show_option_none' => false,
            'attributes'  => array(
                'required'    => 'required',
            ),
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Add checkout fields for all guests', 'ba-book-everything' ),
            'id'           => $prefix . 'other_guests',
            'desc'       => __( 'User will be prompted to enter names off all guest in order', 'ba-book-everything' ),
            'type'         => 'radio_inline',
            'options'          => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Make extra checkout fields mandatory', 'ba-book-everything' ),
            'id'           => $prefix . 'other_guests_mandatory',
            'desc'       => __( 'User must fill all extended guest data to finish order', 'ba-book-everything' ),
            'type'         => 'radio_inline',
            'options'          => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Remove guests from booking form', 'ba-book-everything' ),
            'id'           => $prefix . 'remove_guests',
            'type'         => 'radio_inline',
            'options'          => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Add taxes?', 'ba-book-everything' ),
            'id'           => $prefix . 'add_taxes',
            'type'         => 'radio_inline',
            'options'          => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        $cmb_term->add_field( array(
            'name'       => __( 'Taxes, %', 'ba-book-everything' ),
            // 'desc'       => __( 'field description (optional)', 'ba-book-everything' ),
            'id'         => $prefix . 'tax',
            'type'       => 'text',
        ) );

        $cmb_term->add_field( array(
            'name'       => __( 'Taxes title', 'ba-book-everything' ),
            // 'desc'       => __( 'field description (optional)', 'ba-book-everything' ),
            'id'         => $prefix . 'tax_title',
            'type'       => 'text',
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Add services?', 'ba-book-everything' ),
            'id'           => $prefix . 'services',
            'type'         => 'radio_inline',
            'options'          => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 1,
        ) );

        $cmb_term->add_field( array(
            'name'       => __( 'Services title', 'ba-book-everything' ),
            // 'desc'       => __( 'field description (optional)', 'ba-book-everything' ),
            'id'         => $prefix . 'services_title',
            'type'       => 'text',
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Add FAQ?', 'ba-book-everything' ),
            'id'           => $prefix . 'faq',
            'type'         => 'radio_inline',
            'options'          => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 1,
        ) );

        $cmb_term->add_field( array(
            'name'       => __( 'FAQ title', 'ba-book-everything' ),
            // 'desc'       => __( 'field description (optional)', 'ba-book-everything' ),
            'id'         => $prefix . 'faq_title',
            'type'       => 'text',
        ) );

        $cmb_term->add_field( array(
            'name'       => __( 'Include taxonomies', 'ba-book-everything' ),
            // 'desc'       => __( 'field description (optional)', 'ba-book-everything' ),
            'id'         => $prefix . 'taxonomies',
            'type'    => 'multicheck',
            'options_cb' => array( __CLASS__ , 'get_taxonomies_options'),
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Add step by step description?', 'ba-book-everything' ),
            'id'           => $prefix . 'step_by_step',
            'type'         => 'radio_inline',
            'options'          => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        $cmb_term->add_field( array(
            'name'       => __( 'Step title', 'ba-book-everything' ),
            // 'desc'       => __( 'field description (optional)', 'ba-book-everything' ),
            'id'         => $prefix . 'step_title',
            'type'       => 'text',
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Add address field?', 'ba-book-everything' ),
            'id'           => $prefix . 'address',
            'type'         => 'radio_inline',
            'options'          => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Add Google map to booking objects?', 'ba-book-everything' ),
            'id'           => $prefix . 'gmap_active',
            'type'         => 'radio_inline',
            'options'          => array(
                0 => __( 'No', 'ba-book-everything' ),
                1 => __( 'Yes', 'ba-book-everything' ),
            ),
            'default' => 0,
        ) );

        $cmb_term->add_field( array(
            'name' => __( 'Featured Image', 'ba-book-everything' ),
            //	'desc' => __( 'field description (optional)', 'ba-book-everything' ),
            'id'   => $prefix . 'image',
            'type' => 'file',
            'preview_size' => array( 100, 100 ),
        ) );

        $cmb_term->add_field( array(
            'name'         => __( 'Slideshow Images', 'ba-book-everything' ),
            'desc'         => __( 'Upload or add multiple images.', 'ba-book-everything' ),
            'id'           => $prefix . 'file_list',
            'type'         => 'file_list',
            'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
        ) );
    }

    private static function get_category_disable_dates_tbody_html( int $category_id ): string
    {
        $output = '';

        $exclude_dates_rows = BABE_Post_types::get_category_exclude_dates($category_id);

        if ( !empty($exclude_dates_rows) ){
            foreach( $exclude_dates_rows as $exclude_dates_row ){

                $date_from = new DateTime($exclude_dates_row['deactivate_date_from']);
                $date_to = new DateTime($exclude_dates_row['deactivate_date_to']);

                $output .= '
                <tr>
                <td class="category_exclude_date_from">'
                    .BABE_Calendar_functions::date_from_sql($date_from->format('Y-m-d'))
                    .'</td>
                <td class="category_exclude_date_to">'
                    .BABE_Calendar_functions::date_from_sql($date_to->format('Y-m-d'))
                    .'</td>
                <td class="category_exclude_dates_del" data-id="'.$exclude_dates_row['id'].'">
                <i class="fas fa-trash-alt" title="'.__('Delete these dates', 'ba-book-everything').'"></i>
                </td>
                </tr>';
            }
        }

        return $output;
    }

    public static function cmb2_category_disable_dates($field, $value, $object_id, $object_type, $field_type)
    {
        $output = self::get_category_disable_dates_tbody_html($object_id);

        $output = '<p class="cmb2-metabox-description">'. $field->args['desc'] .'</p>
<div class="category_exclude_dates_wrapper postbox">        
       <table id="category_exclude_dates_table">
          <thead>
            <tr class="category_exclude_dates_thead">
              <th>'.__('Date from', 'ba-book-everything').'</th>
              <th>'.__('Date to', 'ba-book-everything').'</th>
              <th>'.__('Action', 'ba-book-everything').'</th>
            </tr>
          </thead>
          <tbody id="category_exclude_dates_tbody">
            '.$output.'
          </tbody>
       </table>
       <div class="category_exclude_dates_new">
          <div class="category_exclude_dates_title"><strong>'.__('Exclude new dates:', 'ba-book-everything').'</strong></div>
          <span>
             <label for="start_date">'.__('from', 'ba-book-everything').'</label>
             <input type="text" id="start_date" class="category_exclude_date" value="" autocomplete="off">
          </span>
          <span>
             <label for="end_date">'.__('to', 'ba-book-everything').'</label>
             <input type="text" id="end_date" class="category_exclude_date" value="" autocomplete="off">
          </span>
          <button id="category_exclude_dates_add" data-category-id="'.$object_id.'" class="button-secondary">'.__('Add', 'ba-book-everything').'</button>
          <span id="save_category_exclude_dates_spinner"></span>
       </div>
      </div>';

        echo $output;
    }

    public static function ajax_add_category_exclude_dates(){

        $output = '';

        if (
            empty($_POST['category_id'])
            || empty($_POST['start_date'])
            || !BABE_Calendar_functions::isValidDate($_POST['start_date'], BABE_Settings::$settings['date_format'])
            || empty($_POST['end_date'])
            || !BABE_Calendar_functions::isValidDate($_POST['end_date'], BABE_Settings::$settings['date_format'])
            || !isset($_POST['nonce'])
            || !wp_verify_nonce( $_POST['nonce'], self::$nonce_title )
            || !current_user_can( 'manage_options' )
        ){
            echo $output;
            wp_die();
        }

        $category_term = get_term( (int)$_POST['category_id'] );

        if ( !$category_term instanceof WP_term ){
            echo $output;
            wp_die();
        }

        $date_from = new DateTime( BABE_Calendar_functions::date_to_sql($_POST['start_date']) . ' 00:00:00' );

        $date_to = new DateTime( BABE_Calendar_functions::date_to_sql($_POST['end_date']) . ' 23:59:59' );

        BABE_Post_types::add_category_exclude_dates( $category_term->term_id, $date_from, $date_to );

        $output = self::get_category_disable_dates_tbody_html($category_term->term_id);

        echo $output;
        wp_die();
    }

    public static function ajax_delete_category_exclude_dates(){

        $output = '';

        if (
            empty($_POST['category_id'])
            || empty($_POST['category_exclude_dates_id'])
            || !isset($_POST['nonce'])
            || !wp_verify_nonce( $_POST['nonce'], self::$nonce_title )
            || !current_user_can( 'manage_options' )
        ){
            echo $output;
            wp_die();
        }

        $category_term = get_term( (int)$_POST['category_id'] );

        if ( !$category_term instanceof WP_term ){
            echo $output;
            wp_die();
        }

        BABE_Post_types::delete_category_exclude_dates( (int)$_POST['category_exclude_dates_id'] );
        $output = self::get_category_disable_dates_tbody_html($category_term->term_id);

        echo $output;
        wp_die();
    }

    public static function ajax_recalculate_rating(){

        $output = '';

        if (
            empty($_POST['post_id'])
            || !wp_verify_nonce( $_POST['nonce'], self::$nonce_title )
            || !BABE_Post_types::is_post_booking_obj($_POST['post_id'])
            || !BABE_Users::current_user_can_edit_post($_POST['post_id'])
        ){
            echo $output;
            wp_die();
        }

        $post_id = (int)$_POST['post_id'];

        $comments = get_comments([
            'status' => 1,
            'post_id' => $post_id,
        ]);

        BABE_Rating::recalculate_post_rating( $post_id, $comments );

        echo __('Success!', 'ba-book-everything');
        wp_die();
    }

///////////////////////////////////////
    /**
	 * Register taxonomies_list extra fields.
	 */
    public static function taxonomies_list_metabox() {
        $prefix = '';
        
        $cmb_term = new_cmb2_box( array(
		'id'               => 'taxonomies_list_edit',
		'title'            => __( 'Taxonomies Metabox', 'ba-book-everything' ), // Doesn't output for term boxes
		'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
		'taxonomies'       => array( BABE_Post_types::$taxonomies_list_tax ), // Tells CMB2 which taxonomies should have these fields
		// 'new_term_section' => true, // Will display in the "Add New Category" section
	    ) );
        
        $cmb_term->add_field( array(
		'name'         => __( 'Google map: add latitude/longitude fields to terms?', 'ba-book-everything' ),
		'id'           => $prefix . 'gmap_active',
		'type'         => 'radio_inline',
		'options'          => array(
		  0 => __( 'No', 'ba-book-everything' ),
		  1 => __( 'Yes', 'ba-book-everything' ),
        ),
        'default' => 0,
        ) );
        
        $cmb_term->add_field( array(
		'name'         => __( 'Terms selection mode', 'ba-book-everything' ),
		'id'           => $prefix . 'select_mode',
		'type'         => 'radio',
		'options'          => array(
		  'multi_checkbox' => __( 'Multi checkbox', 'ba-book-everything' ),
		  'multi_select' => __( 'Multi select', 'ba-book-everything' ),
          'single_radio' => __( 'Single radio', 'ba-book-everything' ),
          'single_select' => __( 'Single select', 'ba-book-everything' ),
        ),
        'default' => 'multi_checkbox',
        ) );
        
        $cmb_term->add_field( array(
		'name'         => __( 'Front-end style', 'ba-book-everything' ),
		'id'           => $prefix . 'frontend_style',
		'type'         => 'radio',
		'options'          => array(
		  'buttons' => __( 'Buttons', 'ba-book-everything' ),
		  'text' => __( 'Comma-separated text', 'ba-book-everything' ),
          'col_3' => __( '3-column list', 'ba-book-everything' ),
          'col_2' => __( '2-column list', 'ba-book-everything' ),
          'col_1' => __( '1-column list', 'ba-book-everything' ),
        ),
        'default' => 'col_3',
        ) );
        
        $cmb_term->add_field( array(
        'name'       => __( 'Front-end custom class', 'ba-book-everything' ),
        'id'         => $prefix . 'frontend_class',
        'desc' => __( 'Optional', 'ba-book-everything' ),
        'type'       => 'text',
        ) );
    
    }        
    
///////////////////////////////////////
    /**
	 * Register custom taxonomies extra fields.
	 */
    public static function taxonomies_metabox() {
        global $pagenow;
        
        $prefix = '';
        
        $taxonomies_arr = array();
        foreach(BABE_Post_types::$taxonomies_list as $taxonomy_id => $taxonomy){
           $taxonomies_arr[] = $taxonomy['slug'];
        }
        
        if (!empty($taxonomies_arr)){
        
        $cmb_term = new_cmb2_box( array(
		'id'               => 'custom_taxonomies_' . 'edit',
		'title'            => __( 'Taxonomies Metabox', 'ba-book-everything' ), // Doesn't output for term boxes
		'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
		'taxonomies'       => $taxonomies_arr, // Tells CMB2 which taxonomies should have these fields
		// 'new_term_section' => true, // Will display in the "Add New Category" section
	    ) );
        
        if(($pagenow === 'edit-tags.php' || $pagenow === 'term.php') && isset($_GET['taxonomy']) && isset($_GET['post_type']) && $_GET['post_type'] === BABE_Post_types::$booking_obj_post_type && !BABE_Post_types::is_core_taxonomy($_GET['taxonomy'])){
            
            $taxonomy_meta = BABE_Post_types::get_taxonomy_meta_by_slug($_GET['taxonomy']);
            if ( !empty($taxonomy_meta['gmap_active']) ){
              $cmb_term->add_field( array(
                'name'       => __( 'Location Latitude', 'ba-book-everything' ),
                'id'         => $prefix . 'latitude',
                'type'       => 'text',
              ) );
              
              $cmb_term->add_field( array(
              'name'       => __( 'Location Longitude', 'ba-book-everything' ),
              'id'         => $prefix . 'longitude',
              'type'       => 'text',
              ) );
            }
        }

            $cmb_term->add_field( array(
                'name'         => __( 'Make terms of all levels of the hierarchy available for selection', 'ba-book-everything' ),
                'id'           => $prefix . 'all_level_terms_selectable',
                'type'         => 'radio_inline',
                'options'          => array(
                    0 => __( 'No', 'ba-book-everything' ),
                    1 => __( 'Yes', 'ba-book-everything' ),
                ),
                'default' => 0,
            ) );
        
        $cmb_term->add_field( array(
		'name' => __( 'Icon image', 'ba-book-everything' ),
	    //'desc' => __( 'field description (optional)', 'ba-book-everything' ),
		'id'   => $prefix . 'image',
		'type' => 'file',
        'preview_size' => array( 100, 100 ),
        ) );
        
        $cmb_term->add_field( array(
        'name'       => __( 'Icon image width, px', 'ba-book-everything' ),
        'desc'       => __( '0 to auto width', 'ba-book-everything' ),
        'id'         => $prefix . 'image_width',
        'type'       => 'text',
        'default' => '50',
        ) );
        
        $cmb_term->add_field( array(
        'name'       => __( 'Icon image height, px', 'ba-book-everything' ),
        'desc'       => __( '0 to auto height', 'ba-book-everything' ),
        'id'         => $prefix . 'image_height',
        'type'       => 'text',
        'default' => '50',
        ) );
        
        }    
    
    }
        
//////////////////////////////
    /**
	 * Get booking rules option list.
     * @return array
	 */
    public static function get_booking_rules_options() {
        $options = array();
        $booking_rules = BABE_Booking_Rules::get_all_rules();
        foreach($booking_rules as $rule_id => $rule){
            $options[$rule_id] = $rule['rule_title']; 
        }
        return $options;
    }

//////////////////////////////
    /**
	 * Get posts list.
     * 
     * @param string $post_type
     * @param object $cmb
     * @param array $ids
     * @param array $more_args
     * @return array
	 */
    public static function get_posts_options($post_type, $cmb, $ids = [], $more_args = []) {

        $args = array(
            'post_type'   => $post_type,
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'suppress_filters' => false,
        );
        if (!empty($ids)){
            $args['post__in'] = $ids;
        }

        $args = array_merge($args, $more_args);

        $args = apply_filters('babe_cmb2_get_posts_options_args', $args, $post_type, $cmb);

        // create hash of the query args
        $args_hash = hash('sha256', json_encode($args, 512));
        if ( isset( self::$posts_options[$args_hash] ) ){
            // return from cache
            return self::$posts_options[$args_hash];
        }

        $posts = get_posts( $args );
        $post_options = array();
        if ( $posts ) {
            foreach ( $posts as $post ) {

                if(apply_filters('babe_cmb2_get_posts_options_add_post', true, $post, $post_type, $cmb)){
                    $post_options[ $post->ID ] = $post->post_title;
                }
            }
        }

        self::$posts_options[$args_hash] = $post_options;

        return $post_options;
    }

///////////////////////////////////
    /**
	 * Get Taxonomies option list.
     * @return array
	 */
    public static function get_taxonomies_options(){
        $post_options = array();
        foreach(BABE_Post_types::$taxonomies_list as $taxonomy_id => $taxonomy){
           $post_options[$taxonomy_id] = $taxonomy['name'];
        }
       return $post_options;
    }
    
///////////////////////////////
    /**
	 * Update availability calendar.
     * Fires after all fields have been saved.
     * 
	 *
	 * @param int    $post_id   The ID of the current object
	 * @param string $updated     Array of field ids that were updated.
	 *                            Will only include field ids that had values change.
	 * @param object  $cmb         This CMB2 object
	 */ 
    public static function update_booking_obj_post( $post_id, $updated, $cmb ) {
        
      $post = get_post($post_id);

      if ( BABE_Post_types::$booking_obj_post_type !== $post->post_type || $post->post_status === 'auto-draft' ) {
        return;
      }

        $start_date = get_post_meta( $post_id, 'start_date', true);
        $start_date_before = get_post_meta( $post_id, '_start_date_before', true);
        $end_date = get_post_meta( $post_id, 'end_date', true);
        $end_date_before = get_post_meta( $post_id, '_end_date_before', true);
        $cyclic_start_every = get_post_meta( $post_id, 'cyclic_start_every', true);
        $cyclic_start_every_before = get_post_meta( $post_id, '_cyclic_start_every_before', true);
        $cyclic_av = get_post_meta( $post_id, 'cyclic_av', true);
        $cyclic_av_before = get_post_meta( $post_id, '_cyclic_av_before', true);

        $included_dates = (array)get_post_meta( $post_id, 'included_dates', true );
        $included_dates_before = (array)get_post_meta( $post_id, '_included_dates_before', true );

        $excluded_dates = (array)get_post_meta( $post_id, 'excluded_dates', true );
        $excluded_dates_before = (array)get_post_meta( $post_id, '_excluded_dates_before', true );

        $guests = get_post_meta( $post_id, 'guests', true );
        $guests_before = get_post_meta( $post_id, '_guests_before', true );

        $items_number = '';
        $items_number_before = '';

        $term = BABE_Post_types::get_post_category($post_id);
        if ( !empty($term) ){
            $slug = '_'.$term->slug;
            $items_number = get_post_meta( $post_id, 'items_number' . $slug, true);
            $items_number_before = get_post_meta( $post_id, '_items_number_before' . $slug, true);
        }

        if (
            $start_date !== $start_date_before
            || $end_date !== $end_date_before
            || $guests !== $guests_before
            || $items_number !== $items_number_before
            || $cyclic_start_every !== $cyclic_start_every_before
            || $cyclic_av !== $cyclic_av_before
            || json_encode($included_dates) !== json_encode($included_dates_before)
            || json_encode($excluded_dates) !== json_encode($excluded_dates_before)
        ){
            update_post_meta($post_id, '_guests_before', $guests);
            update_post_meta($post_id, '_start_date_before', $start_date);
            update_post_meta($post_id, '_end_date_before', $end_date);
            update_post_meta($post_id, '_cyclic_start_every_before', $cyclic_start_every);
            update_post_meta($post_id, '_cyclic_av_before', $cyclic_av);
            update_post_meta($post_id, '_included_dates_before', $included_dates);
            update_post_meta($post_id, '_excluded_dates_before', $excluded_dates);
            update_post_meta($post_id, '_items_number_before'. $slug, $items_number);

            $rules = BABE_Booking_Rules::get_rule_by_obj_id($post_id);

            $schedule = array();

            if( isset($rules['rules']['basic_booking_period']) ) {

                if ( $rules['rules']['basic_booking_period'] === 'recurrent_custom' ){

                    $schedule = get_post_meta( $post_id, 'schedule', true);
                    $schedule = empty($schedule) ? array() : $schedule;

                } elseif( $rules['rules']['basic_booking_period'] === 'single_custom'){

                    $start_time = get_post_meta( $post_id, 'start_time_'.$rules['category_slug'], true);
                    $start_date_obj = new DateTime( BABE_Calendar_functions::date_to_sql($start_date).' '.$start_time );
                    $day_num = BABE_Calendar_functions::get_week_day_num($start_date_obj);
                    $schedule[$day_num] = array($start_date_obj->format('H:i'));
                }
            }

            BABE_Calendar_functions::update_av_cal($post_id, $start_date, $end_date, $schedule, $cyclic_start_every, $cyclic_av);

        } else {
            BABE_Calendar_functions::update_av_cal_excluded_dates($post_id);
        }

        $current_lang = BABE_Functions::get_current_language();
        $current_lang = apply_filters( 'wpml_current_language', $current_lang );
        $all_languages = BABE_Functions::get_all_languages();
        unset($all_languages[$current_lang]);

        if ( empty($all_languages) ){
            return;
        }

        $processed_post_ids = [ $post_id => $post_id ];

        foreach ($all_languages as $lang_code => $lang_arr ){

            $booking_obj_id = (int)apply_filters( 'wpml_object_id', $post_id, BABE_Post_types::$booking_obj_post_type, false, $lang_code );
            if ( isset($processed_post_ids[$booking_obj_id]) ){
                continue;
            }
            BABE_Calendar_functions::sync_two_calendars($post_id, $booking_obj_id);
            update_post_meta($booking_obj_id, 'start_date', $start_date);
            update_post_meta($booking_obj_id, 'end_date', $end_date);
            update_post_meta($booking_obj_id, 'cyclic_start_every', $cyclic_start_every);
            update_post_meta($booking_obj_id, 'cyclic_av', $cyclic_av);
            update_post_meta($post_id, 'included_dates', $included_dates);
            update_post_meta($post_id, 'excluded_dates', $excluded_dates);
            $term = BABE_Post_types::get_post_category($post_id);
            if ( !empty($term) ){
                $slug = '_'.$term->slug;
                update_post_meta($post_id, 'items_number'. $slug, $items_number);
            }
            $processed_post_ids[$booking_obj_id] = $booking_obj_id;
        }
     }

    /**
     * Post duplicator plugin hook: clone other things of the booking obj post
     * @param int $original_id
     * @param int $duplicate_id
     * @param array $settings
     */
    public static function mtphr_post_duplicator_created($original_id, $duplicate_id, $settings ){

        $original_post = get_post($original_id);

        if ( BABE_Post_types::$booking_obj_post_type !== $original_post->post_type ) {
            return;
        }

        $start_date = get_post_meta( $original_id, 'start_date', true);
        $end_date = get_post_meta( $original_id, 'end_date', true);
        $cyclic_start_every = get_post_meta( $original_id, 'cyclic_start_every', true);
        $cyclic_av = get_post_meta( $original_id, 'cyclic_av', true);
        $schedule = get_post_meta( $original_id, 'schedule', true);

        BABE_Calendar_functions::update_av_cal($duplicate_id, $start_date, $end_date, $schedule, $cyclic_start_every, $cyclic_av);

        $original_rates = BABE_Prices::get_rates($original_id);
        BABE_Prices::delete_rates_by_booking_obj_id($duplicate_id);
        BABE_Prices::add_booking_obj_rates($duplicate_id, $original_rates);
        BABE_Rating::delete_post_rating($duplicate_id);
    }
     
///////////////////////////////
    /**
	 * Update tmp post data.
     * @param int $post_id
     * @param object $post
     * @param boolean $update
	 */
    public static function update_tmp_post_data( $post_id, $post, $update ) {

      if (
              !is_admin()
              || !in_array(
                      $post->post_type,
                      [
                          BABE_Post_types::$booking_obj_post_type,
                          BABE_Post_types::$service_post_type,
                          BABE_Post_types::$faq_post_type,
                          BABE_Post_types::$fee_post_type,
                      ]
              )
      ) {
        return;
      }
      
            update_post_meta($post_id, 'tmp_post_title', $post->post_title);
            update_post_meta($post_id, 'tmp_post_content', $post->post_content);
            
          if ( BABE_Post_types::$booking_obj_post_type == $post->post_type ) {  
            $featured_image_id = get_post_thumbnail_id( $post_id );
            $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'full');
            if ($featured_image_id && $featured_image_url){
              update_post_meta($post_id, 'tmp_featured_image_id', $featured_image_id);
              update_post_meta($post_id, 'tmp_featured_image', $featured_image_url);
            } else {
                delete_post_meta( $post_id, 'tmp_featured_image_id' );
                delete_post_meta( $post_id, 'tmp_featured_image' );
            }
           }
     }

     ////////////////////////////////////////////
    /**
     * Fires once a post, its terms and meta data has been saved.
     *
     * @since 5.6.0
     *
     * @param int          $post_id     Post ID.
     * @param WP_Post      $post        Post object.
     * @param bool         $update      Whether this is an existing post being updated.
     * @param null|WP_Post $post_before Null for new posts, the WP_Post object prior
     *                                  to the update for updated posts.
     * @return void
     */
    public static function wpml_clone_post_meta_fields( $post_id, $post, $update, $post_before ) {

        if (
                $post_before !== null
                || $update
                || empty($_GET['trid'])
                || !in_array($post->post_type, [
                    BABE_Post_types::$booking_obj_post_type,
                    BABE_Post_types::$faq_post_type,
                    BABE_Post_types::$service_post_type,
                    BABE_Post_types::$mpoints_post_type,
                    BABE_Post_types::$fee_post_type,
                ], false)
                || !class_exists('SitePress')
                || !function_exists('wpml_get_term_translation_util')
        ){
            return;
        }

        $master_post_id = (int)SitePress::get_original_element_id_by_trid( (int)$_GET['trid'] );

        if ( !$master_post_id ){
            return;
        }

        global $wpdb;

        /*
        $master_post = get_post($master_post_id);
        if ( !empty($master_post->post_content) ){
            $post_args = array(
                'ID'           => $post_id,
                'post_title' => __( 'The copy of: ', 'ba-book-everything' ).$master_post->post_title,
                'post_content'   => $master_post->post_content,
            );
            wp_update_post( $post_args );
        }
        */

        $lang = apply_filters( 'wpml_current_language', null );
        $default_lang = apply_filters('wpml_default_language', null );

        // clone terms
        $taxonomies = get_post_taxonomies( $master_post_id );

        foreach ( $taxonomies as $tax ) {
            $terms_on_original = wp_get_object_terms( $master_post_id, $tax );
            /** @var int[] $translated_terms translated term_ids */
            $translated_terms = BABE_Functions::wpml_get_translated_term_ids( $terms_on_original, $lang, $tax, true );
            wp_set_object_terms( $post_id, $translated_terms, $tax );
        }

        // clone all post meta fields
        $sql = "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d";

        $meta_fields = $wpdb->get_results( $wpdb->prepare( $sql, $master_post_id ), ARRAY_A );

        if ( empty($meta_fields) ){
            return;
        }

        $wpdb->query( "DELETE " . $wpdb->prepare( "FROM {$wpdb->postmeta} WHERE post_id=%d", $post_id ) );

        $values = [];
        foreach ( $meta_fields as $post_meta ) {
            $values[] = $wpdb->prepare(
                '( %d, %s, %s )',
                $post_id,
                $post_meta['meta_key'],
                $post_meta['meta_value']
            );
        }

        $values = implode( ', ', $values );
        $wpdb->query(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES {$values}"
        );

        clean_post_cache( $post );

        // clone calendar
        BABE_Calendar_functions::sync_two_calendars($master_post_id, $post_id);

        // clone rates
        // get age ids master => current
        $ages_master_to_translated = [];
        $ages_translated = get_terms( array(
            'taxonomy' => BABE_Post_types::$ages_tax,
            'hide_empty' => false,
            'order' => 'ASC',
            'orderby' => 'meta_value_num',
            'meta_key' => 'menu_order',
        ) );

        if ( !empty($ages_translated) ){
            foreach ( $ages_translated as $age_term ){
                $original_term_id = apply_filters( 'wpml_object_id', (int)$age_term->term_id, BABE_Post_types::$ages_tax, true, $default_lang );
                $ages_master_to_translated[$original_term_id] = (int)$age_term->term_id;
            }
        }

        $new_rates = [];
        $master_rates = BABE_Prices::get_rates($master_post_id);
        foreach ($master_rates as $rate){
            // update age id
            $new_rate = $rate;
            $new_rate['booking_obj_id'] = $post_id;
            $new_rate['price_general'] = [];
            if ( !empty($rate['price_general']) ){
                foreach( $rate['price_general'] as $age_id => $age_price ){
                    $new_age_id = isset($ages_master_to_translated[$age_id]) ? $ages_master_to_translated[$age_id] : $age_id;
                    $new_rate['price_general'][$new_age_id] = $age_price;
                }
            }
            if ( !empty($rate['prices_conditional']) ){
                foreach( $rate['prices_conditional'] as $ind => $price_conditional ){

                    $new_rate['prices_conditional'][$ind]['conditional_price'] = [];

                    foreach( $price_conditional['conditional_price'] as $age_id => $age_price ){
                        $new_age_id = isset($ages_master_to_translated[$age_id]) ? $ages_master_to_translated[$age_id] : $age_id;
                        $new_rate['prices_conditional'][$ind]['conditional_price'][$new_age_id] = $age_price;
                    }
                }
            }
            $new_rates[] = $new_rate;
        }
        // save rates
        BABE_Prices::delete_rates_by_booking_obj_id($post_id);
        BABE_Prices::add_booking_obj_rates($post_id, $new_rates);

    }

//////////////////////////////    

}

BABE_CMB2_admin::init();
