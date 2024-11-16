<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Booking_Rules_admin Class.
 * Create and manage Booking rules - templates for creating and storing booking rules in Booking Objects later
 * @class 		BABE_Booking_Rules_admin
 * @version		1.4.18
 * @author 		Booking Algorithms
 */

class BABE_Booking_Rules_admin {
    
    private static $nonce_title = 'booking-rules-tpl-nonce';
    
///////////////////////////////////////    
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_booking_rules_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'booking_rules_page_init' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
        
        add_action( 'wp_ajax_del_rule', array( __CLASS__, 'ajax_del_booking_rule'));      
	}
///////////////////////////////////////
    /**
	 * Enqueue assets.
	 */
    public static function admin_enqueue_scripts() {

        if (
                !isset($_GET['post_type'], $_GET['page'])
                || $_GET['post_type'] !== BABE_Post_types::$booking_obj_post_type
                || $_GET['page'] !== 'booking_rules'
        ){
            return;
        }

        wp_enqueue_script( 'babe-sweetalert', plugins_url( 'js/sweetalert/sweetalert2.all.min.js', BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );

        wp_enqueue_script( 'babe-admin-rules-js', plugins_url( "js/admin/babe-admin-rules.js", BABE_PLUGIN ), array('jquery'), BABE_VERSION, true );

        wp_localize_script( 'babe-admin-rules-js', 'babe_rules_lst', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce(self::$nonce_title),
                'messages' => [
                    'are_you_sure' => _x('Are you sure?', 'admin modal messages', 'ba-book-everything'),
                    'ok' => _x('Ok', 'admin modal messages', 'ba-book-everything'),
                    'cancel' => _x('Cancel', 'admin modal messages', 'ba-book-everything'),
                    'oops' => _x('Oops...', 'admin modal messages', 'ba-book-everything'),
                    'something_wrong' => _x('Something went wrong!', 'admin modal messages', 'ba-book-everything'),
                    'page_will_be_reloaded' => _x('The page will be reloaded', 'admin modal messages', 'ba-book-everything'),
                    'done' => _x('Done!', 'admin modal messages', 'ba-book-everything'),
                    'save' => _x('Save', 'admin modal messages', 'ba-book-everything'),
                    'delete' => _x('Delete', 'admin modal messages', 'ba-book-everything'),
                    'add_rule' => __('Add rule', 'ba-book-everything'),
                    'update_rule' => __('Update rule', 'ba-book-everything'),
                ],
            )
        );

        wp_enqueue_style( 'babe-admin-rules-style', plugins_url( "css/admin/babe-admin-rules.css", BABE_PLUGIN ), array(), BABE_VERSION);

     }
///////////////////////////////////////    
    /**
	 * Add Booking rules admin page to menu.
	 */
    public static function add_booking_rules_page(){
        
        add_submenu_page( 'edit.php?post_type='.BABE_Post_types::$booking_obj_post_type, __('Booking rules', 'ba-book-everything'), __('Booking rules', 'ba-book-everything'), 'manage_options', 'booking_rules', array( __CLASS__, 'create_booking_rules_page' ));
        
    }
///////////////////////////////////////
    /**
	 * Create Booking rules admin page.
	 */
    public static function create_booking_rules_page(){
                
        ?>
        <div class="wrap">
            <h2><?php echo __('Booking rules', 'ba-book-everything'); ?></h2>
            
            <table id="booking-rules-table">
            <?php echo self::get_booking_rules_thead().self::get_booking_rules_list(); ?>
            </table>
            
            <h2><?php // echo __('Add new booking rule', 'ba-book-everything'); ?></h2>
            <form method="post" action="options.php">
                <input type="hidden" id="rule_id" value="" name="babe_tmp_settings[rule_id]">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'babe-tmp-settings' );
                do_settings_sections( 'babe-tmp-settings' );
            ?>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Add rule', 'ba-book-everything'); ?>">
                    <input type="button" name="cancel" id="cancel" class="button button-secondary hidden" value="<?php echo __('Cancel', 'ba-book-everything'); ?>">
                </p>
            </form>
        </div>
        <?php
    }
