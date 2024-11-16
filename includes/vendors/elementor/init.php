<?php
/**
 * Initialize Elementor
 *
 * @since   1.3.13
 */
 
// Add a custom category for panel widgets
use Elementor\Widgets_Manager;

add_action( 'elementor/init', 'babe_el_init', 0);
function babe_el_init(){
   
   \Elementor\Plugin::$instance->elements_manager->add_category( 
   	  'book-everything-elements',
   	  array(
   		'title' => __( 'Book Everything', 'ba-book-everything' ),
   		'icon' => 'fa fa-plug', //default icon
   	  ),
      1 // position
   );
   
   // Include custom functions of elementor widgets
   $widgets = [
       'allitems',
       'search_form',
       'booking_form',
       'item_stars',
       'item_address_map',
       'item_calendar',
       'item_slideshow',
       'item_faqs',
       'item_steps',
       'item_related',
       'item_price_from',
       'item_custom_section',
   ];

   foreach ( $widgets as $file ) {
       include_once BABE_PLUGIN_DIR . '/includes/vendors/elementor/widgets/' . $file . '.php';
   }
}

// Register widgets
add_action( 'elementor/widgets/register', 'babe_el_register_widgets' );
/**
 * Fires after Elementor widgets are registered.
 * @param Widgets_Manager $widgets_manager The widgets manager.
 */
function babe_el_register_widgets( $widgets_manager ){

    if ( !class_exists('BABE_Elementor_Allitems_Widget') ) {
        return;
    }

    $widgets_manager->register(new BABE_Elementor_Searchform_Widget());
    $widgets_manager->register(new BABE_Elementor_Bookingform_Widget());
    $widgets_manager->register(new BABE_Elementor_Allitems_Widget());

    $widgets_manager->register(new BABE_Elementor_Itemrelated_Widget());

    $widgets_manager->register(new BABE_Elementor_Itempricefrom_Widget());

    $widgets_manager->register(new BABE_Elementor_Itemstars_Widget());
    $widgets_manager->register(new BABE_Elementor_Itemslideshow_Widget());

    $widgets_manager->register(new BABE_Elementor_Itemcalendar_Widget());

    $widgets_manager->register(new BABE_Elementor_Itemfaqs_Widget());

    $widgets_manager->register(new BABE_Elementor_Itemsteps_Widget());
    $widgets_manager->register(new BABE_Elementor_Itemaddressmap_Widget());
    $widgets_manager->register(new BABE_Elementor_ItemCustomSection_Widget());
}


