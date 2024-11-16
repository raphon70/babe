<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

BABE_My_account::init();

/**
 * BABE_My_account Class.
 * Get general settings
 * @class 		BABE_My_account
 * @version		1.6.21
 * @author 		Booking Algorithms
 */

class BABE_My_account {
    
    // variables to use
    private static $nonce_title = 'babe-nonce';
    
    public static $account_page_var = 'inner_page';
    
    public static $icons = array(
      'dashboard' => 'fas fa-home',
      'profile' => 'fas fa-user',
      'edit-profile' => 'fas fa-edit',
      'company-profile' => 'fas fa-university',
      'edit-company-profile' => 'fas fa-edit',
      'change-password' => 'fas fa-unlock-alt',
      'activity' => 'far fa-calendar-check',
      'my-bookings' => 'fas fa-shopping-cart',
      'my-orders' => 'fas fa-shopping-cart',
      'logout' => 'fas fa-sign-out-alt',
      'login' => 'fas fa-sign-in-alt',
      'default' => 'fas fa-tag',
      'post_to_book' => 'fas fa-globe',
      'all-posts-to_book' => 'fas fa-folder-open',
      'new-post-to_book' => 'fas fa-edit',
      'post_service' => 'fas fa-coffee',
      'all-posts-service' => 'fas fa-folder-open',
      'new-post-service' => 'fas fa-edit',
        'post_fee' => 'fas fa-comment-dollar',
        'all-posts-fee' => 'fas fa-folder-open',
        'new-post-fee' => 'fas fa-edit',
      'post_faq' => 'far fa-comment-dots',
      'all-posts-faq' => 'fas fa-comments',
      'new-post-faq' => 'far fa-comment',
     );
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {

        add_action( 'template_redirect', array( __CLASS__, 'admin_av_confirmation'), 25);

        if ( class_exists('BABE_Settings') && !empty(BABE_Settings::$settings['my_account_disable']) ){
            return;
        }
        
        add_action( 'init', array( __CLASS__, 'init_icons') );
        
        add_filter( 'babe_my_account_content', array( __CLASS__, 'my_account_content'), 10, 1 );
        
        add_filter( 'babe_myaccount_page_content_customer', array( __CLASS__, 'customer_dashboard'), 10, 2 );
        add_filter( 'babe_myaccount_page_content_customer', array( __CLASS__, 'edit_profile'), 10, 2 );
        add_filter( 'babe_myaccount_page_content_customer', array( __CLASS__, 'change_user_password'), 10, 2 );
        add_filter( 'babe_myaccount_page_content_customer', array( __CLASS__, 'my_bookings'), 10, 2 );
        
        add_filter( 'babe_myaccount_page_content_manager', array( __CLASS__, 'manager_dashboard'), 10, 2 );
        add_filter( 'babe_myaccount_page_content_manager', array( __CLASS__, 'edit_profile'), 10, 2 );
        add_filter( 'babe_myaccount_page_content_manager', array( __CLASS__, 'change_user_password'), 10, 2 );
        
        add_filter( 'babe_myaccount_page_content_manager', array( __CLASS__, 'admin_orders'), 10, 2 );
        
        add_filter( 'babe_myaccount_page_content_manager', array( __CLASS__, 'edit_post'), 10, 2 );
        
        add_filter( 'babe_myaccount_page_content_manager', array( __CLASS__, 'all_posts'), 10, 2 );
        
        add_action( 'template_redirect', array( __CLASS__, 'login_action'), 10);
        
        add_action( 'template_redirect', array( __CLASS__, 'register_customer'), 10);
        
        add_filter( 'babe_login_form_message', array( __CLASS__, 'after_registration_message'), 10, 1);
        
        add_action( 'template_redirect', array( __CLASS__, 'my_account_update'), 20);
        
        add_action( 'template_redirect', array( __CLASS__, 'new_post'), 30);
        
        add_action( 'wp_logout', array( __CLASS__, 'logout_page') );
        add_action( 'wp_login_failed', array( __CLASS__, 'login_failed' ));
        add_filter( 'wp_login_errors', array( __CLASS__, 'login_errors'), 10, 2 );
         
	}
    
///////////////////////////////////////
    /**
	 * Logout page redirect.
     * @return
	 */
    public static function logout_page() {
        
        wp_redirect( BABE_Settings::get_my_account_page_url() );
        exit;
    }
    
///////////////////////////////////////
    /**
	 * Login failed page redirect.
     * @return
	 */
    public static function login_failed() {
        
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';  // where did the post submission come from?
     // if there's a valid referrer, and it's not the default log-in screen
        if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
          wp_redirect(BABE_Settings::get_my_account_page_url(array('login' => 'failed')));  // let's append some information (login=failed) to the URL for the theme to use
          exit;
        }
        
        return;
    }
    
////////////////////////////
    /**
	 * Filters the login page errors.
     * 
	 * @param WP_Error $errors
	 * @param string $redirect_to Redirect destination URL.
	 */
    public static function login_errors($errors, $redirect_to){
    
       if ( 'incorrect_password' === $errors->get_error_code() || 'empty_password' === $errors->get_error_code() ){
        wp_redirect(BABE_Settings::get_my_account_page_url(array('login' => 'failed')));  // let's append some information (login=failed) to the URL for the theme to use
        exit;
       }
    
       return $errors;
    }
    
//////////////////////////////    
    /**
	 * After registration message.
     * 
     * @param string $content
     * @return string
	 */
    public static function after_registration_message($content){
        
        $output = $content;
        
        if (isset($_GET['status']) && $_GET['status'] == 'new_user_registered'){
            $output .= '          
          <!-- Modal -->
<div class="modal fade" id="user_registered_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="exampleModalLabel">'.__( 'Thank you for registration! Check email for your password.', 'ba-book-everything' ).'</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" data-dismiss="modal">'.__( 'Close', 'ba-book-everything' ).'</button>
      </div>
    </div>
  </div>
</div>
          ';
        }
        
        return $output;
    }    
    
