<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Prices Class.
 * 
 * @class 		BABE_Prices
 * @version		1.4.22
 * @author 		Booking Algorithms
 */

class BABE_Prices {
    
    // DB tables
    public static $table_rate;
    
    public static $table_discount;
    
    private static $nonce_title = 'prices-tpl-nonce';
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {  
       global $wpdb;
       self::$table_rate = $wpdb->prefix.'babe_rates';
       self::$table_discount = $wpdb->prefix.'babe_discount';
       
       add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
       add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );
       
       add_action( 'wp_ajax_get_price_details_form', array( __CLASS__, 'ajax_get_price_details_form'));
       add_action( 'wp_ajax_get_price_details_block', array( __CLASS__, 'ajax_get_price_details_block'));
       add_action( 'wp_ajax_save_rate', array( __CLASS__, 'ajax_save_rate'));
       add_action( 'wp_ajax_delete_rate', array( __CLASS__, 'ajax_delete_rate'));
       add_action( 'wp_ajax_check_base_rate', array( __CLASS__, 'ajax_check_base_rate'));
       add_action( 'wp_ajax_rates_reorder', array( __CLASS__, 'ajax_rates_reorder'));
	}

    /**
	 * Enqueue assets
     * @return void
	 */
    public static function wp_enqueue_scripts() {

        if (
            !isset($_GET['inner_page'])
            || $_GET['inner_page'] !== 'edit-post'
            || empty($_GET['edit_post_id'])
            || !BABE_Users::current_user_can_edit_post(absint($_GET['edit_post_id']))
        ){
            return;
        }

        self::enqueue_scripts();
     }

    /**
	 * Enqueue assets admin
	 */
    public static function admin_enqueue_scripts() {
        
     global $current_screen;

        if (
            empty($current_screen->post_type)
            || !in_array($current_screen->post_type, [BABE_Post_types::$booking_obj_post_type, BABE_Post_types::$service_post_type])
            || !in_array($current_screen->base, ['post','post-new'])
        ){
            return;
        }

        self::enqueue_scripts();
     }

    /**
     * Enqueue assets
     */
    public static function enqueue_scripts() {

        wp_enqueue_script( 'babe-sweetalert', plugins_url( 'js/sweetalert/sweetalert2.all.min.js', BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );

        wp_enqueue_script( 'babe-prices', plugins_url( "js/admin/babe-admin-prices.js", BABE_PLUGIN ), array('jquery', 'babe-sweetalert'), BABE_VERSION, true );
        wp_localize_script( 'babe-prices', 'babe_prices_lst', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'date_format' => BABE_Settings::$settings['date_format'] === 'd/m/Y' ? 'dd/mm/yy' : 'mm/dd/yy',
                'nonce' => wp_create_nonce(self::$nonce_title),
                'messages' => [
                    'at_least_one_rate_required' => __('At least one rate is required to publish the post!', 'ba-book-everything'),
                    'are_you_sure' => _x('Are you sure?', 'admin modal messages', 'ba-book-everything'),
                    'ok' => _x('Ok', 'admin modal messages', 'ba-book-everything'),
                    'cancel' => _x('Cancel', 'admin modal messages', 'ba-book-everything'),
                    'oops' => _x('Oops...', 'admin modal messages', 'ba-book-everything'),
                    'something_wrong' => _x('Something went wrong!', 'admin modal messages', 'ba-book-everything'),
                    'page_will_be_reloaded' => _x('The page will be reloaded', 'admin modal messages', 'ba-book-everything'),
                    'done' => _x('Done!', 'admin modal messages', 'ba-book-everything'),
                    'save_rate' => _x('Save rate', 'admin modal messages', 'ba-book-everything'),
                    'delete' => _x('Delete', 'admin modal messages', 'ba-book-everything'),
                ],
            )
        );

        wp_enqueue_style( 'babe-prices', plugins_url( "css/admin/babe-admin-prices.css", BABE_PLUGIN ), array(), BABE_VERSION);
    }

//////////////////////////////
    /**
	 * Get rate for $booking_obj_id
     * @param int $rate_id
     * @param int $booking_obj_id
     * @return array
	 */
    public static function get_rate_by_id($rate_id, $booking_obj_id = 0) {
       global $wpdb;
       
       $clauses = '';
       
       if ($booking_obj_id){
           $clauses .= " AND booking_obj_id = ".(int)$booking_obj_id;
       }

        $rate = $wpdb->get_row("SELECT * FROM ".self::$table_rate." WHERE rate_id = ".absint($rate_id).$clauses, ARRAY_A);

        return array_map( 'maybe_unserialize', $rate);
	}

    /**
     * Get rates for $booking_obj_id.
     * @param int $booking_obj_id.
     * @param datetime $datetime_from - MySQL datetime YYYY-MM-DD HH:MM:SS.
     * @param datetime $datetime_to - MySQL datetime YYYY-MM-DD HH:MM:SS.
     * @return array
     */
    public static function get_rates($booking_obj_id, $datetime_from = '', $datetime_to = '') {
        global $wpdb;

        $clauses = '';

        if ($datetime_from){
            $clauses .= " AND ( date_to >= '".$datetime_from."' OR date_to IS NULL )";
        }

        if ($datetime_to){
            $clauses .= " AND ( date_from <= '".$datetime_to."' OR date_from IS NULL )";
        }

        if ($datetime_from && $datetime_to){
            $rate_date_from = new DateTime( $datetime_from );
            $rate_date_to = new DateTime( $datetime_to );
            if ($rate_date_to == $rate_date_from){
                $rate_date_to->modify('+1 day');
            }
            $d_interval = date_diff($rate_date_from, $rate_date_to);
            $days_total = (int)$d_interval->format('%a'); // total days
            // if < 7 days check the apply days
            if ( 0 < $days_total && $days_total < 7){
                $tmp_clauses_arr = [];
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($rate_date_from, $interval, $rate_date_to);
                foreach($daterange as $date){
                    $date_cal_day_num = BABE_Calendar_functions::get_week_day_num($date);
                    $tmp_clauses_arr[] = "LOCATE('i:".$date_cal_day_num.";', apply_days) > 0";
                }
                if ( !empty($tmp_clauses_arr) ){
                    $clauses .= " AND ( ".implode(' OR ', $tmp_clauses_arr)." )";
                }
            }
        }

        $rates = $wpdb->get_results("SELECT * FROM ".self::$table_rate." WHERE booking_obj_id = ".absint($booking_obj_id).$clauses." ORDER BY rate_order ASC, price_from ASC, date_from DESC, date_to DESC", ARRAY_A);

        return self::parse_db_rates_to_rates_arr($rates);
    }

    /**
     * @param array $rates
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @param array $guests
     * @return array
     */
    public static function selectRate(
        array $rates,
        DateTime $dateFrom,
        DateTime $dateTo,
        array $guests = []
    ){
        if ( empty($rates) ){
            return [];
        }

        $d_interval = date_diff($dateFrom, $dateTo);
        $days_total = (int)$d_interval->format('%a'); // total days
        if (!$days_total){
            $days_total = 1;
        }

        foreach ( $rates as $rate ){

            $returnRate = $rate;

            $rateDTO = RateDTO::instanceFromArray($rate);
            if ( !$rateDTO->minBookingPeriod && !$rateDTO->maxBookingPeriod ){
                return $returnRate;
            }

            if ($rateDTO->minBookingPeriod && $rateDTO->minBookingPeriod > $days_total){
                continue;
            }

            if ($rateDTO->maxBookingPeriod && $rateDTO->maxBookingPeriod < $days_total){
                continue;
            }

            if ( $days_total < 7 ){

            }

            return $returnRate;
        }

        return $returnRate;
    }

	//////////////////////////////////////
    /**
     * Parse rates from DB to rates array
     * @param array $rates
     * @return array
     */
    private static function parse_db_rates_to_rates_arr($rates){
       
       $rates_arr = array(); 

       foreach ($rates as $rate){
           $rates_arr[] = array_map( 'maybe_unserialize', $rate);
       }
       
       return $rates_arr;
    }

   /**
	 * Get conditional signs
     * 
     * @return array
	 */
    public static function get_conditional_signs() {
        
        return array(
                       1 => '>',
                       2 => '>=',
                       3 => '<',
                       4 => '<=',
                       5 => '=',
                       6 => '!=',
                    );
    }

   /**
	 * Get rate units
     * 
     * @param array $rules
     * @return array
	 */
    public static function get_rate_units($rules) {
        
        $output = array(
           'units' => '',
           'unit' => '',
        );
        
        if ($rules['basic_booking_period'] === 'night'){
            $output['units'] = __('nights', 'ba-book-everything');
            $output['unit'] = __('night', 'ba-book-everything');
        } elseif ($rules['basic_booking_period'] === 'day'){
            $output['units'] = __('days', 'ba-book-everything');
            $output['unit'] = __('day', 'ba-book-everything');
        } elseif ($rules['basic_booking_period'] === 'hour'){
            $output['units'] = __('hours', 'ba-book-everything');
            $output['unit'] = __('hour', 'ba-book-everything');
        }
                
        if ($output['unit']){
            if ($rules['booking_mode'] !== 'object'){
                $output['unit'] .= ' '.__('person', 'ba-book-everything');
            }
        } elseif ($rules['booking_mode'] !== 'object') {
            if ($rules['booking_mode'] === 'places'){
                $output['units'] = __('places', 'ba-book-everything');
                $output['unit'] = __('place', 'ba-book-everything');
            } else {
                $output['units'] = __('tickets', 'ba-book-everything');
                $output['unit'] = __('ticket', 'ba-book-everything');
            }
        }
        
        if ($output['unit']){
            $output['unit'] = ' / '.$output['unit'];
        }

        return apply_filters('babe_get_rate_units', $output, $rules);
    }           

    /**
	 * Is there a base rate?
     * @param int $booking_obj_id.
     * @return boolean
	 */
    public static function base_rate_exists($booking_obj_id) {
        global $wpdb;
        
        $rate_id = $wpdb->get_var("SELECT rate_id FROM ".self::$table_rate." WHERE booking_obj_id = ".absint($booking_obj_id)." LIMIT 1");
        
        return $rate_id ? true : false;
    }

