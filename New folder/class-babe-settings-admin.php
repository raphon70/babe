<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Settings_admin Class.
 * Create and manage general settings
 * @class 		BABE_Settings_admin
 * @version		1.4.8
 * @author 		Booking Algorithms
 */

class BABE_Settings_admin {
    
///////////////////////////////////////    
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'admin_menu_settings_page' ), 10 );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ), 10 );
        add_action( 'admin_init', array( __CLASS__, 'admin_footer_babe_text' ), 20 );

        add_action( 'babe_settings_standard_emails', array( __CLASS__, 'settings_standard_emails' ), 10, 2 );
        add_filter( 'babe_sanitize_'.BABE_Settings::$option_name, array( __CLASS__, 'sanitize_standard_emails' ), 10, 2);

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 10 );
        
        add_action( 'wp_ajax_setup_demo_content', array( __CLASS__, 'ajax_setup_demo_content'));
     
	}
///////////////////////////////////////
    /**
	 * Enqueue assets.
	 */
    public static function admin_enqueue_scripts() {
        
     if ( isset($_GET['page']) && ($_GET['page'] == BABE_Settings::$option_menu_slug || $_GET['page'] == 'babe-addons' || $_GET['page'] == 'babe-demo-content' ) ){   
     
         wp_enqueue_style( 'wp-color-picker');
         wp_enqueue_script( 'wp-color-picker');
        if(function_exists( 'wp_enqueue_media' )){
         wp_enqueue_media();
         } else {
           wp_enqueue_style('thickbox');
           wp_enqueue_script('media-upload');
           wp_enqueue_script('thickbox');
           }
           
         wp_enqueue_style( 'babe-admin-settings', plugins_url( "css/admin/babe-admin-settings.css", BABE_PLUGIN ), array(), BABE_VERSION);
      }
     }
     
///////////////////////////////////////
    /**
	 * Change admin footer.
	 */
    public static function admin_footer_babe_text() {
        
     if ( (isset($_GET['page']) && $_GET['page'] == BABE_Settings::$option_menu_slug) || (isset($_GET['post_type']) && $_GET['post_type'] == BABE_Post_types::$booking_obj_post_type)){
     
        add_filter('admin_footer_text', array( __CLASS__, 'text_rate_babe' ), 99);
          
      }
     }
     
///////////////////////////////////////
    /**
	 * Change admin footer text.
	 */
    public static function text_rate_babe() {
    
      echo 'Please rate <strong>BA Book Everything</strong> <a href="https://wordpress.org/support/plugin/ba-book-everything/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="https://wordpress.org/support/plugin/ba-book-everything/reviews/?filter=5#new-post" target="_blank">WordPress.org</a> to help us spread the word. Thank you!';
     
    }          
     
///////////////////////////////////////    
    /**
	 * Add Settings admin page to menu.
	 */
    public static function admin_menu_settings_page(){
        
        add_menu_page(
            __('BA Book Everything Settings', 'ba-book-everything'),
            __('BA Settings', 'ba-book-everything'),
            'manage_options',
            BABE_Settings::$option_menu_slug,
            array( __CLASS__, 'create_settings_page' ),
            '',
            26
        );
        
        add_submenu_page( 
          BABE_Settings::$option_menu_slug,
          __('Addons and Themes', 'ba-book-everything'),
          __('Addons and Themes', 'ba-book-everything'),
          'manage_options',
          'babe-addons',
          array( __CLASS__, 'create_addons_page' )
        );
        
        add_submenu_page( 
          BABE_Settings::$option_menu_slug,
          __('Demo content', 'ba-book-everything'),
          __('Demo content', 'ba-book-everything'),
          'manage_options',
          'babe-demo-content',
          array( __CLASS__, 'create_demo_content_page' )
        );
        
    }

///////////////////////////////////////    
    /**
     * Demo content page callback
     */
    public static function create_demo_content_page()
    {
        ?>
        <div class="wrap babe-demo-content-wrap">
            <h2><?php echo __('Demo content setup page', 'ba-book-everything'); ?></h2>
            
            <div class="babe-addons-browser wp-clearfix"><?php echo __('This tool will allow you to setup demo content to make it easy to get started with the BA Book Everything plugin. Demo content includes: booking rules, ages, features taxonomy, categories, service posts, places posts, FAQ posts and booking object posts. Please do not close this page during the installation of demo content.', 'ba-book-everything'); ?>
            </div>
            <p>
              <button id="babe_demo_setup_button" class="button button-primary button-hero"><?php echo __('Setup Demo Content', 'ba-book-everything'); ?></button>
            </p>
            <p>
               <div id="babe_demo_setup_result"></div>
               <span class="spin_f"><i class="fas fa-spinner fa-spin fa-2x"></i></span>
            </p>
        </div>

        <script>
(function($){
	"use strict";   

////////////////////////////////////

$(document).ready(function(){
    
    $('#babe_demo_setup_button').on('click', function(){
        
        $('.babe-demo-content-wrap .spin_f').addClass('active');
        
        setup_demo_content(1);
        
    });
    
});

function setup_demo_content(i){
              
        $.ajax({
		url : '<?php echo admin_url( 'admin-ajax.php' );?>',
		type : 'POST',
		data : {
			action : 'setup_demo_content',
            i: i,
            // check
	        nonce : '<?php echo wp_create_nonce('setup-demo'); ?>'
		},
		success : function( msg ) {
		  $('#babe_demo_setup_result').append('<p>'+msg+'</p>');
          
          if (i < 5){
            i++;
            setup_demo_content(i);
          } else {
            //$('#babe_demo_setup_button').remove();
            $('.babe-demo-content-wrap .spin_f').removeClass('active');
          }
            ///////////////    
		 },
         error : function( msg ){
            $('.babe-demo-content-wrap .spin_f').removeClass('active');
         }
        });
        
        return;
        
        }
    
})( jQuery );
       </script>

        <?php
    }
    
///////////////////////////////////////    
    /**
	 * Ajax setup demo content
	 */
    public static function ajax_setup_demo_content(){
        
        if (isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], 'setup-demo' ) && isset($_POST['i'])){  
            $i = absint($_POST['i']);
            $starttime = microtime(true);
            
            if (function_exists('ini_set')){
              ini_set('max_execution_time', '300');
              ini_set('memory_limit','256M');
            }
            
            if ($i == 1):
            
            echo __('Setup ages and taxonomies...', 'ba-book-everything');
            
            elseif ($i == 2): 
            
            BABE_Install::setup_ages();
            
            BABE_Install::setup_tax_features();
            
            echo __('Setup images...', 'ba-book-everything');
            
            elseif ($i == 3):
            
            BABE_Install::setup_images();
            
            echo __('Setup places, service and FAQ posts ...', 'ba-book-everything');
            
            elseif ($i == 4):
            
            BABE_Install::setup_posts_places();
            
            BABE_Install::setup_posts_services();
            
            BABE_Install::setup_posts_faq();
            
            echo __('Setup rules, categories, booking object posts...', 'ba-book-everything');
            
            elseif ($i == 5):
            
            BABE_Install::setup_rules();
            
            BABE_Install::setup_categories();
            
            $created_post_ids = BABE_Install::setup_posts_booking_objects();
            
            $page_all_items_id = BABE_Install::create_page_all_items();
            
            echo __('Done!', 'ba-book-everything');
            
            echo '<div class="setup_result">
             <div class="setup_result_title">'.__('Next steps:', 'ba-book-everything').'</div>
             <ul>
              <li>'.sprintf(__('Go to %1$sAll items%2$s page and check created items on front-end', 'ba-book-everything'), '<a href="' . esc_url( get_permalink($page_all_items_id) ) . '" target="_blank">', '</a>').'</li>
              <li>'.sprintf(__('Edit demo content or create your own (read setting details in %1$sDocumentation%2$s)', 'ba-book-everything'), '<a href="https://ba-booking.com/ba-book-everything/documentation/setup-environment/" target="_blank">', '</a>').'</li>
             </ul>
            </div>';
            
            endif;
            
            $endtime = microtime(true);

        }
                      
        wp_die();                   
    }    
        