//////////////////////////////    
    /**
	 * Registration form.
     * 
     * @return string
	 */
    public static function get_register_form(){
        
        $output = '';
        
        if ( get_option( 'users_can_register' ) ) :
        
       $output .= '
       
       <div id="login_registration">
        
        <h3>'.__('Do not have an account?', 'ba-book-everything').'</h3>
        
        <!-- Button trigger modal -->
        <div class="registration_link"><a href="#registration" data-toggle="modal" data-target="#registration">'.__('Create an account', 'ba-book-everything').'</a></div>
        <!-- -->
        
		<!-- Modal -->
<div class="modal fade" id="registration" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">'.__('Create an account', 'ba-book-everything').'</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body">
      
        <form name="registration_form" id="registration_form" action="'.BABE_Settings::get_my_account_page_url(array('action' => 'registration')).'" method="post">
        
            <div class="new-username">
				<label for="new_username">'.__('Username*', 'ba-book-everything').'</label>
				<input type="text" name="new_username" id="new_username" class="input" value="" size="20" required="required">
			</div>
            
            <div class="new-username-check">
              <span class="new-username-check-msg">'.__('This username already exists', 'ba-book-everything').'</span>
            </div>
            
            <div class="new-first-name">
				<label for="new_first_name">'.__('First name*', 'ba-book-everything').'</label>
				<input type="text" name="new_first_name" id="new_first_name" class="input" value="" size="20" required="required">
			</div>
            <div class="new-last-name">
				<label for="new_last_name">'.__('Last name*', 'ba-book-everything').'</label>
				<input type="text" name="new_last_name" id="new_last_name" class="input" value="" size="20" required="required">
			</div>
            
            <div class="new-email">
				<label for="new_email">'.__('Your email*', 'ba-book-everything').'</label>
				<input type="text" name="new_email" id="new_email" class="input" value="" size="20" required="required">
                <div class="new-email-check-msg">'.__('This email already exists', 'ba-book-everything').'</div>
			</div>
            <div class="new-email">
				<label for="new_email_confirm">'.__('Confirm email*', 'ba-book-everything').'</label>
				<input type="text" name="new_email_confirm" id="new_email_confirm" class="input" value="" size="20" required="required">
			</div>
            			
            <div class="statement">
               <span class="register-notes">'.__('A password will be emailed to you.', 'ba-book-everything').'</span>
            </div>
			
			<div class="new-submit">
				<input type="submit" name="new-submit" id="new-submit" class="button button-primary" value="'.__('Sign up', 'ba-book-everything').'">
                <div class="form-spinner"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
			</div>
			
		</form>
        
        
      </div>
      
    </div>
  </div>
</div>         
		
        </div>';
        
        endif;
        
        return $output;
    }        
    
////////////////////////////        
    /**
	 * Login form.
     * 
     * @return string
	 */
    public static function get_login_form() {
        
        $output = '';
    
    if (!is_user_logged_in()){
        
        /*
        
        $args = array(
          'echo'           => false,
          'remember'       => false,
        //  'redirect'       => admin_url(),
          'form_id'        => 'loginform',
          'id_username'    => 'user_login',
          'id_password'    => 'user_pass',
          'id_remember'    => 'rememberme',
          'id_submit'      => 'wp-submit',
          'label_username' => __( 'Your email:', 'ba-book-everything' ),
          'label_password' => __( 'Password:', 'ba-book-everything' ),
          'label_remember' => __( 'Remeber Me', 'ba-book-everything' ),
          'label_log_in'   => __( 'LOGIN', 'ba-book-everything' ),
          'value_username' => '',
          'value_remember' => false
        );
        $output .= '<div id="login_form">
          '.wp_login_form( $args ).'
        </div>
        ';
        
        */
        
        $message = '';
        
        if (isset($_GET['action']) && $_GET['action'] == 'login_error'){
            $message .= '
          <div id="login_error">
            '.__( '<strong>ERROR</strong>: Invalid username or password.', 'ba-book-everything' ).' <a href="'.BABE_Settings::get_my_account_page_url(array('action' => 'lostpassword')).'">'.__( 'Forgot your password?', 'ba-book-everything' ).'</a>
          </div>';
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'password_reseted'){
            $message .= '
          <div id="password_reseted">
            '.__( 'Check your email address for instructions to reset password', 'ba-book-everything' ).'
          </div>';
        }
        
        if (!isset($_GET['action'])){
            $message .= '
          <div id="forgot_url">
            <a href="'.BABE_Settings::get_my_account_page_url(array('action' => 'lostpassword')).'">'.__( 'Forgot password?', 'ba-book-everything' ).'</a>
          </div>';
        }
        
        $message = apply_filters('babe_login_form_message', $message);
        
        $output .= '
        <div id="login_form">
        
          <h3>'.__('Login', 'ba-book-everything').'</h3>
          
          <form name="babe_login" id="babe_login" action="'.BABE_Settings::get_my_account_page_url(array('action' => 'login')).'" method="post">
          
            '.apply_filters('babe_login_form_before_fields', '').'
			
			<div class="login_username">
				<label for="login_username">'.__('Username or email', 'ba-book-everything').'</label>
				<input type="text" name="login_username" id="login_username" class="input" value="" size="20" required="required">
			</div>
			<div class="login_pw">
				<label for="login_pw">'.__('Password', 'ba-book-everything').'</label>
				<input type="password" name="login_pw" id="login_pw" class="input" value="" size="20" required="required">
			</div>
            
            '.apply_filters('babe_login_form_after_fields', '').'
            
            <div class="login_submit">
				<input type="submit" name="login_submit" id="login_submit" class="button button-primary" value="'.__('Sign in', 'ba-book-everything').'">
			</div>
            
            '.apply_filters('babe_login_form_after_button', '').'
            
            '.$message.'
			
		   </form>
          
            '.apply_filters('babe_login_form_after_form', '').'
          
        </div>';
        
        $output = apply_filters('babe_get_login_form', $output);
        
    }
             
    return $output;
    
    }
    
////////////////////////////        
    /**
	 * Lost password form.
     * 
     * @return string
	 */
    public static function get_lostpassword_form() {
        
        $output = '';
    
    if (!is_user_logged_in()){
        
        $output .= '
        <div id="lostpassword">
        
        <h2>'.__( 'Reset password', 'ba-book-everything' ).'</h2>
        
        <form id="lostpassword_reset" name="lostpassword_reset" method="post" action="">
            
            '.apply_filters('babe_lostpassword_form_before_fields', '').'
            
            <div class="lost_username">
				<label for="user_email">'.__( 'Your email', 'ba-book-everything' ).'</label>
				<input type="text" name="user_email" id="user_email" class="input" value="" size="20">
			</div>
            
            <input type="hidden" name="nonce" value="'.wp_create_nonce(self::$nonce_title).'">
            <input type="hidden" name="action" value="lostpassword_reset">
            
            <div class="lost_submit">
               <button class="btn button lostpassword_submit">'.__('Get new password', 'ba-book-everything').'</button>
            </div>
            
            '.apply_filters('babe_lostpassword_form_after_fields', '').'
       </form>
       
       </div>';
       
       $output = apply_filters('babe_get_lostpassword_form', $output);
        
    }
             
    return $output;
    
    }
    
/////////admin_av_confirmation////////        
    /**
	 * Confirm or reject the order.
     * 
     * @return
	 */
    public static function admin_av_confirmation(){
        
        global $post;
        
        if (is_singular() && $post->ID == (int)BABE_Settings::$settings['my_account_page']){
        
        $args = wp_parse_args( $_GET, array(
            'order_id' => 0,
            'order_num' => '',
            'order_admin_hash' => '',
            'admin_action' => '',
            'action_value' => '',
        ));
        
        $order_id = absint($args['order_id']);
        
        if ($order_id){
        
        $user_info = wp_get_current_user();
        if ($user_info->ID > 0){
         $check_role = self::validate_role($user_info);
         
         if ($check_role === 'manager' && $order_id){
            
            if ($args['admin_action'] === 'av_confirmation' && BABE_Settings::$settings['order_availability_confirm'] !== 'auto' && BABE_Order::is_order_admin_valid($order_id, $args['order_num'], $args['order_admin_hash'])){
            
            $order_status = BABE_Order::get_order_status($order_id);
            
            if ('av_confirmation' === $order_status){
                if ($args['action_value'] === 'confirm'){
                   BABE_Order::update_order_status($order_id, 'payment_expected');
                   BABE_Emails::send_email_new_order_to_pay($order_id); 
                } else {
                    //not_available
                   BABE_Order::update_order_status($order_id, 'not_available');
                }
                
                //// redirect
                unset($args['order_id']);
                unset($args['order_num']);
                unset($args['order_admin_hash']);
                unset($args['admin_action']);
                unset($args['action_value']);
                $url = BABE_Settings::get_my_account_page_url($args);
                wp_safe_redirect($url);                  
            }
            
            }
         
         } ///// end if $check_role == 'manager'
         
        } //// end if ($user_info->ID > 0)
        
        } //// end if $order_id
        
        } //// if my_account_page   
        
        return;
     }
     