///////////////////////////////////////    
    /**
	 * Create fields for price adding
	 */
    public static function ajax_get_price_details_form(){

        $output = '';

        if (
            !isset($_POST['post_id'], $_POST['cat_slug'], $_POST['nonce'])
            || !wp_verify_nonce($_POST['nonce'], self::$nonce_title)
            || !BABE_Users::current_user_can_edit_post($_POST['post_id'])
        ){
            echo $output;
            wp_die();
        }

        $post_id = (int)$_POST['post_id'];
        $cat_slug = sanitize_text_field($_POST['cat_slug']);
        $rules = BABE_Booking_Rules::get_rule_by_cat_slug($cat_slug);

        if ( empty($rules) ){
            echo $output;
            wp_die();
        }

        $output .= '<h3>'.__('New rate', 'ba-book-everything').' <span id="rate_new_open" class="btn button button-secondary">+</span></h3>';

        $output .= self::get_price_details_form($cat_slug);

        echo $output;
        wp_die();
    }

    /**
     * Get fields for price adding form
     */
    public static function get_price_details_form($cat_slug){

        $output = '';

        $rules = BABE_Booking_Rules::get_rule_by_cat_slug($cat_slug);

        if ( empty($rules) ){
            return $output;
        }

        $days_arr = BABE_Calendar_functions::get_week_days_arr();
        $ages = BABE_Post_types::get_ages_arr();

        $units_arr = self::get_rate_units($rules);

        $units = $units_arr['units'];
        $unit = $units_arr['unit'];

        $with_day_night = in_array( $rules['basic_booking_period'], [ 'night', 'day', 'hour' ]);
        $with_ages = (bool)$rules['ages'];

        $output .= '<div id="swal_new_rate" class="swal_new_rate_add">
             <div id="swal_new_rate_fields" class="rate_form_inner ">
                 ';

        $output .= '<div class="rate_title">
                        <label for="_rate_title">'.__('Rate Title:', 'ba-book-everything').'</label>
                        <input type="text" name="_rate_title" id="_rate_title" value="" placeholder="'.__('(required)', 'ba-book-everything').'">
                      <div class="rate_dates">
                      <div class="rate_dates_inner">
                       <label for="_rate_type">'.__('Date from:', 'ba-book-everything').'</label>
                       <input type="text" class="cmb2-text-small" name="_rate_date_from" id="_rate_date_from" value="" placeholder="'.__('no limits', 'ba-book-everything').'">
                       </div>
                       <div class="rate_dates_inner">
                       <label for="_rate_type">'.__('Date to:', 'ba-book-everything').'</label>
                       <input type="text" class="cmb2-text-small" name="_rate_date_to" id="_rate_date_to" value="" placeholder="'.__('no limits', 'ba-book-everything').'">
                       </div>
                      </div>
                    </div>
                    
                    <div class="rate_min_max">
                      <div class="rate_min_max_inner">
                       <label for="_rate_type">'.__('Minimum booking:', 'ba-book-everything').'</label>
                       <input type="text" class="cmb2-text-small" name="_rate_min_booking" id="_rate_min_booking" value="" placeholder="'.__('no limits', 'ba-book-everything').'"><span class="rate-label-units">'.$units.'</span>
                      </div>
                      <div class="rate_min_max_inner">
                       <label for="_rate_type">'.__('Maximum booking:', 'ba-book-everything').'</label>
                       <input type="text" class="cmb2-text-small" name="_rate_max_booking" id="_rate_max_booking" value="" placeholder="'.__('no limits', 'ba-book-everything').'"><span class="rate-label-units">'.$units.'</span>
                      </div> 
                    </div>
                    ';

        $output .= '
                    <div class="rate_apply_days">
                        <label>'.__('Rate applies to days:', 'ba-book-everything').'</label>
                        <ul class="cmb2-checkbox-list cmb2-list">
                    ';
        foreach ($days_arr as $day_num => $day_title){
            $output .= '<li><input type="checkbox" class="cmb2-option" name="apply_days['.$day_num.']" id="apply_days'.$day_num.'" value="'.$day_num.'" checked="checked"><label for="apply_days'.$day_num.'">'.$day_title.'</label></li>';
        }
        $output .= '
                        </ul>
                    </div>
                    <div class="rate_start_days">
                        <label>'.__('Start days:', 'ba-book-everything').'</label>
                        <ul class="cmb2-checkbox-list cmb2-list">
                    ';
        foreach ($days_arr as $day_num => $day_title){
            $output .= '<li><input type="checkbox" class="cmb2-option" name="start_days['.$day_num.']" id="start_days'.$day_num.'" value="'.$day_num.'" checked="checked"><label for="start_days'.$day_num.'">'.$day_title.'</label></li>';
        }
        $output .= '
                        </ul>
                    </div>
                    ';
        ////// price
        $output .= '
                    <div class="set-price-general">
                      <h4>'.__('General price', 'ba-book-everything').'</h4>
                      <table class="age-prices">
                        <tbody>';

        $price_type = 'general';
        if ($rules['ages']){
            foreach ($ages as $age_arr){
                $output .= '<tr><td><span class="age_title">'. $age_arr['name'] . ' (' . $age_arr['description'] . ')</span> '.BABE_Currency::get_currency_symbol().' </td><td><input class="set-age-price age-price-'.$price_type.'" name="_price_'.$price_type.'['.$age_arr['age_id'].']" data-ind="'.$age_arr['age_id'].'" type="text" value="">'.$unit.'</td></tr>';
            }
        } else {
            $output .= '<tr><td> '.BABE_Currency::get_currency_symbol().' </td><td><input class="set-age-price age-price-'.$price_type.'" name="_price_'.$price_type.'[0]" data-ind="0" type="text" value="">'.$unit.'</td></tr>';
        }
        $output .= '
                        </tbody>
                      </table>
                    </div>';
        ////// price from
        $output .= '
                    <div class="set-price-from">
                      <label for="_price_from">'.__('Price from: ', 'ba-book-everything').'</label>
                      '.BABE_Currency::get_currency_symbol().' <input class="age-price-from cmb2-text-small" name="_price_from" id="_price_from" type="text" value="">'.$unit.'
                    </div>
                    ';
        ////// conditional prices

        $output .= self::get_conditional_price_fields( $units, $unit, $with_day_night, $with_ages, false );

        $output .= '
      </div>
</div>';

        return $output;
    }

    /**
     * Get conditional price fields for service post
     * @param int $post_id
     * @return string
     */
    public static function get_service_conditional_price_fields( $post_id ){

        $conditional_prices = get_post_meta( $post_id, 'conditional_prices', true);
        $conditional_prices_value = !empty($conditional_prices) && is_array($conditional_prices) ? json_encode($conditional_prices) : '';

        return self::get_conditional_price_fields( __('days/nights', 'ba-book-everything'), __('day/night', 'ba-book-everything'), true, true, true )
            . '<input id="rate-price-conditional-value" type="hidden" name="rate_conditional_prices" value="'.htmlspecialchars($conditional_prices_value, ENT_QUOTES | JSON_HEX_APOS, 'UTF-8').'">';
    }

    /**
     * Get conditional price fields
     * @param string $units
     * @param string $unit
     * @param bool $with_day_night
     * @return string
     */
    public static function get_conditional_price_fields( $units, $unit, $with_day_night, $with_ages, $with_general_age = false ){

        $output = '';

        $ages = BABE_Post_types::get_ages_arr();

        $signs = self::get_conditional_signs();

        $output .= '
                    <div class="set-price-conditional">
                      <h4>'.__('Conditional prices', 'ba-book-everything').'</h4>
                      <ol id="rate-price-conditional-holder"></ol>
                      <div id="rate-price-conditional-generator">
                         <input type="hidden" name="conditional_tmp_ind" value="">
                         <span class="conditional_start_label">'.__('IF', 'ba-book-everything').'</span>
                         <select class="cmb2-select-list cmb2-list select_option_gray" name="conditional_guests_sign_tmp">
                           <option class="option_gray" value="0" selected="selected">'.__("(don't use)", 'ba-book-everything').'</option>
                    ';
        foreach ($signs as $key => $sign){
            $output .= '<option value="'.$key.'">'.$sign.'</option>';
        }
        $output .= '
                        </select>
                        <input class="cmb2-text-small" name="conditional_guests_number_tmp" type="text" value=""><span class="conditional_guests_number_label">'.__('guests', 'ba-book-everything').'</span>';

        if ( $with_day_night ){

            $output .= '
                        <span class="conditional_operator_label">'.__('AND', 'ba-book-everything').'</span>
                        
                        <select class="cmb2-select-list cmb2-list select_option_gray" name="conditional_units_sign_tmp">
                           <option class="option_gray" value="0" selected="selected">'.__("(don't use)", 'ba-book-everything').'</option>
                    ';
            foreach ($signs as $key => $sign){
                $output .= '<option value="'.$key.'">'.$sign.'</option>';
            }
            $output .= '
                        </select>
                        <input class="cmb2-text-small" name="conditional_units_number_tmp" type="text" value=""><span class="conditional_units_number_label">'.$units.'</span>';
        }

        $output .= '
                    <div class="conditional_result_label">'.__('Price', 'ba-book-everything').'</div>';
        $output .= '
                      <table class="age-prices">
                        <tbody>';
        $price_type = 'conditional-tmp';

        if ( $with_general_age || !$with_ages ){
            $output .= '<tr><td><span class="age_title"> '.BABE_Currency::get_currency_symbol().' </span></td><td><input class="set-age-price age-price-'.$price_type.'" name="_price_'.$price_type.'[0]" data-ind="0" type="text" value="">'.$unit.'</td></tr>';
        }

        if ( $with_ages ){
            foreach ($ages as $age_arr){
                $output .= '<tr><td><span class="age_title">'. $age_arr['name'] . ' (' . $age_arr['description'] . ') '.BABE_Currency::get_currency_symbol().' </span></td><td><input class="set-age-price age-price-'.$price_type.'" name="_price_'.$price_type.'['.$age_arr['age_id'].']" data-ind="'.$age_arr['age_id'].'" type="text" value="">'.$unit.'</td></tr>';
            }
        }

        $output .= '
                        </tbody>
                      </table>
                       <button class="btn button button-primary" id="add_price_conditional">'.__('Add conditional price', 'ba-book-everything').'</button>
                       <button class="btn button button-secondary" id="add_price_conditional_cancel">'.__('Cancel', 'ba-book-everything').'</button>
                  </div>
             </div> 
                    ';

        return $output;
    }
        
