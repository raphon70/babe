<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

BABE_Coupons::init();
/**
 * BABE_Coupons Class.
 * 
 * @class 		BABE_Coupons
 * @version		1.5.7
 * @author 		Booking Algorithms
 */

class BABE_Coupons {
    
    static $coupon_statuses = array();
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {
        
        self::$coupon_statuses = array(
          'active' => __( 'Active', 'ba-book-everything' ),
          'pending' => __( 'Pending', 'ba-book-everything' ),
          'used' => __( 'Used', 'ba-book-everything' ),
          'expired' => __( 'Expired', 'ba-book-everything' ),
        );
        
        add_filter('wp_insert_post_data', array( __CLASS__, 'change_coupon_title' ), 99, 2);
        add_action( 'delete_post', array( __CLASS__, 'delete_order_post'));
	}    
    
////////////////////////
     /**
	 * Restore coupon status to active on delete uncompleted order
     * 
     * @param int $order_id
     * @return void
	 */
     public static function delete_order_post($order_id){
        
        $post_type = get_post_type( $order_id );
        if( $post_type == BABE_Post_types::$order_post_type ) {
             $coupon = self::get_coupon_by_order_id($order_id);
             if ($coupon){
                self::reduce_coupon_number_of_uses($coupon->ID);
                delete_post_meta( $coupon->ID, '_order_id' );
             }
        }
    }

////////////////////////
    /**
     * Apply coupon to the order
     *
     * @param int $order_id
     * @param string $coupon_num
     * @return boolean
     */
    public static function apply_coupon_to_the_order($order_id, $coupon_num){

        $coupon_before = get_post_meta($order_id, '_coupon_num', true);

        /// get coupon id
        $coupon = get_page_by_title($coupon_num, 'OBJECT', BABE_Post_types::$coupon_post_type);

        if (
            !$coupon
            || $coupon_before
            || !self::coupon_is_active($coupon->ID)
            || !self::can_coupon_be_applied_to_order( $coupon->ID, $order_id )
        ){
            return false;
        }

        /// get coupon amount
        $coupon_amount_arr = self::get_coupon_amount($coupon->ID);
        $coupon_amount = $coupon_amount_arr["value"];
        $order_amount = BABE_Order::get_order_total_amount($order_id);

        if ($coupon_amount_arr["type"] === 'percent') {
            $coupon_amount = $order_amount *  ( $coupon_amount / 100 );
        } else {
            // convert amount to the current currency
            $coupon_amount = BABE_Prices::localize_price($coupon_amount, BABE_Order::get_order_currency($order_id) );
            $coupon_amount = min($coupon_amount, $order_amount);
        }

        /// add meta _coupon_num & _coupon_amount
        update_post_meta($order_id, '_coupon_num', $coupon_num);
        update_post_meta($order_id, '_coupon_amount_applied', $coupon_amount);
        update_post_meta($coupon->ID, '_order_id', $order_id);

        // increase coupon uses
        self::increase_coupon_number_of_uses($coupon->ID);
        return true;
    }

    public static function can_coupon_be_applied_to_order( $coupon_id, $order_id ) {

        $coupon_booking_categories  = get_post_meta($coupon_id, '_coupon_booking_category', true);
        $coupon_booking_items  = get_post_meta($coupon_id, '_coupon_booking_items', true);

        if ( empty($coupon_booking_categories) && empty($coupon_booking_items) ){

            return apply_filters('babe_can_coupon_be_applied_to_order', true, $coupon_id, $order_id);
        }

        $order_items = BABE_Order::get_order_items($order_id);
        $order_item = reset($order_items);
        $booking_obj_id = (int)$order_item['booking_obj_id'];

        if (
            !empty($coupon_booking_items)
            && is_array($coupon_booking_items)
            && in_array($booking_obj_id, $coupon_booking_items)
        ){

            return apply_filters('babe_can_coupon_be_applied_to_order', true, $coupon_id, $order_id);
        }

        $booking_obj_category_id = BABE_Post_types::get_post_category($booking_obj_id)->term_id;

        if (
            !empty($coupon_booking_categories)
            && is_array($coupon_booking_categories)
            && in_array($booking_obj_category_id, $coupon_booking_categories)
        ){

            return apply_filters('babe_can_coupon_be_applied_to_order', true, $coupon_id, $order_id);
        }

        return apply_filters('babe_can_coupon_be_applied_to_order', false, $coupon_id, $order_id);
    }