////////////////////////////
     /**
	 * Do login actions
     * 
     * @return
	 */
     public static function login_action(){
        
       global $post; 
        
       if (is_singular() && $post->ID == (int)BABE_Settings::$settings['my_account_page'] && isset($_GET['action']) && $_GET['action'] === 'login' && isset($_POST['login_username']) && isset($_POST['login_pw']) && $_POST['login_username'] && $_POST['login_pw']){
        
       $redirect_flag = false; 
        
       $test_user_info = wp_get_current_user();
       if ($test_user_info->ID == 0){
        
            $username = sanitize_text_field($_POST['login_username']);
            $password = sanitize_text_field($_POST['login_pw']);
            
            if ( $user_info = get_user_by( 'login', $username ) ) {
                
                $user_id = $user_info->ID;
                
            } elseif (is_email($username) && $user_info = get_user_by( 'email', $username )) {
                
                $user_id = $user_info->ID;
                
            } else {
                
                $user_id = 0;
                
            }
            
            if ($user_id && wp_check_password( $password, $user_info->user_pass, $user_info->ID )){
                
                wp_set_auth_cookie($user_info->ID);
                wp_set_current_user($user_info->ID);
                do_action('wp_login', $user_info->user_login, $user_info);
                
                $redirect_flag = true;
                
            }
            
            //////////////////
            
            if ($redirect_flag){
                
                $url = BABE_Settings::get_my_account_page_url();
                wp_safe_redirect($url);
                
            } else {
                
                if ($user_id){
                   //// forgot password
                   wp_safe_redirect(BABE_Settings::get_my_account_page_url(array('action' => 'login_error')));
                    
                } else {
                   //// forgot username
                   wp_safe_redirect(BABE_Settings::get_my_account_page_url(array('action' => 'login_error')));
                    
                }
                
            }
          
         
       } //// end if ($test_user_info->ID == 0)
       
       }
     }     
         
/////////////////////////////                                          
     /**
	 * Do update actions
     * 
     * @return void
	 */
     public static function my_account_update(){

         global $post;

         if (
             !is_singular()
             || $post->ID !== (int)BABE_Settings::$settings['my_account_page']
             || !isset($_POST['nonce'])
             || !wp_verify_nonce( $_POST['nonce'], self::$nonce_title)
         ){
             return;
         }

         $redirect_flag = false;

         $user_info = wp_get_current_user();

         if ( $user_info !== null && $user_info->ID > 0 ){
             $check_role = self::validate_role($user_info);
             if ($check_role && !empty($_POST['action'])){

                 ////////edit_user_profile//////////
                 if ('edit_user_profile' === $_POST['action']){
                     $args = self::sanitize_user_profile_vars($_POST);
                     $check = $args['first_name'] && $args['last_name'] && $args['email'];
                     $check = apply_filters('babe_myaccount_edit_user_profile_ready', $check, $args);
                     if ($check){
                         ///// update profile
                         BABE_Users::update_current_user($args);
                         $redirect_flag = true;
                     }
                 }
                 ////////change_user_password//////////
                 if (
                     'change_user_password' === $_POST['action']
                     && isset($_POST['old_password'])
                     && wp_check_password( $_POST['old_password'], $user_info->user_pass, $user_info->ID )
                     && isset($_POST['new_password'])
                     && isset($_POST['new_password_again'])
                     && $_POST['new_password'] === $_POST['new_password_again']
                 ){
                     // Change password.
                     wp_set_password($_POST['new_password'], $user_info->ID);
                     // Log-in again.
                     wp_set_auth_cookie($user_info->ID);
                     wp_set_current_user($user_info->ID);
                     do_action('wp_login', $user_info->user_login, $user_info);

                     $redirect_flag = true;
                 }
                 //////////////////

                 if ($redirect_flag){
                     $url_args['inner_page'] = 'edit-profile';
                     $url_args['updated'] = 1;
                     $url = BABE_Settings::get_my_account_page_url($url_args);
                     wp_safe_redirect($url);
                 }

             } //// end if $check_role

         } elseif (
             isset($_GET['action'])
             && $_GET['action'] === 'lostpassword'
             && isset($_POST['action'])
             && $_POST['action'] === 'lostpassword_reset'
             && isset($_POST['user_email'])
             && is_email($_POST['user_email'])
             && email_exists( $_POST['user_email'] )
         ){
             ///// reset password for $_POST['user_email']
             $result = BABE_Users::reset_user_password($_POST['user_email']);
             if ( $result === true ){
                 wp_safe_redirect(BABE_Settings::get_my_account_page_url(array('action' => 'password_reseted')));
             }
         }
     }
     
//////////////////////////////////////
    /**
	 * Register new customer
     * 
     * @return void
	 */
    public static function register_customer(){    
        
        global $post;
        
        if (is_user_logged_in()) {
                return;
        }
        
        if (
            is_singular()
            && $post->ID == (int)BABE_Settings::$settings['my_account_page']
            && isset($_GET['action']) && $_GET['action'] === 'registration'
            && !empty($_POST['new_username'])
            && !username_exists($_POST['new_username'])
            && !empty($_POST['new_email'])
            && is_email( $_POST['new_email'] )
            && !email_exists($_POST['new_email'])
            && !empty($_POST['new_email_confirm'])
            && $_POST['new_email_confirm'] == $_POST['new_email']
            && !empty($_POST['new_first_name'])
            && !empty($_POST['new_last_name'])
        ){
            
            $meta['email'] = sanitize_email($_POST['new_email']);
            $meta['username'] = sanitize_user($_POST['new_username']);
            $meta['first_name'] = sanitize_text_field($_POST['new_first_name']);
            $meta['last_name'] = sanitize_text_field($_POST['new_last_name']);
            
            $customer_id = BABE_Users::create_customer($meta['email'], $meta, $meta['username']);
            if ($customer_id){
                
               $url = BABE_Settings::get_my_account_page_url(array('status' => 'new_user_registered'));
               wp_safe_redirect($url);
                
            }
        }
     }         
    
///////////////////////////////////////
    /**
	 * Init icons array.
     * @return void
	 */
    public static function init_icons() {
        
        self::$icons = apply_filters('babe_init_myaccount_icons', self::$icons);
        
        if (empty(self::$icons)){
            self::$icons['default'] = 'fas fa-tag';
        }
    }
    
///////////////////////////////////////
  /**
   * Validate customer role.
   * 
   * @param WP_User $user_info
   * @return string
   */
  public static function validate_role( $user_info ){
    
     $check_role = in_array('customer', $user_info->roles) || in_array('administrator', $user_info->roles) ? 'customer' : '';
       
     $check_role = in_array('manager', $user_info->roles) || in_array('administrator', $user_info->roles) ? 'manager' : $check_role;
     
     $check_role = apply_filters('babe_myaccount_validate_role', $check_role, $user_info );
       
     return $check_role;
  }            