///////////////////////////////////////    
    /**
	 * Create fields for price viewing.
	 */
    public static function ajax_get_price_details_block(){
        
        $output = '';

        if (
            !isset($_POST['post_id'], $_POST['cat_slug'], $_POST['nonce'])
            || !wp_verify_nonce($_POST['nonce'], self::$nonce_title)
            || !BABE_Users::current_user_can_edit_post($_POST['post_id'])
        ){
            echo $output;
            wp_die();
        }

        $post_id = (int)$_POST['post_id'];
        $cat_slug = sanitize_text_field($_POST['cat_slug']);
        $rules = BABE_Booking_Rules::get_rule_by_cat_slug($cat_slug);
        $ages = BABE_Post_types::get_ages_arr();
        $ages_arr_ordered_by_id = BABE_Post_types::get_ages_arr_ordered_by_id();

        if ( empty($rules) ){
            echo $output;
            wp_die();
        }

        $rates = self::get_rates($post_id);
        $days_arr = BABE_Calendar_functions::get_week_days_arr();

        if ( empty($rates) ){
            echo $output;
            wp_die();
        }

        $units_arr = self::get_rate_units($rules);
        $units = $units_arr['units'];
        $unit = $units_arr['unit'];

        foreach($rates as $rate){

            $output .= '
                <div class="view-rate-block" data-rate-id="'.$rate['rate_id'].'" data-order="'.$rate['rate_order'].'">
                    <div class="view-rate-title opened" data-rate-id="'.$rate['rate_id'].'">'.$rate['rate_title'];

            $output .= '<div class="view-rate-dates">';
            if ($rate['date_from']){
                $date_from = new DateTime($rate['date_from']);
                $output .= $date_from->format(get_option('date_format')).' - ';
            }

            if ($rate['date_to']){
                $date_to = new DateTime($rate['date_to']);
                $output .= $rate['date_from'] ? '' : ' - ';
                $output .= $date_to->format(get_option('date_format'));
            }
            $output .= '</div>';

            $output .= '
                    </div>
                    <div class="view-rate-details opened" data-rate-id="'.$rate['rate_id'].'">';
            //// get details
            $output .= '
                    <div class="view-rate-details-item">       
                    ';

            if ( is_array($rate['apply_days']) ){
                $output .= '
                    <div class="rate_apply_days">
                        <span class="rate_details_label">'.__('Rate applies to days:', 'ba-book-everything').'</span> <span class="rate_details_value rate_apply_days_value">';
                $tmp_days = array();
                foreach ($days_arr as $day_num => $day_title){
                    if ( in_array($day_num, $rate['apply_days']) ){
                        $tmp_days[] = $day_title;
                    }
                }
                if ( count($tmp_days) == 7 ){
                    $tmp_days = array( __('All', 'ba-book-everything') );
                }
                $output .= implode(', ', $tmp_days);
                $output .= '</span>
                    </div>
                    ';
            }

            if ( is_array($rate['start_days']) ){
                $output .= '
                    <div class="rate_start_days">
                        <span class="rate_details_label">'.__('Start days:', 'ba-book-everything').'</span> <span class="rate_details_value rate_start_days_value">';
                $tmp_days = array();
                foreach ($days_arr as $day_num => $day_title){
                    if ( in_array($day_num, $rate['start_days']) ){
                        $tmp_days[] = $day_title;
                    }
                }
                if ( count($tmp_days) == 7 ){
                    $tmp_days = array( __('All', 'ba-book-everything') );
                }
                $output .= implode(', ', $tmp_days);
                $output .= '</span>
                    </div>
                    ';
            }

            $output .= '
                    <div class="rate_min_max">';
            if ( $rate['min_booking_period'] ){
                $output .= '
                        <span class="rate_details_label">'.__('Minimum booking:', 'ba-book-everything').'</span> <span class="rate_details_value">'.$rate['min_booking_period'].' '.$units.'</span>
                        ';
            }
            if ( $rate['max_booking_period'] ){
                $output .= '
                        <span class="rate_details_label">'.__('Maximum booking:', 'ba-book-everything').'</span> <span class="rate_details_value">'.$rate['max_booking_period'].' '.$units.'</span>
                        ';
            }
            $output .= '
                    </div>';

            $output .= '
                    <div class="rate_price_from">
                      <span class="rate_details_label">'.__('Price from: ', 'ba-book-everything').'</span> <span class="rate_details_value">'.BABE_Currency::get_currency_price($rate['price_from']).$unit.'</span>
                    </div>
                    ';

            if ( !empty($rate['price_general']) ){
                $output .= '
                    <div class="rate_price_general">
                      <span class="rate_details_label">'.__('General price: ', 'ba-book-everything').'</span>';

                $tmp_prices = array();
                foreach($rate['price_general'] as $age_id => $price){
                    $age_title = !$age_id || !isset($ages_arr_ordered_by_id[$age_id]) ? '' : $ages_arr_ordered_by_id[$age_id]['name'] . ' (' . $ages_arr_ordered_by_id[$age_id]['description'] . ')';
                    $menu_order = isset($ages_arr_ordered_by_id[$age_id]) ? $ages_arr_ordered_by_id[$age_id]['menu_order'] : 0;
                    $tmp_prices[$menu_order] = '<span class="price_age_title">'. $age_title . '</span> <span class="price_age_value">'.BABE_Currency::get_currency_price($price).$unit.'</span>';
                }
                ksort($tmp_prices);
                $output .= implode(' | ', $tmp_prices);
                $output .= '
                    </div>
                    ';
            }

            if ( !empty($rate['prices_conditional']) ){

                $signs = self::get_conditional_signs();
                $output .= '
                    <div class="rate_prices_conditional">
                      <h4 class="rate_details_label">'.__('Conditional prices', 'ba-book-everything').'</h4>
                      <ol class="rate_prices_conditional_details">';

                foreach($rate['prices_conditional'] as $price_conditional){

                    $output .= '<li class="conditional_price_block">';

                    $tmp_output = '';

                    if ( isset($price_conditional['conditional_guests_sign']) && isset($price_conditional['conditional_guests_number']) && isset($signs[$price_conditional['conditional_guests_sign']]) ){
                        $tmp_output .= '<span class="prices_conditional_if">'.__('guests', 'ba-book-everything').' '.$signs[$price_conditional['conditional_guests_sign']].' '.$price_conditional['conditional_guests_number']. '</span> ';
                    }

                    if ( isset($price_conditional['conditional_units_sign']) && isset($price_conditional['conditional_units_number']) && isset($signs[$price_conditional['conditional_units_sign']]) ){
                        $tmp_output .= $tmp_output ? '<span class="prices_conditional_if">'.__('AND', 'ba-book-everything'). '</span> ' : '';

                        $tmp_output .= '<span class="prices_conditional_if">'.$units.' '.$signs[$price_conditional['conditional_units_sign']].' '.$price_conditional['conditional_units_number']. '</span>';
                    }

                    $output .= $tmp_output.' <span class="prices_conditional_then">'.__('Price', 'ba-book-everything').'</span> ';

                    $tmp_prices = array();
                    foreach($price_conditional['conditional_price'] as $age_id => $price){
                        $age_title = !$age_id || !isset($ages_arr_ordered_by_id[$age_id]) ? '' : $ages_arr_ordered_by_id[$age_id]['name'] . ' (' . $ages_arr_ordered_by_id[$age_id]['description'] . ')';
                        $menu_order = isset($ages_arr_ordered_by_id[$age_id]) ? $ages_arr_ordered_by_id[$age_id]['menu_order'] : 0;
                        $tmp_prices[$menu_order] = '<span class="price_age_title">'. $age_title . '</span> <span class="price_age_value">'.BABE_Currency::get_currency_price($price).$unit.'</span>';
                    }
                    ksort($tmp_prices);
                    $output .= implode(' | ', $tmp_prices);
                    $output .= '</li>';
                }

                $output .= '
                      </ol>  
                    </div>
                    ';
            }

            $output .= '
                    </div>'; // end view-rate-details-item

            $rate_to_json = $rate;
            $rate_to_json['date_from'] = $rate['date_from'] ? BABE_Calendar_functions::date_from_sql( substr($rate['date_from'],0,10) ) : '';
            $rate_to_json['date_to'] = $rate['date_to'] ? BABE_Calendar_functions::date_from_sql( substr($rate['date_to'],0,10) ) : '';

            $output .= '
                    <div class="view-rate-details-item-edit" data-rate-id="'.$rate['rate_id'].'" data-rate="'.htmlspecialchars(json_encode($rate_to_json), ENT_QUOTES | JSON_HEX_APOS, 'UTF-8').'" title="'.__('Edit', 'ba-book-everything').'">
                           <i class="rate_item_action_edit fas fa-edit"></i>
                    </div>
                    <div class="view-rate-details-item-clone" data-rate-id="'.$rate['rate_id'].'" data-rate="'.htmlspecialchars(json_encode($rate_to_json), ENT_QUOTES | JSON_HEX_APOS, 'UTF-8').'" title="'.__('Clone', 'ba-book-everything').'">
                           <i class="rate_item_action_edit fas fa-copy"></i>
                    </div>
                    <div class="view-rate-details-item-del" data-rate-id="'.$rate['rate_id'].'" title="'.__('Delete', 'ba-book-everything').'">
                           <i class="rate_item_action_delete fas fa-trash-alt"></i>
                    </div>';

            $output .= '
                    </div>
                 </div>                
            '; //// end view-rate-block

        } //////// end foreach rates
         
        echo $output;
        wp_die();  
    }
    