    /**
     * Remove coupon from the order
     *
     * @param int $order_id
     * @param string $coupon_num
     * @return boolean
     */
    public static function remove_coupon_from_the_order($order_id, $coupon_num){

        $coupon_before = get_post_meta($order_id, '_coupon_num', true);

        /// get coupon id
        $coupon = get_page_by_title($coupon_num, 'OBJECT', BABE_Post_types::$coupon_post_type);

        if (
            !$coupon
            || $coupon_before !== $coupon_num
        ){
            return false;
        }

        /// update meta _coupon_num & _coupon_amount
        delete_post_meta($order_id, '_coupon_num');
        delete_post_meta($order_id, '_coupon_amount_applied');
        delete_post_meta($coupon->ID, '_order_id', $order_id);

        // reduce coupon uses
        self::reduce_coupon_number_of_uses($coupon->ID);
        return true;
    }
    
//////////////////////////////////////                       
     /**
	 * Get coupon applied to order, by order ID
     * 
     * @param int $order_id
     * @return WP_Post|string
	 */
    public static function get_coupon_by_order_id( $order_id ) {
        
        $args = array(
               'post_type' => BABE_Post_types::$coupon_post_type,
               'meta_query' => array(
                  'relation' => 'AND',
                  array(
                    'key'     => '_order_id',
                    'value'   => $order_id,
                    'compare' => '=',
                  ),
               ),
             );
             
         $coupons = get_posts( $args );
         return $coupons ? $coupons[0] : '';
    }        

//////////////////////////////////////                       
     /**
	 * Check coupon status for active, update if necessary
     * 
     * @param int $coupon_id
     * @return boolean
	 */
    public static function coupon_is_active( $coupon_id ) {

        $output = self::get_coupon_status($coupon_id) === 'active'
            && !self::maybe_used_coupon($coupon_id)
            && !self::maybe_expire_coupon($coupon_id)
        ;

        return apply_filters('babe_coupon_is_active', $output, $coupon_id);
    }

    /**
     * @param int $coupon_id
     * @return bool
     */
    public static function maybe_expire_coupon($coupon_id) {

        $expiration_date = self::get_coupon_expiration_date($coupon_id);
        $expiration_date_obj = DateTime::createFromFormat(BABE_Settings::$settings['date_format'], $expiration_date);
        $date_now_obj = BABE_Functions::datetime_local();

        if ($expiration_date_obj <= $date_now_obj){
            // update status
            self::set_coupon_status($coupon_id, 'expired');
            return true;
        }
        return false;
    }

    /**
     * @param int $coupon_id
     * @return bool
     */
    public static function maybe_used_coupon($coupon_id) {

        $coupon_usage = self::get_coupon_usage($coupon_id);
        $limit = self::get_coupon_usage_limit($coupon_id);

        if ( $limit && $limit <= $coupon_usage ){
            self::set_coupon_status($coupon_id, 'used');
            return true;
        }
        return false;
    }
    
//////////////////////////////////////
    /**
     * Get coupon amount
     *
     * @param int $coupon_id
     * @return array
     */
    public static function get_coupon_amount( $coupon_id) {

        $percent  = (float)get_post_meta($coupon_id, '_coupon_percent', true);
	    if ($percent){
	    	$return = array( 'value' => $percent, 'type'=> 'percent');
	    } else {
		    $amount = (float)get_post_meta($coupon_id, '_coupon_amount', true);
		    $return = array( 'value' => $amount, 'type'=> 'amount');
	    }

	    return $return;
    }

     /**
	 * Get coupon number
     * 
     * @param int $coupon_id
     * @return string
	 */
    public static function get_coupon_num( $coupon_id ) {

       return get_post_meta($coupon_id, '_coupon_number', true);
    }        

     /**
	 * Get coupon status
     * 
     * @param int $coupon_id
     * @return string
	 */
    public static function get_coupon_status( $coupon_id ) {

       return get_post_meta($coupon_id, '_coupon_status', true);
    }

     /**
	 * Set coupon status
     * 
     * @param int $coupon_id
     * @param string $status
     * @return void
	 */
    public static function set_coupon_status( $coupon_id, $status ) {

       update_post_meta($coupon_id, '_coupon_status', $status);
    }

    /**
     * Get coupon usage
     *
     * @param int $coupon_id
     * @return int
     */
    public static function get_coupon_usage( $coupon_id ) {

        return (int)get_post_meta($coupon_id, '_coupon_usage', true);
    }

    /**
     * Set coupon usage
     *
     * @param int $coupon_id
     * @param int $coupon_usage
     * @return void
     */
    public static function set_coupon_usage( $coupon_id, $coupon_usage ) {

        update_post_meta($coupon_id, '_coupon_usage', $coupon_usage);
    }

    /**
     * Set coupon usage limit
     *
     * @param int $coupon_id
     * @param int $limit
     * @return void
     */
    public static function set_coupon_usage_limit( $coupon_id, $limit ) {

        update_post_meta($coupon_id, '_coupon_usage_limit', $limit);
    }

    /**
     * Get coupon usage limit
     *
     * @param int $coupon_id
     * @return int
     */
    public static function get_coupon_usage_limit( $coupon_id ) {

        $limit = get_post_meta($coupon_id, '_coupon_usage_limit', true);

        return $limit === false || $limit === '' ? 1 : (int)$limit;
    }

