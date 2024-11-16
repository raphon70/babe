<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Posts_admin Class.
 * Create and manage new Booking Objects posts
 * @class 		BABE_Posts_admin
 * @version		1.0.0
 * @author 		Booking Algorithms
 */

class BABE_Posts_admin {
    
///////////////////////////////////////    
    public static function init() {
        //add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueued_assets' ) );
        add_action( 'do_meta_boxes', array( __CLASS__, 'remove_plugin_metaboxes') );
    
	}

////////////////////////////////////
    /**
     * Remove meta boxes
     */
    public static function remove_plugin_metaboxes(){
        remove_meta_box( 'tagsdiv-'.BABE_Post_types::$categories_tax, BABE_Post_types::$booking_obj_post_type, 'side' );
        remove_meta_box( 'tagsdiv-'.BABE_Post_types::$ages_tax, BABE_Post_types::$booking_obj_post_type, 'side' );
        remove_meta_box( 'tagsdiv-'.BABE_Post_types::$taxonomies_list_tax, BABE_Post_types::$booking_obj_post_type, 'side' );
        
        foreach(BABE_Post_types::$taxonomies_list as $taxonomy_id => $taxonomy){
           remove_meta_box( 'tagsdiv-'.$taxonomy['slug'], BABE_Post_types::$booking_obj_post_type, 'side' );
        }
        
    }     
    
/////////////////////////////////////
    
}

BABE_Posts_admin::init();