///////////ajax_check_base_rate////////
    /**
	 * Is there a base rate?
	 */
    public static function ajax_check_base_rate(){
        $output = '';
        
        if (isset($_POST['post_id'], $_POST['nonce']) && wp_verify_nonce($_POST['nonce'], self::$nonce_title) && BABE_Users::current_user_can_edit_post($_POST['post_id'])){
             $post_id = (int)$_POST['post_id'];
             $output = self::base_rate_exists($post_id) ? 1 : '';
        }     
        
        echo $output;
        wp_die();  
    }
    
//////////////ajax_delete_rate/////////    
    /**
	 * Delete selected rate.
	 */
    public static function ajax_delete_rate(){
        
        $output = 0;
        
        if (isset($_POST['post_id'], $_POST['rate_id'], $_POST['nonce']) && wp_verify_nonce($_POST['nonce'], self::$nonce_title) && BABE_Users::current_user_can_edit_post($_POST['post_id'])){
           $rate_id = absint($_POST['rate_id']);
           $output = self::delete_rate_by_id($rate_id); 
        }
        
        echo (int)$output;
        wp_die();  
    }
    
///////////////////////    
    /**
	 * Delete rate by id
     * 
     * @param int $rate_id
     * 
     * @return int
	 */
    public static function delete_rate_by_id($rate_id){
        global $wpdb;
        
        $output = 0;
        
        $rate_id = absint($rate_id); 
        $old_rate = $wpdb->get_row("SELECT * FROM ".self::$table_rate." WHERE rate_id = ".$rate_id, ARRAY_A);
       
       if(!empty($old_rate)){
           /// delete from DB
           $wpdb->query( $wpdb->prepare( 'DELETE FROM '.self::$table_rate.' WHERE rate_id = %d', $rate_id ) );
           $output = 1;
       }
            
       return $output;  
    }
    
///////////////////////    
    /**
	 * Delete discounts by booking object id
     * 
     * @param int $booking_obj_id
     * 
     * @return int
	 */
    public static function delete_discounts_by_booking_obj_id($booking_obj_id){
        global $wpdb;
        
        $output = 0;
        
        $booking_obj_id = absint($booking_obj_id); 
        $old_discounts = $wpdb->get_results("SELECT * FROM ".self::$table_discount." WHERE booking_obj_id = ".$booking_obj_id, ARRAY_A);
       
       if(!empty($old_discounts)){
           /// delete from DB
           $wpdb->query( $wpdb->prepare( 'DELETE FROM '.self::$table_discount.' WHERE booking_obj_id = %d', $booking_obj_id) );
           
           $output = 1;
       }
       
       return $output;  
    }    
    
///////////////////////    
    /**
	 * Delete rates by booking object id
     * 
     * @param int $booking_obj_id
     * 
     * @return int
	 */
    public static function delete_rates_by_booking_obj_id($booking_obj_id){
        global $wpdb;
        
        $output = 0;
        
        $booking_obj_id = absint($booking_obj_id); 
        $old_rates = $wpdb->get_results("SELECT * FROM ".self::$table_rate." WHERE booking_obj_id = ".$booking_obj_id, ARRAY_A);
       
       if(!empty($old_rates)){
           /// delete from DB
           $wpdb->query( $wpdb->prepare( 'DELETE FROM '.self::$table_rate.' WHERE booking_obj_id = %d', $booking_obj_id ) );
           
           $output = 1;
       }
            
       return $output;  
    }    
        
///////////////////////////////////////    
    /**
	 * Save rate.
	 */
    public static function ajax_save_rate(){
        global $wpdb;
        $output = '';
        
        if (isset($_POST['post_id'], $_POST['cat_slug'], $_POST['nonce']) && wp_verify_nonce($_POST['nonce'], self::$nonce_title) && BABE_Users::current_user_can_edit_post($_POST['post_id'])){
             $post_arr = self::sanitize_prices_post_arr();
             $rate_id = self::save_rate($post_arr);
        }
        
        echo $output;
        wp_die();                   
    }
    
