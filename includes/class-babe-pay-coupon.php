<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

BABE_Pay_coupon::init();

/**
 * BABE_Pay_coupon Class.
 * Get general settings
 * @class 		BABE_Pay_cash
 * @version		1.5.25
 * @author 		Booking Algorithms
 */
class BABE_Pay_coupon {
    
    // payment method name
    private static $payment_method = 'coupon';
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {
        
        add_filter('babe_checkout_payment_title_'.self::$payment_method, array( __CLASS__, 'payment_method_title'), 10, 3);
        
        add_filter('babe_checkout_payment_fields_'.self::$payment_method, array( __CLASS__, 'payment_method_fields_html'), 10, 3);
        
        add_action( 'babe_payment_methods_init', array( __CLASS__, 'init_payment_method'));
        
        add_action( 'babe_order_to_pay_by_'.self::$payment_method, array( __CLASS__, 'order_to_pay'), 10, 4);
	}

////////////////////////
     /**
	 * Init payment method
     * @param array $payment_methods
     * @return void
	 */
     public static function init_payment_method($payment_methods){
        
        if (!isset($payment_methods[self::$payment_method])){
            BABE_Payments::add_payment_method(self::$payment_method, __('Pay by Coupon', 'ba-book-everything'));
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
        
        return $method_title;
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
        
        $fields .= __( 'This order is paid by coupon! You can complete the order', 'ba-book-everything' );
        
        return $fields;
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
        
        BABE_Order::update_order_status($order_id, 'payment_received');
        
        do_action('babe_order_completed', $order_id);
                  
        wp_safe_redirect($success_url);
     }                
        
////////////////////    
}