///////////////////////////////////////    
    /**
     * Settings page callback
     */
    public static function create_addons_page()
    {
        ?>
        <div class="wrap">
            <h2><?php echo __('Addons and Themes for BA Book Everything plugin', 'ba-book-everything'); ?></h2>
            
            <hr class="wp-header-end">
            
            <div class="babe-addons-browser">
            
            <div class="wp-clearfix babe-addons-inner">
            
            <!-- //////////////////////// -->
            
          <div class="babe-addon">
	
		    <div class="babe-addon-screenshot">
             <a href="https://ba-booking.com/shop/downloads/ba-book-everything-payment_pack/">
			   <img src="<?php echo plugins_url( "css/img/ba_payments_banners.png", BABE_PLUGIN ); ?>" alt="">
             </a>  
		    </div>
            
            <div class="babe-addon-container">
              <a href="https://ba-booking.com/shop/downloads/ba-book-everything-payment_pack/">
			   <h2 class="babe-addon-name"><?php echo __('BABE Payment pack', 'ba-book-everything'); ?></h2>
              </a> 
               <div class="babe-addon-description">
				   <?php echo __('Integrates PayPal and Credit Cards (Stripe) payments. Stripe integration supports 3D Secure card payments, Apple Pay, Google Pay, Microsoft Pay and the Payment Request API.', 'ba-book-everything'); ?>
               </div>
            
               <div class="babe-addon-actions">
				   <a class="button button-primary" href="https://ba-booking.com/shop/downloads/ba-book-everything-payment_pack/"><?php echo __('Learn more', 'ba-book-everything'); ?></a>
               </div>
            </div>
            
          </div>
            
            <!--  //////////////////////      -->
            
            <div class="babe-addon">
	
		    <div class="babe-addon-screenshot">
             <a href="https://ba-booking.com/shop/downloads/babe-backoffice/">
			   <img src="<?php echo plugins_url( "css/img/ba_backoffice_banner.png", BABE_PLUGIN ); ?>" alt="">
             </a>  
		    </div>
            
            <div class="babe-addon-container">
              <a href="https://ba-booking.com/shop/downloads/babe-backoffice/">
			   <h2 class="babe-addon-name"><?php echo __('BABE Backoffice', 'ba-book-everything'); ?></h2>
              </a> 
               <div class="babe-addon-description">
				   <?php echo __('Management tools: PDF invoices, backend bookings, editing/cancelation orders, extra charge, full and partial refund, iCal synchronization.', 'ba-book-everything'); ?>
               </div>
            
               <div class="babe-addon-actions">
				   <a class="button button-primary" href="https://ba-booking.com/shop/downloads/babe-backoffice/"><?php echo __('Learn more', 'ba-book-everything'); ?></a>
               </div>
            </div>
            
          </div>
          
          <!--  //////////////////////      -->
          
          <div class="babe-addon">
	
		    <div class="babe-addon-screenshot">
             <a href="https://ba-booking.com/ba-tours/">
			   <img src="<?php echo plugins_url( "css/img/ba_tours_banner.png", BABE_PLUGIN ); ?>" alt="">
             </a>  
		    </div>
            
            <div class="babe-addon-container">
              <a href="https://ba-booking.com/ba-tours/">
			   <h2 class="babe-addon-name"><?php echo __('BA Tours theme', 'ba-book-everything'); ?></h2>
              </a> 
               <div class="babe-addon-description">
				   <?php echo __('BA Tours booking theme developed for travel agencies and tour operators of any size. It\'s bundled with BABE Payment pack - <b>you can save up to $49</b>!', 'ba-book-everything'); ?>
               </div>
            
               <div class="babe-addon-actions">
				   <a class="button button-primary" href="https://ba-booking.com/ba-tours-demo/"><?php echo __('View Demo', 'ba-book-everything'); ?></a>
                   <a class="button button-primary" href="https://ba-booking.com/ba-tours/"><?php echo __('Learn more', 'ba-book-everything'); ?></a>
               </div>
            </div>
            
          </div>
          
          <!--  //////////////////////      -->
          
          <div class="babe-addon">
	
		    <div class="babe-addon-screenshot">
             <a href="https://ba-booking.com/ba-hotel/">
			   <img src="<?php echo plugins_url( "css/img/ba_hotel_banner.png", BABE_PLUGIN ); ?>" alt="">
             </a>  
		    </div>
            
            <div class="babe-addon-container">
              <a href="https://ba-booking.com/ba-hotel/">
			   <h2 class="babe-addon-name"><?php echo __('BA Hotel theme', 'ba-book-everything'); ?></h2>
              </a> 
               <div class="babe-addon-description">
				   <?php echo __('Hotel booking theme developed for mini hotels and hostels. It\'s created with SEO in mind and gives you a high pagespeed score even without caching plugins. BABE Payment pack is included - <b>you can save up to $49</b>!', 'ba-book-everything'); ?>
               </div>
            
               <div class="babe-addon-actions">
				   <a class="button button-primary" href="https://ba-booking.com/ba-hotel-demo/"><?php echo __('View Demo', 'ba-book-everything'); ?></a>
                   <a class="button button-primary" href="https://ba-booking.com/ba-hotel/"><?php echo __('Learn more', 'ba-book-everything'); ?></a>
               </div>
            </div>
            
          </div>
          
          <!--  //////////////////////      -->
            
            </div>
          </div>  
        </div>

        <?php
    }    
    
///////////////////////////////////////    
    /**
     * Settings page callback
     */
    public static function create_settings_page()
    {
        ?>
        <div class="wrap babe-settings-wrap">
            <h2><?php echo __('BA Book Everything Settings', 'ba-book-everything'); ?></h2>
            <form method="post" action="options.php" enctype="multipart/form-data">
            <?php
                // This prints out all hidden setting fields
                settings_fields( BABE_Settings::$option_menu_slug );
                self::do_settings_sections( BABE_Settings::$option_menu_slug );
                submit_button();
            ?>
            </form>
        </div>

        <script>
(function( $ ) {
	// Add Color Picker to all inputs that have 'color-field' class
	$(function() {
	$('.color-field').wpColorPicker();
	});
})( jQuery );
       </script>

        <?php
    }    
         