///////////////////////////////////////
  /**
   * Create my account page html.
   * 
   * @param  string $content
   * @return string
   */
  public static function my_account_content( $content ){
	$output = $content;
    
    $user_info = wp_get_current_user();

    if ($user_info->ID > 0){
        
       $check_role = self::validate_role($user_info); 
       
       if ($check_role){
        
       $output .= '<div id="my_account_page_wrapper">';
       
       $nav_arr = self::get_nav_arr($check_role);
       
       $current_nav_slug_arr = self::get_current_nav_slug($nav_arr);
       $current_nav_slug = key($current_nav_slug_arr);
       
       $output .= '
       <div class="my_account_page_nav_wrapper">
         <input type="text" class="my_account_page_nav_selector" name="'.$current_nav_slug.'_label" value="'.$current_nav_slug_arr[$current_nav_slug].'">
         <i class="fas fa-chevron-down my_account_page_nav_selector_i"></i>
         <div class="my_account_page_nav_list">         
         '.self::get_nav_html($nav_arr, $current_nav_slug).'
         </div>
       </div>';
       
       $output .= '
       <div class="my_account_page_content_wrapper">';
       
       $output .= apply_filters('babe_myaccount_page_content_'.$check_role, '', $user_info );
         
       $output .= '
       </div>';
       
       $output .= '</div>';
       
       } //// end if ($check_role)
       
    } else {
        
        if (isset($_GET['action']) && $_GET['action'] == 'lostpassword'){
            
            $output .= '
       <div class="my_account_page_content_wrapper">
        '.self::get_lostpassword_form().'
       </div>';
            
        } else {
        //// user login form
        
        $classes = get_option( 'users_can_register' ) ? 'login_register_page' : 'login_page';
        
        $output .= '
       <div class="my_account_page_content_wrapper '.$classes.'">
        '.self::get_login_form().'
        '.self::get_register_form().'
       </div>';
       }
        
    } //// end if ($user_info->ID > 0)
    
	return $output;
  }
  
///////////////////////////////////////
  /**
   * Get nav array for nav items on the My account page.
   * 
   * @param  string $check_role
   * @return array
   */
  public static function get_nav_arr( $check_role ){
	
    $output = array();
    
    if ($check_role == 'customer'){
        $output = array(
         'dashboard' => __('Dashboard', 'ba-book-everything'),
         'profile' => array(
            'title' => __('My Profile', 'ba-book-everything'),
            'edit-profile' => __('Edit Profile', 'ba-book-everything'),
            'change-password' => __('Change Password', 'ba-book-everything'),
         ),
         'activity' => array(
            'title' => __('Activity', 'ba-book-everything'),
            'my-bookings' => __('My Bookings', 'ba-book-everything'),
         ),
         'logout' => __('Sign Out', 'ba-book-everything'),
       );
    }
    
    if ($check_role == 'manager'){
        $output = array(
         'dashboard' => __('Dashboard', 'ba-book-everything'),
         'activity' => array(
            'title' => __('Reservations', 'ba-book-everything'),
            'my-orders' => __('Orders', 'ba-book-everything'),
         ),
       );
       
       $post_type_arr = array(
           BABE_Post_types::$booking_obj_post_type,
           BABE_Post_types::$service_post_type,
           BABE_Post_types::$faq_post_type,
           BABE_Post_types::$fee_post_type,
       );
       
       $post_type_arr = apply_filters('babe_myaccount_get_nav_arr_post_types', $post_type_arr);
       
       foreach($post_type_arr as $post_type){
        
          $post_type_obj = get_post_type_object( $post_type );
        
          $output['post_'.$post_type] = array(
            'title' => $post_type_obj->labels->menu_name,
            'all-posts-'.$post_type => $post_type_obj->labels->all_items,
            'new-post-'.$post_type => $post_type_obj->labels->add_new,
          );
       }
       
       $output['profile'] = array(
            'title' => __('My Profile', 'ba-book-everything'),
            'edit-profile' => __('Edit Profile', 'ba-book-everything'),
            'change-password' => __('Change Password', 'ba-book-everything'),
       );
       
       $output['logout'] = __('Sign Out', 'ba-book-everything');
       
    }
    
    $output = apply_filters('babe_myaccount_get_nav_arr', $output, $check_role);
    
	return $output;
  }
  
///////////////////////////////////////
  /**
   * Get current nav slug.
   * 
   * @param array $nav_arr
   * 
   * @return array
   */
  public static function get_current_nav_slug($nav_arr){
    
    $output = array();
    
    $slug = 'dashboard';
    
    $flat_nav_arr = array();
    
    foreach ($nav_arr as $key => $val){
        
        if (is_array($val)){
            
            $flat_nav_arr[$key] = $val['title'];
            
            foreach($val as $sub_key => $sub_val){
                $flat_nav_arr[$sub_key] = $sub_val;
            }
            
        } else {
            $flat_nav_arr[$key] = $val;
        }       
    }
    
    
    if (isset($_GET[self::$account_page_var])){
        
        if (isset($flat_nav_arr[$_GET[self::$account_page_var]])){
            
            $slug = $_GET[self::$account_page_var];
        
        } elseif ($_GET[self::$account_page_var] == 'edit-post' && isset($_GET['edit_post_id'])) {
            
            $nav_slug = 'new-post-'.get_post_type( (int)$_GET['edit_post_id'] );
            
            if (isset($flat_nav_arr[$nav_slug])){
                
                $slug = $nav_slug;
                
            }
            
        }
        
    }
    
    $output[$slug] = $flat_nav_arr[$slug];
    
    return $output;
  } 
  
///////////////////////////////////////
  /**
   * Get nav header for the My account page.
   * 
   * @param WP_User $user_info
   * @return string
   */
  public static function get_nav_header($user_info){
	
    $output = '<div class="my_account_nav_header">
    <div class="my_account_nav_header_avatar">';
    
    $output .= get_avatar( $user_info->ID, 64, 'mm' );
    
    $output .= '
    </div>
    <div class="my_account_nav_header_info">
      <div class="my_account_nav_header_name">
       '.$user_info->display_name.'
       <i class="fas fa-angle-down"></i>
      </div>
      <div class="my_account_nav_header_email">
        '.$user_info->user_email.'
      </div>
    </div>';
    
    $output .= '</div>';
    
	return $output;
  }  
  
///////////////////////////////////////
  /**
   * Get nav items html for the My account page.
   * 
   * @param  array $nav_arr
   * @param int $depth
   * @return string
   */
  public static function get_nav_html( $nav_arr, $current_nav_slug, $depth = 0 ){
	
    $output = '';
    
    $output .= '<ul class="my_account_nav_list my_account_nav_list_'.$depth.'">';
    
    foreach($nav_arr as $nav_slug => $nav_item){
        
        $current_page_class = 'my_account_nav_item my_account_nav_item_'.$nav_slug.' my_account_nav_item_'.$depth;
        $current_page_class .= $current_nav_slug == $nav_slug ? ' my_account_nav_item_current' : '';
        
        if (is_array($nav_item)){
            
            $current_page_class .= ' my_account_nav_item_with_menu';
            
            $output .= '
        <li class="'.$current_page_class.'">
        ';
        
            $nav_item['title'] = isset($nav_item['title']) ? $nav_item['title'] : '';
            $output .= self::get_nav_item_html($nav_slug, $nav_item['title'], $depth, false);
            unset($nav_item['title']);
            $output .= self::get_nav_html($nav_item, $current_nav_slug, ($depth+1));
            
        } else {
            $output .= '
        <li class="'.$current_page_class.'">
        ';
        
            $output .= self::get_nav_item_html($nav_slug, $nav_item, $depth);
        }
        
        $output .= '
        </li>';
    }
    
    $output .= '
    </ul>
    ';
    
    $output = !$depth ? apply_filters('babe_myaccount_nav_html', $output, $nav_arr) : $output;
    
	return $output;
  }
  