//////////////////////////////////////    
    /**
	 * Booking rules table header.
     * @return string
	 */
    public static function get_booking_rules_thead(){
       $output = '';
       
       $output .= '<thead>
       <tr class="booking-rules-thead">
            <th>'.__('Title', 'ba-book-everything').'</th>
            <th>'.__('Basic booking period', 'ba-book-everything').'</th>
            <th>'.__('Use Ages', 'ba-book-everything').'</th>
            <th>'.__('Payment model', 'ba-book-everything').'</th>
            <th>'.__('Deposit, %', 'ba-book-everything').'</th>
            <th>'.__('Hold', 'ba-book-everything').'</th>
            <th>'.__('Stop booking .. hours before the start', 'ba-book-everything').'</th>
            <th>'.__('Booking mode', 'ba-book-everything').'</th>
            <th></th>
            <th></th>
            </tr>
            </thead>';
            
       /*
       '<th>'.__('Min booking period', 'ba-book-everything').'</th>
            <th>'.__('Max booking period', 'ba-book-everything').'</th>'
            <th>'.__('Reccurent payments', 'ba-book-everything').'</th>
       */     
       
       return $output; 
    }    
    
//////////////////////////////////////    
    /**
	 * Booking rules list.
     * @return string
	 */
    public static function get_booking_rules_list(){
    $output = ''; 
    
    $booking_rules = BABE_Booking_Rules::get_all_rules();
    
    if (!empty($booking_rules)){
   
      foreach ($booking_rules as $booking_rule_id => $rule){
        
       $ages_val = $rule['ages'] ? __('yes', 'ba-book-everything') : __('no', 'ba-book-everything');
       $recurrent_val = $rule['recurrent_payments'] ? __('yes', 'ba-book-everything') : __('no', 'ba-book-everything');
       $stop_before = isset($rule['stop_booking_before']) && $rule['stop_booking_before'] ? $rule['stop_booking_before'].' '. __('hours', 'ba-book-everything').' '.'<span class="booking-rule-hidden-label">'.__(' before the start', 'ba-book-everything').'</span>' : '';
       $hold = isset($rule['hold']) && $rule['hold'] ? $rule['hold'].' '. __('hours', 'ba-book-everything') : '';
       
       switch($rule['payment_model']){
        case 'deposit':
          $payment_model_val = __('deposit', 'ba-book-everything');
          break;
        case 'deposit_full':
          $payment_model_val = __('deposit and full', 'ba-book-everything');
          break;
        case 'full':
        default:
          $payment_model_val = __('full', 'ba-book-everything');
          break;    
       }
       
       switch($rule['basic_booking_period']){
        case 'single_custom':
          $basic_booking_val = __('single custom', 'ba-book-everything');
          break;
        case 'recurrent_custom':
          $basic_booking_val = __('recurrent custom', 'ba-book-everything');
          break;  
        case 'day':
          $basic_booking_val = __('1 day', 'ba-book-everything');
          break;
        case 'month':
          $basic_booking_val = __('1 month', 'ba-book-everything');
          break;
       case 'hour':
	       $basic_booking_val = __('1 hour', 'ba-book-everything');
	       break;
	       case 'night':
        default:
          $basic_booking_val = __('1 night', 'ba-book-everything');
          break;    
       }
        
       $output .= '
       <tr data-i="'.$booking_rule_id.'">
       
       <td class="booking-rule-title">
       <span class="booking-rule-hidden-label">'.__('Title', 'ba-book-everything').':</span>       
       '.$rule['rule_title'].'
       </td>
       
       <td class="booking-rule-basic">
       <span class="booking-rule-hidden-label">'.__('Basic booking period', 'ba-book-everything').':</span>       
       '.$basic_booking_val.'
       </td>
       
       <td class="booking-rule-ages">
       <span class="booking-rule-hidden-label">'.__('Use Ages', 'ba-book-everything').':</span>       
       '.$ages_val.'
       </td>
       
       <td class="booking-rule-payment">
       <span class="booking-rule-hidden-label">'.__('Payment model', 'ba-book-everything').':</span>       
       '.$payment_model_val.'
       </td>
       
       <td class="booking-rule-deposit">
       <span class="booking-rule-hidden-label">'.__('Deposit, %', 'ba-book-everything').':</span>       
       '.$rule['deposit'].'
       </td>
       
       <td class="booking-rule-stop-before">
       <span class="booking-rule-hidden-label">'.__('Hold', 'ba-book-everything').':</span>       
       '.$hold.'
       </td>
       
       <td class="booking-rule-stop-before">
       <span class="booking-rule-hidden-label">'.__('Stop booking ', 'ba-book-everything').':</span>       
       '.$stop_before.'
       </td>
       
       <td class="booking-rule-mode">
       <span class="booking-rule-hidden-label">'.__('Booking mode', 'ba-book-everything').':</span>       
       '.$rule['booking_mode'].'
       </td>
       
       <td class="booking_rule_edit" data-i="'.$booking_rule_id.'" data-rule="'.htmlspecialchars(json_encode($rule), ENT_QUOTES | JSON_HEX_APOS, 'UTF-8').'">
       <span class="booking-rule-hidden-label">'.__('Edit this rule', 'ba-book-everything').':</span>
       <i class="fas fa-edit" title="'.__('Edit this rule', 'ba-book-everything').'"></i>
       </td>
       
       <td class="booking_rule_del" data-i="'.$booking_rule_id.'">
       <span class="booking-rule-hidden-label">'.__('Delete this rule', 'ba-book-everything').':</span>
       <i class="fas fa-trash-alt" title="'.__('Delete this rule', 'ba-book-everything').'"></i></td>  
       </tr>';
       
       }
     }
     
   $output = '<tbody>'.$output.'</tbody>';  
        
   return $output; 
   }    