///////////////////////////////////////    
    /**
	 * Reorder rates
	 */
    public static function ajax_rates_reorder(){
        global $wpdb;
        $output = '';
        
        if (isset($_POST['post_id'], $_POST['nonce']) && absint($_POST['post_id']) && !empty($_POST['rate_orders']) && is_array($_POST['rate_orders']) && wp_verify_nonce($_POST['nonce'], self::$nonce_title) && BABE_Users::current_user_can_edit_post(absint($_POST['post_id']))){
             
             $rate_orders = array_map('absint', $_POST['rate_orders']);
             $rates = $wpdb->get_results("SELECT rate_id, rate_order FROM ".self::$table_rate." WHERE booking_obj_id = ".absint($_POST['post_id'])." ORDER BY rate_order ASC", ARRAY_A);
             
             if (!empty($rates)){
                
                $new_rates = array();
                foreach($rates as $ind => $rate){
                    $new_rates[$ind] = $rate;
                    if ( isset( $rate_orders[$rate['rate_id']] ) ){
                        $new_rates[$ind]['rate_order'] = $rate_orders[$rate['rate_id']];
                    }
                }
                
                $sql = "INSERT INTO ".self::$table_rate." (rate_id,rate_order) VALUES ";
                
                $start_loop = true;
                foreach ($new_rates as $new_rate){
                    if (!$start_loop) {
                        $sql .= ", ";
                    } else {
                        $start_loop = false;
                    }
                    $sql .= "(".$new_rate['rate_id'].",".$new_rate['rate_order'].")";
                }
                
                $sql .= " ON DUPLICATE KEY UPDATE rate_order=VALUES(rate_order)";
                $new_result = $wpdb->query($sql);
             }             
        }
        
        echo $output;
        wp_die();                   
    }

///////////////////////////////////////
    /**
     * Add booking obj rates
     *
     * @param int $post_id
     * @param array $rates
     * @return void
     */
    public static function add_booking_obj_rates($post_id, $rates){

        global $wpdb;

        if ( empty($rates) ){
            return;
        }

        $new_values = [];
        foreach( $rates as $rate){

            $new_values[] = $wpdb->prepare(
                '( %d, %s, %s, %s, %s, %s, %d, %d, %f, %s, %s, %s, %d )',
                $post_id,
                $rate['rate_title'],
                ( !empty($rate['date_from']) ? $rate['date_from'] : '###NULL###' ),
                ( !empty($rate['date_to']) ? $rate['date_to'] : '###NULL###' ),
                ( !empty($rate['apply_days']) ? serialize($rate['apply_days']) : '###NULL###' ),
                ( !empty($rate['apply_days']) ? serialize($rate['start_days']) : '###NULL###' ),
                $rate['min_booking_period'],
                $rate['max_booking_period'],
                $rate['price_from'],
                ( !empty($rate['apply_days']) ? serialize($rate['price_general']) : '###NULL###' ),
                ( !empty($rate['apply_days']) ? serialize($rate['prices_conditional']) : '###NULL###' ),
                ( !empty($rate['color']) ? $rate['color'] : '###NULL###' ),
                $rate['rate_order']
            );
        }

        $sql = implode( ', ', $new_values );
        $sql = str_ireplace( "'###NULL###'", "NULL", $sql );

        $wpdb->query(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "INSERT INTO ".self::$table_rate." (booking_obj_id, rate_title, date_from, date_to, apply_days, start_days, min_booking_period, max_booking_period, price_from, price_general, prices_conditional, color, rate_order) VALUES ".$sql
        );
    }
    
///////////////////////////////////////    
    /**
	 * Save rate.
     * 
     * @param array $post_arr
     * 
     * @return int - rate ID
	 */
    public static function save_rate($post_arr){
        global $wpdb;
        $output = 0;
        
        $rules = BABE_Booking_Rules::get_rule_by_cat_slug($post_arr['cat_slug']);

        if (
            empty($rules)
            || !$post_arr['post_id']
            || empty($post_arr['_price_general'])
            || empty($post_arr['_rate_title'])
        ){
            return $output;
        }

        $rate_id = $post_arr['rate_id'];
        if( !empty($rate_id) ){
            $rate = self::get_rate_by_id($rate_id, $post_arr['post_id']);
            if( empty($rate) ){
                return $output;
            }
        }

        if ( !empty($post_arr['_prices_conditional']) ){
            usort($post_arr['_prices_conditional'], 'BABE_Functions::compare_arrays_by_order_asc');
        }

        $ins_arr = array(
            'booking_obj_id' => $post_arr['post_id'],
            'rate_title' => $post_arr['_rate_title'],
            'apply_days' => serialize($post_arr['apply_days']),
            'start_days' => serialize($post_arr['start_days']),
            'min_booking_period' => $post_arr['_rate_min_booking'],
            'max_booking_period' => $post_arr['_rate_max_booking'],
            'price_general' => serialize($post_arr['_price_general']),
            'prices_conditional' => serialize($post_arr['_prices_conditional']),
        );

        if ($post_arr['_rate_date_from']){
            $ins_arr['date_from'] = $post_arr['_rate_date_from'];
        }
        if ($post_arr['_rate_date_to']){
            $ins_arr['date_to'] = $post_arr['_rate_date_to'];
        }

        if ($post_arr['_price_from'] === ''){
            $main_age_id = BABE_Post_types::get_main_age_id($rules);
            $post_arr['_price_from'] = isset($post_arr['_price_general'][$main_age_id]) ? $post_arr['_price_general'][$main_age_id] : ( isset($post_arr['_price_general'][0]) ? $post_arr['_price_general'][0] : 0 );
        }

        $ins_arr['price_from'] = $post_arr['_price_from'];

        if ( $rate_id ){

            // update
            $wpdb->update(self::$table_rate, $ins_arr, ['rate_id' => $rate_id] );
            $output = $rate_id;

        } else {
            //// create new row
            $wpdb->insert(
                self::$table_rate,
                $ins_arr
            );
            $output = $wpdb->insert_id;
        }
        
        return $output;                 
    }    
    
//////////////////////////////
    /**
	 * Sanitize prices POST array
     * @return array
	 */
    public static function sanitize_prices_post_arr(){
        $output = array();

        $output['post_id'] = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        $output['rate_id'] = !empty($_POST['rate_id']) ? (int)$_POST['rate_id'] : 0;
        $output['cat_slug'] = !empty($_POST['cat_slug']) ? sanitize_text_field($_POST['cat_slug']) : '';
        $output['_rate_title'] = !empty($_POST['_rate_title']) ? sanitize_text_field($_POST['_rate_title']) : '';

        $output['_rate_date_from'] = isset($_POST['_rate_date_from']) && BABE_Calendar_functions::isValidDate($_POST['_rate_date_from'], BABE_Settings::$settings['date_format']) ? BABE_Calendar_functions::date_to_sql($_POST['_rate_date_from']).' 00:00:00' : ''; /// now in Y-m-d format

        $output['_rate_date_to'] = isset($_POST['_rate_date_to']) && BABE_Calendar_functions::isValidDate($_POST['_rate_date_to'], BABE_Settings::$settings['date_format']) ? BABE_Calendar_functions::date_to_sql($_POST['_rate_date_to']).' 23:59:59' : ''; /// now in Y-m-d format

        $output['_price_from'] = isset($_POST['_price_from']) && $_POST['_price_from'] !== '' ? (float)$_POST['_price_from'] : '';

        $output['_rate_min_booking'] = isset($_POST['_rate_min_booking']) ? (int)$_POST['_rate_min_booking'] : 0;

        $output['_rate_max_booking'] = isset($_POST['_rate_max_booking']) ? (int)$_POST['_rate_max_booking'] : 0;

        $output['_price_general'] = isset($_POST['_price_general']) ? array_map( 'floatval', (array)$_POST['_price_general']) : array();

        $output['_prices_conditional'] = isset($_POST['_prices_conditional']) ? BABE_Functions::array_map_r( 'floatval', (array)$_POST['_prices_conditional'] ) : array();

        $output['start_days'] = isset($_POST['start_days']) ? array_map( 'absint', (array)$_POST['start_days']) : array();

        $output['apply_days'] = isset($_POST['apply_days']) ? array_map( 'absint', (array)$_POST['apply_days']) : array();

        return $output;
    }

