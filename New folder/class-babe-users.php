<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Users Class.
 * Get general settings
 * @class 		BABE_Users
 * @version		1.0.0
 * @author 		Booking Algorithms
 */

class BABE_Users {
    
    // variables to use
    private static $nonce_title = 'babe-nonce';
    
//////////////////////////////
    /**
	 * Hook in tabs.
	 */
    public static function init() {
        
        add_filter( 'user_has_cap', array( __CLASS__, 'customer_has_cap'), 10, 3 );
        add_filter( 'editable_roles', array( __CLASS__, 'modify_editable_roles') );
        add_filter( 'map_meta_cap', array( __CLASS__, 'modify_map_meta_cap'), 10, 4 );
        add_filter( 'show_admin_bar', array( __CLASS__, 'disable_admin_bar'), 10, 1 );
        add_action( 'deleted_user', array( __CLASS__, 'reset_order_customer_id') );

        add_filter( 'wp_dropdown_users_args', array( __CLASS__, 'wp_dropdown_users_args'), 10, 2 );
        add_filter( 'rest_user_query', array( __CLASS__, 'rest_user_query'), 10, 2 );
        
        add_action( 'wp_ajax_check_free_username', array( __CLASS__, 'ajax_check_free_username'));
        add_action( 'wp_ajax_nopriv_check_free_username', array( __CLASS__, 'ajax_check_free_username'));
        
        add_action( 'wp_ajax_check_free_username_email', array( __CLASS__, 'ajax_check_free_username_email'));
        add_action( 'wp_ajax_nopriv_check_free_username_email', array( __CLASS__, 'ajax_check_free_username_email'));
        
        add_action( 'wp_ajax_check_free_email', array( __CLASS__, 'ajax_check_free_email'));
        add_action( 'wp_ajax_nopriv_check_free_email', array( __CLASS__, 'ajax_check_free_email'));
         
	}
    
///////////////////////////////////////    
    /**
	 * Ajax check if email and username are free
	 */
    public static function ajax_check_free_username_email(){
        
        $output = array(
          'email' => '',
          'username' => '',
        );
        
        if (isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) && isset($_POST['email']) && isset($_POST['username'])){
            
           $user_info = wp_get_current_user(); 
            
           $email = sanitize_email($_POST['email']);
           if ($email && (!email_exists( $email ) || (email_exists( $email ) && $user_info->ID > 0 && $user_info->user_email === $email))){
               $output['email'] = $email;
           }
           
           $username = sanitize_user($_POST['username']);
           if ($username && (!username_exists($username) || (username_exists($username) && $user_info->ID > 0 && $user_info->user_login === $username))){
               $output['username'] = $username;
           }  
        }
        
        echo json_encode($output);
        wp_die();                   
    }    
    
///////////////////////////////////////    
    /**
	 * Ajax check if email is free
	 */
    public static function ajax_check_free_email(){
        
        $output = '';
        
        if (isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) && isset($_POST['email'])){
            
           $user_info = wp_get_current_user(); 
            
           $email = sanitize_email($_POST['email']);

           if ($email && (!email_exists( $email ) || (email_exists( $email ) && $user_info->ID > 0 && $user_info->user_email === $email))){
               $output = $email;
           }  
        }
        
        echo $output;
        wp_die();                   
    }    
    
///////////////////////////////////////    
    /**
	 * Ajax check if username is free
	 */
    public static function ajax_check_free_username(){
        
        $output = '';
        
        if (isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) && isset($_POST['username'])){
           
           $user_info = wp_get_current_user();
           
           $username = sanitize_user($_POST['username']);
           if ($username && (!username_exists($username) || (username_exists($username) && $user_info->ID > 0 && $user_info->user_login === $username))){
               $output = $username;
           }  
        }
        
        echo $output;
        wp_die();                   
    }        

