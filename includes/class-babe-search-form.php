<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Search_From Class.
 * Search form constructor
 * 
 * @class 		BABE_Search_From
 * @version		1.3.19
 * @author 		Booking Algorithms
 */

class BABE_Search_From {
    
    public static $option_search_form_tabs = 'babe_search_form_tabs';

    public static $option_search_form_general_settings = 'babe_search_form_general_settings';

    public static $option_search_form_fields = 'babe_search_form_fields';
    
    public static $option_search_form_fields_order = 'babe_search_form_fields_order';
    
    public static $option_search_form_fields_icons = 'babe_search_form_fields_icons';

    public static $option_search_form_fields_advanced = 'babe_search_form_fields_advanced';

    public static $option_language_suffix = '';
    
    public static $search_form_all_fields = [];

    public static $search_form_general_settings = [];

    public static $search_form_tabs = [];
    
    public static $search_form_fields = [];
    
    public static $search_form_fields_order = [];
    
    public static $search_form_fields_icons = [];

    public static $search_form_fields_advanced = [];
    
///////////////////////////////////////    
    public static function init() {
        
        add_action( 'init', array( __CLASS__, 'init_settings' ), 5 );
              
	}

//////////////////////////////////////    
    /**
	 * Init settings
     * 
     * @return void
	 */
   public static function init_settings(){

       self::$option_language_suffix = '_' . BABE_Functions::get_current_language();

       self::$search_form_general_settings = self::get_search_form_option(self::$option_search_form_general_settings);

       self::$search_form_fields = self::get_search_form_option(self::$option_search_form_fields);

       self::$search_form_tabs = self::get_search_form_option(self::$option_search_form_tabs);

       self::$search_form_fields_order = self::get_search_form_option(self::$option_search_form_fields_order);

       self::$search_form_fields_icons = self::get_search_form_option(self::$option_search_form_fields_icons);

       self::$search_form_fields_advanced = self::get_search_form_option(self::$option_search_form_fields_advanced);
      
      //// all fields ////
      
      foreach ( BABE_Post_types::$taxonomies_list as $taxonomy ){
          self::$search_form_all_fields[$taxonomy['slug']] = $taxonomy['name'];
      }
      
      self::$search_form_all_fields['keyword'] = __( 'Keyword', 'ba-book-everything' );
      self::$search_form_all_fields['date_from'] = __( 'Date from', 'ba-book-everything' );
      self::$search_form_all_fields['date_to'] = __( 'Date to', 'ba-book-everything' );
      self::$search_form_all_fields['guests'] = __( 'Guests', 'ba-book-everything' );
      
      self::$search_form_all_fields = self::reorder_all_fields(self::$search_form_all_fields, self::$search_form_fields_order);
      
      self::$search_form_all_fields = apply_filters('babe_search_form_all_fields', self::$search_form_all_fields);
   }
   
//////////////////////////////////////    
    /**
	 * Reorder all fields
     * 
     * @param array $fields
     * @param array $fields_order
     * 
     * @return array
	 */
   public static function reorder_all_fields($fields, $fields_order){

       if ( empty($fields_order) || empty($fields) ){
           return $fields;
       }

       $prepend_arr = array();
       $reordered_arr = array();

       foreach( $fields as $field_slug => $field_name){

           if ( isset($fields_order[$field_slug]) ){
               $reordered_arr[$field_slug] = $field_name;
           } else {
               $prepend_arr[$field_slug] = $field_name;
           }
       }

       $fields = array_merge($prepend_arr, $reordered_arr);

       return $fields;
   }
   
////////////////////////////////

    public static function update_search_form_option($option, $value){
        update_option($option . self::$option_language_suffix, $value);
        return apply_filters($option, $value);
    }

    public static function get_search_form_option( $option ){

        $option_value = get_option($option . self::$option_language_suffix, false);

        if ( $option_value === false ){

            $option_value = get_option($option, []);
            if ( !empty($option_value) ){
                self::update_search_form_option($option, $option_value);
            } elseif ( $option === self::$option_search_form_fields_icons ) {
                $option_value = [
                    'keyword' => 'fas fa-search',
                    'date_from' => 'far fa-calendar-alt',
                    'date_to' => 'far fa-calendar-alt',
                    'guests' => 'far fa-user',
                ];
            }
        }

        return apply_filters($option, $option_value);
    }

