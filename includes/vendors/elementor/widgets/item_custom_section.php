<?php
/**
 * Add widget all-items to Elementor
 *
 * @since   1.6.3
 */
 
class BABE_Elementor_ItemCustomSection_Widget extends \Elementor\Widget_Base {

    /**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'babe-item-custom-section';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Custom section', 'ba-book-everything' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-text-area';
	}
    
    /**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'description' ];
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

	    $this->start_controls_section(
			'babe_item_custom_section',
			array(
				'label' => __( 'Content', 'ba-book-everything' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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
        $args_row = !empty($settings['title']) ? ' title="'.esc_attr($settings['title']).'"' : '';

        return '[babe-item-custom-section'.$args_row.']';
	}

	/**
	 * Render widget output on the frontend.
	 * Written in PHP and used to generate the final HTML.
	 */
	protected function render() {
		echo do_shortcode( $this->create_shortcode() );
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