///////////////////////////////////////
     /**
	 * Create a new customer or return existing one.
	 *
	 * @param  string $email Customer email.
	 * @param  array $args first last name, phone etc..
	 * @param  string $password Customer password.
	 * @return int Returns user ID on success or 0.
	 */
	public static function create_customer( $email, $args = array(), $username = '', $password = '' ) {

		// Check the email address.
		if ( empty( $email ) || ! is_email( $email ) ) {
			return 0;
		}
        
        $customer_id = email_exists( $email );
        
        $username_test = $username ? username_exists($username) : '';
        
        if (!$customer_id && !$username_test){
            
            unset($args['email']);
            unset($args['email_check']);
            unset($args['username']);
            
            $arg_username = sanitize_user( $username );
            
            // create user
            $username = $arg_username ? $arg_username : sanitize_user( $email );
            
            if ( empty( $password ) ) {
			  $password = wp_generate_password(12, false);
			  $password_generated = true;
		    } else {
			  $password_generated = false;
            }
            
            $new_customer_data = array(
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => $email,
			'role'       => 'customer',
            );
            
            if (isset($args['first_name']) && $args['first_name']){
               $new_customer_data['first_name'] = $args['first_name'];
               $new_customer_data['display_name'] = $args['first_name'];
               unset($args['first_name']);
            }
            
            if (isset($args['last_name']) && $args['last_name']){
               $new_customer_data['last_name'] = $args['last_name'];
               $new_customer_data['display_name'] = isset($new_customer_data['display_name']) ? $new_customer_data['display_name'].' '.$args['last_name'] : $args['last_name'];
               unset($args['last_name']);
            }
            
            if ($arg_username){
               $new_customer_data['display_name'] = $arg_username;
            }
            
            $new_customer_data = apply_filters( 'babe_new_customer_data', $new_customer_data);
            
            $customer_id = wp_insert_user( $new_customer_data );
            
            if ( is_wp_error( $customer_id ) ) {
			  return 0;
            }
            
            update_user_meta($customer_id, 'contacts', $args);
            
            if (isset($args['phone']) && $args['phone']){
                update_user_meta($customer_id, 'phone', $args['phone']);
            }
            
            do_action('babe_created_customer', $customer_id, $new_customer_data, $password_generated);
        }
        

		return $customer_id;
	}
    
///////////////////////////////////////
     /**
	 * Update current user data.
	 *
	 * @param  array $args first last name, phone etc..
	 * @return 
	 */
	public static function update_current_user( $args = array() ) {

		$user_info = wp_get_current_user();
        
        if ($user_info->ID > 0){
            
            $new_user_data = array();
            
            if (isset($args['email']) && $user_info->user_email != $args['email']){
              $new_user_data['user_email'] = $args['email'];
            }
            unset($args['email']);
            
            if (isset($args['user_login']) && $user_info->user_login != $args['user_login']){
              $new_user_data['user_login'] = $args['user_login'];
            }
            unset($args['user_login']);
            
            if (isset($args['first_name']) && $args['first_name'] && $user_info->first_name != $args['first_name']){
               $new_user_data['first_name'] = $args['first_name'];
               $new_user_data['display_name'] = $args['first_name'];
            }
            unset($args['first_name']);
            
            if (isset($args['last_name']) && $args['last_name'] && $user_info->last_name != $args['last_name']){
               $new_user_data['last_name'] = $args['last_name'];
               $new_user_data['display_name'] = isset($new_user_data['display_name']) ? $new_user_data['display_name'].' '.$args['last_name'] : $user_info->first_name.' '.$args['last_name'];
            }
            unset($args['last_name']);
            
            $new_user_data = apply_filters( 'babe_update_user_data', $new_user_data);
            
            if (!empty($new_user_data)){
                $new_user_data['ID'] = $user_info->ID;
                $user_id = wp_update_user( $new_user_data );
            }
            
            /// update user meta
            update_user_meta($user_info->ID, 'contacts', $args);
            if (isset($args['phone']) && $args['phone']){
                update_user_meta($user_info->ID, 'phone', $args['phone']);
            }
            
            do_action('babe_updated_user', $user_info->ID, $new_user_data, $user_info);
        }

		return $user_info->ID;
	}
    