///////////////////////////////////////    
    /**
	 * Delete Booking rule by buking_rule_id.
	 */
    public static function ajax_del_booking_rule(){
        
        $output = '<li>'.__('An error occurred while deleting the rule', 'ba-book-everything').'</li>';
        
        if (
                isset($_POST['booking_rule_id'])
                && isset($_POST['nonce'])
                && wp_verify_nonce( $_POST['nonce'], self::$nonce_title )
                && current_user_can( 'manage_options' )
        ){
           $deleted = BABE_Booking_Rules::delete_rule((int)$_POST['booking_rule_id']);
           $output = $deleted ? self::get_booking_rules_thead().self::get_booking_rules_list() : $output;
        }
        
        echo $output;
        wp_die();
    }

///////////////////////////////////////    
    /**
	 * Booking rules page form init.
	 */
    public static function booking_rules_page_init(){
        register_setting(
            'babe-tmp-settings', // Option group
            'babe_tmp_settings', // Option name
            array( __CLASS__, 'sanitize' ) // Sanitize
        );

        ///////// Add new booking rule
        
        add_settings_section(
            'setting_section_1', // ID
            __('Add new booking rule','ba-book-everything'), // Title
            array( __CLASS__, 'print_section_info_1' ), // Callback
            'babe-tmp-settings' // Page
        );

        add_settings_field(
            'rule_title', // ID
            __('Title','ba-book-everything'), // Title
            array( __CLASS__, 'callback_title' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        
        add_settings_field(
            'basic_booking_period', // ID
            __('Basic booking period','ba-book-everything'), // Title
            array( __CLASS__, 'callback_basic_booking_period' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        
        /*
        add_settings_field(
            'min_booking_period', // ID
            __('Minimum booking period','ba-book-everything'), // Title
            array( __CLASS__, 'callback_min_booking_period' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        
        add_settings_field(
            'max_booking_period', // ID
            __('Maximum booking period','ba-book-everything'), // Title
            array( __CLASS__, 'callback_max_booking_period' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        
        */
        
        add_settings_field(
            'hold', // ID
            __('Hold after each booking for service actions (applied to 1 day booking priod only)','ba-book-everything'), // Title
            array( __CLASS__, 'callback_hold' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        
        add_settings_field(
            'stop_booking_before', // ID
            __('Stop booking .. hours before the start (applied to recurrent custom booking period only)','ba-book-everything'), // Title
            array( __CLASS__, 'callback_stop_booking_before' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        
        add_settings_field(
            'ages', // ID
            __('Use Age categories for prices?','ba-book-everything'), // Title
            array( __CLASS__, 'callback_ages' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        
        add_settings_field(
            'payment_model', // ID
            __('Payment model','ba-book-everything'), // Title
            array( __CLASS__, 'callback_payment_model' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        
        add_settings_field(
            'deposit', // ID
            __('Deposit, %','ba-book-everything'), // Title
            array( __CLASS__, 'callback_deposit' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        
        /*
        add_settings_field(
            'recurrent_payments', // ID
            __('Recurrent payments','ba-book-everything').' <span class="babe_developement">'.__('(*in the development)', 'ba-book-everything').'</span>', // Title
            array( __CLASS__, 'callback_recurrent_payments' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );
        */
        
        add_settings_field(
            'booking_mode', // ID
            __('Booking mode','ba-book-everything'), // Title
            array( __CLASS__, 'callback_booking_mode' ), // Callback
            'babe-tmp-settings', // Page
            'setting_section_1' // Section
        );

    }
///////////////////////////////////////    
    /**
	 * Sanitize form inputs and save our new booking rule.
	 */
    public static function sanitize($input){

        $rule['rule_title'] = isset($input['rule_title']) ? sanitize_text_field($input['rule_title']) : '';

        $rule['basic_booking_period'] = isset($input['basic_booking_period'], BABE_Booking_Rules::$booking_periods[$input['basic_booking_period']]) ? $input['basic_booking_period'] : 'night';
       /*
       $rule['min_booking_period'] = isset($input['min_booking_period']) ? intval($input['min_booking_period']) : 0;
       $rule['max_booking_period'] = intval($input['max_booking_period']);
       */

        $rule['hold'] = isset($input['hold']) && absint($input['hold']) < 24 ? absint($input['hold']) : 0;

        $rule['stop_booking_before'] = isset($input['stop_booking_before']) ? (int)$input['stop_booking_before'] : 0;

        $rule['deposit'] = isset($input['deposit']) ? (float)$input['deposit'] : '';

        $rule['ages'] = isset($input['ages']) ? (int)$input['ages'] : 0;

        $rule['payment_model'] = isset($input['payment_model'], BABE_Booking_Rules::$payment_models[$input['payment_model']]) ? $input['payment_model'] : 'full';

        $rule['recurrent_payments'] = isset($input['recurrent_payments']) ? (int)$input['recurrent_payments'] : 0;

        $rule['booking_mode'] = isset($input['booking_mode'], BABE_Booking_Rules::$booking_modes[$input['booking_mode']]) ? $input['booking_mode'] : 'object';

        $rule['rule_id'] = isset($input['rule_id']) ? abs((int)$input['rule_id']) : 0;

        if ($rule['rule_title']){
            BABE_Booking_Rules::add_rule($rule);
        }

        return [];
    }    
//////////////////////////////////////
    /**
	 * Section info.
	 */
    public static function print_section_info_1(){        
    }    
//////////////////////////////////////
    /**
	 * Title form input.
	 */
    public static function callback_title(){
        echo '<input type="text" id="rule_title" name="babe_tmp_settings[rule_title]" value="" />';        
    }    
//////////////////////////////////////
    /**
	 * Basic booking period form input.
	 */
    public static function callback_basic_booking_period(){
        echo '<p><input id="babe_tmp_settings[basic_booking_period]1" name="babe_tmp_settings[basic_booking_period]" type="radio" value="single_custom" class="booking_period_data" data-label="'.__('not used', 'ba-book-everything').'"/><label for="babe_tmp_settings[basic_booking_period]1">'.__('Single custom (one-time events, etc.)', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[basic_booking_period]2" name="babe_tmp_settings[basic_booking_period]" type="radio" value="recurrent_custom" class="booking_period_data" data-label="'.__('not used', 'ba-book-everything').'"/><label for="babe_tmp_settings[basic_booking_period]2">'.__('Recurrent custom (weekly schedule for tours, events, etc.)', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[basic_booking_period]3" name="babe_tmp_settings[basic_booking_period]" type="radio" value="day" class="booking_period_data" data-label="'.__('days', 'ba-book-everything').'" /><label for="babe_tmp_settings[basic_booking_period]3">'.__('1 day (cars, bikes, etc.)', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[basic_booking_period]4" name="babe_tmp_settings[basic_booking_period]" type="radio" value="night" class="booking_period_data" data-label="'.__('nights', 'ba-book-everything').'" checked="checked" /><label for="babe_tmp_settings[basic_booking_period]4">'.__('1 night (hotels, apartments, hostels, etc.)', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[basic_booking_period]5" name="babe_tmp_settings[basic_booking_period]" type="radio" value="hour" class="booking_period_data" data-label="'.__('hours', 'ba-book-everything').'" /><label for="babe_tmp_settings[basic_booking_period]5">'.__('1 hour (hourly rent: bikes, sport equipment, etc.)', 'ba-book-everything').'</label></p>';
        //'
        //<p><input id="babe_tmp_settings[basic_booking_period]6" name="babe_tmp_settings[basic_booking_period]" type="radio" value="month"  class="booking_period_data" data-label="'.__('not used', 'ba-book-everything').'" /><label for="babe_tmp_settings[basic_booking_period]6">'.__('1 month (realty, properties, etc.)', 'ba-book-everything').'</label></p>';
    }
/////////////////////////////////////
    /**
	 * Minimum booking period form input.
	 */
    public static function callback_min_booking_period(){
        echo '<input type="text" id="min_booking_period" name="babe_tmp_settings[min_booking_period]" value="" /><span class="booking_period_label"></span>';        
    }    
//////////////////////////////////////
    /**
	 * Maximum booking period form input.
	 */
    public static function callback_max_booking_period(){
        echo '<input type="text" id="max_booking_period" name="babe_tmp_settings[max_booking_period]" value="" /><span class="booking_period_label"></span>';        
    }    
//////////////////////////////////////
    /**
	 * Hold after each booking form input.
	 */
    public static function callback_hold(){
        echo '<input type="text" id="hold" name="babe_tmp_settings[hold]" value="" /><span class="booking_period_label2">'.__('hours. 0 or nothing for no hold period.', 'ba-book-everything').'</span>';
    }
    
/////////stop_booking_before////////////
    /**
	 * Stop booking before form input.
	 */
    public static function callback_stop_booking_before(){
        echo '<input type="text" id="stop_booking_before" name="babe_tmp_settings[stop_booking_before]" value="" /><span class="booking_period_label2">'.__('hours', 'ba-book-everything').'</span>';
    }
    
//////////////////////////////////////
    /**
	 * Deposit, %.
	 */
    public static function callback_deposit(){
        echo '<input type="text" id="deposit" name="babe_tmp_settings[deposit]" value="" />';
    }
        
//////////////////////////////////////    
    /**
	 * Use Age categories for prices form input.
	 */
    public static function callback_ages(){
        echo '<p><input id="babe_tmp_settings[ages]1" name="babe_tmp_settings[ages]" type="radio" value="0" checked="checked" /><label for="babe_tmp_settings[ages]1">'.__('No', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[ages]2" name="babe_tmp_settings[ages]" type="radio" value="1" /><label for="babe_tmp_settings[ages]2">'.__('Yes', 'ba-book-everything').'</label></p>';
    }
/////////////////////////////////////     
    /**
	 * Payment model form input.
	 */
    public static function callback_payment_model(){
        echo '<p><input id="babe_tmp_settings[payment_model]1" name="babe_tmp_settings[payment_model]" type="radio" value="deposit" /><label for="babe_tmp_settings[payment_model]1">'.__('Pay deposit amount', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[payment_model]2" name="babe_tmp_settings[payment_model]" type="radio" value="full" checked="checked" /><label for="babe_tmp_settings[payment_model]2">'.__('Pay full amount', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[payment_model]3" name="babe_tmp_settings[payment_model]" type="radio" value="deposit_full" /><label for="babe_tmp_settings[payment_model]3">'.__('Pay deposit or full amount (the customer will choose)', 'ba-book-everything').'</label></p>';
    }
///////////////////////////////////// 
    /**
	 * Recurrent Payments form input.
	 */
    public static function callback_recurrent_payments(){
        echo '<p><input id="babe_tmp_settings[recurrent_payments]1" name="babe_tmp_settings[recurrent_payments]" type="radio" value="0" checked="checked" /><label for="babe_tmp_settings[recurrent_payments]1">'.__('No', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[recurrent_payments]2" name="babe_tmp_settings[recurrent_payments]" type="radio" value="1" /><label for="babe_tmp_settings[recurrent_payments]2">'.__('Yes (for 1 month basic booking period and pay full amount mode only)', 'ba-book-everything').'</label></p>';
    }
/////////////////////////////////////
    /**
	 * Booking mode form input.
	 */
    public static function callback_booking_mode(){
        echo '<p><input id="babe_tmp_settings[booking_mode]1" name="babe_tmp_settings[booking_mode]" type="radio" value="object" checked="checked" /><label for="babe_tmp_settings[booking_mode]1">'.__('Object booking', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[booking_mode]2" name="babe_tmp_settings[booking_mode]" type="radio" value="places" /><label for="babe_tmp_settings[booking_mode]2">'.__('Places booking', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[booking_mode]3" name="babe_tmp_settings[booking_mode]" type="radio" value="tickets" /><label for="babe_tmp_settings[booking_mode]3">'.__('Tickets booking', 'ba-book-everything').'</label></p>
        <p><input id="babe_tmp_settings[booking_mode]4" name="babe_tmp_settings[booking_mode]" type="radio" value="request" /><label for="babe_tmp_settings[booking_mode]4">'.__('Request for price and details', 'ba-book-everything').'</label></p>';
    }
/////////////////////////////////////
    
}

BABE_Booking_Rules_admin::init();