//////////////////////////////
//////////////////////////////
    /**
	 * Get total price clear and price with taxes by booking_obj_id from calculated $price_arr
     * 
     * @param int $booking_obj_id
     * @param array $price_arr
     * @return array
	 */
    public static function get_obj_total_price($booking_obj_id, $price_arr = []){

        $price['tax_percentage'] = BABE_Post_types::get_post_tax($booking_obj_id);

        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($booking_obj_id);
        
        $price['total'] = 0;
        $price['total_with_taxes'] = 0;
        
        $price['total_item'] = 0;
        $price['total_item_with_taxes'] = 0;
        
        $price['total_services'] = [];
        $price['total_services_with_taxes'] = [];

        $price['total_fees'] = $price_arr['fees'] ?? [];
        
        if (isset($price_arr['clear_with_taxes'])){

            foreach ($price_arr['clear'] as $age_id => $item_price){

                $discount = apply_filters('babe_get_obj_total_price_discount', $price_arr['discount'], $age_id, $booking_obj_id, $price_arr );
                $discountMultiplier = (100 - $discount)/100;

                $price['total_item'] += $item_price*$discountMultiplier;

                if ( $rules_cat['rules']['booking_mode'] === 'object' ){
                    break;
                }
            }

            foreach ($price_arr['clear_with_taxes'] as $age_id => $item_price){

                $discount = apply_filters('babe_get_obj_total_price_discount', $price_arr['discount'], $age_id, $booking_obj_id, $price_arr );
                $discountMultiplier = (100 - $discount)/100;

                $price['total_item_with_taxes'] += $item_price*$discountMultiplier;

                if ( $rules_cat['rules']['booking_mode'] === 'object' ){
                    break;
                }
            }
        }
        
        if (isset($price_arr['services'])){
            foreach ($price_arr['services'] as $service_id => $service_prices){
                  $price['total_services'][$service_id] = 0;
                  $price['total_services_with_taxes'][$service_id] = 0;
                  foreach ($service_prices['clear'] as $age_id => $item_price){
                    $price['total_services'][$service_id] += $item_price;
                  }
                  foreach ($service_prices['clear_with_taxes'] as $age_id => $item_price){
                    $price['total_services_with_taxes'][$service_id] += $item_price;
                  }
            }
        }
        
        $price['total'] = $price['total_item'] + array_sum($price['total_services']);
        $price['total_taxable_amount'] = $price['total'];
        $price['total_taxes'] = $price['total_item_with_taxes'] + array_sum($price['total_services_with_taxes']) - $price['total'];

        $price['total_with_taxes'] = $price['total_item_with_taxes'] + array_sum($price['total_services_with_taxes']) + array_sum($price['total_fees']);
        $price['total_with_taxes_fees'] = $price['total_with_taxes'];

        $precision = BABE_Currency::get_currency_precision();

        $price['total_coupon_amount_applied'] = 0;

        if ( !empty($price_arr['coupon']['coupon_amount_applied']) ){
            $price['total_coupon_amount_applied'] = round( $price_arr['coupon']['coupon_amount_applied'], $precision);
            $price['total_with_taxes'] -= $price['total_coupon_amount_applied'];
        }

        $price['total_payment_gateway_fee'] = 0;

        if ( isset($price_arr['payment_gateway_fee']) && is_array($price_arr['payment_gateway_fee']) ){
            $price['total_payment_gateway_fee'] = round( array_sum($price_arr['payment_gateway_fee']) * $price['total_with_taxes']/100, $precision);
            $price['total_with_taxes'] += $price['total_payment_gateway_fee'];
        }
        
        $price['deposit'] = $price_arr['deposit'];
        $price['total_deposit'] = isset($price_arr['deposit_fixed']) && $price_arr['deposit_fixed'] > 0 ? $price_arr['deposit_fixed'] : round($price['total_with_taxes']*$price_arr['deposit']/100, $precision);
        $price['total_deposit_taxable'] = $price['total_deposit']/(1+$price['tax_percentage']/100);
        $price['total_deposit_taxes'] = $price['total_deposit'] - $price['total_deposit_taxable'];

        $price['payment_model'] = $price_arr['payment_model'];
        
        $price = apply_filters('babe_obj_total_price', $price, $booking_obj_id, $price_arr);
        
        return $price;
    }

//////////////////////////////
    /**
	 * Get total prices array by booking_obj_id
     *
     * @param int $booking_obj_id
     * @param string $date_from - format Y-m-d H:i
     * @param array $guests
     * @param string $date_to - format Y-m-d H:i
     * @param array $services
     * @param array $fees
     *
     * @return array
	 */
    public static function get_obj_total_price_arr($booking_obj_id, $date_from, $guests = [], $date_to = '', $services = [], $fees = [], $order_id = 0 ){

        $date_from = (string)apply_filters('babe_get_obj_total_price_arr_date_from', $date_from, $booking_obj_id, $date_to, $services );
        $date_to = (string)apply_filters('babe_get_obj_total_price_arr_date_to', $date_to, $booking_obj_id, $date_from, $services );
        
        $prices = [];

        $currency = $order_id ? BABE_Order::get_order_currency($order_id) : BABE_Currency::get_currency();
        
        $date_to = !$date_to ? $date_from : $date_to;
        
        $begin = new DateTime( $date_from );
        $end = new DateTime( $date_to );
        
        $begin_check = new DateTime( $begin->format('Y-m-d') );
        $end_check = new DateTime( $end->format('Y-m-d') );
        
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($booking_obj_id);
        
        $rates = self::get_rates($booking_obj_id, $begin->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        
        $guests_for_obj = $rules_cat['rules']['ages'] ? $guests : array( 0 => array_sum($guests));

        //// get complex price

        if ( in_array($rules_cat['rules']['basic_booking_period'], ['recurrent_custom', 'single_custom']) && $begin_check == $end_check){
            $end->modify( '+1 day' );
        }

        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval, $end);
        foreach($daterange as $date){
            $date_rates_arr[$date->format("Y-m-d")] = 0; //// initial dates arr
        }

        foreach ($rates as $rate){

            $rateDTO = RateDTO::instanceFromArray($rate);

            // workaround for base rate
            $rate['date_from'] = $rate['date_from'] ?: $date_from;
            $rate['date_to'] = $rate['date_to'] ?: $date_to;

            //////
            $rate_date_from = new DateTime( $rate['date_from'] );
            $rate_date_to = new DateTime( $rate['date_to'] );

            $rate_begin_obj = $rate_date_from < $begin ? clone($begin) : clone($rate_date_from);
            $rate_end_obj = $rate_date_to > $end ? clone($end) : clone($rate_date_to);

            if ( in_array($rules_cat['rules']['basic_booking_period'], ['recurrent_custom', 'single_custom']) && $rate_begin_obj >= $rate_end_obj){
                $rate_end_obj->modify( '+1 day' );
            }

            if (
                $rules_cat['rules']['basic_booking_period'] === 'day'
                && count($rates) > 1
                && $rate_begin_obj > $begin
            ){
                // skip first day up to begin time
                $begin_time = $begin->format('H:i');
                $rate_begin_obj_date = $rate_begin_obj->format('Y-m-d');
                $rate_begin_obj = new DateTime( $rate_begin_obj_date . ' ' . $begin_time );
                if ($rate_begin_obj > $rate_end_obj){
                    continue;
                }
            }

            $date_subrange = new DatePeriod($rate_begin_obj, $interval, $rate_end_obj);

            $d_interval = date_diff($rate_begin_obj, $rate_end_obj);
            $days_total = (int)$d_interval->format('%a'); // total days
            if (!$days_total){
                $days_total = 1;
            }
            $hours_total = (int)$d_interval->format('%h');

            if (
                $rateDTO->minBookingPeriod
                && (
                    (
                        $rules_cat['rules']['basic_booking_period'] !== 'hour'
                        && $rateDTO->minBookingPeriod > $days_total
                    )
                    || (
                        $rules_cat['rules']['basic_booking_period'] === 'hour'
                        && $rateDTO->minBookingPeriod > $hours_total
                    )
                )
            ){
                continue;
            }

            $rate_start = '';
            $rate_end = '';
            $last_date = '';

            $checkedDays = 0;

            foreach($date_subrange as $date){

                $checkedDays++;

                if (
                    $rateDTO->maxBookingPeriod
                    && (
                        (
                            $rules_cat['rules']['basic_booking_period'] !== 'hour'
                            && $rateDTO->maxBookingPeriod < $checkedDays
                        )
                        || (
                            $rules_cat['rules']['basic_booking_period'] === 'hour'
                            && $rateDTO->maxBookingPeriod < $hours_total
                        )
                    )
                ){
                    continue;
                }

                $date_cal_day_num = BABE_Calendar_functions::get_week_day_num($date);

                if (
                    !$date_rates_arr[$date->format("Y-m-d")]
                    && isset($rate['apply_days'][$date_cal_day_num])
                ){
                    $rate_start = !$rate_start ? $date->format("Y-m-d H:i") : $rate_start;
                    $date_rates_arr[$date->format("Y-m-d")] = $rate['rate_id'];
                } elseif (
                    (
                        ( !$date_rates_arr[$date->format("Y-m-d")]
                            && !isset($rate['apply_days'][$date_cal_day_num])
                        )
                        ||
                        ( $date_rates_arr[$date->format("Y-m-d")]
                            && $date_rates_arr[$date->format("Y-m-d")] != $rate['rate_id']
                        )
                    ) && $rate_start
                ){
                    $rate_end = $last_date;
                }
                if ($rate_start && $rate_end){
                    //// add prices
                    $prices = self::calculate_price($rate_start, $rate_end, $rate, $guests_for_obj, $rules_cat, $prices, $currency);

                    $rate_start = '';
                    $rate_end = '';
                }
                $last_date = $date->format("Y-m-d H:i");
            }
            if ($rate_start && !$rate_end){
                if ($rules_cat['rules']['basic_booking_period'] === 'hour'){
                    $last_date = $rate_end_obj->format("Y-m-d H:i");
                }
                //// add prices
                $prices = self::calculate_price($rate_start, $last_date, $rate, $guests_for_obj, $rules_cat, $prices, $currency);
            }
        }

        //// get discount
        $discount_arr = BABE_Post_types::get_post_discount($booking_obj_id);
        $prices += $discount_arr;
        
        $deposit = apply_filters('babe_deposit_percents', $rules_cat['rules']['deposit'], $booking_obj_id, $rules_cat );
        
        $prices['deposit'] = $rules_cat['rules']['payment_model'] !== 'full' && $deposit && $deposit <= 100 && $deposit > 0 ? $deposit : 100;
        
        $prices['deposit_fixed'] = $rules_cat['rules']['payment_model'] !== 'full' ? self::localize_price((float)get_post_meta($booking_obj_id, 'deposit_fixed', true), $currency) : 0;
        
        $prices['payment_model'] = $rules_cat['rules']['payment_model'];

        /////

        $services = BABE_Functions::normalize_order_services( $services, $guests_for_obj );

        foreach ($services as $service_id => $service_age_qty){
            $serv_tmp_prices = self::get_service_price($booking_obj_id, $service_id, $rules_cat, $service_age_qty, $date_from, $guests, $date_to, $prices, $currency);

            if ( !isset($serv_tmp_prices['services']) ){
                continue;
            }

            $prices['services'] = isset($prices['services']) ? $prices['services'] + $serv_tmp_prices['services'] : $serv_tmp_prices['services'];
        }

        /////

        foreach ($fees as $fee_id){
            $fee_tmp_prices = self::get_fee_price($booking_obj_id, $fee_id, $rules_cat, $date_from, $guests, $date_to, $prices, $currency);

            $prices['fees'] = isset($prices['fees']) ? $prices['fees'] + $fee_tmp_prices['fees'] : $fee_tmp_prices['fees'];
        }

        /////

        if ( $order_id ){
            $payment_gateway_name = BABE_Order::get_order_payment_method($order_id);
            $payment_gateway_fee_percents = BABE_Order::get_order_payment_gateway_fee_percents($order_id);
            $prices[ 'payment_gateway_fee' ] = [
                $payment_gateway_name => $payment_gateway_fee_percents,
            ];

            $prices[ 'coupon' ] = [
                'coupon_num' => BABE_Order::get_order_coupon_num($order_id),
                'coupon_amount_applied' => BABE_Order::get_order_coupon_amount_applied($order_id)
            ];
        }
        
        return $prices;
    }