///////////////////////////////////////
     /**
	 * Reset user password.
	 *
	 * @param  string $email
	 * @return true|WP_Error True when finished, WP_Error object on error.
	 */
	public static function reset_user_password( $email ) {

        $errors = new WP_Error();

        $user = get_user_by( 'email', $email );

        if ( empty($user) || $user->ID === 0 ){
            $errors->add( 'invalid_email', __( '<strong>Error</strong>: There is no account with that email address.', 'ba-book-everything' ));
            return $errors;
        }

        //do_action('babe_user_password_reseted', $user, $password);

        return retrieve_password( $email );
	}        
    
////////////////////////
   /**
   * Checks if a user has a certain capability.
   * 
   * Filter on the current_user_can() function.
   * 
   * @param array $allcaps All the capabilities of the user
   * @param array $cap     [0] Required capability
   * @param array $args    [0] Requested capability
   *                       [1] User ID
   *                       [2] Associated object ID
   * @return bool
   */
   
   public static function customer_has_cap( $allcaps, $caps, $args ) {
	if ( isset( $caps[0] ) ) {
		switch ( $caps[0] ) {
			case 'view_order' :
				if ( $args[1] == BABE_Order::get_order_customer($args[2]) ) {
					$allcaps['view_order'] = true;
				}
			break;
			case 'cancel_order' :
				if ( $args[1] == BABE_Order::get_order_customer($args[2]) ) {
					$allcaps['cancel_order'] = true;
				}
			break;
		}
	}
	return $allcaps;
   }
   
////////////////////
  /**
   * Modify the list of editable roles to prevent non-admin adding admin users.
   * @param  array $roles
   * @return array
   */
  public static function modify_editable_roles( $roles ){
	if ( ! current_user_can( 'administrator' ) ){
		unset( $roles[ 'administrator' ] );
	}
	return $roles;
  }

///////////////////////////////
  /**
   * Modify capabiltiies to prevent non-admin users editing admin users.
   *
   * $args[0] will be the user being edited in this case.
   *
   * @param  array $caps Array of caps
   * @param  string $cap Name of the cap we are checking
   * @param  int $user_id ID of the user being checked against
   * @param  array $args
   * @return array
   */
  public static function modify_map_meta_cap( $caps, $cap, $user_id, $args ){
	switch ( $cap ){
		case 'edit_user' :
		case 'remove_user' :
		case 'promote_user' :
		case 'delete_user' :
			if ( ! isset( $args[0] ) || $args[0] === $user_id ){
				break;
			} else {
				if ( user_can( $args[0], 'administrator' ) && ! current_user_can( 'administrator' ) ){
					$caps[] = 'do_not_allow';
				}
			}
		break;
	}
	return $caps;
  }
  
//////////////////////
  /**
   * Prevent any user who cannot 'edit_posts' (subscribers, customers etc) from seeing the admin bar.
   *
   * @param bool $show_admin_bar
   * @return bool
   */
  public static function disable_admin_bar( $show_admin_bar ) {
	if ( apply_filters( 'babe_disable_admin_bar', (!(current_user_can( 'edit_posts' ) || current_user_can( 'manage_bookeverything' ))))) {
		$show_admin_bar = false;
	}

	return $show_admin_bar;
  }
  
////////////////////
  /**
   * Reset _customer_user on orders when a user is deleted.
   * @param int $user_id
   */
  public static function reset_order_customer_id( $user_id ) {
	global $wpdb;

	$wpdb->update( $wpdb->postmeta, array( 'meta_value' => 0 ), array( 'meta_key' => '_customer_user', 'meta_value' => $user_id ) );
  }
  