///////////////////////////////////////
  /**
   * Get nav item html for the My account page.
   * 
   * @param  string $nav_slug
   * @param  string $nav_title
   * @param  int $depth
   * @param  boolean $with_link
   * @return string
   */
  public static function get_nav_item_html( $nav_slug, $nav_title = '', $depth = 0, $with_link = true){
	
    $output = '';
      
    $nav_icon_class = self::get_nav_item_icon($nav_slug);  
    
    $url = $nav_slug == 'logout' ? wp_logout_url( BABE_Settings::get_my_account_page_url() ) : BABE_Settings::get_my_account_page_url(array(self::$account_page_var => $nav_slug));
    
    $output .= $with_link ? '<a href="'.$url.'">' : '';
    
    $output .= '<span class="my_account_nav_item_title"><i class="my_account_nav_item_icon '.$nav_icon_class.'"></i>'.$nav_title.'</span>';
    
    $output .= $with_link ? '</a>' : '';
    
    $output = apply_filters('babe_myaccount_nav_item_html', $output, $nav_slug, $nav_title, $with_link);
    
	return $output;
  }  
  
///////////////////////////////////////
  /**
   * Get icon class for nav item on the My account page.
   * 
   * @param  string $item_slug
   * @return string
   */
  public static function get_nav_item_icon( $item_slug ){
	
    $output = isset(self::$icons[$item_slug]) ? self::$icons[$item_slug] : self::$icons['default'];
    
    $output = apply_filters('babe_myaccount_nav_item_icon_class', $output, $item_slug);
    
	return $output;
  }
  
///////////////////////////////////////
  /**
   * Get customer dashboard content.
   * 
   * @param string $content
   * @param WP_User $user_info
   * @return string
   */
  public static function customer_dashboard( $content, $user_info ){
	
    $output = $content;
    
    if ( !isset($_GET[self::$account_page_var]) || $_GET[self::$account_page_var] == 'dashboard'){

        //// get customer info block
        $output .= self::get_customer_info_html($user_info);
        
        //// get customer orders table
        $output .= self::get_my_bookings_html(__('My Bookings', 'ba-book-everything'), $user_info);
        
        $output = apply_filters('babe_myaccount_customer_dashboard', $output, $user_info);
    }
    
	return $output;
  } 
  
///////////////////////////////////////
  /**
   * Get customer info html for the My account page.
   * 
   * @param WP_User $user_info
   * @return string
   */
  public static function get_customer_info_html($user_info){
    
    $args['first_name'] = $user_info->first_name;
    $args['last_name'] = $user_info->last_name;
    $args['email'] = $user_info->user_email;
    
    $contacts = get_user_meta($user_info->ID, 'contacts', 1);
    if (is_array($contacts)){
        $args += $contacts;
    }
	
    $output = '<div class="my_account_inner_page_block my_account_user_profile">
    <div class="my_account_user_avatar">';
    
    $output .= get_avatar( $user_info->ID, 96, 'mm' );
    
    $output .= '
    </div>
    <div class="my_account_user_info">
    <table class="my_account_user_info_table"><tbody>
    ';
    
    foreach($args as $field_name => $field_content){
         
      $output .= '
         <tr>
            <td class="my_account_label">'.BABE_html::checkout_field_label($field_name).'</td>
            <td class="my_account_label_profile_value">'.$field_content.'</td>
         </tr>';
    }
    
    $output .= '
    </tbody></table>
    </div>
    
    </div>';
    
	return $output;
  }

///////////////////////////////////////
  /**
   * Get edit profile form.
   * 
   * @param string $content
   * @param WP_User $user_info
   * @return string
   */
  public static function edit_profile( $content, $user_info ){
	
    $output = $content;
    
    if (isset($_GET[self::$account_page_var]) && $_GET[self::$account_page_var] == 'edit-profile'){
        //// get customer info block
        $output .= self::get_customer_edit_profile_html($user_info);
        
        $output = apply_filters('babe_myaccount_customer_edit_profile', $output, $user_info);        
    }
    
	return $output;
  }  
  
///////////////////////////////////////
  /**
   * Get customer edit profile html for the My account page.
   * 
   * @param WP_User $user_info
   * @return string
   */
  public static function get_customer_edit_profile_html($user_info){
    
    $args['username'] = $user_info->user_login;
    $args['first_name'] = $user_info->first_name;
    $args['last_name'] = $user_info->last_name;
    
    $contacts = get_user_meta($user_info->ID, 'contacts', 1);
    if (is_array($contacts)){
        $args += $contacts;
    }
    
    $input_fields = array();
    
    $input_fields[] = '
            <div class="edit-profile-form-block edit-profile-avatar">
              '.get_avatar( $user_info->ID, 96, 'mm' ).'
              <a class="btn button button_link" href="https://gravatar.com" target="_blank">'.__('Change Profile Picture', 'ba-book-everything').'</a>
            </div>';
    
    ///////// fields
            
      foreach($args as $field_name => $field_content){
         /*
            $input_fields[] = '
            <div class="edit-profile-form-block">
                <label class="edit_profile_form_input_label">'.apply_filters('babe_checkout_field_label', '', $field_name).'</label>
            <div class="edit_profile_form_input_field">
				   <input type="text" class="edit_profile_input_field edit_profile_input_required" name="'.$field_name.'" id="'.$field_name.'" value="'.$field_content.'" placeholder="'.apply_filters('babe_checkout_field_placeholder', '', $field_name).'" '.apply_filters('babe_checkout_field_required', '', $field_name).'/>
			</div>
            </div>';
          */
            $add_content_class = $field_content ? 'checkout_form_input_field_content' : '';
            
            $check_msg = $field_name == 'username' ? '
            <div class="new-username-check">
              <span class="new-username-check-msg">'.__('This username already exists', 'ba-book-everything').'</span>
            </div>' : '';
            
            $check_msg = $field_name == 'email' ? '
            <div class="new-email-check-msg">'.__('This email already exists', 'ba-book-everything').'</div>' : $check_msg;
         
            $input_fields[] = '
            <div class="checkout-form-block edit-profile-form-block">
                
               <div class="checkout_form_input_field '.$add_content_class.'">
                   <label class="checkout_form_input_label">'.BABE_html::checkout_field_label($field_name).'</label>
				   <input type="text" class="checkout_input_field checkout_input_required edit_profile_input_field edit_profile_input_required" name="'.$field_name.'" id="'.$field_name.'" value="'.$field_content.'" '.apply_filters('babe_checkout_field_required', '', $field_name).'/>
                   <div class="checkout_form_input_underline"><span class="checkout_form_input_ripple"></span></div>
			   </div>
               '.$check_msg.'
               
            </div>';  
       }
    ////////////////        
	
    $output = '
    <div class="my_account_inner_page_block my_account_edit_user_profile">
       <h2>'.__('Edit Profile', 'ba-book-everything').'</h2>
       <form id="edit_user_profile" name="edit_user_profile" method="post" action="">
            
            '.apply_filters('babe_edit_user_profile_before_fields', '', $args).'
            
            <div class="user_profile_fields_group input_group">
            
            '.implode('', $input_fields).'
            
            </div>
            
            '.apply_filters('babe_edit_user_profile_after_fields', '', $args).'
            
            <input type="hidden" name="nonce" value="'.wp_create_nonce(self::$nonce_title).'">
            <input type="hidden" name="action" value="edit_user_profile">
            
            <div class="submit_group">
               <button class="btn button edit_user_profile_submit">'.__('Update profile', 'ba-book-everything').'</button>
               <div class="form-spinner"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
       </form>
    </div>';
    
	return $output;
  }
  
