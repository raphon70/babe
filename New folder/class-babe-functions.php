<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

BABE_Functions::init();

/**
 * BABE_Functions Class.
 * 
 * @class 		BABE_Functions
 * @version		1.3.0
 * @author 		Booking Algorithms
 */

class BABE_Functions {
    
    ///// cache
    public static string $timezone = '';
    private static $is_wpml_active;
    
//////////////////////////////////////

    public static function init() {
        add_action( 'init', array( __CLASS__, 'init_timezone'), 0 );
    }

    /**
	 * Get range select options.
	 */ 
     public static function get_range_select_options($start, $end, $step = 1, $value = false){
     
        $output = '';
      
        for($i = $start; $i <= $end; $i += $step){
           $output .= '<option value="'. $i .'" '. selected( $value, $i, false ) .'>'. $i .'</option>';
        }
        return $output;
     }
     
//////////////////////////////////////
     /**
	 * Array map recursive
     * 
     * @param string $callback
     * @param array $input
     * @return array
	 */ 
     public static function array_map_r($callback, $input) {
        
        $output = array();
        foreach ($input as $key => $data) {
            if (is_array($data)) {
                $output[$key] = self::array_map_r($callback, $data);
            } else {
                $output[$key] = $callback($data);
            }
        }
        return $output;
    }

    public static function sanitize_r($callback, $input) {

        $output = array();
        foreach ($input as $key => $data) {
            $key = sanitize_key($key);
            if (is_array($data)) {
                $output[$key] = self::sanitize_r($callback, $data);
            } else {
                $output[$key] = $callback($data);
            }
        }
        return $output;
    }
     
//////////////////////////////////////
     /**
	 * Compare arrays by 'order' field for usort() ASC
     * @param array $a
     * @param array $b
     * @return int
	 */ 
     public static function compare_arrays_by_order_asc($a, $b){
        return (int)$a['order'] - (int)$b['order'];
     }     
     
//////////////////////////////////////
     /**
	 * Compare dates for usort() ASC
     * @param string $date_1 - format Y-m-d H:i
     * @param string $date_2 - format Y-m-d H:i
     * @return int
	 */ 
     public static function compare_sql_dates_asc($date_1, $date_2){
     
        $ad = new DateTime($date_1);
        $bd = new DateTime($date_2);
        
        if ($ad == $bd) {
            return 0;
        }
        return $ad < $bd ? -1 : 1;
     }
     
//////////////////////////////////////
     /**
	 * Compare dates for usort() DESC
     * @param string $date_1 - format Y-m-d H:i
     * @param string $date_2 - format Y-m-d H:i
     * @return int
	 */ 
     public static function compare_sql_dates_desc($date_1, $date_2){
     
        $ad = new DateTime($date_1);
        $bd = new DateTime($date_2);
        
        if ($ad == $bd) {
            return 0;
        }
        return $ad > $bd ? -1 : 1;
     }
     
//////////////////////////////////////
     /**
	 * Calculate number of hours between 2 dates
     * 
     * @param string $date_1 - format Y-m-d H:i
     * @param string $date_2 - format Y-m-d H:i
     * 
     * @return int
	 */ 
     public static function dates_diff_hours($date_start, $date_end){
     
        $date1 = new DateTime($date_start);
        $date2 = new DateTime($date_end);
        
        //determine what interval should be used - can change to weeks, months, etc
        $interval = new DateInterval('PT1H');
        
        $periods = new DatePeriod($date1, $interval, $date2);
        $hours = iterator_count($periods);
        return $hours;
     }
     
//////////////////////////////////////
     /**
	 * Get now DateTime obj with current timezone
     * 
     * @return DateTime
	 */ 
     public static function datetime_local( $date_string = '' ){
         self::init_timezone();
         return new DateTime($date_string, new DateTimeZone(self::$timezone));
     }