////////////////////
  /**
   * Checks if a user can edit the post.
   * 
   * @param  int $post_id
   * @return boolean
   */
  public static function current_user_can_edit_post($post_id){

      $post_id = absint($post_id);
      $user_info = wp_get_current_user();

      $output = false;

      if ( !$user_info || !$user_info->ID || !$post_id ){
          return $output;
      }

      if (
          current_user_can('administrator')
          || in_array('manager', $user_info->roles)
          || (int)$user_info->ID === (int)get_post_field( 'post_author', $post_id)
          || (int)$user_info->ID === (int)get_post_meta( $post_id, '_manager_id', true)
      ){
          $output = true;
      }

      return apply_filters( 'babe_current_user_can_edit_post', $output, $post_id);
  }
  
////////////////////
  /**
   * Checks if a user can edit the order.
   * 
   * @param  int $post_id
   * @return boolean
   */
  public static function current_user_can_edit_order($post_id){

      $output = false;

      $post_id = absint($post_id);

      $user_info = wp_get_current_user();

      if ( $post_id < 1 || !$user_info || !$user_info->ID ){
          return $output;
      }

      $order_customer_id = BABE_Order::get_order_customer($post_id);

      if (
          current_user_can('administrator')
          || in_array('manager', $user_info->roles)
          || (int)$user_info->ID === (int)$order_customer_id
      ){
          $output = true;
      }

      $output = apply_filters( 'babe_current_user_can_edit_order', $output, $post_id);

      return $output;
  }

    ///////////////////////////////
    /**
     * Filters WP_User_Query arguments when querying users via the REST API.
     *
     * @link https://developer.wordpress.org/reference/classes/wp_user_query/
     *
     * @param array           $query_args Array of arguments for WP_User_Query.
     * @param WP_REST_Request $request       The current request.
     */
    public static function rest_user_query ( $query_args, $request ){

        if ( !isset($query_args['who']) || $query_args['who'] !== 'authors' ){
            return $query_args;
        }

        $referer = $request->get_header('referer');

        if ( empty($referer) ){
            return $query_args;
        }

        $url = parse_url($referer);
        parse_str($url['query'], $get_args);

        if ( !isset($get_args['post']) || empty( (int)$get_args['post'] ) ){
            return $query_args;
        }

        $post_type = get_post_type( (int)$get_args['post'] );

        if ( !$post_type ){
            return $query_args;
        }

        return self::update_users_args_by_post_type ( $query_args, $post_type );
    }

  ///////////////////////////////
    /**
     * Filters the query arguments for the list of users in the dropdown.
     *
     * @param array $query_args  The query arguments for get_users().
     * @param array $parsed_args The arguments passed to wp_dropdown_users() combined with the defaults.
     */
    public static function wp_dropdown_users_args ( $query_args, $parsed_args ){

        global $post;

        if (
            !current_user_can('edit_others_posts')
            || !is_admin()
            || ( empty( $_GET['post_type'] ) && !isset($post->post_type) )
        ){
            return $query_args;
        }

        $post_type = isset($post->post_type) ? $post->post_type : sanitize_text_field($_GET['post_type']);

        return self::update_users_args_by_post_type ( $query_args, $post_type );
    }

    ///////////////////////////////
    /**
     * Filters the query arguments for the list of users in the dropdown.
     *
     * @param array $query_args  The query arguments for get_users().
     * @param string $post_type
     */
    public static function update_users_args_by_post_type ( $query_args, $post_type ){

        $babe_post_types = BABE_Post_types::get_babe_post_types();

        if ( !in_array( $post_type, $babe_post_types) ){
            return $query_args;
        }

        $wp_roles = wp_roles();

        foreach ( $wp_roles->roles as $role_name => $role ){

            if (
                isset( $role['capabilities']['edit_others_posts'] )
                || isset( $role['capabilities']['edit_others_'.$post_type.'s'] )
                || isset( $role['capabilities']['edit_'.$post_type] )
            ){
                $query_args['role__in'][] = $role_name;
            }
        }

        $query_args['who'] = '';

        return $query_args;
    }

////////////////////    
}

BABE_Users::init();