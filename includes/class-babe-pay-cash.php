<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

BABE_Pay_cash::init();

/**
 * BABE_Pay_cash Class.
 * Get general settings
 * @class 		BABE_Pay_cash
 * @version		1.6.21
 * @author 		Booking Algorithms
 */

class BABE_Pay_cash {
    
    // payment method name
    private static $payment_method = 'cash';

    private static $tab_title = '';
    private static $description = '';
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {

        add_action( 'babe_settings_payment_method_'.self::$payment_method, array( __CLASS__, 'add_settings' ), 10, 3);

        if (class_exists('BABE_Settings')){
            add_filter( 'babe_sanitize_'.BABE_Settings::$option_name, array( __CLASS__, 'sanitize_settings' ), 10, 2);
        }

        add_action( 'init', array( __CLASS__, 'init_settings'), 20 );
        
        add_filter('babe_checkout_payment_title_'.self::$payment_method, array( __CLASS__, 'payment_method_title'), 10, 3);
        
        add_filter('babe_checkout_payment_fields_'.self::$payment_method, array( __CLASS__, 'payment_method_fields_html'), 10, 3);
        
        add_action( 'babe_payment_methods_init', array( __CLASS__, 'init_payment_method'));
        
        add_action( 'babe_order_to_pay_by_'.self::$payment_method, array( __CLASS__, 'order_to_pay'), 10, 4);
	}

    public static function init_settings() {

        if ( !class_exists('BABE_Settings') ){
            return;
        }

        self::$description = BABE_Settings::$settings[self::$payment_method . '_description'] ?? __( 'Book now, pay later!', 'ba-book-everything' );

        BABE_Settings::$settings[self::$payment_method.'_description'] = self::$description;

        self::$tab_title = BABE_Settings::$settings[self::$payment_method . '_tab_title'] ?? __('Pay later', 'ba-book-everything');

        BABE_Settings::$settings[self::$payment_method.'_tab_title'] = self::$tab_title;
    }

    /**
     * Add settings
     *
     * @param string $section_id
     * @param string $option_menu_slug
     * @param string $option_name
     */
    public static function add_settings($section_id, $option_menu_slug, $option_name) {

        add_settings_field(
            self::$payment_method.'_tab_title', // ID
            __('Payment tab title', 'ba-book-everything'), // Title
            array( 'BABE_Settings_admin', 'text_field_callback' ), // Callback
            $option_menu_slug, // Page
            $section_id,  // Section
            array('option' => self::$payment_method.'_tab_title', 'settings_name' => $option_name) // Args array
        );

        add_settings_field(
            self::$payment_method.'_description', // ID
            __('Payment description', 'ba-book-everything'), // Title
            array( 'BABE_Settings_admin', 'textarea_callback' ), // Callback
            $option_menu_slug, // Page
            $section_id,  // Section
            array('option' => self::$payment_method.'_description', 'settings_name' => $option_name) // Args array
        );
    }

    /**
     * Sanitize settings
     *
     * @param array $new_input
     * @param array $input
     * @return array
     */
    public static function sanitize_settings($new_input, $input) {

        $new_input[self::$payment_method.'_tab_title'] = isset($input[self::$payment_method.'_tab_title']) ? sanitize_text_field( $input[self::$payment_method.'_tab_title'] ) : '';

        $new_input[self::$payment_method.'_description'] = isset($input[self::$payment_method.'_description']) ? sanitize_textarea_field( $input[self::$payment_method.'_description'] ) : '';

        return $new_input;
    }

////////////////////////
     /**
	 * Init payment method
     * @param array $payment_methods
     * @return void
	 */
     public static function init_payment_method($payment_methods){
        
        if (!isset($payment_methods[self::$payment_method])){
            BABE_Payments::add_payment_method(self::$payment_method, __('Pay later', 'ba-book-everything'));
        }
     }

////////////////////////
     /**
	 * Output payment method title for checkout form
     * @param string $method_title
     * @param array $args
     * @param string $input_fields_name
     * @return string
	 */
     public static function payment_method_title($method_title, $args, $input_fields_name){
        
        return self::$tab_title;
     } 
         
////////////////////////
     /**
	 * Output payment method fields html for checkout form
     * @param string $fields
     * @param array $args
     * @param string $input_fields_name
     * @return string
	 */
     public static function payment_method_fields_html($fields, $args, $input_fields_name){
        return self::$description;
     }
     
////////////////////////
     /**
	 * Init payment method
     * @param int $order_id
     * @param array $args
     * @param string $current_url
     * @param string $success_url
     * @return void
	 */
     public static function order_to_pay($order_id, $args, $current_url, $success_url){
        
        BABE_Order::update_order_status($order_id, 'payment_deferred');
        
        do_action('babe_order_completed', $order_id);
                  
        wp_safe_redirect($success_url);
     }                
        
////////////////////    
}