    public static function update_search_form_fields($search_form_fields){
      self::$search_form_fields = self::update_search_form_option(self::$option_search_form_fields, $search_form_fields);
   }

   public static function update_search_form_tabs($search_form_tabs){
      self::$search_form_tabs = self::update_search_form_option(self::$option_search_form_tabs, $search_form_tabs);
   }

   public static function update_search_form_fields_icons($search_form_fields_icons){
      self::$search_form_fields_icons = self::update_search_form_option(self::$option_search_form_fields_icons, $search_form_fields_icons);
   }

   public static function update_search_form_fields_advanced($search_form_fields_advanced){
        self::$search_form_fields_advanced = self::update_search_form_option(self::$option_search_form_fields_advanced, $search_form_fields_advanced);
    }

    public static function update_search_form_fields_order($search_form_fields_order){
      self::$search_form_fields_order = self::update_search_form_option(self::$option_search_form_fields_order, $search_form_fields_order);
   }

   public static function update_search_form_general_settings($general_settings){
      self::$search_form_general_settings = self::update_search_form_option(self::$option_search_form_general_settings, $general_settings);
   }

//////////////////////////////////////    
    /**
	 * Render search form
     * 
     * @param string $title
     * @param array $form_args
     * 
     * @return string
	 */
   public static function render_form( $title = '', $form_args = [] ){
    
      $output = $advanced_tab = $price_range_field = '';
      
      $form_args = wp_parse_args( $form_args, [
            'wrapper_class' => '',
            'form_class' => '',
            'button_title' => __( 'Search', 'ba-book-everything' ),
            ]
      );

       $date_from = isset($_GET['date_from']) && BABE_Calendar_functions::isValidDate($_GET['date_from'], BABE_Settings::$settings['date_format']) ? sanitize_text_field($_GET['date_from']) : '';
       if (
           empty($date_from)
           && !empty(self::$search_form_general_settings['set_default_date_from'])
       ){
           $date_from = (BABE_Functions::datetime_local())->format(BABE_Settings::$settings['date_format']);
       }

       $date_to = isset($_GET['date_to']) && BABE_Calendar_functions::isValidDate($_GET['date_to'], BABE_Settings::$settings['date_format']) ? sanitize_text_field($_GET['date_to']) : '';

       if (
           empty($date_to)
           && !empty(self::$search_form_general_settings['set_default_date_from'])
           && !empty(self::$search_form_general_settings['set_default_date_to_in_days'])
       ){
           $date_to = (BABE_Functions::datetime_local())
               ->modify('+' . self::$search_form_general_settings['set_default_date_to_in_days'] . 'days')
               ->format(BABE_Settings::$settings['date_format']);
       }

       $search_keyword = isset($_GET['keyword']) ? sanitize_text_field($_GET['keyword']) : '';

      $action = isset($_GET['same_page']) ? '' : BABE_Settings::get_search_result_page_url();

       $same_page = isset($_GET['same_page']) ? '<input type="hidden" name="same_page" value="'.absint($_GET['same_page']).'">' : '';

       $min_price_value = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
       $max_price_value = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
      
      $min_price = $min_price_value ? '<input type="hidden" name="min_price" value="'.esc_attr($min_price_value).'">' : '';
      $max_price = $max_price_value ? '<input type="hidden" name="max_price" value="'.esc_attr($max_price_value).'">' : '';
      
      $search_filter_sort_by = isset($_GET['search_results_sort_by']) && isset(BABE_Post_types::$search_filter_sort_by_args[$_GET['search_results_sort_by']]) ? '
          <input type="hidden" name="search_results_sort_by" value="'.esc_attr($_GET['search_results_sort_by']).'">
        ' : '<input type="hidden" name="search_results_sort_by" value="title_asc">';
        
      $hidden_fields = '<input type="hidden" name="request_search_results" value="1">
                '.$same_page.$min_price.$max_price.$search_filter_sort_by;  
        
       $taxonomy_selected_arr = array();      
                
       //// place search form code here
       /////// Tab section //////////////
        
        $tabs = !empty(self::$search_form_tabs) && is_array(self::$search_form_tabs) ? self::$search_form_tabs : [ 0 => [
         'title' => '',
         'categories' => [],
            ]
        ];
        
        reset($tabs);
        $tab_active_key = key($tabs);
        $tab_active_key = isset($_GET['search_tab']) && isset($tabs[$_GET['search_tab']]) ? sanitize_key($_GET['search_tab']) : $tab_active_key;
        
        $tab_section = '';
                
        if ( !empty(self::$search_form_tabs) ){
            /// create a tabs
            $tab_section .= '<div id="search_form_tabs">';
            
            foreach ($tabs as $tab_slug => $tab_arr){
                
                $tab_classes = 'search_form_tab';
                
                $tab_classes .= $tab_active_key === $tab_slug ? ' is-active' : '';
                
                $tab_section .= '<span class="'.$tab_classes.'" data-tab-slug="'.$tab_slug.'" data-tab-categories="'.implode(',', $tab_arr['categories']).'">'.$tab_arr['title'].'</span>';
                
            }
            
            $tab_section .= '</div>';
            
            $hidden_fields .= '<input type="hidden" name="search_tab" value="'.esc_attr($tab_active_key).'">';
        }
        
        $tab_section = apply_filters('babe_search_form_tab_section', $tab_section, $tabs, $tab_active_key);
        
        $fields = !empty( self::$search_form_fields ) && is_array(self::$search_form_fields) ? self::$search_form_fields : [ 0 => [
            'date_from' => [
                'title' => __( 'Date from', 'ba-book-everything' ),
                'active' => 1,
                'extended' => 0,
                ],
            'date_to' => [
                'title' => __( 'Date to', 'ba-book-everything' ),
                'active' => 1,
                'extended' => 0,
                ],
            ]
        ];

	   ///// create fields structure array

       $fields_advanced = self::$search_form_fields_advanced;

       $tabs_with_advanced_bar = [];

	   $output_arr = array();

	   foreach ($fields as $tab_slug => $field_arr){

		   foreach ($field_arr as $field_slug => $field){

			   if ($field['active']){
                   $output_arr[$field_slug]['advanced_tab'] = empty($fields_advanced[$field_slug]) ? 0 : 1;

                   if ($output_arr[$field_slug]['advanced_tab']){
                       $tabs_with_advanced_bar[$tab_slug] = 1;
                   }

				   $output_arr[$field_slug]['data']['active'][$tab_slug] = $output_arr[$field_slug]['advanced_tab'] ? 0 : 1;
                   $output_arr[$field_slug]['data']['advanced-active'][$tab_slug] = $output_arr[$field_slug]['advanced_tab'];
				   $output_arr[$field_slug]['data']['title'][$tab_slug] = $field['title'];
				   if ($tab_active_key == $tab_slug){
					   $output_arr[$field_slug]['title'] = $field['title'];
					   if ( !$output_arr[$field_slug]['advanced_tab'] ){
						   $output_arr[$field_slug]['is-active'] = 1;
					   }
				   }
				   if ($field['extended']){
					   $output_arr[$field_slug]['extended'][$tab_slug] = $tab_slug;
				   }
			   }
		   }
	   }
        
        /////////Build search form ////////
        
        $form_title = $title ? '<div class="search-box-header">
					<h3>'.$title.'</h3>	
				</div>' : '';
                
        $form_title = apply_filters('babe_search_form_header', $form_title, $title);        
        
        $output .= '<div id="search-box" class="babe-search-box '.esc_attr($form_args['wrapper_class']).'">						
				'.$form_title.'
                '.$tab_section.'
                <form name="search_form" action="'.esc_attr($action).'" method="get" id="search_form" class="babe-search-form '.esc_attr($form_args['form_class']).'">
			<div class="input-group">';
        
        ////////////////
        
        $time_select_arr = array();
        $time_date_obj = DateTime::createFromFormat('H:i', '00:00');
        $time_format = get_option('time_format');
        
        for($i=0; $i <= 23; $i++){
            $i_text = $i < 10 ? '0'.$i : $i;
            $time_key = $i_text.':00';
            $time_select_arr[$time_key] = $time_date_obj->format($time_format);
            $time_date_obj->modify('+1 hour');
        }
        
        $age_select_arr = array();
        for($i=0; $i <= BABE_Settings::$settings['max_guests_select']; $i++){
            $age_select_arr[$i] = $i;
        }
        
        $main_age_id = BABE_Post_types::get_main_age_id();
        
        $search_form_fields_order = empty(self::$search_form_fields_order) ? $output_arr : self::$search_form_fields_order;
        
        $output_arr = apply_filters('babe_search_form_fields_structure', $output_arr, $search_form_fields_order);

	    $data_str_adv = 'data-inputfield="1" tabindex="0"';

        if ( !empty(self::$search_form_general_settings['show_price_slider']) ){

            $min_price_available = (int)BABE_Post_types::get_posts_range_price($_GET, 'min');
	        $max_price_available = (int)BABE_Post_types::get_posts_range_price($_GET, 'max');
	        $current_min = $min_price_value ? $min_price_value : $min_price_available;
	        $current_max = $max_price_value ? $max_price_value : $max_price_available;

	        foreach ($tabs as $tab_slug => $data_val){
		        $data_str_adv .= ' data-active-' . $tab_slug . '="1"';
		        $data_str_adv .= ' data-title-' . $tab_slug . '="' .__( 'Advanced', 'ba-book-everything' ) . '"';
	        }

	        $price_range_field .= '
				                    <div class="advanced-price-content">
				                    <h4 class="price-header">' .__( 'Price', 'ba-book-everything' ) . '</h4>
				                        <input type="text" class="search-price-range-slider babe-price-range-slider" name="search_price_range" value="'.(!empty($_GET["search_price_range"]) ? esc_attr($_GET["search_price_range"]) : '').'" 
				                            data-type="double"
				                            data-skin="round"
									        data-prefix="' . BABE_Currency::get_currency_symbol()  . '"
									        data-min="' . $min_price_available . '"
									        data-max="' . $max_price_available . '"
									        data-from="' . $current_min . '"
									        data-to="' . $current_max . '"/>
									</div>
				                    ';
        } else {

        	foreach ($tabs_with_advanced_bar as $tab_slug => $data_val){
		        $data_str_adv .= $data_val ? ' data-active-' . $tab_slug . '="' . $data_val . '"' : '';
	        }
        }

        foreach ($search_form_fields_order as $field_slug => $value){
            
          if ( isset($output_arr[$field_slug]) ){

	          $field_data = $output_arr[ $field_slug ];

	          $add_class                 = isset( $field_data['is-active'] ) ? ' is-active' : '';
	          $add_class_advanced_active = '';
	          $field_title               = isset( $field_data['title'] ) ? $field_data['title'] : '';
	          $data_advanced_str = $data_str = 'data-inputfield="1" tabindex="0"';
	          foreach ( $field_data['data']['active'] as $tab_slug => $data_val ) {
		          $data_str .= ' data-active-' . $tab_slug . '="' . $data_val . '"';
	          }
	          if ( $field_data['advanced_tab'] ) {
		          foreach ( $field_data['data']['advanced-active'] as $tab_slug => $data_val ) {
			          $data_advanced_str .= $data_val ? ' data-active-' . $tab_slug . '="' . $data_val . '"' : '';
			          if ($tab_active_key == $tab_slug){
                          $add_class_advanced_active = ' is-active';
                      }
		          }
	          } else {
		          $data_advanced_str = $data_str;
	          }
	          foreach ( $field_data['data']['title'] as $tab_slug => $data_val ) {
		          $data_advanced_str .= ' data-title-' . $tab_slug . '="' . $data_val . '"';
		          $data_str          .= ' data-title-' . $tab_slug . '="' . $data_val . '"';
	          }
            
              $icon = !empty(self::$search_form_fields_icons[$field_slug]) ? apply_filters('babe_search_form_taxonomy_before_title', '<i class="'.esc_attr(self::$search_form_fields_icons[$field_slug]).'"></i>', $field_slug, self::$search_form_fields_icons) : '';

	        if ( $field_data['advanced_tab'] && $field_slug !== 'keyword'){
            	/// build multicheck taxonomy for advanced tab
                $selected = isset( $_GET['terms']) && is_array($_GET['terms']) ?  array_map('absint', $_GET['terms']) : [];
	            if ($selected) {
	                foreach($selected as $selected_value){
                        $taxonomy_selected_arr[(int)$selected_value] = (int)$selected_value;
                    }
	            }

	            $taxonomy = get_taxonomy( $field_slug );

	            if ( !empty( $taxonomy ) ) {

                    $add_class_advanced_active .= ' ba_search_taxonomy_'.$field_slug;

		            $field_title = $taxonomy->labels->all_items;

		            $args = array(
			            'taxonomy'            => $field_slug,
			            'parent_term_id'      => 0,
			            'view'                => 'multicheck', // 'select', 'multicheck' or 'list'
			            'add_count'           => '',
			            'option_all'          => 1,
			            'option_all_value'    => 0,
			            'option_all_title'    => $field_title,
			            'id'                  => 'add_ids_list_' . $field_slug,
			            'class'               => 'add_ids_list',
			            'class_item'          => 'term_item',
			            'class_item_selected' => 'term_item_selected',
			            'name'                => 'terms',
			            'prefix_char'         => ' ',
			            'term_id_name'        => 'term_id',
		            );

		            $advanced_tab .= '
		            <div class="advanced-taxonomy-block '.$add_class_advanced_active.'" '.$data_advanced_str.'">
		            <h4 class="advanced-header">' .esc_html($field_title) .'</h4>
                            ' . BABE_Post_types::get_terms_children_hierarchy( $args, $selected, false) . '
					</div>';
	            }

            } elseif ( $field_slug === 'keyword' ) {
		          $field_title = $data_val;
		          $search_field = '
                <div class="search-keyword-block'. ( $field_data['advanced_tab'] ? $add_class_advanced_active.'" '.$data_advanced_str : $add_class.'" '.$data_str ) .' data-tax="'.$field_slug.'" tabindex="0">
                    '.$icon.'<input type="text" class="search-keyword" id="'.$field_slug.'" name="'.$field_slug.'" value="'.esc_attr($search_keyword).'" placeholder="'.apply_filters('babe_search_keyword_form_date_from_title', $field_title, $field_data).'">
				</div>';

		          if ( $field_data['advanced_tab'] ){
                      $advanced_tab .= $search_field;
		          } else {
			          $output .= $search_field;
		          }

            } elseif ( $field_slug === 'date_from' || $field_slug === 'date_to' ){

	            if ( $field_slug === 'date_from' ){
                    $output .= apply_filters('babe_search_form_before_date_fields', '');
                    $date_value = $date_from;
                } else {
                    $date_value = $date_to;
                }
                
                $output .= '
                    <div class="search-date search_date_wrapper_'.$field_slug.$add_class.'" '.$data_str.'>
                        '.$icon.'<input type="text" class="search_date" id="'.$field_slug.'" name="'.$field_slug.'" value="'.esc_attr($date_value).'" placeholder="'.apply_filters('babe_search_form_date_from_title', $field_title, $field_data).'">
					</div>';
                    
                if ( isset($field_data['extended']) ){
                    
                    $data_str_ext = 'data-inputfield="1"';
                    $add_class_ext = '';
                    foreach ($field_data['extended'] as $tab_slug => $tab_slug_2){
                        $data_str_ext .= ' data-active-'.$tab_slug.'="1"';
                        if ($tab_active_key == $tab_slug){
                            $add_class_ext = ' is-active';
                        }
                    }
                    
                    //// add time selection                   
                    $ext_field_name = $field_slug === 'date_from' ? 'time_from' : 'time_to';
                    $time_title = $field_slug === 'date_from' ? __( 'Start at', 'ba-book-everything' ) : __( 'End at', 'ba-book-everything' );
                    
                    $output .= BABE_html::input_select_field($ext_field_name, $time_title, $time_select_arr, (isset($_GET[$ext_field_name]) ? esc_attr($_GET[$ext_field_name]) : false), false, $add_class_ext, $data_str_ext );
                    
                }
                
                if ( !isset($output_arr['date_to']) || $field_slug === 'date_to' ){
                    $output .= apply_filters('babe_search_form_after_date_fields', '');
                }                   
                
            } elseif ( $field_slug === 'guests' ){
                
                $output_guests_div = '';
                $total_guests = 0;
                $holder_str_ext = '';
                $holder_class_ext = '';
                
                ///// guests selection
                $ages_arr = BABE_Post_types::get_ages_arr();

                $active_title = __( 'Guests', 'ba-book-everything' );
                
                if ( isset($field_data['extended']) && !empty($ages_arr) ){
                    
                    $data_str_ext = 'data-inputfield="1" tabindex="0"';
                    $add_class_ext = '';
                    foreach ($field_data['extended'] as $tab_slug => $tab_slug_2){
                        $data_str_ext .= ' data-active-'.$tab_slug.'="1"';
                        $holder_str_ext .= ' data-active-'.$tab_slug.'="1"';
                        if ($tab_active_key == $tab_slug){
                            $add_class_ext = ' is-active';
                            $holder_class_ext = ' is-active';
                        }
                    }
	                foreach ( $field_data['data']['title'] as $tab_slug => $data_val ) {
		                $data_str_ext .= ' data-title-' . $tab_slug . '="' . $data_val . '"';
                        if ($tab_active_key == $tab_slug){
                            $active_title = $data_val;
                        }
	                }

                    foreach ($ages_arr as $age_term){
                       $age_selected_value = isset( $_GET['guests'][$age_term['age_id']] ) ? absint( $_GET['guests'][$age_term['age_id']] ) : 0;
                       
                       if ( !$age_selected_value && $age_term['age_id'] == $main_age_id && isset( $_GET['guests'][0] ) ){
                          $age_selected_value = absint($_GET['guests'][0]);
                       }
                       
                       $total_guests += $age_selected_value;
                       
                       //$output_guests_div .= BABE_html::input_select_field('guests['.$age_term['age_id'].']', $age_term['name'], $age_select_arr, $age_selected_value, false, $add_class_ext, $data_str_ext );
                       
                       $output_guests_div .= '
                       <div class="input_select_field input_select_field_guests'.$add_class_ext.'" data-name="guests['.esc_attr($age_term['age_id']).']" '.$data_str_ext.'>
                          <span class="select_guests_value">'.esc_html($age_selected_value).'</span>
                          <span class="select_guests_title">'.esc_html($age_term['name']).'</span>
                          <span class="search_guests_plus btn-search-guests-change btn btn-secondary-outlined" tabindex="0"><i class="fas fa-plus"></i></span>
                          <span class="search_guests_minus btn-search-guests-change btn btn-secondary-outlined" tabindex="0"><i class="fas fa-minus"></i></span>
                          <input type="hidden" class="select_guests_input_value" name="guests['.esc_attr($age_term['age_id']).']" value="'.esc_html($age_selected_value).'">
                       </div>';
                    
                    }
                    
                }
                    
                    $age_term = array(
                      'age_id' => 0, 
                      'name' => esc_html__( 'Guests', 'ba-book-everything' ),
                      'description' => '',
                    );

                    $data_str_ext = '';
                    $add_class_ext = '';
                    foreach ($field_data['data']['active'] as $tab_slug => $data_val ){
                         if (!isset($field_data['extended'][$tab_slug])){
                             $data_str_ext .= ' data-active-'.$tab_slug.'="1"';
                             $holder_str_ext .= ' data-active-'.$tab_slug.'="1"';
                             if ($tab_active_key == $tab_slug){
                                $add_class_ext = ' is-active';
                                $holder_class_ext = ' is-active';
                             }
                         }                     
                    }
		          foreach ( $field_data['data']['title'] as $tab_slug => $data_val ) {
			          $holder_str_ext .= ' data-title-' . $tab_slug . '="' . $data_val . '"';
                      $data_str_ext .= ' data-title-' . $tab_slug . '="' . $data_val . '"';
                      if ( !isset($field_data['extended'][$tab_slug]) && $tab_active_key == $tab_slug ){
                          $age_term['name'] = $data_val;
                          $active_title = $data_val;
                      }
		          }

                if ($data_str_ext){
                    
                   $data_str_ext = 'data-inputfield="1"'.$data_str_ext; 
                   
                   $age_selected_value = isset( $_GET['guests'][$age_term['age_id']] ) ? absint( $_GET['guests'][$age_term['age_id']] ) : 0;
                   
                   if ( !$age_selected_value && $main_age_id && isset( $_GET['guests'][$main_age_id] ) ){
                      $age_selected_value = $total_guests;
                   }
                   
                   if ($age_selected_value && !$total_guests){
                      $total_guests = $age_selected_value;
                   }
                   
                   //$output_guests_div .= BABE_html::input_select_field('guests['.$age_term['age_id'].']', $age_term['name'], $age_select_arr, $age_selected_value, false, $add_class_ext, $data_str_ext );
                   
                   $output_guests_div .= '
                       <div class="input_select_field input_select_field_guests'.$add_class_ext.'" data-name="guests['.esc_attr($age_term['age_id']).']" '.$data_str_ext.'>
                          <span class="select_guests_value">'.esc_html($age_selected_value).'</span>
                          <span class="select_guests_title">'.esc_html($age_term['name']).'</span>
                          <span class="search_guests_plus btn-search-guests-change btn btn-secondary-outlined" tabindex="0"><i class="fas fa-plus"></i></span>
                          <span class="search_guests_minus btn-search-guests-change btn btn-secondary-outlined" tabindex="0"><i class="fas fa-minus"></i></span>
                          <input type="hidden" class="select_guests_input_value" name="guests['.esc_attr($age_term['age_id']).']" value="'.esc_html($age_selected_value).'">
                       </div>';
                    
                }
                
                if ($output_guests_div){

	                $output .= '
                  <div class="search_guests_field'.$holder_class_ext.'"'.$holder_str_ext.' tabindex="0">
					  '.$icon.'<div ><div class="search_guests_title">'.esc_html($active_title).'</div>&#160;<span class="search_guests_title_value">'.esc_html($total_guests).'</span>
		                  <div class="search_guests_select_wrapper close_by_apply_btn">
		                      '.$output_guests_div.'
		                      <div class="search_guests_apply">
		                         <button class="btn button btn-primary search_apply_btn">'.esc_html__( 'Apply', 'ba-book-everything' ).'</button>
		                      </div>
		                  </div>	
	                  </div>
				  </div>
                  ';

                }
                
                ////////
            } else {
                //// add taxonomy select
                $selected = isset( $_GET['add_ids_'.$field_slug]) ? (int) $_GET['add_ids_'.$field_slug]  : 0;
                if ($selected) {
                    $taxonomy_selected_arr[$selected] = $selected;
                }
				preg_match('/(?<tax>\S*)(?<reuse>_reuse_)(?<index>.*)/', $field_slug, $matches);
                if (isset($matches['reuse'])){
                    $taxonomy = get_taxonomy( $matches['tax'] );
                } else {
                    $taxonomy = get_taxonomy( $field_slug );
                }
				
				if ( !empty( $taxonomy ) ) {
				    
                  $field_title = $field_title ? $field_title : $taxonomy->labels->all_items;  
				    
                  $selected_term_name = $field_title;
                  $selected_value = '';
                  
                  if ($selected){
                     $selected_term = get_term_by('id', $selected, (isset($matches['reuse'])? $matches['tax'] : $field_slug));
                     if (!empty($selected_term) && !is_wp_error($selected_term)){
                         $selected_term_name = $selected_term->name;
                         $selected_value = $selected;
                     }
                    
                  }
                  
                  $args = array(
                     'taxonomy' => isset($matches['reuse'])? $matches['tax'] : $field_slug,
                     'parent_term_id' => 0,
                     'view' => 'list', // 'select', 'multicheck' or 'list'
                     'add_count' => '',
                     'option_all' => 1,
                     'option_all_value' => 0,
                     'option_all_title' => $field_title,
                     'id' => 'add_ids_list_'.$field_slug,
                     'class' => 'add_ids_list',
                     'class_item' => 'term_item',
                     'class_item_selected' => 'term_item_selected',
                     'name' => '',
                     'prefix_char' => ' ',
                     'term_id_name' => 'term_id',
                  );
				
					$output .= '
					<div class="add_input_field'.$add_class.' " '.$data_str.' data-tax="'.$field_slug.'" tabindex="0">
                    '.$icon.'<div class="add_ids_title">
                        <div class="add_ids_title_value">' . esc_html($selected_term_name) . '</div><i class="fas fa-chevron-down"></i>
                        ' . BABE_Post_types::get_terms_children_hierarchy( $args, array($selected) ) . '
                        <input type="hidden" class="input_select_input_value" name="add_ids_'.$field_slug.'" value="'.esc_attr($selected).'">
                      </div>  	
					</div>
				';
				}  //// end if !empty( $taxonomy )
                
            }  //// end if $field_slug === 'date_from' || $field_slug === 'date_to'
           
           }  //// end if isset($output_arr[$field_slug])
        
        }  //// end foreach $search_form_fields_order
        
        ///////////////
        
      $terms = isset($_GET['terms']) && is_array($_GET['terms']) && !empty($_GET['terms']) ? array_map('intval', $_GET['terms']) : array();
      
      $term_inputs = '';
      if (!empty($terms)){
        foreach ($terms as $term_taxonomy_id){
            
            $add_term_input_class = isset($taxonomy_selected_arr[$term_taxonomy_id]) ? ' class="search_form_selected_terms"' : '';
            
            $term_inputs .= '
          <input type="hidden"'.$add_term_input_class.' name="terms['.esc_attr($term_taxonomy_id).']" value="'.esc_attr($term_taxonomy_id).'">
        ';
        }
      }
      $hidden_fields .= $term_inputs;
		
        ///////////////

	   if ( !empty($price_range_field) || !empty($advanced_tab)){
	   	$add_class = ' is-active';
		   $advanced_tab_title = empty($advanced_tab) ? esc_html__( 'Price', 'ba-book-everything' ): esc_html__( 'Advanced', 'ba-book-everything' );
		   $advanced_tab = '
					<div class="search_advanced_field ' . $add_class . '" ' . $data_str_adv . ' data-tax="advanced_tab" tabindex="0">
					    <i class="fas fa-filter"></i>
                        <div><div class="search_advanced_title">' . $advanced_tab_title . '</div>
	                        <div class="search_advanced_select_wrapper close_by_apply_btn">
	                        ' . $price_range_field . $advanced_tab . '
	                        <div class="search_guests_apply">
	                             <button class="btn button btn-primary search_apply_btn">'.esc_html__( 'Apply', 'ba-book-everything' ).'</button>
	                          </div>
	                      </div>
	                     </div>
					</div>
				';
	   }

        $output .= $advanced_tab . '
                    <div class="submit">
					  <button name="submit" class="btn button btn-primary btn-search" value="1"><i class="fas fa-search"></i> '.apply_filters('babe_search_form_submit_title', $form_args['button_title']).'</button>
				    </div>
				</div>
                
                '.apply_filters('babe_search_form_before_hidden_fields', '').'
                
                '.$hidden_fields.'   
		</form>
					</div>';
                    
      $output = apply_filters('babe_search_form_html', $output, $hidden_fields, $title);
      
      return $output;
   }                  

/////////////////////////////////////
/////////////////////////////////////
    
}

BABE_Search_From::init();
