<?php

class BABE_Search_form extends WP_Widget {

function __construct() {
parent::__construct(
// Base ID of your widget
'babe_widget_search_form',
// Widget name will appear in UI
__('BA Search Form', 'ba-book-everything'),
// Widget description
array( 'description' => __('Show Search form', 'ba-book-everything'), )
);
}

// Creating widget front-end
// This is where the action happens
function widget( $args, $instance ) {
  Global $post;
    
  if ( ! isset( $args['widget_id'] ) ) {
        $args['widget_id'] = $this->id;
    }
    
  extract( $args );
  $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
  
     echo $before_widget;

     echo BABE_html::get_search_form($title);

     echo $after_widget; 
}

// Updating widget
function update( $new_instance, $old_instance ) {
$instance = $old_instance;
$instance['title'] = strip_tags($new_instance['title']);
return $instance;
}

// Widget Backend
 function form( $instance ) {

    if (isset($instance['title'])) $title = esc_attr($instance['title']);
      else $title = '';

        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
<?php
}

} // Class ends here

// Register and load the widget
function babe_load_widget_search_form() {
	register_widget( 'BABE_Search_form' );
}
add_action( 'widgets_init', 'babe_load_widget_search_form' );
