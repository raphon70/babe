<?php
/**
 * Add widget all-items to Elementor
 *
 * @since   1.3.13
 */
 
class BABE_Elementor_Itemslideshow_Widget extends \Elementor\Widget_Base {

    /**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'babe-item-slideshow';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Item slideshow', 'ba-book-everything' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-slides';
	}
    
    /**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'slideshow' ];
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

	    return;

	}
    
    /**
     * Create shortcode row
	 * 
	 * @return string
	 */
	public function create_shortcode() {

		$settings = $this->get_settings_for_display();
        
        return '[babe-item-slideshow]';

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
         if (jQuery('#unitegallery').length > 0){
             jQuery('#unitegallery').unitegallery( babe_lst.unitegallery_args );
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