///////////////////////////////////////
  /**
   * Get change password form.
   * 
   * @param string $content
   * @param WP_User $user_info
   * @return string
   */
  public static function change_user_password( $content, $user_info ){
	
    $output = $content;
    
    if (isset($_GET[self::$account_page_var]) && $_GET[self::$account_page_var] == 'change-password'){
        
        $output .= self::get_change_user_password_html();
        
        $output = apply_filters('babe_myaccount_change_password', $output, $user_info);
                
    }
    
	return $output;
  }
  
///////////////////////////////////////
  /**
   * Get change password html for the My account page.
   * 
   * @return string
   */
  public static function get_change_user_password_html(){
    
    $output = '
    <div class="my_account_inner_page_block my_account_change_user_password">
       <h2>'.__('Change password', 'ba-book-everything').'</h2>
       <form id="change_user_password" name="change_user_password" method="post" action="">
            
            <div class="user_profile_fields_group input_group">
            
            <div class="edit-profile-form-block">
               <label class="edit_profile_form_input_label">'.__('Old Password *', 'ba-book-everything').'</label>
               <div class="edit_profile_form_input_field">
				   <input type="password" class="edit_profile_input_field edit_profile_input_required" name="old_password" id="old_password" value="" required="required"/>
			   </div>
            </div>
            
            <div class="edit-profile-form-block">
               <label class="edit_profile_form_input_label">'.__('New Password *', 'ba-book-everything').'</label>
               <div class="edit_profile_form_input_field">
				   <input type="password" class="edit_profile_input_field edit_profile_input_required" name="new_password" id="new_password" value="" required="required"/>
			   </div>
            </div>
            
            <div class="edit-profile-form-block">
               <label class="edit_profile_form_input_label">'.__('Confirm Password *', 'ba-book-everything').'</label>
               <div class="edit_profile_form_input_field">
				   <input type="password" class="edit_profile_input_field edit_profile_input_required" name="new_password_again" id="new_password_again" value="" required="required"/>
			   </div>
            </div>
            
            </div>
            
            <input type="hidden" name="action" value="change_user_password">
            
            <div class="submit_group">
               <button class="btn button change_user_password_submit">'.__('Update password', 'ba-book-everything').'</button>
            </div>
       </form>
    </div>';
    
	return $output;
  }    
  
//////////////////////////////
    /**
	 * Sanitize user profile vars
     * @param array $arr
     * @return array
	 */
    public static function sanitize_user_profile_vars($arr){
        
    $output = array();
    
    $user_info = wp_get_current_user();
    
    $output['first_name'] = isset($arr['first_name']) ? sanitize_text_field($arr['first_name']) : '';
    $output['last_name'] = isset($arr['last_name']) ? sanitize_text_field($arr['last_name']) : '';
    $output['phone'] = isset($arr['phone']) ? sanitize_text_field($arr['phone']) : '';
    
    $output['email'] = $user_info->user_email;
    
    if (isset($arr['username']) && $arr['username']){
        $username = sanitize_user($arr['username']);
        if ($username && $user_info->user_login != $username && !username_exists($username)){
            $output['user_login'] = $username;
        }
    }
    
    $output = apply_filters('babe_sanitize_user_profile_vars', $output, $arr);
    
    return $output;
    }
    
///////////////////////////////////////
  /**
   * Get my bookings list.
   * 
   * @param string $content
   * @param WP_User $user_info
   * @return string
   */
  public static function my_bookings( $content, $user_info ){
	
    $output = $content;
    
    if (isset($_GET[self::$account_page_var]) && $_GET[self::$account_page_var] == 'my-bookings'){
        
        $output .= self::get_my_bookings_html(__('My Bookings', 'ba-book-everything'), $user_info);
        
        $output = apply_filters('babe_myaccount_my_bookings', $output, $user_info);        
    }
    
	return $output;
  }
  
///////////////////////////////////////
  /**
   * Get my bookings html for the My account page.
   * 
   * @param string $title
   * @param WP_User $user_info
   * @return string
   */
  public static function get_my_bookings_html($title, $user_info){
    
    $output = '';
    
    $check_role = self::validate_role($user_info); 
      
      if ($check_role == 'manager'){
         $orders = BABE_Order::get_all_orders();
         $posts_pages = BABE_Order::$get_posts_pages;
      } else {
         $orders = BABE_Order::get_customer_orders($user_info->ID);
         $posts_pages = 1;
      }
      
    $orders = apply_filters('babe_myaccount_my_bookings_all_orders', $orders, $user_info);  
    
    $table_head = self::orders_table_head($user_info);
    $cols = count($table_head);
    
    $output .= '
    <table class="my_account_my_bookings_table"><thead>
     <tr>
    ';
    
    foreach($table_head as $column_name => $column_title){
            $output .= '<th>'.$column_title.'</th>';
     }
    
    $output .= '
      </tr>
    </thead>
    <tbody>
     <tr>
    ';
    
    foreach ($orders as $order){
        
        $order_items_html = BABE_html::order_items($order['ID']);
        $customer_html = BABE_html::order_customer_details($order['ID']);
        $customer_html = apply_filters('babe_myaccount_my_bookings_customer_html', $customer_html, $order, $user_info);
        
        $output .= '<tr>';
        
        foreach($table_head as $column_name => $column_title){
            $output .= '<td class="my_account_my_bookings_table_td my_bookings_table_td_'.$column_name.'">'.self::orders_table_content($column_name, $order['ID'], $user_info).'</td>';
        }
        
        $output .= '</tr>
        <tr>
          <td class="my_account_my_bookings_table_td my_bookings_table_td_expand" colspan="'.$cols.'" data-order-id="'.$order['ID'].'">
             '.$order_items_html.$customer_html.'
          </td>
        </tr>';
    }
    
    $output .= '
    </tbody></table>'; 
    
    ////////////////       
	
    $output = '
    <div class="my_account_inner_page_block my_account_my_bookings">
       <h2>'.$title.'</h2>
       
       <div class="my_account_my_bookings_inner">
            '.$output.BABE_Functions::pager($posts_pages).'
       </div>
    </div>';
    
	return $output;
  }
  