//////////////////////////////
    /**
     * Recalculate total prices array by order_id
     *
     * @param int $order_id
     *
     * @return array
     */
    public static function recalculate_obj_total_price_arr( $order_id ){

        $order_items = BABE_Order::get_order_items( $order_id );
        $order = reset($order_items);

        return self::get_obj_total_price_arr(
            $order['booking_obj_id'],
            $order['meta']['date_from'],
            $order['meta']['guests'],
            $order['meta']['date_to'],
            $order['meta']['services'],
            $order['meta']['fees'],
            $order_id
        );
    }

    ///////////////////////////////
    /**
     * Calculate price with selected rate
     * @param string $rate_start - format Y-m-d H:i
     * @param string $rate_end - format Y-m-d H:i
     * @param array $rate
     * @param array $guests
     * @param array $rules_cat
     * @param array $prices
     * @param string $currency
     * @return array
     * @throws Exception
     */
    public static function calculate_price($rate_start, $rate_end, $rate, $guests, $rules_cat, $prices, $currency = ''){

      list( 'days_total' => $days_total, 'multiplier' => $multiplier) = self::get_price_multiplier_and_days_total($rate_start, $rate_end, $rules_cat);

      $tax_am = (float)apply_filters('babe_prices_calculate_price_post_tax', BABE_Post_types::get_post_tax($rules_cat['post_id']), $rules_cat['post_id'])/100;

      $guests_total = array_sum($guests);
      $prices_arr = array();
      
      foreach ($guests as $age_id => $guests_number){

          if ( !$guests_number ){
              continue;
          }

          $multiplier_local = $rules_cat['rules']['booking_mode'] !== 'object'
              ? $multiplier*$guests_number : $multiplier;

          $prices_arr['clear'][$age_id] = isset($prices['clear'][$age_id]) ? $prices['clear'][$age_id] : 0;

          if ( isset($rate['price_general'][$age_id]) ){
              $price_clear = $rate['price_general'][$age_id]*$multiplier_local;
          } else {
              $price_clear = isset($rate['price_general'][0]) ? $rate['price_general'][0]*$multiplier_local : 0;
          }

          /// check conditional prices
          if ( !empty($rate['prices_conditional']) ){

              $conditional_price = self::calculate_rate_conditional_price( $rate['prices_conditional'], $age_id, $guests_total, $days_total, $multiplier_local );

              if ( $conditional_price !== false && $conditional_price ){
                  $price_clear = (float)$conditional_price;
              }
          }

          $price_clear = self::localize_price($price_clear, $currency);

          $prices_arr['clear'][$age_id] += $price_clear;

          $prices_arr['clear_with_taxes'][$age_id] = isset($prices['clear_with_taxes'][$age_id]) ? $prices['clear_with_taxes'][$age_id] : 0;
          $prices_arr['clear_with_taxes'][$age_id] += $price_clear + round($price_clear * $tax_am, 2);
      }
      
      return $prices_arr;
   }

    /**
     * @param string $rate_start - format Y-m-d H:i
     * @param string $rate_end - format Y-m-d H:i
     * @param array $rules_cat
     * @return array = [
     * 'days_total' => $days_total, // days or hours
     * 'multiplier' => $multiplier,
     * ]
     */
    public static function get_price_multiplier_and_days_total($rate_start, $rate_end, $rules_cat){

        $days_total = 1;
        $multiplier = 1;

        if ( !in_array($rules_cat['rules']['basic_booking_period'], ['single_custom', 'recurrent_custom']) ){

            $rate_date_from = new DateTime( $rate_start );
            $rate_date_to = new DateTime( $rate_end );

            $d_interval_start = date_diff($rate_date_from, $rate_date_to);

            $rate_date_to->modify( '+1 day' );

            $d_interval = date_diff($rate_date_from, $rate_date_to);

            $days_total = (int)$d_interval->format('%a'); // total days
            $multiplier = $days_total;

            if ( $rules_cat['rules']['basic_booking_period'] === 'hour' ){
                $multiplier = (int)$d_interval_start->format('%a')*24 + (int)$d_interval_start->format('%h');
                $days_total = $multiplier;
            }
        }
        return apply_filters('babe_prices_price_multiplier_and_days_total', [
            'days_total' => $days_total,
            'multiplier' => $multiplier,
        ], $rate_start, $rate_end, $rules_cat);
    }

    /**
     * Calculate rate conditional price
     *
     * @param array $prices_conditional
     * @param int $age_id
     * @param int $guests_total
     * @param int $units_number - days|nights|hours
     * @param int $multiplier_local
     * @return float
     */
    public static function calculate_rate_conditional_price( $prices_conditional, $age_id, $guests_total, $units_number, $multiplier_local ){

        if ( empty($prices_conditional) || !is_array($prices_conditional) ){
            return false;
        }

        $signs = self::get_conditional_signs();

        $price_clear = 0;
        $price_exists = false;

        foreach( $prices_conditional as $price_conditional ){

            $check = false;
            $first = false;

            if ( isset(
                $price_conditional['conditional_guests_sign'],
                $price_conditional['conditional_guests_number'],
                $signs[$price_conditional['conditional_guests_sign']]
            ) ){
                $first = true;
                if( version_compare(
                    (string)$guests_total,
                    (string)$price_conditional['conditional_guests_number'],
                    $signs[$price_conditional['conditional_guests_sign']]
                ) ){
                    $check = true;
                }
            }

            if (
                isset(
                    $price_conditional['conditional_units_sign'],
                    $price_conditional['conditional_units_number'],
                    $signs[$price_conditional['conditional_units_sign']]
                )
                && (!$first || $check)
            ){
                if( version_compare(
                    (string)$units_number,
                    (string)$price_conditional['conditional_units_number'],
                    $signs[$price_conditional['conditional_units_sign']]
                ) ){
                    $check = true;
                } else {
                    $check = false;
                }
            }

            if (!$check){
                continue;
            }

            if ( isset($price_conditional['conditional_price'][$age_id]) ){

                $price_clear = $price_conditional['conditional_price'][$age_id]*$multiplier_local;
                $price_exists = true;

            } elseif( isset($price_conditional['conditional_price'][0]) ) {

                $price_clear = $price_conditional['conditional_price'][0]*$multiplier_local;
                $price_exists = true;
            }
        }

        return $price_exists ? $price_clear : false;
    }

    ///////////////////////////////

    /**
     * Get amount by service
     *
     * @param int $booking_obj_id
     * @param int $service_id
     * @param array $rules_cat
     * @param array $service_age_qty
     * @param string $date_from - format Y-m-d H:i
     * @param array $guests
     * @param string $date_to - format Y-m-d H:i
     * @param array $prices
     * @param string $currency
     * @return array
     * @throws Exception
     */
    public static function get_service_price(
        $booking_obj_id,
        $service_id,
        $rules_cat,
        $service_age_qty,
        $date_from,
        $guests = array(),
        $date_to = '',
        $prices = array(),
        $currency = ''
    ): array
    {
        $prices_arr = array();
        
        $date_to = !$date_to ? $date_from : $date_to;
        $guests_total = array_sum($guests);

        $tax_am = (float)apply_filters('babe_prices_get_service_price_post_tax', BABE_Post_types::get_post_tax($booking_obj_id), $booking_obj_id)/100;

        /// get service meta
        $service_meta = (array)get_post_meta($service_id);

        foreach($service_meta as $key=>$val){
            $service_meta[$key] = maybe_unserialize($val[0]);
        }

        if ( !isset($service_meta['service_type'], $service_meta['price_type']) ){
            return $prices_arr;
        }

        if ( !is_array($service_meta['prices']) || empty($service_meta['prices']) ){
            $service_meta['prices'][0] = 0;
        }

        $begin = new DateTime( $date_from );
        $end = new DateTime( $date_to );

        $babe_post = BABE_Post_types::get_post($booking_obj_id);
        
        if ( $rules_cat['rules']['basic_booking_period'] === 'recurrent_custom' ){

            $end = clone($begin);
            if ( !empty($babe_post['duration']['d']) ){
                $end->modify("+".$babe_post['duration']['d']." days");
            }
            if ( !empty($babe_post['duration']['h']) ){
                $end->modify("+".$babe_post['duration']['h']." hours");
            }
            if ( !empty($babe_post['duration']['i']) ){
                $end->modify("+".$babe_post['duration']['i']." minutes");
            }
        }

        if (
            in_array($service_meta['service_type'], ['day', 'person_day'])
            && $end->format('H:i') != $begin->format('H:i')
        ){
            $end->modify( '+1 day' );
        }

        $d_interval = date_diff($begin, $end);
        $days_total = (int)$d_interval->format('%a'); // total days or nights
        if ( !$days_total ){
            $days_total = 1;
        }

        $multiplier = $service_meta['service_type'] === 'person' || $service_meta['service_type'] === 'booking'
            ? 1 : $days_total;

        if (
            $service_meta['service_type'] === 'booking'
            || $service_meta['service_type'] === 'day'
            || $service_meta['service_type'] === 'night'
        ){  ///// per booking, per day, per night
            $age_id = 0;

            $service_qty = !empty($service_age_qty[$age_id])
            && !empty($service_meta['allow_quantity'])
            && !empty($service_meta['max_quantity'])
            && (int)$service_meta['max_quantity'] >= (int)$service_age_qty[$age_id]
                ? (int)$service_age_qty[$age_id] : 1;

            $price_clear = (float)$service_meta['prices'][0]*$service_qty;

            /// check conditional prices
            if ( isset($service_meta['conditional_prices']) ){
                $conditional_price = self::calculate_rate_conditional_price( $service_meta['conditional_prices'], $age_id, $guests_total, $days_total, 1 );
                if ( $conditional_price !== false ){
                    $price_clear = (float)$conditional_price*$service_qty;
                }
            }

            if ($service_meta['service_type'] === 'booking' && $service_meta['price_type'] === 'percent'){

                $obj_total_price = self::get_obj_total_price($booking_obj_id, $prices);
                $multiplier = $price_clear/100;
                $prices_arr['services'][$service_id]['clear'][0] = $obj_total_price['total_item']*$multiplier;
                $prices_arr['services'][$service_id]['clear_with_taxes'][0] = round($obj_total_price['total_item_with_taxes']*$multiplier, 2);

            } else {
                $price_clear = self::localize_price($price_clear, $currency);
                $prices_arr['services'][$service_id]['clear'][0] = $price_clear*$multiplier;
                $prices_arr['services'][$service_id]['clear_with_taxes'][0] = ($price_clear + round($price_clear * $tax_am, 2))*$multiplier;
            }

        } else { ///// per person, per person_day, per person_night

            $iterator = !empty($service_meta['allow_quantity']) ? $service_age_qty : $guests;

            foreach ($iterator as $age_id => $guests_number){

                if ( !$guests_number || !isset($service_meta['prices'][$age_id]) ){
                    continue;
                }

                $service_age_price = $service_meta['prices'][$age_id] === '' ? (float)$service_meta['prices'][0] : (float)$service_meta['prices'][$age_id];

                $service_qty = isset($service_age_qty[$age_id])
                && !empty($service_meta['allow_quantity'])
                && !empty($service_meta['max_quantity'])
                && (int)$service_meta['max_quantity'] >= (int)$service_age_qty[$age_id]
                    ? (int)$service_age_qty[$age_id] : $guests_number;

                $price_clear = $service_age_price*$service_qty;

                /// check conditional prices
                if ( isset($service_meta['conditional_prices']) ){
                    $conditional_price = self::calculate_rate_conditional_price( $service_meta['conditional_prices'], $age_id, $guests_total, $days_total, 1 );
                    if ( $conditional_price !== false ){
                        $price_clear = (float)$conditional_price*$service_qty;
                    }
                }

                $price_clear = self::localize_price($price_clear, $currency);
                $prices_arr['services'][$service_id]['clear'][$age_id] = $price_clear*$multiplier;
                $prices_arr['services'][$service_id]['clear_with_taxes'][$age_id] = ($price_clear + round($price_clear * $tax_am, 2))*$multiplier;

            } //// end foreach $guests
        }
        
        return $prices_arr;
    }

    ///////////////////////////////
    /**
     * Get amount by fee
     *
     * @param int $booking_obj_id
     * @param int $fee_id
     * @param array $rules_cat - rules with category_meta
     * @param string $date_from - format Y-m-d H:i
     * @param array $guests
     * @param string $date_to - format Y-m-d H:i
     * @param array $prices
     * @param string $currency
     * @return array
     */
    public static function get_fee_price($booking_obj_id, $fee_id, $rules_cat, $date_from, $guests = [], $date_to = '', $prices = [], $currency = ''){

        $prices_arr = [];

        $price_type = get_post_meta($fee_id, 'price_type', true);
        $price = (float)get_post_meta($fee_id, 'price', true);

        if ( $price_type === 'percent' ){
            $obj_total_price = self::get_obj_total_price($booking_obj_id, $prices);
            $prices_arr['fees'][$fee_id] = $obj_total_price['total_item_with_taxes']*$price/100;
        } else {
            $prices_arr['fees'][$fee_id] = self::localize_price($price, $currency);
        }

        return $prices_arr;
    }

    ///////////////////////////
    /**
     * Localize price value
     *
     * @param float $amount
     * @param string $currency
     * @return float
     */
    public static function localize_price( $amount, $currency ){

        return apply_filters( 'babe_prices_localize_price', $amount, $currency);
    }

///////////////////////////////
}

BABE_Prices::init(); 
   