///////////////////////////////////////
////////////////////////////////////////////////

    /**
     * Custom do settings with tabs support
     * 
     * @param string $page
     * @return
     */
    public static function do_settings_sections( $page ) {
        
      global $wp_settings_sections, $wp_settings_fields;
 
      if ( ! isset( $wp_settings_sections[$page] ) ){
        return;
      }
      
      $tabs = '<h2 class="nav-tab-wrapper babe-nav-tab-wrapper">';
      $content = '';
      
      $i=0;
      
      $selected_tab = isset($_GET['setting_tab']) && $_GET['setting_tab'] ? sanitize_key($_GET['setting_tab']) : '' ;  
 
      foreach ( (array) $wp_settings_sections[$page] as $section ) {
        
        //print_r($section);
        
        $class_active = '';
        
        if ( $section['title'] ){
            
            $class_active = (($selected_tab && $section['id'] == $selected_tab) || (!$selected_tab && !$i)) ? 'nav-tab-active' : '';
            
            $tabs .= '<a class="nav-tab '.$class_active.'" href="#'.$section['id'].'" data-target="'.$section['id'].'">'.$section['title'].'</a>
            ';
            
            $i++;
        }    
            
        if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) ){
            continue;
        }
        
        $class_content_active = $class_active ? 'tab-target-active' : '';
        
        ob_start();
        
        echo '<div id="'.$section['id'].'" class="tab-target '.$class_content_active.'">';
        
        if ( $section['callback'] ){
            call_user_func( $section['callback'], $section );
        }
            
        echo '<table class="form-table">';
        do_settings_fields( $page, $section['id'] );
        echo '</table>';
        echo '</div>';
        
        $content .= ob_get_clean();
        
     }
     
     $tabs .= '</h2>';
     
     echo $tabs.$content;
     
     return;
   }

   static function learn_more_banners(){
        ?>
       <div class="babe-addons-info-container">

           <?php
           if (!is_plugin_active('babe-payment-pack/babe-payment-pack.php')):
           ?>

           <div class="babe-addon payment-pack">

               <div class="babe-addon-screenshot">
                   <a href="https://ba-booking.com/shop/downloads/ba-book-everything-payment_pack/">
                       <img src="<?php echo plugins_url( "css/img/ba_payments_banners.png", BABE_PLUGIN ); ?>" alt="">
                   </a>
               </div>

               <div class="babe-addon-container">
                   <a href="https://ba-booking.com/shop/downloads/ba-book-everything-payment_pack/">
                       <h2 class="babe-addon-name"><?php echo __('BABE Payment pack', 'ba-book-everything'); ?></h2>
                   </a>
                   <div class="babe-addon-description">
                       <?php echo __('Integrates PayPal and Credit Cards (Stripe) payments. Stripe integration supports 3D Secure card payments, Apple Pay, Google Pay, Microsoft Pay and the Payment Request API.', 'ba-book-everything'); ?>
                   </div>

                   <div class="babe-addon-actions">
                       <a class="button button-primary" href="https://ba-booking.com/shop/downloads/ba-book-everything-payment_pack/"><?php echo __('Learn more', 'ba-book-everything'); ?></a>
                   </div>
               </div>

           </div>

            <?php
           endif;

           if (!is_plugin_active('babe-backoffice/babe-backoffice.php')):
           ?>

           <!--  //////////////////////      -->

           <div class="babe-addon backoffice-pack">

               <div class="babe-addon-screenshot">
                   <a href="https://ba-booking.com/shop/downloads/babe-backoffice/">
                       <img src="<?php echo plugins_url( "css/img/ba_backoffice_banner.png", BABE_PLUGIN ); ?>" alt="">
                   </a>
               </div>

               <div class="babe-addon-container">
                   <a href="https://ba-booking.com/shop/downloads/babe-backoffice/">
                       <h2 class="babe-addon-name"><?php echo __('BABE Backoffice', 'ba-book-everything'); ?></h2>
                   </a>
                   <div class="babe-addon-description">
                       <?php echo __('Management tools: PDF invoices, backend bookings, editing/cancelation orders, extra charge, full and partial refund, iCal synchronization.', 'ba-book-everything'); ?>
                   </div>

                   <div class="babe-addon-actions">
                       <a class="button button-primary" href="https://ba-booking.com/shop/downloads/babe-backoffice/"><?php echo __('Learn more', 'ba-book-everything'); ?></a>
                   </div>
               </div>

           </div>

           <?php
           endif;
           ?>

       </div>
<?php
   }

