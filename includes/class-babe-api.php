<?php
/**
 * BABE-API endpoint handler
 * @since   1.5.16
 */

defined( 'ABSPATH' ) || exit;

BABE_API::init();

/**
 * BABE_API class
 */
class BABE_API {

	/**
	 * Init the API by setting up action and filter hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_endpoint' ), 0 );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ), 0 );
		add_action( 'parse_request', array( __CLASS__, 'handle_api_requests' ), 0 );
	}

	/**
	 * Add new query vars.
	 *
	 * @since 2.0
	 * @param array $vars Query vars.
	 * @return string[]
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = 'babe-api';
		return $vars;
	}

	public static function add_endpoint() {
		add_rewrite_endpoint( 'babe-api', EP_ALL );
	}

	public static function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET['babe-api'] ) ) { // WPCS: input var okay, CSRF ok.
			$wp->query_vars['babe-api'] = sanitize_key( wp_unslash( $_GET['babe-api'] ) ); // WPCS: input var okay, CSRF ok.
		}

		if ( ! empty( $wp->query_vars['babe-api'] ) ) {

			// Buffer, we won't want any output here.
			ob_start();

			// Clean the API request.
			$api_request = strtolower( sanitize_text_field( $wp->query_vars['babe-api'] ) );

            remove_action( 'template_redirect', array( 'BABE_Payments', 'payment_server_response'), 1);

			// Trigger generic action before request hook.
			do_action( 'babe_api_request', $api_request );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( 'babe_api_' . $api_request ) ? 200 : 400 );

			// Trigger an action which plugins can hook into to fulfill the request.
            if ( strpos($api_request, 'ipn_') === 0 && strlen($api_request) > 4 ){

                $payment_gateway = str_replace('ipn_', '', $api_request);
                do_action( 'babe_payment_server_response');
                do_action( 'babe_payment_server_'.$payment_gateway.'_response');

            } else {

                do_action( 'babe_api_' . $api_request );
            }

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	}
}
