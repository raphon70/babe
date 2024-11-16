<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Booking_Rules Class.
 * 
 * @class 		BABE_Booking_Rules
 * @version		1.0.0
 * @author 		Booking Algorithms
 */

class BABE_Booking_Rules {

    public static $booking_periods = [
        'single_custom' => 'single_custom',
        'recurrent_custom' => 'recurrent_custom',
        'day' => 'day',
        'night' => 'night',
        'month' => 'month',
        'hour' => 'hour',
    ];

    public static $payment_models = [
        'deposit' => 'deposit',
        'full' => 'full',
        'deposit_full' => 'deposit_full',
    ];

    public static $booking_modes = [
        'object' => 'object',
        'places' => 'places',
        'tickets' => 'tickets',
        'request' => 'request',
    ];
    
    private static $booking_rules_option = 'babe_booking_rules';
    
    //// cache
    
    private static $all_rules = array();
    
    // DB tables
    static $table_booking_rules;

//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {  
       global $wpdb;
       self::$table_booking_rules = $wpdb->prefix.'babe_booking_rules';
	}

///////////////////////////////////
	/**
	 * Get all stored booking rules templates
     * @return array
	 */
	public static function get_all_rules() {
	   //$output = get_option(self::$booking_rules_option); 
       global $wpdb;
       
       $rules = array();
       
       if (!empty(self::$all_rules)){
           return self::$all_rules;
       }
       
       $rules_db = $wpdb->get_results("SELECT * FROM ".self::$table_booking_rules." ORDER BY rule_id ASC", ARRAY_A);
       
       if (!empty($rules_db)){
        foreach($rules_db as $rule){
            $rules[$rule['rule_id']] = $rule;
        }
       }
       
       self::$all_rules = $rules;
  
       return $rules;  
	}
///////////////////////////////////
	/**
	 * Get booking rule template by rule id
     * @return array
	 */
	public static function get_rule($booking_rule_id) {
		$rules_arr = self::get_all_rules();
        return isset($rules_arr[$booking_rule_id]) ? $rules_arr[$booking_rule_id] : array();  
	}
///////////////////////////////////
	/**
	 * Get booking rule template by cat_slug
     * @param string $cat_slug
     * @return array
	 */
	public static function get_rule_by_cat_slug($cat_slug) {
	   $output = array();
           $category = get_term_by( 'slug', $cat_slug, BABE_Post_types::$categories_tax );
           if (!empty($category)){
             $category_meta = BABE_Post_types::get_term_meta($category->term_id);
             $rule_id = $category_meta['categories_booking_rule'];
             $output = self::get_rule($rule_id); 
           }
       return $output;        
	} 
    
///////////////////////////////////
	/**
	 * Get booking rule template by cat_id
     * @param int $cat_id
     * @return array
	 */
	public static function get_rule_by_cat_id($cat_id) {
	   $output = array();
           $category = get_term_by( 'id', $cat_id, BABE_Post_types::$categories_tax );
           if (!empty($category)){
             $category_meta = BABE_Post_types::get_term_meta($category->term_id);
             $rule_id = $category_meta['categories_booking_rule'];
             $output = self::get_rule($rule_id); 
           }
       return $output;        
	}
    
///////////////////////////////////
	/**
	 * Get booking rules and category meta by booking_obj_id
     * @param int $booking_obj_id
     * @return array
	 */
	public static function get_rule_by_obj_id($booking_obj_id) {
	   $output = [];
       
       $categories_arr = get_the_terms($booking_obj_id, BABE_Post_types::$categories_tax);
       
       if (!empty($categories_arr) && !is_wp_error($categories_arr)){
           $category = $categories_arr[0];
           $category_meta = BABE_Post_types::get_term_meta($category->term_id);

           if (empty($category_meta['categories_booking_rule'])){
               return $output;
           }

           $rule_id = $category_meta['categories_booking_rule'];
           $output['post_id'] = $booking_obj_id;
           $output['category_term_id'] = $category->term_id;
           $output['category_slug'] = $category->slug;
           $output['post_id'] = $booking_obj_id;
           $output['rules'] = self::get_rule($rule_id);
           $output['category_meta'] = $category_meta;
       }    
       return $output;        
	}         
       
///////////////////////////////////
	/**
	 * Delete booking rule template by rule id
     * @param int $rule_id
     * @return boolean
	 */
	public static function delete_rule($booking_rule_id) {
	   global $wpdb;
       
		$rules_arr = self::get_all_rules();
        $output = false; // booking rule id not found
        if (isset($rules_arr[$booking_rule_id])){
            unset(self::$all_rules[$booking_rule_id]);
            $wpdb->query( $wpdb->prepare( 'DELETE FROM '.self::$table_booking_rules.' WHERE rule_id = %d', $booking_rule_id ) );      
            $output = true; // booking rule id deleted
        }
        return  $output;  
	}
///////////////////////////////////
	/**
	 * Update or add booking rule template
     * 
     * @param array $rule Contains all rule params.
     * @return int
	 */
	public static function update_rule($rule) {
	   global $wpdb;
		$rules_arr = self::get_all_rules();
        $output = 0; // booking rule not updated / added
        if ( isset($rule['rule_id']) && isset($rules_arr[$rule['rule_id']]) ){
            // update existing rule
            self::$all_rules[$rule['rule_id']] = $rule;
            // prepare $rule array to update
            $rule_id = $rule['rule_id'];
            unset($rule['rule_id']);
            //// update existing rule
            $result = $wpdb->update(
              self::$table_booking_rules,
              $rule,
              array( 'rule_id' => $rule_id )
            );
            if (false === $result){
            //// do action on error
            } else {
              $output = $rule_id; // booking rule updated  
            }
        } else {
            // add new rule
            // sanitize
                unset($rule['rule_id']);
            //// create new row
                $wpdb->insert(
                  self::$table_booking_rules,
                  $rule
                 );
            $output = (int)$wpdb->insert_id; // booking rule added
        }
        
        return  $output;  
	}
///////////////////////////////////
	/**
	 * Add booking rule template - alias for update_rule function
     * 
     * @param array $rule Contains all rule params.
     * @return int
	 */
	public static function add_rule($rule) {        
        return self::update_rule($rule);
	}
///////////////////////////
    
}

BABE_Booking_Rules::init();

