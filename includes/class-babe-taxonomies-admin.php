<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Taxonomies_admin Class.
 * 
 * @class 		BABE_Taxonomies_admin
 * @version		1.0.0
 * @author 		Booking Algorithms
 */

class BABE_Taxonomies_admin {
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {
 
       add_action( 'delete_'.BABE_Post_types::$taxonomies_list_tax,  array( __CLASS__, 'taxonomies_list_delete_tax' ), 10, 4  );
       
       add_action( 'edited_'.BABE_Post_types::$taxonomies_list_tax,  array( __CLASS__, 'taxonomies_list_edited_term' ), 10, 2  );
       
       add_filter( 'manage_edit-'.BABE_Post_types::$taxonomies_list_tax.'_columns', array( __CLASS__, 'taxonomies_list_column_header' ) );
       add_action( 'manage_'.BABE_Post_types::$taxonomies_list_tax.'_custom_column',  array( __CLASS__, 'taxonomies_list_column_rows' ), 10, 3  );      
	}

///////////////////////////////////
    /**
	 * Edit taxonomy after term edited in Taxonomies list.
     * 
     * @param int $term_id Term ID.
     * @param int $tt_id Term taxonomy ID.
	 */
    public static function taxonomies_list_edited_term($term_id, $tt_id) {
        global $wpdb;
        if (isset(BABE_Post_types::$taxonomies_list[$term_id])){
         // get old term slug
         $old_tax_slug = BABE_Post_types::$taxonomies_list[$term_id]['slug'];
         
         // get new term
         $edited_term = get_term( $term_id, BABE_Post_types::$taxonomies_list_tax );
         
         // make new term slug
         $new_tax_slug = BABE_Post_types::$attr_tax_pref.$edited_term->slug;
         
         // update mysql table
         $wpdb->update( 
           $wpdb->term_taxonomy, 
           array( 
            'taxonomy' => $new_tax_slug,	// string
           ), 
           array( 'taxonomy' => $old_tax_slug ), 
           array( '%s' ), 
           array( '%s' ) 
         );
         
         // update BABE_Post_types::$taxonomies_list[$term_id]
         BABE_Post_types::$taxonomies_list[$term_id] = array(
                   'name' => $edited_term->name,
                   'slug' => $new_tax_slug,
         );
         
         $term_vals = get_term_meta($term_id);
         foreach($term_vals as $key=>$val){
            BABE_Post_types::$taxonomies_list[$term_id][$key] = $val[0];
         }   
            
        }
    }
        
///////////////////////////////////
    /**
	 * Delete taxonomy after term deleted from Taxonomies list.
     * 
     * @param int $term_id Term ID.
     * @param int $tt_id Term taxonomy ID.
     * @param mixed $deleted_term Copy of the already-deleted term, in the form specified by the parent function. WP_Error otherwise.
     * @param array $object_ids List of term object IDs.
	 */
    public static function taxonomies_list_delete_tax($term_id, $tt_id, $deleted_term, $object_ids) {
        
        global $wpdb;
        
        $tax_to_del = BABE_Post_types::$attr_tax_pref.$deleted_term->slug;
        
        // Prepare & excecute SQL, Delete Terms
        $wpdb->get_results( $wpdb->prepare( "DELETE t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s')", $tax_to_del ) );
        // Delete Taxonomy
        $wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $tax_to_del ), array( '%s' ) );
        
    }
//////////////////////////////
    /**
	 * Add extra columns to Taxonomies list taxonomy.
     * @param array $columns columns header array.
     * @return array
	 */
    public static function taxonomies_list_column_header($columns) {
        $columns['tax_action'] = __( 'Action', 'ba-book-everything' );
        return $columns;
    }
//////////////////////////////
    /**
	 * Add extra columns values to Taxonomies list taxonomy.
     * @param string $content.
     * @param string $column_name.
     * @param int $term_id.
     * @return string
	 */
    public static function taxonomies_list_column_rows($content, $column_name, $term_id) {
        if ( 'tax_action' == $column_name ) {

            $current_lang = BABE_Functions::get_current_language();

            $default_lang = BABE_Functions::get_default_language();

            if ( BABE_Functions::is_wpml_active() && $current_lang !== $default_lang ){
                /** @var WPML_Term_Translation $wpml_term_translations */
                global $wpml_term_translations;
                $term_id = (int) $wpml_term_translations->term_id_in( $term_id, $default_lang );
            }

            if (isset(BABE_Post_types::$taxonomies_list[$term_id]))
            $content = '<a href="'.admin_url( 'edit-tags.php?taxonomy='.BABE_Post_types::$taxonomies_list[$term_id]['slug'] ).'&post_type='.BABE_Post_types::$booking_obj_post_type.'">'.sprintf(__( 'Edit %s taxonomy', 'ba-book-everything' ), BABE_Post_types::$taxonomies_list[$term_id]['name']).'</a>';
        }
       return $content;
    }
//////////////////////////////    

}

BABE_Taxonomies_admin::init();  
    