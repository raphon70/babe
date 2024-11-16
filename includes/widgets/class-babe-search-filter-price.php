<?php

class BABE_Search_filter_price extends WP_Widget {

function __construct() {
    
parent::__construct(
// Base ID of your widget
'babe_search_filter_price',
// Widget name will appear in UI
__('BA Price Filter', 'ba-book-everything'),
// Widget description
array( 'description' => __('Shows a price filter slider in a widget which lets you narrow down the list of shown items', 'ba-book-everything'), )
);

}

// Creating widget front-end
// This is where the action happens
function widget( $args, $instance ) {

    if ( !isset($_GET['request_search_results']) && !isset($_POST['request_search_results']) ){
        return;
    }

    wp_enqueue_script( 'jquery-ui-slider' );
    wp_register_script( 'babe-price-slider', plugins_url( "js/babe-price-slider.js", BABE_PLUGIN ), array('jquery-ui-slider'), BABE_VERSION, true );

    wp_localize_script( 'babe-price-slider', 'babe_price_slider', array(
        'currency_symbol' 	=> BABE_Currency::get_currency_symbol(),
        'currency_pos'      => BABE_Currency::get_currency_place(),
        'min_price'			=> isset( $_GET['min_price'] ) ? esc_attr( $_GET['min_price'] ) : '',
        'max_price'			=> isset( $_GET['max_price'] ) ? esc_attr( $_GET['max_price'] ) : ''
    ) );

    wp_enqueue_script( 'babe-price-slider' );

    $min = (int)BABE_Post_types::get_posts_range_price($_GET, 'min');
    $max = (int)BABE_Post_types::get_posts_range_price($_GET, 'max');

    if ( ! isset( $args['widget_id'] ) ) {
        $args['widget_id'] = $this->id;
    }

    extract( $args );
    $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

    echo $before_widget;
    if ( $title ) {
        echo $before_title . $title . $after_title;
    }

    echo '<div class="widget-babe-price-slider">
        <div class="babe_price_slider_label">
          <input type="text" id="babe_range_price" readonly data-min="'.$min.'" data-max="'.$max.'">
        </div>
        
        <div class="babe_price_slider"></div>
     
     </div>';

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
function babe_load_widget_search_filter_price() {
	register_widget( 'BABE_Search_filter_price' );
}
add_action( 'widgets_init', 'babe_load_widget_search_filter_price' );