///////////////////////////////////////
  /**
   * Get manager dashboard content.
   * 
   * @param string $content
   * @param WP_User $user_info
   * @return string
   */
  public static function manager_dashboard( $content, $user_info ){
	
    $output = $content;
    
    if ( !isset($_GET[self::$account_page_var]) || $_GET[self::$account_page_var] == 'dashboard' ){

        //// get manager info block
        $output .= self::get_customer_info_html($user_info);
        
        //// get manager orders table
        $output .= self::get_my_bookings_html(__('Orders', 'ba-book-everything'), $user_info);
        
        $output = apply_filters('babe_myaccount_manager_dashboard', $output, $user_info);
    }
    
	return $output;
  }   
  
///////////////////////////////////////
  /**
   * Get admin orders list.
   * 
   * @param string $content
   * @param WP_User $user_info
   * @return string
   */
  public static function admin_orders( $content, $user_info ){
	
    $output = $content;
    
    if (isset($_GET[self::$account_page_var]) && $_GET[self::$account_page_var] == 'my-orders'){
        
        $output .= self::get_my_bookings_html(__('Orders', 'ba-book-everything'), $user_info);
        
        $output = apply_filters('babe_myaccount_orders', $output, $user_info);        
    }
    
	return $output;
  }  
  
/////////////////////
    /**
	 * Add orders table heads.
     * 
     * @param WP_User $user_info
     * @return array
	 */
    public static function orders_table_head($user_info) {
    
    $head['order_num']   = __('Order #', 'ba-book-everything');
    $head['items']  = __('Items', 'ba-book-everything');
    $head['date_from']    = __('Date from', 'ba-book-everything');
    $head['date_to']   = __('Date to', 'ba-book-everything');
    $head['guests']   = __('Guests', 'ba-book-everything');
    $head['total_amount'] = __('Total amount', 'ba-book-everything');
    // $head['prepaid_amount'] = __('Prepaid amount', 'ba-book-everything');
    $head['status'] = __('Status', 'ba-book-everything');
    //$head['prepaid_received'] = __('Prepaid received', 'ba-book-everything');
    
    $head = apply_filters('babe_myaccount_my_bookings_table_head', $head, $user_info);
    
    return $head;
}

///////////////////////////////////
    /**
	 * Add orders table column content.
     * 
     * @param string $column_name
     * @param int $post_id
     * @param WP_User $user_info
     * @return array
	 */
    public static function orders_table_content( $column_name, $post_id, $user_info ) {
        
      $output = '';
      $currency = BABE_Order::get_order_currency($post_id);
      
    if ($column_name === 'order_num') {
      $output .= '<a class="my_bookings_table_a_expand" href="#" data-order-id="'.$post_id.'">'.BABE_Order::get_order_number($post_id).'</a>';
    }
    
    if ($column_name === 'items') {
        
      $order_items = BABE_Order::get_order_items($post_id);
      $items = '<ul>';      
      foreach($order_items as $order_item_id => $item_meta){
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($item_meta['booking_obj_id']);
        $items .= '<li>'.$item_meta['order_item_name'].'</li>';
      }
      $items .= '</ul>';
        
      $output .= '<a class="my_bookings_table_a_expand" href="#" data-order-id="'.$post_id.'">'.$items.'</a>';
    }
    
    if ($column_name === 'date_from') {
        
      $order_items = BABE_Order::get_order_items($post_id);
      $date_from = '<ul>';
      
      foreach($order_items as $order_item_id => $item_meta){
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($item_meta['booking_obj_id']);
        $date_from_obj = new DateTime($item_meta['meta']['date_from']);        
        $date_from .= '<li>'.$date_from_obj->format(get_option('date_format').' '.get_option('time_format')).'</li>';
      }
      $date_from .= '</ul>';
        
      $output .= $date_from;
    }
    
    if ($column_name === 'date_to') {
        
      $order_items = BABE_Order::get_order_items($post_id);
      $date_to = '<ul>';
      foreach($order_items as $order_item_id => $item_meta){
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($item_meta['booking_obj_id']);
        $date_to_obj = new DateTime($item_meta['meta']['date_to']);
        if ($rules_cat['rules']['basic_booking_period'] === 'recurrent_custom'){
            $duration = (array)get_post_meta($item_meta['booking_obj_id'], 'duration', true);
            $duration = wp_parse_args( $duration, array(
             'd' => 0,
             'h' => 0,
             'i' => 0,
            ));
            $date_to_obj->modify( '+'.$duration['d'].' days +'.$duration['h'].' hours +'.$duration['i'].' minutes' );
        }
        $date_to .= '<li>'.$date_to_obj->format(get_option('date_format').' '.get_option('time_format')).'</li>';
      }
      $date_to .= '</ul>';
      
      $output .= $date_to;
    }
    
    if ($column_name === 'guests') {
        
      $order_items = BABE_Order::get_order_items($post_id);
      $guests = '<ul>';
      foreach($order_items as $order_item_id => $item_meta){
        $rules_cat = BABE_Booking_Rules::get_rule_by_obj_id($item_meta['booking_obj_id']);
        $guests .= '<li>'.array_sum($item_meta['meta']['guests']).'</li>';
      }
      $guests .= '</ul>';  
        
      $output .= $guests;
    }

    if ($column_name === 'total_amount') {
       $output .= BABE_Currency::get_currency_price(BABE_Order::get_order_total_amount($post_id), $currency);
    }
    
    if ($column_name === 'prepaid_amount') {
       $output .= BABE_Currency::get_currency_price(BABE_Order::get_order_prepaid_amount($post_id), $currency);
    }
    
    if ($column_name === 'prepaid_received') {
       $output .= BABE_Currency::get_currency_price(BABE_Order::get_order_prepaid_received($post_id), $currency);
    }
    
    if ($column_name === 'status') {
        $status = BABE_Order::get_order_status($post_id);
      $output .= '<div class="my_account_my_bookings_order_status order_status_'.$status.'">'.( isset(BABE_Order::$order_statuses[$status]) ? BABE_Order::$order_statuses[$status] : $status).'</div>';
      $check_role = self::validate_role($user_info); 
      
      if ($check_role === 'customer' && $status === 'payment_expected'){
          $output .= '<a class="my_bookings_table_a_button btn button button_link btn-paynow" href="'.BABE_Order::get_order_payment_page($post_id).'">'.__('Pay Now', 'ba-book-everything').'</a>';
      }
      
      if ($check_role === 'manager' && $status === 'av_confirmation'){

          $current_args[self::$account_page_var] = !empty($_GET[self::$account_page_var]) && (
              $_GET[self::$account_page_var] === 'dashboard'
              || $_GET[self::$account_page_var] === 'my-orders'
              || $_GET[self::$account_page_var] === 'my-bookings'
          ) ? $_GET[self::$account_page_var] : 'dashboard';

          $current_args['order_id'] = $post_id;
          $current_args['order_num'] = BABE_Order::get_order_number($post_id);
          $current_args['order_admin_hash'] = BABE_Order::get_order_admin_hash($post_id);
          $current_args['admin_action'] = 'av_confirmation';
          $current_args['action_value'] = 'confirm';
          
          $output .= '<a class="my_bookings_table_icon_button btn button icon-button-confirm" href="#" title="'.__('Confirm', 'ba-book-everything').'"><i class="fas fa-check-square"></i></a>
          <a class="my_bookings_table_icon_button btn button icon-button-reject" href="#" title="'.__('Reject', 'ba-book-everything').'"><i class="fas fa-window-close"></i></a>';
        
          $output .= '<a class="my_bookings_table_a_button btn button button-disabled button_link btn-av-confirm" href="'.BABE_Settings::get_my_account_page_url($current_args).'">'.__('Confirm', 'ba-book-everything').'</a>
          ';
          
          $current_args['action_value'] = 'reject';
          
          $output .= '
          <a class="my_bookings_table_a_button btn button button-disabled button_link btn-av-reject" href="'.BABE_Settings::get_my_account_page_url($current_args).'">'.__('Reject', 'ba-book-everything').'</a>';
      }
      
    }
    
    $output = apply_filters('babe_myaccount_my_bookings_table_content', $output, $column_name, $post_id, $user_info);
    
    return $output;
}

