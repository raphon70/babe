<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

BABE_Coupons_admin::init();
/**
 * BABE_Coupons_admin Class.
 * 
 * @class 		BABE_Coupons_admin
 * @version		1.5.7
 * @author 		Booking Algorithms
 */

class BABE_Coupons_admin {
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {
        
        add_action('babe_settings_after_email_fields', array( __CLASS__, 'add_coupons_settings' ), 10, 2);
        add_filter('babe_sanitize_'.BABE_Settings::$option_name, array( __CLASS__, 'sanitize_coupons_settings' ), 10, 2);
        
        add_filter( 'manage_'.BABE_Post_types::$coupon_post_type.'_posts_columns', array( __CLASS__, 'coupon_table_head'));
        add_action( 'manage_'.BABE_Post_types::$coupon_post_type.'_posts_custom_column', array( __CLASS__, 'coupon_table_content'), 10, 2 );

        add_filter( 'posts_where', array( __CLASS__, 'search_where' ));
        add_filter( 'posts_join', array( __CLASS__, 'search_join' ));
        add_filter( 'posts_groupby', array( __CLASS__, 'search_group_by' ));
	}
    
////////////////////////
    /**
	 * Add search results from post meta.
     * 
     * @param string $join - join sql clauses
     * @return string
	 */
    public static function search_join ($join){
    global $pagenow, $wpdb;
    if ( isset( $_GET['s'] )){
    if ( is_admin() && $pagenow=='edit.php' && $_GET['post_type']==BABE_Post_types::$coupon_post_type && $_GET['s'] != '') {
        $join .='LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
     }
    }
    return $join;
}

////////////////////////
    /**
	 * Add search results from post meta.
     * @param string $where - where sql clauses
     * @return string
	 */
    public static function search_where( $where ){
    global $pagenow, $wpdb;
    if ( isset( $_GET['s'] )){
    if ( is_admin() && $pagenow=='edit.php' && $_GET['post_type']==BABE_Post_types::$coupon_post_type && $_GET['s'] != '') {
        
        $new_where = "(".$wpdb->posts.".post_title LIKE $1) OR ((".$wpdb->postmeta.".meta_key = '_coupon_status') AND (".$wpdb->postmeta.".meta_value LIKE $1)) OR ((".$wpdb->postmeta.".meta_key = '_coupon_amount') AND (".$wpdb->postmeta.".meta_value LIKE $1))";
        
        $where = preg_replace(
       "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
       $new_where, $where );
     }
    }
    return $where;
}

////////////////////////
    /**
	 * Add search results from post meta.
     * @param string $groupby - group by sql clauses
     * @return string
	 */
    public static function search_group_by($groupby) {
    global $pagenow, $wpdb;
    if ( isset( $_GET['s'] )){
      if ( is_admin() && $pagenow == 'edit.php' && $_GET['post_type']==BABE_Post_types::$coupon_post_type && $_GET['s'] != '' ) {
        $groupby = "$wpdb->posts.ID";
      }
    }  
    return $groupby;
}

/////////////////////
    /**
	 * Add coupon custom column heads.
     * 
     * @param array $defaults
     * @return array
	 */
    public static function coupon_table_head( $defaults ) {

        $date_title = $defaults['date'];
        unset($defaults['date'],$defaults['author']);

        $defaults['amount'] = __('Amount', 'ba-book-everything');
        $defaults['status'] = __('Status', 'ba-book-everything');
        $defaults['usage'] = __('Number of uses', 'ba-book-everything');
        $defaults['usage_limit'] = __('Limit of uses', 'ba-book-everything');
        $defaults['expiration_date'] = __('Expiration date', 'ba-book-everything');
        $defaults['date'] = $date_title;

        return $defaults;
    }

///////////////////////////////////
    /**
	 * Add coupon custom column content.
     * 
     * @param string $column_name
     * @param int $post_id
     * @return void
	 */
    public static function coupon_table_content( $column_name, $post_id ) {

        if ($column_name === 'amount') {
            $coupon_amount = BABE_Coupons::get_coupon_amount($post_id);
            if ($coupon_amount["type"] === 'percent') {
                $val = $coupon_amount["value"].'%';
            } else {
                $val = BABE_Currency::get_currency_price($coupon_amount["value"]);
            }
            echo $val;
        }

        if ($column_name === 'status') {
            $status = BABE_Coupons::get_coupon_status($post_id);
            echo '<div class="admin_edit_coupon_status coupon_status_'.$status.'">'.BABE_Coupons::$coupon_statuses[$status].'</div>';
        }

        if ($column_name === 'usage') {
            echo BABE_Coupons::get_coupon_usage($post_id);
        }

        if ($column_name === 'usage_limit') {
            echo BABE_Coupons::get_coupon_usage_limit($post_id);
        }

        if ($column_name === 'expiration_date') {
            echo BABE_Coupons::get_coupon_expiration_date($post_id);
        }
    }
    
//////////////////////////////
    /**
	 * Sanitize coupons settings
     * 
     * @param array $new_input
     * @param array $input
     * @return array
	 */
    public static function sanitize_coupons_settings($new_input, $input) {
        
        $new_input['coupons_active'] = absint($input['coupons_active']);
        $new_input['coupons_expire_days'] = isset($input['coupons_expire_days']) ? absint($input['coupons_expire_days']) : 180;
        
        return $new_input;
    }    
    
//////////////////////////////
    /**
	 * Add coupon settings
     * @param string $option_menu_slug
     * @param string $option_name
	 */
    public static function add_coupons_settings($option_menu_slug, $option_name) {
        
        add_settings_section(
            'setting_section_coupons', // ID
            __('Coupons', 'ba-book-everything'), // Title
            '__return_false', // Callback
            $option_menu_slug // Page
        );
        
        add_settings_field(
            'coupons_active', // ID
            __('Activate Coupons', 'ba-book-everything'), // Title
            array( 'BABE_Settings_admin', 'is_active_callback' ), // Callback
            $option_menu_slug, // Page
            'setting_section_coupons',  // Section
            array('option' => 'coupons_active', 'settings_name' => $option_name) // Args array
        );
        
        add_settings_field(
            'coupons_expire_days', // ID
            __('Default coupon expires in (days)', 'ba-book-everything'), // Title
            array( 'BABE_Settings_admin', 'text_field_callback' ), // Callback
            $option_menu_slug, // Page
            'setting_section_coupons',  // Section
            array('option' => 'coupons_expire_days', 'settings_name' => $option_name) // Args array
        );
    }
////////////////////////////
}
