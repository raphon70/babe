<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Search_From_admin Class.
 * Search form constructor
 * 
 * @class 		BABE_Search_From_admin
 * @version		1.3.19
 * @author 		Booking Algorithms
 */

class BABE_Search_From_admin {
    
    private static $nonce_title = 'search-form-tpl-nonce';
    
///////////////////////////////////////    
    public static function init() {
        
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu_page' ), 20 );
        
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
        
        add_action( 'wp_ajax_search_form_add_tab', array( __CLASS__, 'ajax_add_tab'));
        
        add_action( 'wp_ajax_search_form_tabs_reorder', array( __CLASS__, 'ajax_tabs_reorder'));
        
        add_action( 'wp_ajax_search_form_delete_tab', array( __CLASS__, 'ajax_delete_tab'));
        
        add_action( 'wp_ajax_search_form_update_fields', array( __CLASS__, 'ajax_update_fields'));
            
	}
///////////////////////////////////////
    /**
	 * Enqueue assets.
	 */
    public static function enqueue_scripts() {
        
     if ( isset($_GET['post_type']) && isset($_GET['page']) && $_GET['post_type'] == BABE_Post_types::$booking_obj_post_type && $_GET['page'] == 'search_form' ){
        
        wp_enqueue_script('jquery-ui-sortable');
     
     wp_enqueue_script( 'babe-admin-modal-js', plugins_url( "js/babe-modal.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );

     wp_enqueue_script( 'babe-admin-searchform-js', plugins_url( "js/admin/babe-admin-searchform.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );

     wp_localize_script( 'babe-admin-searchform-js', 'babe_searchform_lst', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce(self::$nonce_title)
         ]
     );
     
     wp_enqueue_style( 'babe-admin-searchform-style', plugins_url( "css/admin/babe-admin-searchform.css", BABE_PLUGIN ), array(), BABE_VERSION);
     
         wp_enqueue_style( 'babe-admin-modal-style', plugins_url( "css/babe-modal.css", BABE_PLUGIN ), array(), BABE_VERSION);
      }
     }
///////////////////////////////////////    
    /**
	 * Add admin page to menu.
	 */
    public static function add_admin_menu_page(){
        
        add_submenu_page( 'edit.php?post_type='.BABE_Post_types::$booking_obj_post_type, __('Search Form', 'ba-book-everything'), __('Search Form', 'ba-book-everything'), 'manage_options', 'search_form', array( __CLASS__, 'create_admin_page' ));
        
    }
///////////////////////////////////////
    /**
	 * Create admin page.
	 */
    public static function create_admin_page(){
                
        ?>
        <div class="wrap">
            <h2><?php echo __('Search Form builder', 'ba-book-everything'); ?></h2>
            
            <hr />
            
            <h3><?php echo __('Search form Tabs', 'ba-book-everything'); ?></h3>
            
            <div id="search-form-tabs">
              <?php echo self::get_search_form_tab_list(); ?>
            </div>
            <br />
            <div id="search-form-tabs-new">
               <label for="search_form_tab_title"><?php echo __('Tab Title:', 'ba-book-everything'); ?></label> <input type="text" id="search_form_tab_title" name="search_form_tab_title" value=""/>
               <label for="search_form_tab_slug"><?php echo __('Tab slug:', 'ba-book-everything'); ?></label> <input type="text" id="search_form_tab_slug" name="search_form_tab_slug" value=""/>
                <i><?php echo __('Only a-z letters allowed.', 'ba-book-everything'); ?></i>
                <br />
               <?php 
               
               $categories = BABE_Post_types::get_categories_arr();
               if ( !empty($categories) ){
                 ?>
                <label><?php echo __('Apply to categories (check nothing for all):', 'ba-book-everything'); ?></label><ul id="search_form_tab_categoties">
                 <?php
                 foreach ( $categories as $category_id => $category_title){
                
                   echo '<li><input type="checkbox" id="tab_category_'.$category_id.'" name="tab_category['.$category_id.']" value="'.$category_id.'" /><label for="tab_category_'.$category_id.'">'.$category_title.'</label></li>';
                
                 }
                 ?>
                 </ul>
                 <?php
               }  
            
               ?>
               <span id="add_search_tab" class="button-primary btn"><?php echo __('Add new Tab', 'ba-book-everything'); ?></span> <span class="spinner" id="search_form_tabs_spinner"></span><span id="search_form_tabs_spinner_done" class="task_done"><?php echo __('Done!', 'ba-book-everything'); ?></span>
            </div>
            <br />
            <hr />
            <h3><?php echo __('General settings', 'ba-book-everything'); ?></h3>
            <?php
            echo '<input type="checkbox" id="show_price_slider" name="show_price_slider" value="1"'. (!empty(BABE_Search_From::$search_form_general_settings['show_price_slider']) ? ' checked="checked"' : '' ) .'/>';

            echo '<label for="show_price_slider">'.__('Show price range picker in Advanced field', 'ba-book-everything').'</label>';
            ?>
            <br />
            <?php
            echo '<input type="checkbox" id="set_default_date_from" name="set_default_date_from" value="1"'. (!empty(BABE_Search_From::$search_form_general_settings['set_default_date_from']) ? ' checked="checked"' : '' ) .'/>';

            echo '<label for="set_default_date_from">'.__('Set the current date in the "date from" field by default', 'ba-book-everything').'</label>';
            ?>
            <br />
            <?php
            echo '<label for="set_default_date_to_in_days">'.__('Set "date to" to N days from the current date by default (leave blank to avoid setting a default date)', 'ba-book-everything').'</label>';
            echo '<input type="text" id="set_default_date_to_in_days" name="set_default_date_to_in_days" value="'
                . (!empty(BABE_Search_From::$search_form_general_settings['set_default_date_to_in_days'])
                    ? BABE_Search_From::$search_form_general_settings['set_default_date_to_in_days'] : '' )
                .'"/>';
            ?>
            <br />
            <br />
            <hr />

            <h3><?php echo __('Managing search form fields', 'ba-book-everything'); ?></h3>
            
            <table id="search-form-fields-table">
            <?php echo self::get_search_form_fields_thead().self::get_search_form_fields_list(); 
            ?>
            </table>
            <br />
            <div id="search-form-fields-update">
              <span id="update_search_fields" class="button-primary btn"><?php echo __('Save changes in fields', 'ba-book-everything'); ?></span> <span class="spinner" id="search_form_fields_spinner"></span><span id="search_form_fields_spinner_done" class="task_done"><?php echo __('Done!', 'ba-book-everything'); ?></span>
            </div>
            
            <?php echo '
            <div id="babe_overlay_container">
            <div id="confirm_del_tab" class="babe_overlay_inner">
              <span id="modal_close"><i class="fa fa-remove"></i></span>
              <h1>'.__('Delete selected tab?', 'ba-book-everything').'</h1>
                  <input type="button" name="cancel" id="cancel" class="button babe-button-1" value="'.__('Cancel', 'ba-book-everything').'">
                  <input type="button" name="delete" id="delete" class="button babe-button-2" value="'.__('Delete', 'ba-book-everything').'">
            </div>
            </div>
            <div id="babe_overlay"></div>';
            ?>
            
            
            
        </div>
        <?php
    }
    
//////////////ajax_delete_tab/////////    
    /**
	 * Delete selected rate.
	 */
    public static function ajax_delete_tab(){
        
        if ( !empty($_POST['tab_slug']) && isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ){
            
             $tabs = BABE_Search_From::$search_form_tabs;
             
             $tab_slug = sanitize_key($_POST['tab_slug']);
           
             $tab_slug = str_replace('-', '_', $tab_slug);
             
             if ($tab_slug){
                
                unset($tabs[$tab_slug]);
                BABE_Search_From::update_search_form_tabs($tabs);
                
                $search_form_fields = BABE_Search_From::$search_form_fields;
                unset($search_form_fields[$tab_slug]);
                BABE_Search_From::update_search_form_fields($search_form_fields);
                
             }
        }
        
        $output['tabs'] = self::get_search_form_tab_list();
        $output['fields'] = self::get_search_form_fields_thead().self::get_search_form_fields_list();
        
        echo json_encode($output);
        wp_die();  
    }    
    
///////////////////////////////////////    
    /**
	 * Add new tab
	 */
    public static function ajax_add_tab(){
        
        $output = [
            'tabs' => __('An error occurred while processing...', 'ba-book-everything'),
            'fields' => '',
        ];
        
        if ( isset($_POST['tab_title']) && isset($_POST['tab_slug']) && isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title )){
           
           $tab_title = sanitize_text_field($_POST['tab_title']);
           
           $tab_slug = sanitize_key($_POST['tab_slug']);
           
           $tab_slug = str_replace('-', '_', $tab_slug);
           
           $tab_category = !empty($_POST['tab_category']) && is_array($_POST['tab_category']) ? array_map('absint', $_POST['tab_category']) : [];
           
           if ( $tab_title && $tab_slug ){
            
              self::add_search_form_tab( $tab_title, $tab_slug, $tab_category );
              
              $tabs = BABE_Search_From::$search_form_tabs;
              unset($tabs[0]);
              BABE_Search_From::update_search_form_tabs($tabs);
            
              $output['tabs'] = self::get_search_form_tab_list();
              $output['fields'] = self::get_search_form_fields_thead().self::get_search_form_fields_list();
            
           } else {
            
              $output['tabs'] = __('Please, fill in the correct data', 'ba-book-everything');
            
           }
        }
        
        echo json_encode($output);
        wp_die();
        
    }
    
//////////////////////////////////////    
    /**
	 * Add search form tab
     * 
     * @param string $tab_title
     * @param string $tab_slug
     * @param array $tab_category
     * 
     * @return
	 */
   public static function add_search_form_tab( $tab_title, $tab_slug, $tab_category ){
    
      $tabs = is_array(BABE_Search_From::$search_form_tabs) ? BABE_Search_From::$search_form_tabs : [];
      
      $tabs[$tab_slug] = [
          'title' => $tab_title,
          'categories' => $tab_category,
      ];
    
      BABE_Search_From::update_search_form_tabs($tabs);
      
      return;
   }
   
///////////////////////////////////////    
    /**
	 * Reorder tabes
	 */
    public static function ajax_tabs_reorder(){
        
        if ( !empty($_POST['tab_orders']) && is_array($_POST['tab_orders']) && isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ){
             
             $tab_orders = array_map('absint', $_POST['tab_orders']);
             
             /// replaces the values of the first array with values having the same keys in the second array.
             $tabs = array_replace($tab_orders, BABE_Search_From::$search_form_tabs);
             
             BABE_Search_From::update_search_form_tabs($tabs);
                          
        }
        
        $output['tabs'] = self::get_search_form_tab_list();
        $output['fields'] = self::get_search_form_fields_thead().self::get_search_form_fields_list();
        
        echo json_encode($output);
        wp_die();                   
    }          
    
//////////////////////////////////////    
    /**
	 * Search form tab list
     * 
     * @return string
	 */
    public static function get_search_form_tab_list(){
       $output = '';
       
       if ( !empty(BABE_Search_From::$search_form_tabs) ){
        
          $i = 1;
          
          $categories_all = BABE_Post_types::get_categories_arr();
            
          foreach ( BABE_Search_From::$search_form_tabs as $tab_slug => $tab_arr){
            
            $tab_categories_list = '';
            
            if ( !empty($tab_arr['categories']) ){
                
                $tab_categories_arr = [];
                
                foreach ($tab_arr['categories'] as $cat_id => $val){
                    if ( empty($categories_all[$cat_id]) ){
                        continue;
                    }
                    $tab_categories_arr[$cat_id] = $categories_all[$cat_id];
                }
                
                $tab_categories_list = !empty($tab_categories_arr) ? implode(', ', $tab_categories_arr) : __('All', 'ba-book-everything');
                
            } else {
                $tab_categories_list = __('All', 'ba-book-everything');
            }
            
            $output .= '
            <div class="search_form_tab_item" data-order="'.$i.'" data-tab-slug="'.$tab_slug.'">'.$tab_arr['title'].' ('.$tab_slug.')
                 <div class="search_form_tab_item_del"><i class="fas fa-trash-alt"></i></div>
                 <div class="search_form_tab_item_cats">'.__('categories:', 'ba-book-everything').' '.$tab_categories_list.'</div>
            </div>';
            
            $i++;
          }
       
       }
       
       return $output; 
    }    
    
//////////////////////////////////////    
    /**
	 * Search form fields list
     * 
     * @return string
	 */
   public static function get_search_form_fields_list(){
      
      $output = '';
      
      $tabs = empty(BABE_Search_From::$search_form_tabs) ? [
              0 => [
                  'title' => '',
                  'categories' => [],
              ]
      ] : BABE_Search_From::$search_form_tabs;
      
      $i = 1;

      foreach ( BABE_Search_From::$search_form_all_fields as $field_slug => $field_name){
        
          $output .= '
          <tr class="search-form-fields-row" data-field-slug="'.$field_slug.'" data-order="'.$i.'">
             <td class="search-form-fields-row-title"><h3>'.$field_name.'</h3>
                 <label for="search_field_icon_'.$field_slug.'">'.__('Icon classes:', 'ba-book-everything').'</label>
                 <input type="text" id="search_field_icon_'.$field_slug.'" name="search_field_icon_'.$field_slug.'" data-field-slug="'.$field_slug.'" value="'. ( isset(BABE_Search_From::$search_form_fields_icons[$field_slug]) ? esc_attr(BABE_Search_From::$search_form_fields_icons[$field_slug]) : '' ) .'">';

          if ( $field_slug !== 'date_from' && $field_slug !== 'date_to' && $field_slug !== 'guests'  ){
              $output .= '<br />
                    <input type="checkbox" id="search_field_advanced_tab_'.$field_slug.'" name="search_field_advanced_tab_'.$field_slug.'" data-field-slug="'.$field_slug.'" value="1"'. ( !empty(BABE_Search_From::$search_form_fields_advanced[$field_slug]) ? ' checked="checked"' : '' ) .'>
                    <label for="search_field_advanced_tab_'.$field_slug.'">'.__('advanced field', 'ba-book-everything').'</label>
                    ';
          }

          $output .= '
              </td>';

          foreach ( $tabs as $tab_ind => $tab_arr ){

              $data_str = 'data-tab-slug="'.$tab_ind.'" data-field-slug="'.$field_slug.'" data-field-arg';

              $output .= '
             <td class="search-form-fields-row-value">
                 <label for="search_field_title_'.$field_slug.'_'.$tab_ind.'">'.__('Title:', 'ba-book-everything').'</label>
                 <input type="text" id="search_field_title_'.$field_slug.'_'.$tab_ind.'" name="search_field_title_'.$field_slug.'_'.$tab_ind.'" '.$data_str.'="title" value="'. ( isset(BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['title']) ? esc_attr(BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['title']) : $field_name ) .'"><br />';

              if ( $field_slug !== 'date_from' ){

                  $output .= '
                    <input type="checkbox" id="search_field_active_'.$field_slug.'_'.$tab_ind.'" name="search_field_active_'.$field_slug.'_'.$tab_ind.'" '.$data_str.'="active" value="1"'. ( isset(BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['active']) && BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['active'] ? ' checked="checked"' : '' ) .'>
                    <label for="search_field_active_'.$field_slug.'_'.$tab_ind.'">'.__('active', 'ba-book-everything').'</label><br />
                    ';

              } else {

                  $output .= '
                    <input type="hidden" id="search_field_active_'.$field_slug.'_'.$tab_ind.'" name="search_field_active_'.$field_slug.'_'.$tab_ind.'" '.$data_str.'="active" value="'. ( isset(BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['active']) ? BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['active'] : 1 ) .'">';

              }

              if ($field_slug === 'date_from' || $field_slug === 'date_to' || $field_slug === 'guests'){

                  $option_title = $field_slug == 'guests' ? __('with ages', 'ba-book-everything') : __('add time', 'ba-book-everything');

                  $output .= '
                    <input type="checkbox" id="search_field_extended_'.$field_slug.'_'.$tab_ind.'" name="search_field_extended_'.$field_slug.'_'.$tab_ind.'" '.$data_str.'="extended" value="1"'. ( isset(BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['extended']) && BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['extended'] ? ' checked="checked"' : '' ) .'>
                    <label for="search_field_extended_'.$field_slug.'_'.$tab_ind.'">'.esc_html($option_title).'</label><br />
                    ';

              } else {

                  $output .= '
                    <input type="hidden" id="search_field_extended_'.$field_slug.'_'.$tab_ind.'" name="search_field_extended_'.$field_slug.'_'.$tab_ind.'" '.$data_str.'="extended" value="'. ( isset(BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['extended']) ? BABE_Search_From::$search_form_fields[$tab_ind][$field_slug]['extended'] : 0 ) .'">';

              }

              $output .= '</td>';

          }
          
          $output .= '
          </tr>
          ';
          
          $i++;
      }
      
      
      $output = '<tbody>'.$output.'</tbody>'; 
        
      return $output; 
   }
   
//////////////////////////////////////    
    /**
	 * Search form fields table header
     * 
     * @return string
	 */
    public static function get_search_form_fields_thead(){
       $output = '';
       
       $output .= '
       <thead>
          <tr class="search-form-fields-thead">
          <th>'.__('Feild name', 'ba-book-everything').'</th>';
            
       if ( !empty(BABE_Search_From::$search_form_tabs) ){
            
          foreach ( BABE_Search_From::$search_form_tabs as $tab_slug => $tab_arr){
            $output .= '<th>'.$tab_arr['title'].'</th>';
          }
       
       } else {
          $output .= '<th>'.__('- without tabs -', 'ba-book-everything').'</th>';
       }
       
       $output .= '
          </tr>
       </thead>';
            
       return $output; 
    }
           
///////////////////////////////////////    
    /**
	 * Update fields
	 */
    public static function ajax_update_fields(){
        
        $output = '';
        
        if (
                isset($_POST['input_arr'])
                && isset($_POST['fields_orders'])
                && isset($_POST['fields_icons'])
                && isset($_POST['fields_advanced'])
                && isset($_POST['nonce'])
                && wp_verify_nonce( $_POST['nonce'], self::$nonce_title )
        ){

            $fields_icons = [];
            if ( is_array($_POST['fields_icons']) ){
                foreach ($_POST['fields_icons'] as $fields_icon_slug => $fields_icon_val){
                    $fields_icons[ sanitize_key($fields_icon_slug) ] = sanitize_text_field($fields_icon_val);
                }
            }

           $fields_advanced = $_POST['fields_advanced'];
            $fields_advanced = [];
            if ( is_array($_POST['fields_advanced']) ){
                foreach ($_POST['fields_advanced'] as $fields_advanced_slug => $fields_advanced_val){
                    $fields_advanced[ sanitize_key($fields_advanced_slug) ] = $fields_advanced_val ? 1 : 0;
                }
            }

            $fields_orders = [];
            if ( is_array($_POST['fields_orders']) ){
                foreach ($_POST['fields_orders'] as $fields_order_slug => $fields_order_val){
                    $fields_orders[ sanitize_key($fields_order_slug) ] = (int)$fields_order_val;
                }
            }

           $general_settings = BABE_Search_From::$search_form_general_settings;
           $general_settings['show_price_slider'] = !empty($_POST["show_price_slider"]) ? 1 : 0;
           $general_settings['set_default_date_from'] = !empty($_POST["set_default_date_from"]) ? 1 : 0;
           $general_settings['set_default_date_to_in_days'] = !empty($_POST["set_default_date_to_in_days"]) ? (int)$_POST["set_default_date_to_in_days"] : '';

           if ( !empty($_POST['input_arr']) && is_array($_POST['input_arr']) ){

               foreach ($_POST['input_arr'] as $tab_slug => $tab_arr){

                   $tab_slug = sanitize_key($tab_slug);

                   if ( empty($tab_arr) || !is_array($tab_arr) ){
                       continue;
                   }

                   foreach ( $tab_arr as $field_slug => $field_arr){

                       $field_slug = sanitize_key($field_slug);

                       if ( empty($field_arr) || !is_array($field_arr) ){
                           continue;
                       }

                       foreach ( $field_arr as $field_arg => $value){

                           $field_arg = sanitize_key($field_arg);

                           if ( $field_arg !== 'title' ){
                               $input_arr[$tab_slug][$field_slug][$field_arg] = (int)$value;
                           } else {
                               $input_arr[$tab_slug][$field_slug][$field_arg] = sanitize_text_field($value);
                           }
                       }
                   }
               }

               BABE_Search_From::update_search_form_fields($input_arr);

               BABE_Search_From::update_search_form_fields_icons($fields_icons);

               BABE_Search_From::update_search_form_fields_advanced($fields_advanced);

               BABE_Search_From::update_search_form_fields_order($fields_orders);

               BABE_Search_From::update_search_form_general_settings($general_settings);

               BABE_Search_From::$search_form_all_fields = BABE_Search_From::reorder_all_fields( BABE_Search_From::$search_form_all_fields, $fields_orders);
           } 
           
        }
        
        $output = self::get_search_form_fields_thead().self::get_search_form_fields_list();
        
        echo $output;
        wp_die();
        
    }
/////////////////////////////////////
/////////////////////////////////////
    
}

BABE_Search_From_admin::init();
