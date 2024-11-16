<?php

class BABE_Search_filter_terms extends WP_Widget {

function __construct() {
parent::__construct(
// Base ID of your widget
'babe_widget_search_filter_terms',
// Widget name will appear in UI
__('BA Search filter', 'ba-book-everything'),
// Widget description
array( 'description' => __('Filter search results by terms from selected taxonomy', 'ba-book-everything'), )
);
}

// Creating widget front-end
// This is where the action happens
function widget( $args, $instance ) {
  Global $post;
    
    if (isset($_GET['request_search_results']) || isset($_POST['request_search_results'])){
    
    if ( ! isset( $args['widget_id'] ) ) {
        $args['widget_id'] = $this->id;
    }
    
  extract( $args );
  $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
  
  $taxonomy_id = isset($instance['taxonomy_id']) ? (int)$instance['taxonomy_id'] : 0;
      echo $before_widget;
      if ( $title ) {
        echo $before_title . $title . $after_title;
      }
     
     if (isset(BABE_Post_types::$taxonomies_list[$taxonomy_id])){
        
     $taxonomy = BABE_Post_types::$taxonomies_list[$taxonomy_id]['slug'];
     $id = 'filter_'.$taxonomy;  
      
     $args = array(
		'taxonomy' => $taxonomy,
		'level' => 0,
        'view' => 'multicheck', // 'select', 'multicheck' or 'list'
        'id' => $id,
        'class' => 'babe-search-filter-terms',
        'name' => $id,
        'term_id_name' => 'term_taxonomy_id',
	  );
      
      $selected_arr = isset($_GET['terms']) ? (array)$_GET['terms'] : array();
      $selected_arr = array_map('intval', $selected_arr); 

     echo '<div class="widget-babe-search-filter-terms">'.BABE_Post_types::get_terms_children_hierarchy($args, $selected_arr).'</div>';
     
     }

     echo $after_widget;
     
     }
}

// Updating widget
function update( $new_instance, $old_instance ) {
$instance = $old_instance;
$instance['title'] = strip_tags($new_instance['title']);
$instance['taxonomy_id'] = intval($new_instance['taxonomy_id']);
return $instance;
}

// Widget Backend
 function form( $instance ) {

    if (isset($instance['title'])) $title = esc_attr($instance['title']);
      else $title = '';
      
    $select_taxonomy = '';  
    $selected_taxonomy_id = isset($instance['taxonomy_id']) ? intval($instance['taxonomy_id']) : 0;
      
    foreach(BABE_Post_types::$taxonomies_list as $taxonomy_id => $taxonomy){
           $select_taxonomy .= '<option value="'.$taxonomy_id.'" '.selected($selected_taxonomy_id, $taxonomy_id, 0).'>'.$taxonomy['name'].'</option>';
    }  

        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        
        <p>
          <label for="<?php echo $this->get_field_id('taxonomy_id'); ?>"><?php _e('Taxonomy:'); ?></label>
          <select class="widefat" id="<?php echo $this->get_field_id('taxonomy_id'); ?>" name="<?php echo $this->get_field_name('taxonomy_id'); ?>"><?php echo $select_taxonomy; ?>
          </select>
        </p>
<?php
}

} // Class ends here

// Register and load the widget
function babe_load_widget_search_filter_terms() {
	register_widget( 'BABE_Search_filter_terms' );
}
add_action( 'widgets_init', 'babe_load_widget_search_filter_terms' );