///////////////////////////////////////
  /**
   * Create new post, redirect to edit form.
   * 
   * @return
   */
  public static function new_post(){
    
    global $post;
    
    $post_type_arr = array(
        'new-post-to_book' => BABE_Post_types::$booking_obj_post_type,
        'new-post-faq' => BABE_Post_types::$faq_post_type,
        'new-post-service' => BABE_Post_types::$service_post_type,
        'new-post-fee' => BABE_Post_types::$fee_post_type,
    );
    
    if (
        is_singular()
        && $post->ID == (int)BABE_Settings::$settings['my_account_page']
        && isset($_GET[self::$account_page_var])
        && isset($post_type_arr[$_GET[self::$account_page_var]])
    ){
        $user_info = wp_get_current_user();
        if ($user_info->ID > 0){
         $check_role = self::validate_role($user_info);
         if ($check_role == 'manager'){
            //// create new default post
            $post_id = wp_insert_post( array( 'post_title' => __( 'Auto Draft' ), 'post_type' => $post_type_arr[$_GET[self::$account_page_var]], 'post_status' => 'auto-draft' ) );
        
            if ($post_id){            
             //// redirect to edit post
             $url = BABE_Settings::get_my_account_page_url(array('inner_page' => 'edit-post', 'edit_post_id' => $post_id));
             wp_safe_redirect($url);
            }
        }
        
        }                
    }
    
	return;
  }
  
///////////////////////////////////////
  /**
   * Get edit booking obj form.
   * 
   * @param string $content
   * @param WP_User $user_info
   * @return string
   */
  public static function edit_post( $content, $user_info ){
	
    $output = $content;
    
    if (isset($_GET[self::$account_page_var]) && $_GET[self::$account_page_var] == 'edit-post' && !empty($_GET['edit_post_id']) && BABE_Users::current_user_can_edit_post((int)$_GET['edit_post_id'])){
        //// get customer info block
        $output .= self::get_edit_post_html((int)$_GET['edit_post_id'], $user_info);
        
        $output = apply_filters('babe_myaccount_manager_edit_post', $output, (int)$_GET['edit_post_id'], $user_info);
    }
    
	return $output;
  }  
  
///////////////////////////////////////
  /**
   * Get edit post form html for the My account page.
   * 
   * @param WP_User $user_info
   * @return string
   */
  public static function get_edit_post_html($post_id, $user_info){
    
    $output = '';
    
    $post_type = get_post_type($post_id);
    $post_type_arr = array(
        BABE_Post_types::$booking_obj_post_type => 'booking_obj_metabox',
        BABE_Post_types::$faq_post_type => 'faq_metabox',
        BABE_Post_types::$service_post_type => 'service_metabox',
        BABE_Post_types::$fee_post_type => 'fee_metabox',
    );

      if ( !$post_type || !isset($post_type_arr[$post_type]) ){
          return $output;
      }

      $post_type_obj = get_post_type_object( $post_type );

      $output .= '
    <div class="my_account_inner_page_block my_account_edit_post">
        ';

      $output .= '<h2>'.$post_type_obj->labels->edit_item.'</h2>';

      $output .= '<div class="my_account_edit_post_inner">
            '.cmb2_get_metabox_form( $post_type_arr[$post_type], $post_id ).'
       </div>
    </div>';

      return $output;
  }
  
///////////////////////////////////////
  /**
   * Get all posts table.
   * 
   * @param string $content
   * @param WP_User $user_info
   * @return string
   */
  public static function all_posts( $content, $user_info ){
	
    $output = $content;
    
    $post_type_arr = array(
        'all-posts-to_book' => BABE_Post_types::$booking_obj_post_type,
        'all-posts-faq' => BABE_Post_types::$faq_post_type,
        'all-posts-service' => BABE_Post_types::$service_post_type,
        'all-posts-fee' => BABE_Post_types::$fee_post_type,
    );
    
    if (isset($_GET[self::$account_page_var]) && isset($post_type_arr[$_GET[self::$account_page_var]])){
        //// get posts list
        $output .= self::get_all_posts_html($post_type_arr[$_GET[self::$account_page_var]], $user_info);
        
        $output = apply_filters('babe_myaccount_manager_all_posts', $output, $post_type_arr[$_GET[self::$account_page_var]], $user_info);        
    }
    
	return $output;
  }
  
///////////////////////////////////////
  /**
   * Get all posts html for the My account page.
   * 
   * @param string $post_type
   * @return string
   */
  public static function get_all_posts_html($post_type, $user_info){
    
    $output = '';
    
    $post_type_obj = get_post_type_object( $post_type );
    
    //$output = print_r($post_type_obj->labels, 1);
    $args = array(
      'post_type'   => $post_type,
      'posts_per_page' => 10,
      'paged' => get_query_var('paged'),
      'post_status' => 'any',
      'orderby' => 'post_date',
      'order' => 'DESC',
    );
    
    if (
        !(
            in_array('manager', $user_info->roles)
            || in_array('administrator', $user_info->roles)
        )
    ){
       $args['author__in'] = [ $user_info->ID ];
    }
             
    $args = apply_filters('babe_myaccount_all_posts_get_post_args', $args, $post_type, $user_info);
             
    $the_query = new WP_Query( $args );
    $max_num_pages = $the_query->max_num_pages;
    $found_posts = $the_query->found_posts;
    
    while ( $the_query->have_posts() ) : $the_query->the_post();
       $post_id = get_the_ID();
       $edit_url = BABE_Settings::get_my_account_page_url(array('inner_page' => 'edit-post', 'edit_post_id' => $post_id));
       $output .= '<tr><td class="my_account_all_posts_td my_account_all_posts_td_title"><a href="'.$edit_url.'" title="'.__( 'Edit', 'ba-book-everything' ).'">'.get_the_title($post_id).'</a></td></tr>';
    
    endwhile;
    /* Restore original Post Data */
    wp_reset_postdata();
    
    if ($output){
        $output = '
    <div class="my_account_all_posts_total">'.__( 'Total: ', 'ba-book-everything' ).$found_posts.'</div>    
    <table class="my_account_all_posts_table">
     <tbody>
    '.$output.'
     </tbody>
    </table> 
    ';
    } 
        
    $output = '
    <div class="my_account_inner_page_block my_account_all_posts">
        <h2>'.$post_type_obj->labels->all_items.'</h2>
        
        <div class="my_account_all_posts_inner">
            '.$output.'
            '.BABE_Functions::pager($max_num_pages).'
       </div>
    </div>';
    
	return $output;
  }                                        
        
////////////////////    
}
