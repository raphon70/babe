<?php
/**
 * Add widget all-items to Elementor
 *
 * @since   1.3.13
 */
 
class BABE_Elementor_Itemaddressmap_Widget extends \Elementor\Widget_Base {

    /**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'babe-item-address-map';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Item address map', 'ba-book-everything' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-google-maps';
	}
    
    /**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'address', 'map' ];
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'book-everything-elements' ];
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 */
	protected function register_controls() {
       
       /////////////////////

	    $this->start_controls_section(
			'babe_item_address_map',
			array(
				'label' => __( 'Content', 'ba-book-everything' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

        $this->add_control(
            'title',
            array(
                'label' => esc_html__( 'Section title', 'ba-book-everything' ),
                'description' => esc_html__( 'Optional', 'ba-book-everything' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'input_type' => 'text',
                'placeholder' => '',
            )
        );

		$this->end_controls_section();

	}
    
    /**
     * Create shortcode row
	 * 
	 * @return string
	 */
	public function create_shortcode() {

		$settings = $this->get_settings_for_display();
        
        $args_row = '';

        $args_row .= $settings['title'] ? ' title="'.esc_attr($settings['title']).'"' : '';

        return '[babe-item-address-map'.$args_row.']';

	}

	/**
	 * Render widget output on the frontend.
	 * Written in PHP and used to generate the final HTML.
	 */
	protected function render() {

		echo do_shortcode( $this->create_shortcode() );

        if ( is_admin() ){
            echo "
         <script>
          if (jQuery('#block_address_map').length > 0){
              init_address_map('#google_map_address');
          }
          function init_address_map(map_div){
        
        var dom_obj = jQuery(map_div)[0];
        var post_id = jQuery(map_div).data('obj-id');
        var var_lat = parseFloat(jQuery(map_div).data('lat'));
        var var_lng = parseFloat(jQuery(map_div).data('lng'));
        var address = jQuery(map_div).data('address'); 
        
        var map = new google.maps.Map(dom_obj, {
          center: {lat: var_lat, lng: var_lng},
        //  mapTypeControl: false,
        //  panControl: false,
        //  streetViewControl: false,
          zoom: parseInt(babe_lst.start_zoom)
        });
        
        var infowindow = new google.maps.InfoWindow();
        var marker = new google.maps.Marker({
          map: map,
          position: {lat: var_lat, lng: var_lng}
        });
        
        marker.setIcon(/** @type {google.maps.Icon} */({
            url: babe_lst.marker_icon,
            size: new google.maps.Size(40, 50),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(20, 50)
          }));
          
          marker.setShape({
                coords: [1, 1, 1, 50, 40, 50, 40, 1],
                type: 'poly'
            });
          infowindow.setContent('<div>' + address + '</div>');
          infowindow.open(map, marker);     
        
    }
         </script>";
        }

        return;

	}
    
    /**
	 * Render widget as plain content.
	 * Override the default behavior by printing the shortcode instead of rendering it.
	 */
	 public function render_plain_content() {
	   
		// In plain mode, render shortcode name and params
		echo $this->create_shortcode();
        
	 }

}
/////////////////////
