<?php
/**
 * Add widget all-items to Elementor
 *
 * @since   1.3.13
 */
 
class BABE_Elementor_Allitems_Widget extends \Elementor\Widget_Base {

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        wp_enqueue_style( 'babe-admin-elementor-style', plugins_url( "css/admin/babe-admin-elementor.css", BABE_PLUGIN ));
    }
    
    /**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'babe-all-items';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'All items', 'ba-book-everything' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-archive-posts';
	}
    
    /**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'item', 'items', 'all', 'products', 'product', 'book everything' ];
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
	   
       // Get all terms of categories
       $categories = BABE_Post_types::get_categories_arr();
       
       $item_titles = [ 0 => __( 'All', 'ba-book-everything' ) ];
       
       $items = get_posts( [
            'post_type'              => BABE_Post_types::$booking_obj_post_type,
			'posts_per_page'         => -1,
			'post_status'            => 'publish',
			'cache_results'          => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
            'suppress_filters' => false,
       ] );
       if ( !empty($items) ){
        foreach($items as $item){
            $item_titles[$item->ID] = get_the_title($item->ID);
        }
       }
       
       /////////////////////

	    $this->start_controls_section(
			'babe_allitems',
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

        $this->add_control(
            'fa_icon',
            array(
                'label' => esc_html__( 'Title Icon', 'claue' ),
                'description' => esc_html__( 'Optional', 'ba-book-everything' ),
                'exclude_inline_options' => ['svg'],
                'is_svg_enabled' => false,
                'skin' => 'inline',
                'type' => \Elementor\Controls_Manager::ICONS,
            )
        );

        $this->add_control(
            'text',
            array(
                'label' => esc_html__( 'Section text', 'claue' ),
                'description' => esc_html__( 'Optional. Will be displayed before items', 'ba-book-everything' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 10,
            )
        );

        $this->add_control(
            'category_ids',
            array(
                'label' => esc_html__( 'Item Category', 'ba-book-everything' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $categories,
                'default' => '0',
            )
        );

        $this->add_control(
            'ids',
            array(
                'label' => esc_html__( 'Items', 'ba-book-everything' ),
                'description' => esc_html__( 'Show selected items only. Input item title to see suggestions', 'ba-book-everything' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $item_titles,
            )
        );

        $this->add_control(
            'per_page',
            array(
                'label' => esc_html__( 'Per Page', 'ba-book-everything' ),
                'description' => esc_html__( 'How much items per page to show (-1 to show all items)', 'ba-book-everything' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'input_type' => 'text',
                'placeholder' => '',
                'default' => '12',
            )
        );
        
        $this->add_control(
			'sort',
			array(
				'label' => esc_html__( 'Order By', 'ba-book-everything' ),
                'description' => '',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'rating' => esc_html__( 'Rating', 'ba-book-everything' ),
					'price_from' => esc_html__( 'Price from', 'ba-book-everything' ),
				),
				'default' => 'rating',
			)
		);
        
        $this->add_control(
			'sortby',
			array(
				'label' => esc_html__( 'Order', 'ba-book-everything' ),
                'description' => esc_html__( 'Designates the ascending or descending order. Default by DESC', 'ba-book-everything') ,
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'ASC' => esc_html__( 'Ascending', 'ba-book-everything' ),
					'DESC' => esc_html__( 'Descending', 'ba-book-everything' ),
				),
				'default' => 'DESC',
			)
		);

        $this->add_control(
            'date_from',
            array(
                'label' => esc_html__( 'Date from', 'ba-book-everything' ),
                'description' => esc_html__( 'Show items which are available from selected date.', 'ba-book-everything' ),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'picker_options' => [
                    'dateFormat' => BABE_Settings::$settings['date_format'],
                    'enableTime' => false,
                ],
            )
        );

        $this->add_control(
            'date_to',
            array(
                'label' => esc_html__( 'Date to', 'ba-book-everything' ),
                'description' => esc_html__( 'Show items which are available up to selected date.', 'ba-book-everything' ),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'picker_options' => [
                    'dateFormat' => BABE_Settings::$settings['date_format'],
                    'enableTime' => false,
                ],
            )
        );

        $this->add_control(
			'classes',
			array(
				'label' => esc_html__( 'Extra class name', 'ba-book-everything' ),
                'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'ba-book-everything' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => '',
			)
		);

        $this->add_control(
            'bg_img_id',
            array(
                'label' => esc_html__( 'Section background image', 'ba-book-everything' ),
                'description' => esc_html__( 'Optional', 'ba-book-everything' ),
                'type' => \Elementor\Controls_Manager::MEDIA,
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

        $args_row .= $settings['sort'] ? ' sort="'.esc_attr($settings['sort']).'"' : '';
        $args_row .= $settings['sortby'] ? ' sortby="'.esc_attr($settings['sortby']).'"' : '';

        $args_row .= !empty($settings['category_ids']) ? ' category_ids="'.esc_attr( implode(',', $settings['category_ids']) ).'"' : '';

        $args_row .= !empty($settings['ids']) ? ' ids="'.esc_attr( implode(',', $settings['ids']) ).'"' : '';
        
        $args_row .= absint($settings['per_page']) ? ' per_page="'. (int)$settings['per_page'] .'"' : '';

        $args_row .= $settings['date_from'] ? ' date_from="'.esc_attr($settings['date_from']).'"' : '';

        $args_row .= $settings['date_to'] ? ' date_to="'.esc_attr($settings['date_to']).'"' : '';

        ///////////////////////

        $args_row .= $settings['title'] ? ' title="'.esc_attr($settings['title']).'"' : '';

        $args_row .= $settings['fa_icon'] ? ' fa_icon="'.esc_attr($settings['fa_icon']['value']).'"' : '';

        $args_row .= !empty($settings['bg_img_id']['id']) ? ' bg_img_id="'.esc_attr($settings['bg_img_id']['id']).'"' : '';
        
        $args_row .= $settings['classes'] ? ' classes="'.esc_attr($settings['classes']).'"' : '';
        
       // error_log('$settings: '.print_r($settings, 1));
        
        return '[all-items'.$args_row.']'.wp_kses_post($settings['text']).'[/all-items]';

	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 */
	protected function render() {

		echo do_shortcode( $this->create_shortcode() );

        return;

	}
    
    /**
	 * Render widget as plain content.
	 *
	 * Override the default behavior by printing the shortcode instead of rendering it.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	 public function render_plain_content() {
	   
		// In plain mode, render shortcode name and params
		echo $this->create_shortcode();
        
	 }

}
/////////////////////