    /**
     * Increase coupon usage
     *
     * @param int $coupon_id
     * @return void
     */
    public static function increase_coupon_number_of_uses( $coupon_id ) {

        $coupon_usage = (int)get_post_meta($coupon_id, '_coupon_usage', true );
        $coupon_usage++;
        self::set_coupon_usage($coupon_id, $coupon_usage);
        self::maybe_used_coupon($coupon_id);
    }

    /**
     * Reduce coupon usage
     *
     * @param int $coupon_id
     * @return void
     */
    public static function reduce_coupon_number_of_uses( $coupon_id ) {

        $coupon_usage = self::get_coupon_usage($coupon_id);
        if ( !$coupon_usage ){
            return;
        }

        $coupon_usage--;
        self::set_coupon_usage($coupon_id, $coupon_usage);
        if ( self::get_coupon_status($coupon_id) === 'used' ){
            self::set_coupon_status($coupon_id, 'active');
            self::maybe_expire_coupon($coupon_id);
        }
    }

    /**
     * Create coupon
     *
     * @param float $amount
     * @return int $post_id
     */
    public static function create_coupon_post($amount = 1) {
        
       $coupon_number = self::generate_coupon_num();
       $amount = abs( (float)$amount );
       
       $user_info = wp_get_current_user();
       $user_id = $user_info !== null && !empty($user_info->ID) ? $user_info->ID : 1;

       $post_id = wp_insert_post(array (
         'post_type' => BABE_Post_types::$coupon_post_type,
         'post_title' => $coupon_number,
         'post_content' => '',
         'post_status' => 'publish',
         'comment_status' => 'closed',
         'post_author'   => $user_id,
         'meta_input'   => array(
             '_coupon_number' => $coupon_number,
             '_coupon_amount' => $amount,
             '_coupon_status' => 'active',
             '_coupon_usage_limit' => 1,
             '_coupon_end_date' => self::get_coupon_default_expiration_date(),
         ),
       ));
       
       return $post_id;
    }

    /**
	 * Get expiration date of the coupon
     * 
     * @param int $coupon_id
     * @return string
	 */
    public static function get_coupon_expiration_date($coupon_id){

        $end_date = get_post_meta($coupon_id, '_coupon_end_date', true);
        if ( empty($end_date) ){
            $post_date = get_post_field( 'post_date', $coupon_id );
            $d = DateTime::createFromFormat('Y-m-d H:i:s', $post_date);
            $add_days = self::get_coupon_expire_days();
            $d->modify('+'.$add_days.' days');
            $end_date = $d->format(BABE_Settings::$settings['date_format']);
            self::set_coupon_expiration_date( $coupon_id, $end_date );
        }
        return $end_date;
    }

    /**
     * Get default expiration date for the new coupon
     *
     * @return string
     */
    public static function get_coupon_default_expiration_date(){
        $d = BABE_Functions::datetime_local();
        $add_days = BABE_Coupons::get_coupon_expire_days();
        $d->modify('+'.$add_days.' days');
        return $d->format(BABE_Settings::$settings['date_format']);
    }

    /**
     * Set coupon expiration date
     *
     * @param int $coupon_id
     * @param string $end_date - d/m/Y | m/d/Y
     * @return void
     */
    public static function set_coupon_expiration_date( $coupon_id, $end_date ) {

        update_post_meta($coupon_id, '_coupon_end_date', $end_date);
    }

    /**
	 * Get days after which coupons expire
     * 
     * @return int
	 */
    public static function get_coupon_expire_days(){
        return isset(BABE_Settings::$settings['coupons_expire_days']) ?
            absint(BABE_Settings::$settings['coupons_expire_days']) : 180;
    }

    /**
	 * Are coupons active in settings?
     * 
     * @return int
	 */
    public static function coupons_active(){
        return isset(BABE_Settings::$settings['coupons_active']) ?
            absint(BABE_Settings::$settings['coupons_active']) : 0;
    }

     /**
	 * Generate unique coupon number
     * 
     * @return string
	 */
    public static function generate_coupon_num(){
        
        $output = '';
        
        do {
          $output = substr(base_convert(sha1(random_int(100, 99999)), 16, 36), 0, 8);
          
          $output = strtoupper($output);
        
          $args = array(
            'post_type' => BABE_Post_types::$coupon_post_type, 
            'meta_key'     => '_coupon_number',
            'meta_value'   => $output,
          );
          $coupons = get_posts( $args );
        } while (!empty($coupons));
        
        $output = apply_filters('babe_generate_coupon_num', $output);
        
        return $output; 
    }

     /**
	 * Change coupon title
     * @param array $data
     * @param array $postarr
     * @return array
	 */
    public static function change_coupon_title($data, $postarr){

      if ($data['post_type'] == BABE_Post_types::$coupon_post_type) {
        
      // If it is our form has not been submitted, so we dont want to do anything
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $data;
        
        $data['post_title'] = isset($postarr['_coupon_number']) ? $postarr['_coupon_number'] : $data['post_title'];
        
        $data['post_name'] = sanitize_title($data['post_title']);
      }
        return $data; 
    }

////////////////////////////
}