     public static function init_timezone(){

         if (self::$timezone){
             return;
         }

         $timezone = get_option('timezone_string');
         $wp_offset = get_option('gmt_offset');

         if (!$timezone && $wp_offset){

             $sign = $wp_offset > 0 ? '+' : '-';
             $min = 60*abs($wp_offset);

             $h = floor($min/60);
             $h = $h < 10 ? '0'.$h : $h;

             $m = $min%60;
             $m = $m < 10 ? '0'.$m : $m;

             $timezone = $sign.$h.':'.$m;

         } elseif (!$timezone) {
             $timezone = 'UTC';
         }

         self::$timezone = $timezone;
     }
     
//////////////////////////////    
    /**
	 * Close tags
     * @return string
	 */
    public static function closetags($html) {
        
    preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    for ($i=0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</'.$openedtags[$i].'>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
    
    }
    
///////////////////////////
    /**
	 * Get distance between two points (given the latitude/longitude of those points).
     * @param float $lat1, $lon1 = Latitude and Longitude of point 1 (in decimal degrees)
     * @param float $lat2, $lon2 = Latitude and Longitude of point 2 (in decimal degrees)
     * @param string $unit = the unit you desire for results:
     * 'M' is statute miles (default), 'K' is kilometers, 'N' is nautical miles
     * @return float
	 */
    public static function distance($lat1, $lon1, $lat2, $lon2, $unit = 'K') {
        
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit === "K") {
            return ($miles * 1.609344);
        }

        if ($unit === "N") {
            return ($miles * 0.8684);
        }

        return $miles;
    }

///////////////////////////////////////
    /**
	 * Get page url with args.
     * @param int $post_id
     * @param array $args
     * @return string
	 */
    public static function get_page_url_with_args($post_id, $args = array()) {

        $url = $post_id ? get_permalink($post_id) : '';

        if ( !empty($args) && $url ){
            $args = self::sanitize_r('sanitize_text_field', $args);
            $query = defined('PHP_QUERY_RFC3986') ? http_build_query($args, "", '&', PHP_QUERY_RFC3986) : http_build_query($args);
            $addon = strpos( $url, '?' ) !== false ? '&' : '?';
            $url = $url.$addon.$query;
        }

        return $url;
    }    
    
///////////////////////////////////////
    /**
	 * Get page content.
     * @param int $post_id
     * @return string
	 */
    public static function get_page_content($post_id) {
        
        $post = get_post($post_id);
        return !empty($post) ? apply_filters('the_content', $post->post_content) : '';
    }    
         
///////////////////////////////////////

/**
 * Get template, passing attributes and including the file.
 *
 * @param string $template_name
 * @param array $args
 * @param string $template_dir
 * @param string $default_path
 * @return void
 */
public static function get_template( $template_name, $args = array(), $template_dir = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = self::locate_template( $template_name, $template_dir, $default_path );

	if ( ! file_exists( $located ) ) {
		return;
	}

	$located = apply_filters( 'babe_get_template', $located, $template_name, $args, $template_dir, $default_path );

	do_action( 'babe_before_template_part', $template_name, $template_dir, $located, $args );

	include( $located );

	do_action( 'babe_after_template_part', $template_name, $template_dir, $located, $args );
}

///////////////////////////////////////////////

/**
 * Get the HTML template
 * @param string $template_name
 * @return string
 */
public static function get_template_html( $template_name, $args = array(), $template_dir = '', $default_path = '' ) {
	ob_start();
	self::get_template( $template_name, $args, $template_dir, $default_path );
	return ob_get_clean();
}

///////////////////////////////////////////////
/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_dir	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @param string $template_name
 * @param string $template_dir
 * @param string $default_path
 * @return string
 */
public static function locate_template( $template_name, $template_dir = '', $default_path = '' ) {
    
	if ( ! $template_dir ) {
		$template_dir = BABE_PLUGIN_SLUG;
	}

	if ( ! $default_path ) {
		$default_path = BABE_PLUGIN_DIR . '/templates/';
	}

	// Look theme first
	$template = locate_template(
		array(
			trailingslashit( $template_dir ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'babe_locate_template', $template, $template_name, $template_dir );
}

//////////////////////////////////
/**
 * Get the pager HTML
 * @param int $max_num_pages
 * @return string
 */
public static function pager($max_num_pages){

     $pl_args = array(
     'base'     => add_query_arg('paged','%#%'),
     'format'   => '?paged=%#%',
     'total'    => $max_num_pages,
     'current'  => max(1, get_query_var('paged')),
     //How many numbers to either side of current page, but not including current page.
     'end_size' => 1,
     //Whether to include the previous and next links in the list or not.
     'mid_size' => 2,
     'prev_text' => __('&laquo; Previous', 'ba-book-everything'), // text for previous page
     'next_text' => __('Next &raquo;', 'ba-book-everything'), // text for next page
     );
      
     $pl_args = apply_filters('babe_pager_args', $pl_args);

     return '<div class="babe_pager">'.paginate_links($pl_args).'</div>';
}

///////////////////////////////////////
    /**
     * Get current language code, two letters
     *
     * @return string
     */
    public static function get_current_language() {

        return strtolower(
            substr(
                apply_filters( 'wpml_current_language', get_locale() ),
                0,
                2
            )
        );
    }

///////////////////////////////////////
    /**
     * Get default language code, two letters
     *
     * @return string
     */
    public static function get_default_language() {

        $wp_lang = strtolower(substr(get_locale(), 0, 2));

        return apply_filters('wpml_default_language', $wp_lang );
    }

///////////////////////////////////////
    /**
     * Get all languages def
     *
     * @return array [ $language_code => [
    'native_name' => string,
    'translated_name' => string,
    'language_code' => string,
    'url' => string, // current page URL
    ] ]
     */
    public static function get_all_languages() {

        global $wp;

        $current_language = self::get_current_language();
        $current_language = apply_filters( 'wpml_current_language', $current_language );

        $current_language_name = strtoupper($current_language);

        $languages = [
            $current_language => [
                'native_name' => $current_language_name,
                'translated_name' => $current_language_name,
                'language_code' => $current_language_name,
                'url' => home_url( $wp->request ),
            ]
        ];

        $languages = apply_filters( 'wpml_active_languages', $languages );

        $args = !empty($_GET) ? self::array_map_r( 'urlencode', $_GET) : [];

        foreach( $languages as $language_code => $language_desc ){
            $languages[$language_code]['url'] = add_query_arg( $args, $language_desc['url'] );
        }

        return $languages;
    }

///////////////////////////////////////
    /**
     * Get post ID in current language
     *
     * @param int $post_id
     * @param string $post_type
     * @param boolean $return_original_if_missing
     *
     * @return int
     */
    public static function get_current_language_post_id( $post_id, $post_type = '', $return_original_if_missing = true){

        $post_type = $post_type ? $post_type : get_post_type($post_id);

        return apply_filters( 'wpml_object_id', $post_id, $post_type, $return_original_if_missing );
    }

    /////////////////////////////////
    /**
     * @param object[] $terms
     * @param string   $lang
     * @param string   $taxonomy
     * @param bool     $duplicate sets whether missing terms should be created by duplicating the original term
     *
     * @return array
     */
    public static function wpml_get_translated_term_ids( $terms, $lang, $taxonomy, $duplicate = true ) {

        if ( !class_exists('WPML_Terms_Translations') ){
            return [];
        }

        /** @var WPML_Term_Translation $wpml_term_translations */
        global $wpml_term_translations;

        $term_utils = new WPML_Terms_Translations();
        $wpml_term_translations->reload();
        $translated_terms = [];
        foreach ( $terms as $orig_term ) {
            $translated_id = (int) $wpml_term_translations->term_id_in( $orig_term->term_id, $lang );
            if ( ! $translated_id && $duplicate ) {
                $translation   = $term_utils->create_automatic_translation(
                    array(
                        'lang_code'       => $lang,
                        'taxonomy'        => $taxonomy,
                        'trid'            => $wpml_term_translations->get_element_trid( $orig_term->term_taxonomy_id ),
                        'source_language' => $wpml_term_translations->get_element_lang_code(
                            $orig_term->term_taxonomy_id
                        ),
                    )
                );
                $translated_id = isset( $translation['term_id'] ) ? $translation['term_id'] : false;
                self::clone_term_meta( $orig_term->term_id, $translated_id );
                if ( !empty($orig_term->description) ){
                    wp_update_term($translated_id, $taxonomy, [ 'description' => $orig_term->description ]);
                }
            }
            if ( $translated_id ) {
                $translated_terms[] = $translated_id;
            }
        }

        return $translated_terms;
    }

    /////////////////////////////////
    /**
     * @param int $term_id_from
     * @param int $term_id_to
     * @return void
     */
    public static function clone_term_meta( $term_id_from, $term_id_to ) {

        global $wpdb;

        $sql         = "SELECT meta_key, meta_value FROM {$wpdb->termmeta} WHERE term_id=%d";
        $values_from = $wpdb->get_results(
            $wpdb->prepare(
                $sql,
                array( $term_id_from )
            ),
            ARRAY_A
        );

        $delete_prepared = $wpdb->prepare(
            "DELETE FROM {$wpdb->termmeta}
						WHERE term_id=%d",
            array( $term_id_to )
        );
        $wpdb->query( $delete_prepared );

        if ( empty($values_from) ){
            wp_cache_init();
            return;
        }

        $values = [];
        foreach ( $values_from as $term_meta ) {
            $values[] = $wpdb->prepare(
                '( %d, %s, %s )',
                $term_id_to,
                $term_meta['meta_key'],
                $term_meta['meta_value']
            );
        }

        $values = implode( ', ', $values );
        $wpdb->query(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "INSERT INTO {$wpdb->termmeta}(term_id, meta_key, meta_value) VALUES {$values}"
        );

        wp_cache_init();
    }

///////////////////////////////////////
    /**
     * Check if WPML plugin is active
     *
     * @return boolean
     */
    public static function is_wpml_active() {

        if ( self::$is_wpml_active === null ){
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            self::$is_wpml_active = is_plugin_active( 'sitepress-multilingual-cms/sitepress.php');
        }

        return self::$is_wpml_active;
    }

////////////////////////////////
    /**
     * Validate card number by Luhn algorithm
     *
     * @param string $ciphertext
     * @return boolean
     */
    public static function is_valid_card_number( $card_number ) {

        $card_number=preg_replace('/\D/', '', $card_number);

        if (empty($card_number)){
            return false;
        }

        $card_number = (string)$card_number;
        $strlen = strlen($card_number);

        if ( $strlen < 12 || $strlen > 19 ){
            return false;
        }

        $sum = 0;
        $flag = 0;

        for ($i = strlen($card_number) - 1; $i >= 0; $i--) {
            $add = (($flag++) & 1) ? $card_number[$i] * 2 : $card_number[$i];
            $sum += $add > 9 ? $add - 9 : $add;
        }

        return $sum % 10 === 0;
    }

    ///////////////////

    /**
     * @param string $card_number
     * @return string
     */
    public static function card_number_mask( $card_number )
    {
        return substr($card_number, 0, 6). str_repeat('*', strlen($card_number) - 10) . substr($card_number, -4);
    }

    /**
     * Format services in ['service_id' => [ 'age_id' => 'service_qty'] ]
     */
    public static function normalize_order_services( array $services, array $guests ): array
    {
        reset($services);
        if ( !empty($services) && empty(key($services)) ){
            $new_services = [];
            foreach ($services as $service_id){
                foreach( $guests as $age_id => $guest_num){
                    $new_services[$service_id][$age_id] = $guest_num;
                }
            }
            $services = $new_services;
        }
        return $services;
    }
    
///////////////////////////////////////
}