////////////////////////////////////////////////
    /**
     * Register and add settings
     */
    public static function register_settings()
    {
        register_setting(
            BABE_Settings::$option_menu_slug, // Option group
            BABE_Settings::$option_name, // Option name
            array( __CLASS__, 'sanitize_settings' ) // Sanitize
        );

        ///////// General

        add_settings_section(
            'setting_section_general', // ID
            __('General','ba-book-everything'), // Title
	        array( __CLASS__, 'learn_more_banners' ), // Callback
            BABE_Settings::$option_menu_slug // Page
        );
        
         add_settings_field(
            'date_format', // ID
            __('Date format','ba-book-everything'), // Title
            array( __CLASS__, 'setting_date_format' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general' // Section
        );
        
        add_settings_field(
            'booking_obj_gutenberg', // ID
            __('Use Gutenberg for booking object posts','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'booking_obj_gutenberg', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'zero_price_display_value', // ID
            __('When service price is 0 display','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'zero_price_display_value', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'booking_obj_post_slug', // ID
            __('Booking object post slug','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'booking_obj_post_slug', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'booking_obj_post_name', // ID
            __('Booking object singular name','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'booking_obj_post_name', 'settings_name' => BABE_Settings::$option_name, 'translate' => 1) // Args array
        );
        
        add_settings_field(
            'booking_obj_post_name_general', // ID
            __('Booking object general (plural) name','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'booking_obj_post_name_general', 'settings_name' => BABE_Settings::$option_name, 'translate' => 1) // Args array
        );
        
        add_settings_field(
            'booking_obj_menu_name', // ID
            __('Booking object menu name','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'booking_obj_menu_name', 'settings_name' => BABE_Settings::$option_name, 'translate' => 1) // Args array
        );

        add_settings_field(
            'attr_tax_prefix', // ID
            __('Prefix for custom taxonomies (*before changing this option it is required to remove all custom taxonomies)','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'attr_tax_prefix', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'search_result_page', // ID
            __('Select search result page','ba-book-everything'), // Title
            array( __CLASS__, 'setting_page_select' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'search_result_page', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'services_page', // ID
            __('Services page','ba-book-everything'), // Title
            array( __CLASS__, 'setting_page_select' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'services_page', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'checkout_page', // ID
            __('Checkout page','ba-book-everything'), // Title
            array( __CLASS__, 'setting_page_select' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'checkout_page', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'confirmation_page', // ID
            __('Confirmation page','ba-book-everything'), // Title
            array( __CLASS__, 'setting_page_select' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'confirmation_page', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'terms_page', // ID
            __('Terms & Conditions page','ba-book-everything'), // Title
            array( __CLASS__, 'setting_page_select' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'terms_page', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'admin_confirmation_page', // ID
            __('Admin page for order confirmation (for manually availability confirmation)','ba-book-everything'), // Title
            array( __CLASS__, 'setting_page_select' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'admin_confirmation_page', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'my_account_page', // ID
            __('My Account page','ba-book-everything'), // Title
            array( __CLASS__, 'setting_page_select' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'my_account_page', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'my_account_disable', // ID
            __('Disable My Account page and new user mail?','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'my_account_disable', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'max_guests_select', // ID
            __('Booking form: maximum number of guests available to choose','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'max_guests_select', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'results_per_page', // ID
            __('Search result: items per page','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'results_per_page', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'posts_per_taxonomy_page', // ID
            __('Taxonomy archive page: items per page','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'posts_per_taxonomy_page', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'av_calendar_max_months', // ID
            __('Maximum number of months in availability calendar','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'av_calendar_max_months', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'results_without_av_check', // ID
            __('Return search results without checking availability if no dates are specified','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'results_without_av_check', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'results_without_av_cal', // ID
            __('Do not include availability calendar data in search results','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'results_without_av_cal', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'results_view', // ID
            __('Search result view','ba-book-everything'), // Title
            array( __CLASS__, 'setting_results_view' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general' // Section
        );
        ////

        add_settings_field(
            'content_in_tabs', // ID
            __('Show booking object post content in tabs?','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'content_in_tabs', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'reviews_in_tabs', // ID
            __('Show reviews in tab on booking object page?','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'reviews_in_tabs', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'reviews_comment_template', // ID
            __('Reviews comment template (leave empty to use the default "/comments.php")','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',  // Section
            array('option' => 'reviews_comment_template', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'mpoints_active', // ID
            __('Add meeting points functionality?','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'mpoints_active', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'view_only_uploaded_images', // ID
            __('Media library filter: show only current user attachments and unattached madia in the Media library', 'ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'view_only_uploaded_images', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'unitegallery_remove', // ID
            __('Remove unitegallery from booking object pages', 'ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'unitegallery_remove', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'av_calendar_remove', // ID
            __('Remove availability calendar from booking object pages', 'ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'av_calendar_remove', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'av_calendar_remove_hover_prices', // ID
            __('Remove price details on hover from availability calendar', 'ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'av_calendar_remove_hover_prices', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'google_map_remove', // ID
            __('Remove google map from booking object pages', 'ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'google_map_remove', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'services_to_booking_form', // ID
            __('Add services to booking form', 'ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'services_to_booking_form', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'prefill_date_in_booking_form', // ID
            __('Prepopulates the date field on the booking form, starting with the nearest available date', 'ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'prefill_date_in_booking_form', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'use_minimized_css', // ID
            __('Use minimized version of the main CSS file', 'ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'use_minimized_css', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'use_minimized_js', // ID
            __('Use minimized version of the main JS file', 'ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general', // Section
            array('option' => 'use_minimized_js', 'settings_name' => BABE_Settings::$option_name) // Args array
        );


        add_settings_field(
            'reset_settings', // ID
            __('Reset settings', 'ba-book-everything'), // Title
            array( __CLASS__, 'setting_reset_settings' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_general',
            array('class' => 'babe_settings_subtitle', 'option' => 'reset_settings', 'settings_name' => BABE_Settings::$option_name)
        );

        ////////////////
        
        do_action('babe_settings_after_general_fields', BABE_Settings::$option_menu_slug, BABE_Settings::$option_name);
        
        ///////// Currency

        add_settings_section(
            'setting_section_currency', // ID
            __('Currency','ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug // Page
        );
        
        add_settings_field(
            'currency', // ID
            __('Select base currency','ba-book-everything'), // Title
            array( __CLASS__, 'setting_currency' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_currency' // Section
        );
        
        add_settings_field(
            'currency_place', // ID
            __('Place of currency symbol in displayed prices','ba-book-everything'), // Title
            array( __CLASS__, 'setting_currency_place' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_currency' // Section
        );
        
        add_settings_field(
            'price_thousand_separator', // ID
            __('Thousand Separator of displayed prices','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_currency',  // Section
            array('option' => 'price_thousand_separator', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'price_decimal_separator', // ID
            __('Decimal Separator of displayed prices','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_currency',  // Section
            array('option' => 'price_decimal_separator', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'price_decimals', // ID
            __('Number of decimal points shown in displayed prices','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_currency',  // Section
            array('option' => 'price_decimals', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'price_from_label', // ID
            __('Label before "price from" amount on the booking item page. Use "%s" to add currency three-letter code ISO 4217','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_currency',  // Section
            array('option' => 'price_from_label', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        ////////////////
        
        do_action('babe_settings_after_currency_fields', BABE_Settings::$option_menu_slug, BABE_Settings::$option_name);
        
        ///////// Payments

        add_settings_section(
            'setting_section_payment', // ID
            __('Payments','ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug // Page
        );
        
        add_settings_field(
            'payment_methods', // ID
            __('Activate payment methods','ba-book-everything'), // Title
            array( __CLASS__, 'setting_payment_methods' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_payment' // Section
        );
        
        if (empty(BABE_Payments::$payment_methods)){
            BABE_Payments::$payment_methods['cash'] = __('Pay later','ba-book-everything');
        }
        
        foreach(BABE_Payments::$payment_methods as $method => $method_title){
            
            $section_id = 'setting_section_payment';
            $settings_page = BABE_Settings::$option_menu_slug;
            $option_name = BABE_Settings::$option_name;
            
            add_settings_field(
              'title_payment_method_'.$method, // ID
              __('Gateway: ','ba-book-everything').$method_title, // Title
              '__return_false', // Callback
              BABE_Settings::$option_menu_slug, // Page
              'setting_section_payment', // Section
              ['class' => 'babe_setting_subtitle']
            );
            
            do_action('babe_settings_payment_method_'.$method, $section_id, $settings_page, $option_name);
        }
        
        ////////////////
        
        do_action('babe_settings_after_payment_fields', BABE_Settings::$option_menu_slug, BABE_Settings::$option_name);
        
        ///////// Order

        add_settings_section(
            'setting_section_order', // ID
            __('Order','ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug // Page
        );

        add_settings_field(
            'checkout_add_billing_address', // ID
            __('Add billing address fields to the checkout form','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_order', // Section
            array('option' => 'checkout_add_billing_address', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'order_availability_confirm', // ID
            __('Confirm item availability','ba-book-everything'), // Title
            array( __CLASS__, 'setting_order_availability_confirm' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_order' // Section
        );

        add_settings_field(
            'disable_guest_bookings', // ID
            __('Disable guest bookings?','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_order', // Section
            array('option' => 'disable_guest_bookings', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'order_payment_processing_waiting', // ID
            __('Waiting before delete orders with "payment processing" status, minutes','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_order',  // Section
            array('option' => 'order_payment_processing_waiting', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        ////////////////
        
        do_action('babe_settings_after_order_fields', BABE_Settings::$option_menu_slug, BABE_Settings::$option_name);
        
        ///////// Confirmation

        add_settings_section(
            'setting_section_confirm', // ID
            __('Confirmation', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug // Page
        );
        
        add_settings_field(
            'message_av_confirmation', // ID
            __('Message for status "availability confirmation"', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_confirm',  // Section
            array('option' => 'message_av_confirmation', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'message_not_available', // ID
            __('Message for status "not available"', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_confirm',  // Section
            array('option' => 'message_not_available', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'message_payment_deferred', // ID
            __('Message for status "payment deferred"', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_confirm',  // Section
            array('option' => 'message_payment_deferred', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'message_payment_expected', // ID
            __('Message for status "payment expected"', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_confirm',  // Section
            array('option' => 'message_payment_expected', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'message_payment_processing', // ID
            __('Message for status "payment processing"', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_confirm',  // Section
            array('option' => 'message_payment_processing', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'message_payment_received', // ID
            __('Message for status "payment received"', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_confirm',  // Section
            array('option' => 'message_payment_received', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'message_draft', // ID
            __('Message for status "draft"', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_confirm',  // Section
            array('option' => 'message_draft', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        ////////////////
        
        do_action('babe_settings_after_confirm_fields', BABE_Settings::$option_menu_slug, BABE_Settings::$option_name);
        
        ///////// Emails

        add_settings_section(
            'setting_section_emails', // ID
            __('Emails','ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug // Page
        );

        do_action('babe_settings_before_email_fields', BABE_Settings::$option_menu_slug, BABE_Settings::$option_name);

        //
        add_settings_field(
            'shop_email', // ID
            __('Shop admin email (site admin email by default)'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'shop_email', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_template_separator', // ID
            __('Emails template settings', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );
        
        add_settings_field(
            'email_logo', // ID
            __('Logo image','ba-book-everything'), // Title
            array( __CLASS__, 'img_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_logo', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_header_image', // ID
            __('Header image','ba-book-everything'), // Title
            array( __CLASS__, 'img_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_header_image', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_footer_message', // ID
            __('Footer text (contacts, etc.)','ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_footer_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_footer_credit', // ID
            __('Credits text (copyright, etc.)','ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_footer_credit', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        //-----------------------//

        do_action('babe_settings_standard_emails', BABE_Settings::$option_menu_slug, BABE_Settings::$option_name);
        
        //-----------------------//
        
        add_settings_field(
            'email_new_customer_created_separator', // ID
            __('Customer account created email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );
        
        add_settings_field(
            'email_new_customer_created_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_customer_created_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_new_customer_created_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_customer_created_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_new_customer_created_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_customer_created_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        //-----------------------//
        
        add_settings_field(
            'email_password_reseted_separator', // ID
            __('Customer password reseted email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );
        
        add_settings_field(
            'email_password_reseted_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_password_reseted_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_password_reseted_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_password_reseted_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_password_reseted_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_password_reseted_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        //-----------------------//
        
        add_settings_field(
            'email_template_color_separator', // ID
            __('Emails color scheme', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );
        
        add_settings_field(
            'email_color_font', // ID
            __('Font color','ba-book-everything'), // Title
            array( __CLASS__, 'color_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_color_font', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_color_background', // ID
            __('Background color','ba-book-everything'), // Title
            array( __CLASS__, 'color_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_color_background', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_color_title', // ID
            __('Titles color','ba-book-everything'), // Title
            array( __CLASS__, 'color_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_color_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_color_link', // ID
            __('Links color','ba-book-everything'), // Title
            array( __CLASS__, 'color_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_color_link', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_color_button', // ID
            __('Button default color','ba-book-everything'), // Title
            array( __CLASS__, 'color_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_color_button', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_color_button_yes', // ID
            __('Confirm button color','ba-book-everything'), // Title
            array( __CLASS__, 'color_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_color_button_yes', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'email_color_button_no', // ID
            __('Cancel button color','ba-book-everything'), // Title
            array( __CLASS__, 'color_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails', // Section
            array('option' => 'email_color_button_no', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        ////////////////
        
        do_action('babe_settings_after_email_fields', BABE_Settings::$option_menu_slug, BABE_Settings::$option_name);
        
        ///////// Google

        add_settings_section(
            'setting_section_google', // ID
            __('Google map','ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug // Page
        );

        add_settings_field(
            'google_map_active', // ID
            __('Add google map functionality?','ba-book-everything'), // Title
            array( __CLASS__, 'is_active_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_google', // Section
            array('option' => 'google_map_active', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'google_api', // ID
            __('Google API key (see ','ba-book-everything').'<a href="https://developers.google.com/maps/documentation/embed/get-api-key" target="blank">Google Maps API docs</a>'.__(' for details).','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_google',  // Section
            array('option' => 'google_api', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'google_map_zoom', // ID
            __('Map zoom','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_google',  // Section
            array('option' => 'google_map_zoom', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'google_map_start_lat', // ID
            __('Map start latitude','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_google',  // Section
            array('option' => 'google_map_start_lat', 'settings_name' => BABE_Settings::$option_name) // Args array
        );
        
        add_settings_field(
            'google_map_start_lng', // ID
            __('Map start Longitude','ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_google',  // Section
            array('option' => 'google_map_start_lng', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'google_map_marker', // ID
            __('Select map marker','ba-book-everything'), // Title
            array( __CLASS__, 'setting_map_marker' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_google' // Section
        );
        
        ////////////////
        
        do_action('babe_settings_after_google_fields', BABE_Settings::$option_menu_slug, BABE_Settings::$option_name);
        
    }

    ////////////////////////////////////////////////
    /**
     * Add standard email settings
     */
    public static function settings_standard_emails()
    {

        add_settings_field(
            'email_new_order_separator', // ID
            __('Customer New order email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_new_order_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_order_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_new_order_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_order_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_new_order_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_order_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        //-----------------------//

        add_settings_field(
            'email_order_updated_separator', // ID
            __('Customer order updated email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_order_updated_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_order_updated_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_order_updated_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_order_updated_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_order_updated_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_order_updated_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        //-----------------------//

        add_settings_field(
            'email_new_order_av_confirm_separator', // ID
            __('Customer availability confirmation email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_new_order_av_confirm_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_order_av_confirm_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_new_order_av_confirm_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_order_av_confirm_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_new_order_av_confirm_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_order_av_confirm_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        //-----------------------//

        add_settings_field(
            'email_new_order_to_pay_separator', // ID
            __('Customer order to pay email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_new_order_to_pay_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_order_to_pay_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_new_order_to_pay_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_order_to_pay_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_new_order_to_pay_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_new_order_to_pay_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        //-----------------------//

        add_settings_field(
            'email_order_rejected_separator', // ID
            __('Customer order rejected email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_order_rejected_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_order_rejected_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_order_rejected_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_order_rejected_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_order_rejected_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_order_rejected_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        //-----------------------//

        add_settings_field(
            'email_order_canceled_separator', // ID
            __('Customer canceled the order email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_order_canceled_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_order_canceled_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_order_canceled_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_order_canceled_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_order_canceled_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_order_canceled_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        //-----------------------//

        add_settings_field(
            'email_admin_new_order_separator', // ID
            __('Admin New order email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_admin_new_order_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_new_order_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_admin_new_order_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_new_order_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_admin_new_order_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_new_order_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        //-----------------------//

        add_settings_field(
            'email_admin_order_updated_separator', // ID
            __('Admin order updated email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_admin_order_updated_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_order_updated_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_admin_order_updated_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_order_updated_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_admin_order_updated_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_order_updated_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        //-----------------------//

        add_settings_field(
            'email_admin_new_order_av_confirm_separator', // ID
            __('Admin availability confirmation email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_admin_new_order_av_confirm_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_new_order_av_confirm_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_admin_new_order_av_confirm_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_new_order_av_confirm_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_admin_new_order_av_confirm_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_new_order_av_confirm_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        //-----------------------//

        add_settings_field(
            'email_admin_order_canceled_separator', // ID
            __('Admin order canceled email', 'ba-book-everything'), // Title
            '__return_false', // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',
            array('class' => 'babe_settings_subtitle')
        );

        add_settings_field(
            'email_admin_order_canceled_subject', // ID
            __('Subject', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_order_canceled_subject', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_admin_order_canceled_title', // ID
            __('Body title', 'ba-book-everything'), // Title
            array( __CLASS__, 'text_field_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_order_canceled_title', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

        add_settings_field(
            'email_admin_order_canceled_message', // ID
            __('Body message', 'ba-book-everything'), // Title
            array( __CLASS__, 'textarea_callback' ), // Callback
            BABE_Settings::$option_menu_slug, // Page
            'setting_section_emails',  // Section
            array('option' => 'email_admin_order_canceled_message', 'settings_name' => BABE_Settings::$option_name) // Args array
        );

    }

    //////////////////////////////

    /**
     * Sanitize standard emails
     *
     * @param array $new_input
     * @param array $input Contains all settings fields as array keys
     * @return array
     */
    static function sanitize_standard_emails( $new_input, $input ){

        $new_input['email_admin_new_order_subject'] = sanitize_text_field($input['email_admin_new_order_subject']);
        $new_input['email_admin_new_order_title'] = sanitize_text_field($input['email_admin_new_order_title']);
        $new_input['email_admin_new_order_message'] = wp_kses_post($input['email_admin_new_order_message']);

        $new_input['email_admin_order_updated_subject'] = sanitize_text_field($input['email_admin_order_updated_subject']);
        $new_input['email_admin_order_updated_title'] = sanitize_text_field($input['email_admin_order_updated_title']);
        $new_input['email_admin_order_updated_message'] = wp_kses_post($input['email_admin_order_updated_message']);

        $new_input['email_admin_new_order_av_confirm_subject'] = sanitize_text_field($input['email_admin_new_order_av_confirm_subject']);
        $new_input['email_admin_new_order_av_confirm_title'] = sanitize_text_field($input['email_admin_new_order_av_confirm_title']);
        $new_input['email_admin_new_order_av_confirm_message'] = wp_kses_post($input['email_admin_new_order_av_confirm_message']);

        $new_input['email_new_order_av_confirm_subject'] = sanitize_text_field($input['email_new_order_av_confirm_subject']);
        $new_input['email_new_order_av_confirm_title'] = sanitize_text_field($input['email_new_order_av_confirm_title']);
        $new_input['email_new_order_av_confirm_message'] = wp_kses_post($input['email_new_order_av_confirm_message']);

        $new_input['email_new_order_subject'] = sanitize_text_field($input['email_new_order_subject']);
        $new_input['email_new_order_title'] = sanitize_text_field($input['email_new_order_title']);
        $new_input['email_new_order_message'] = wp_kses_post($input['email_new_order_message']);

        $new_input['email_order_updated_subject'] = sanitize_text_field($input['email_order_updated_subject']);
        $new_input['email_order_updated_title'] = sanitize_text_field($input['email_order_updated_title']);
        $new_input['email_order_updated_message'] = wp_kses_post($input['email_order_updated_message']);

        $new_input['email_new_order_to_pay_subject'] = sanitize_text_field($input['email_new_order_to_pay_subject']);
        $new_input['email_new_order_to_pay_title'] = sanitize_text_field($input['email_new_order_to_pay_title']);
        $new_input['email_new_order_to_pay_message'] = wp_kses_post($input['email_new_order_to_pay_message']);

        $new_input['email_order_rejected_subject'] = sanitize_text_field($input['email_order_rejected_subject']);
        $new_input['email_order_rejected_title'] = sanitize_text_field($input['email_order_rejected_title']);
        $new_input['email_order_rejected_message'] = wp_kses_post($input['email_order_rejected_message']);

        $new_input['email_admin_order_canceled_subject'] = sanitize_text_field($input['email_admin_order_canceled_subject']);
        $new_input['email_admin_order_canceled_title'] = sanitize_text_field($input['email_admin_order_canceled_title']);
        $new_input['email_admin_order_canceled_message'] = wp_kses_post($input['email_admin_order_canceled_message']);

        $new_input['email_order_canceled_subject'] = sanitize_text_field($input['email_order_canceled_subject']);
        $new_input['email_order_canceled_title'] = sanitize_text_field($input['email_order_canceled_title']);
        $new_input['email_order_canceled_message'] = wp_kses_post($input['email_order_canceled_message']);

        return $new_input;
    }

////////////////////////////////

     /**
      * Sanitize each setting field as needed
      *
      * @param array $input Contains all settings fields as array keys
      */
      public static function sanitize_settings( $input ){

          $new_input = array();

          $new_input['date_format'] = $input['date_format'] === 'd/m/Y' || $input['date_format'] === 'm/d/Y' ? $input['date_format'] : 'd/m/Y';

          $new_input['attr_tax_prefix'] = sanitize_text_field($input['attr_tax_prefix']);
          $new_input['booking_obj_post_slug'] = sanitize_text_field($input['booking_obj_post_slug']);
          $new_input['zero_price_display_value'] = sanitize_text_field($input['zero_price_display_value']);

          $new_input['booking_obj_post_name'] = sanitize_text_field($input['booking_obj_post_name']);
          $new_input['booking_obj_post_name_general'] = sanitize_text_field($input['booking_obj_post_name_general']);
          $new_input['booking_obj_menu_name'] = sanitize_text_field($input['booking_obj_menu_name']);

          $new_input['mpoints_active'] = absint($input['mpoints_active']);
          $new_input['content_in_tabs'] = absint($input['content_in_tabs']);
          $new_input['reviews_in_tabs'] = absint($input['reviews_in_tabs']);
          $new_input['reviews_comment_template'] = sanitize_text_field($input['reviews_comment_template']);
          $new_input['view_only_uploaded_images'] = absint($input['view_only_uploaded_images']);

          $new_input['results_per_page'] = (int)$input['results_per_page'];
          $new_input['posts_per_taxonomy_page'] = (int)$input['posts_per_taxonomy_page'];
          $new_input['max_guests_select'] = absint($input['max_guests_select']);
          $new_input['av_calendar_max_months'] = absint($input['av_calendar_max_months']);

          $new_input['results_without_av_check'] = !$input['results_without_av_check'] ? 0 : 1;
          $new_input['results_without_av_cal'] = !$input['results_without_av_cal'] ? 0 : 1;
          $new_input['booking_obj_gutenberg'] = $input['booking_obj_gutenberg'] ? 1 : 0;
          $new_input['my_account_disable'] = $input['my_account_disable'] ? 1 : 0;

          $new_input['results_view'] = $input['results_view'] === 'full' ? 'full' : 'grid';

          $new_input['google_api'] = $input['google_api'];
          $new_input['google_map_start_lat'] = $input['google_map_start_lat'];
          $new_input['google_map_start_lng'] = $input['google_map_start_lng'];

          $new_input['google_map_zoom'] = absint($input['google_map_zoom']);
          $new_input['google_map_active'] = absint($input['google_map_active']);
          $new_input['google_map_marker'] = absint($input['google_map_marker']);

          $new_input['search_result_page'] = absint($input['search_result_page']);
          $new_input['checkout_page'] = absint($input['checkout_page']);
          $new_input['services_page'] = absint($input['services_page']);
          $new_input['confirmation_page'] = absint($input['confirmation_page']);
          $new_input['my_account_page'] = absint($input['my_account_page']);
          $new_input['terms_page'] = absint($input['terms_page']);
          $new_input['admin_confirmation_page'] = absint($input['admin_confirmation_page']);

          $currency_code_options = BABE_Currency::get_currencies();
          $currency = sanitize_text_field($input['currency']);
          $new_input['currency'] = isset($currency_code_options[$currency]) ? $currency : 'USD';

          $new_input['currency_place'] = sanitize_text_field($input['currency_place']);
          $new_input['price_thousand_separator'] = sanitize_text_field($input['price_thousand_separator']);
          $new_input['price_decimal_separator'] = sanitize_text_field($input['price_decimal_separator']);
          $new_input['price_decimals'] = absint($input['price_decimals']);
          $new_input['price_from_label'] = sanitize_text_field($input['price_from_label']);

          $new_input['checkout_add_billing_address'] = $input['checkout_add_billing_address'] ? 1 : 0;
          $new_input['disable_guest_bookings'] = $input['disable_guest_bookings'] ? 1 : 0;
          //order_availability_confirm
          $new_input['order_availability_confirm'] = $input['order_availability_confirm'] === 'manually' ? 'manually' : 'auto';

          $new_input['order_payment_processing_waiting'] = absint($input['order_payment_processing_waiting']);

          $new_input['unitegallery_remove'] = !$input['unitegallery_remove'] ? 0 : 1;
          $new_input['av_calendar_remove'] = !$input['av_calendar_remove'] ? 0 : 1;
          $new_input['av_calendar_remove_hover_prices'] = !$input['av_calendar_remove_hover_prices'] ? 0 : 1;
          $new_input['google_map_remove'] = !$input['google_map_remove'] ? 0 : 1;

          $new_input['services_to_booking_form'] = !$input['services_to_booking_form'] ? 0 : 1;
          $new_input['prefill_date_in_booking_form'] = empty($input['prefill_date_in_booking_form']) ? 0 : 1;
          $new_input['use_minimized_css'] = empty($input['use_minimized_css']) ? 0 : 1;
          $new_input['use_minimized_js'] = empty($input['use_minimized_js']) ? 0 : 1;

          $new_input['message_av_confirmation'] = wp_kses_post($input['message_av_confirmation']);
          $new_input['message_not_available'] = wp_kses_post($input['message_not_available']);
          $new_input['message_payment_deferred'] = wp_kses_post($input['message_payment_deferred']);
          $new_input['message_payment_expected'] = wp_kses_post($input['message_payment_expected']);
          $new_input['message_payment_processing'] = wp_kses_post($input['message_payment_processing']);
          $new_input['message_payment_received'] = wp_kses_post($input['message_payment_received']);
          $new_input['message_draft'] = wp_kses_post($input['message_draft']);

          $new_input['shop_email'] = isset($input['shop_email']) ? sanitize_text_field($input['shop_email']) : '';

          $new_input['email_new_customer_created_subject'] = sanitize_text_field($input['email_new_customer_created_subject']);
          $new_input['email_new_customer_created_title'] = sanitize_text_field($input['email_new_customer_created_title']);
          $new_input['email_new_customer_created_message'] = wp_kses_post($input['email_new_customer_created_message']);

          $new_input['email_password_reseted_subject'] = sanitize_text_field($input['email_password_reseted_subject']);
          $new_input['email_password_reseted_title'] = sanitize_text_field($input['email_password_reseted_title']);
          $new_input['email_password_reseted_message'] = wp_kses_post($input['email_password_reseted_message']);

          $new_input['email_logo'] = $input['email_logo'];
          $new_input['email_header_image'] = $input['email_header_image'];
          $new_input['email_footer_message'] = $input['email_footer_message'];
          $new_input['email_footer_credit'] = $input['email_footer_credit'];
          $new_input['email_color_font'] = $input['email_color_font'];
          $new_input['email_color_background'] = $input['email_color_background'];
          $new_input['email_color_title'] = $input['email_color_title'];
          $new_input['email_color_link'] = $input['email_color_link'];
          $new_input['email_color_button'] = $input['email_color_button'];
          $new_input['email_color_button_yes'] = $input['email_color_button_yes'];
          $new_input['email_color_button_no'] = $input['email_color_button_no'];

          ///////////////////
          $payment_methods_arr = isset($input['payment_methods']) ? (array)$input['payment_methods'] : array();
          $payment_methods_arr = empty($payment_methods_arr) ? array( 0 => 'cash') : $payment_methods_arr;

          foreach ($payment_methods_arr as $ind => $method){
              $method = sanitize_key($method);
              if (isset(BABE_Payments::$payment_methods[$method])){
                  $new_input['payment_methods'][] = $method;
              }
          }
          ///////////////////

          $new_input = apply_filters('babe_sanitize_'.BABE_Settings::$option_name, $new_input, $input);

          if ( !empty($input['reset_settings']) ){
              $new_input = BABE_Settings::get_default_settings();
          }

          return $new_input;
      }

//////////////////////////////////////    

    public static function color_field_callback($args){
        printf(
            '<input type="text" id="'.$args['option'].'" class="color-field" name="'.$args['settings_name'].'['.$args['option'].']" value="%s" />',
            isset( BABE_Settings::$settings[$args['option']] ) ? esc_attr( BABE_Settings::$settings[$args['option']]) : $args['color']
        );
}
    
////////////////////////////////////
    
    public static function text_field_callback($args){
        $add_class = isset($args['translate']) ? ' class="q_translatable"' : '';
        
        printf(
            '<input type="text"'.$add_class.' id="'.$args['option'].'" name="'.$args['settings_name'].'['.$args['option'].']" value="%s" />',
            isset( BABE_Settings::$settings[$args['option']] ) ? esc_attr( BABE_Settings::$settings[$args['option']]) : ''
        );
    }    

/////////////////////////////////////////
    
    public static function textarea_callback($args){
        printf(
            '<textarea id="'.$args['option'].'" class="q_translatable" name="'.$args['settings_name'].'['.$args['option'].']" rows=5>%s</textarea>',
            isset( BABE_Settings::$settings[$args['option']] ) ? esc_attr( BABE_Settings::$settings[$args['option']]) : ''
        );
}    

/////////////////////////////////////

    public static function img_field_callback($args){
       $img_src = isset( BABE_Settings::$settings[$args['option']] ) ? BABE_Settings::$settings[$args['option']] : '';

        echo '<div id="'.$args['option'].'_upload_block"><img id="'.$args['option'].'_preview" src="'.$img_src.'" class="_img_field_preview"/>';
        echo '<input type="text" id="'.$args['option'].'" name="'.$args['settings_name'].'['.$args['option'].']" value="'.$img_src.'" /><input type="button" class="'.$args['option'].'_upload" value="'.__('Upload', 'ba-book-everything').'"></div>';

        echo '<script>
    jQuery(document).ready(function($) {
        $(\'.'.$args['option'].'_upload\').click(function(e) {
            e.preventDefault();

            var custom_uploader = wp.media({
                title: \''.__('Custom Image', 'ba-book-everything').'\',
                button: {
                    text: \''.__('Upload Image', 'ba-book-everything').'\'
                },
                multiple: false  // Set this to true to allow multiple files to be selected
            })
            .on(\'select\', function() {
                var attachment = custom_uploader.state().get(\'selection\').first().toJSON();
                $(\'#'.$args['option'].'_preview\').attr(\'src\', attachment.url);
                $(\'#'.$args['option'].'\').val(attachment.url);

            })
            .open();
        });
    });
</script>';
}

////////////// map_marker

    public static function setting_map_marker(){

        $check = isset(BABE_Settings::$settings['google_map_marker']) ?  BABE_Settings::$settings['google_map_marker'] : 1;

        foreach (BABE_Settings::$markers_urls as $key => $url){
           $checked = $key == $check ? ' checked' : '';

           echo '<div class="map_marker_block"><label class="map_marker_img" id="google_map_marker'.$key.'" for="'.BABE_Settings::$option_name.'[google_map_marker]'.$key.'"><img src="'.plugins_url( $url, BABE_PLUGIN ).'"></label><input id="'.BABE_Settings::$option_name.'[google_map_marker]'.$key.'" name="'.BABE_Settings::$option_name.'[google_map_marker]" type="radio" value="'.$key.'"'.$checked.'/></div>';
        }
    }
    
////////////search_result_page 

   public static function setting_page_select($args){
    
    //$args['settings_name']

        $selected_page = isset(BABE_Settings::$settings[$args['option']]) ?  BABE_Settings::$settings[$args['option']] : 0;
        
        $args2 = array(
        'post_type'   => 'page',
        'numberposts' => -1,
        'post_status' => 'publish',
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'suppress_filters' => false,
        );
        
        $posts = get_posts( $args2 );
        
        $post_options = '';
        if ( $posts ) {
          foreach ( $posts as $post ) {
             $post_options .= '<option value="'. $post->ID .'" '. selected( $selected_page, $post->ID, false ) .'>'. $post->post_title .'</option>';
          }
        }
        
        $post_options = $post_options ? '<select id="'.$args['option'].'" name="'.$args['settings_name'].'['.$args['option'].']">
        '.$post_options.'
        </select>' : '';

           echo $post_options;

    }
    
/////////////////////////////////////////

    public static function setting_currency_place(){
        
        $selected_place = isset(BABE_Settings::$settings['currency_place']) ?  BABE_Settings::$settings['currency_place'] : 'left';
        
        $place_select = '';
        
		$options  = array(
			'left'        => __( 'Left', 'ba-book-everything' ) . ' (' . BABE_Currency::get_currency_symbol() . '99.99)',
			'right'       => __( 'Right', 'ba-book-everything' ) . ' (99.99' . BABE_Currency::get_currency_symbol() . ')',
			'left_space'  => __( 'Left with space', 'ba-book-everything' ) . ' (' . BABE_Currency::get_currency_symbol() . ' 99.99)',
			'right_space' => __( 'Right with space', 'ba-book-everything' ) . ' (99.99 ' . BABE_Currency::get_currency_symbol() . ')'
		);
        
        foreach ( $options as $code => $name ) {
			$place_select .= '<option value="'. $code .'" '. selected( $selected_place, $code, false ) .'>'. $name . '</option>';
		}
        
        $place_select = $place_select ? '<select id="currency_place" name="'.BABE_Settings::$option_name.'[currency_place]">
        '.$place_select.'
        </select>' : '';
        
        echo $place_select;
        
    }    
    
///////////////////////////////////////

    public static function setting_currency(){
        $currency_code_options = BABE_Currency::get_currencies();
        
        $selected_currency = isset(BABE_Settings::$settings['currency']) ?  BABE_Settings::$settings['currency'] : 'USD';
        
        $currency_select = '';

		foreach ( $currency_code_options as $code => $name ) {
			$currency_select .= '<option value="'. $code .'" '. selected( $selected_currency, $code, false ) .'>'. $name . ' (' . BABE_Currency::get_currency_symbol( $code ) . ')</option>';
		}
        
        $currency_select = $currency_select ? '<select id="currency" name="'.BABE_Settings::$option_name.'[currency]">
        '.$currency_select.'
        </select>' : '';
        
        echo $currency_select;
    }
    
///////////////////////////////////////

    public static function setting_payment_methods(){
        
        $selected_methods_arr = isset(BABE_Settings::$settings['payment_methods']) ?  BABE_Settings::$settings['payment_methods'] : array( 0 => 'cash' );
        
        $output = '';
        
        $i = 1;

		foreach ( BABE_Payments::$payment_methods as $method => $method_title ) {
		  
          $checked = in_array($method, $selected_methods_arr) ? ' checked="checked"' : '';
		  
		  $output .= '<span><input id="'.BABE_Settings::$option_name.'[payment_methods]'.$i.'" name="'.BABE_Settings::$option_name.'[payment_methods][]" type="checkbox" value="'.$method.'" '.$checked.'/><label for="'.BABE_Settings::$option_name.'[payment_methods]'.$i.'">'.$method_title.'</label></span>';
		  $i++;
        }
        
        echo $output;
    }

    ///////////////////////////////////////

    public static function setting_reset_settings($args){

        $output = '<span><input id="'.$args['settings_name'].'['.$args['option'].']" name="'.$args['settings_name'].'['.$args['option'].']" type="checkbox" value="1"/><label for="'.$args['settings_name'].'['.$args['option'].']">'.__('Check this box and click "Save Changes" button to reset all settings to their defaults. <strong>All custom settings will be lost!</strong>', 'ba-book-everything').'</label></span>';

        echo $output;
    }

///////////////////////////////////////////////
    
    public static function is_active_callback($args){

          $default = isset($args['default']) && $args['default'] ? 1 : 0;
        
        $check = BABE_Settings::$settings[$args['option']] ?? $default;

        $checked1 = $check ? 'checked' : '';
        $checked2 = !$check ? 'checked' : '';

        echo '<p><input id="'.$args['settings_name'].'['.$args['option'].']1" name="'.$args['settings_name'].'['.$args['option'].']" type="radio" value="1" '.$checked1.'/><label for="'.$args['settings_name'].'['.$args['option'].']1">'.__('Yes', 'ba-book-everything').'</label></p>';
       echo '<p><input id="'.$args['settings_name'].'['.$args['option'].']2" name="'.$args['settings_name'].'['.$args['option'].']" type="radio" value="0" '.$checked2.'/><label for="'.$args['settings_name'].'['.$args['option'].']2">'.__('No', 'ba-book-everything').'</label></p>';
       
    }
   
/////////////results_view///////////////////////

     public static function setting_results_view(){

        $check = isset(BABE_Settings::$settings['results_view']) ?  BABE_Settings::$settings['results_view'] : 'grid';

        echo '<p><input id="'.BABE_Settings::$option_name.'[results_view]1" name="'.BABE_Settings::$option_name.'[results_view]" type="radio" value="full" '. checked( 'full', $check, 0 ) .'/><label for="'.BABE_Settings::$option_name.'[results_view]1">'.__('Full', 'ba-book-everything').'</label></p>';
       echo '<p><input id="'.BABE_Settings::$option_name.'[results_view]2" name="'.BABE_Settings::$option_name.'[results_view]" type="radio" value="grid" '. checked( 'grid', $check, 0 ) .'/><label for="'.BABE_Settings::$option_name.'[results_view]2">'.__('Grid', 'ba-book-everything').'</label></p>';

   }      

////////setting_order_availability_confirm

    public static function setting_order_availability_confirm(){

        $check = isset(BABE_Settings::$settings['order_availability_confirm']) ?  BABE_Settings::$settings['order_availability_confirm'] : 'auto';

        $checked1 = $check == 'auto' ? 'checked' : '';
        $checked2 = $check == 'manually' ? 'checked' : '';

        echo '<p><input id="'.BABE_Settings::$option_name.'[order_availability_confirm]1" name="'.BABE_Settings::$option_name.'[order_availability_confirm]" type="radio" value="auto" '.$checked1.'/><label id="'.BABE_Settings::$option_name.'_order_availability_confirm1" for="'.BABE_Settings::$option_name.'[order_availability_confirm]1">'.__('Automatically', 'ba-book-everything').'</label></p>';
        echo '<p><input id="'.BABE_Settings::$option_name.'[order_availability_confirm]2" name="'.BABE_Settings::$option_name.'[order_availability_confirm]" type="radio" value="manually" '.$checked2.'/><label id="'.BABE_Settings::$option_name.'_order_availability_confirm2" for="'.BABE_Settings::$option_name.'[order_availability_confirm]2">'.__('Manually (by dashboard or e-mail)', 'ba-book-everything').'</label></p>';

    }

/////////////////////

    public static function setting_date_format(){

        $check = isset(BABE_Settings::$settings['date_format']) ?  BABE_Settings::$settings['date_format'] : 'd/m/Y';

        $checked1 = $check == 'd/m/Y' ? 'checked' : '';
        $checked2 = $check == 'm/d/Y' ? 'checked' : '';

        echo '<p><input id="'.BABE_Settings::$option_name.'[date_format]1" name="'.BABE_Settings::$option_name.'[date_format]" type="radio" value="d/m/Y" '.$checked1.'/><label id="'.BABE_Settings::$option_name.'_date_format1" for="'.BABE_Settings::$option_name.'[date_format]1">'.__('d/m/Y', 'ba-book-everything').'</label></p>';
        echo '<p><input id="'.BABE_Settings::$option_name.'[date_format]2" name="'.BABE_Settings::$option_name.'[date_format]" type="radio" value="m/d/Y" '.$checked2.'/><label id="'.BABE_Settings::$option_name.'_date_format2" for="'.BABE_Settings::$option_name.'[date_format]2">'.__('m/d/Y', 'ba-book-everything').'</label></p>';

    }

///////////////////////////
  
}

BABE_Settings_admin::init();
