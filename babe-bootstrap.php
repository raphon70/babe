<?php
/**
 * @wordpress-plugin
 * Plugin Name:       BA Book Everything
 * Plugin URI: https://wordpress.org/plugins/ba-book-everything/
 * Description: The really fast and powerful Booking engine for any booking or rental sites (tours, hostels, apartments, cars, events etc., or all together).
 * Version:           1.6.24
 * Author:            Booking Algorithms
 * Author URI: https://ba-booking.com
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Tested up to: 6.6.2
 * Text Domain: ba-book-everything
 * Domain Path: /languages/
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

define( 'BABE_VERSION', '1.6.24' );
define( 'BABE_PLUGIN_SLUG', 'ba-book-everything' );
define( 'BABE_PLUGIN', __FILE__ );
define( 'BABE_PLUGIN_DIR', untrailingslashit( dirname( BABE_PLUGIN ) ) );
define( 'BABE_TEXTDOMAIN', 'ba-book-everything' );
define( 'BABE_DEV', true );

if ( file_exists(  BABE_PLUGIN_DIR . '/includes/plugins/cmb2/init.php' ) ) {
  require_once BABE_PLUGIN_DIR . '/includes/plugins/cmb2/init.php';
  
  require_once BABE_PLUGIN_DIR . '/includes/plugins/cmb2-conditionals/cmb2-conditionals.php';
}

include_once BABE_PLUGIN_DIR . '/includes/class-babe-install.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-api.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-functions.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-locales.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-settings.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-currency.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-calendar-functions.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-booking-rules.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-prices.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-post-types.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-search-form.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-order.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-payments.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-pay-cash.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-pay-coupon.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-users.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-my-account.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-html.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-shortcodes.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-html-emails.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-emails.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-rating.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-coupons.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-cmb2-admin.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-banner-notice.php';

include_once BABE_PLUGIN_DIR . '/includes/class-babe-import-export.php';

include_once BABE_PLUGIN_DIR . '/includes/DTO/RateDTO.php';

///// widgets

include_once BABE_PLUGIN_DIR . '/includes/widgets/class-babe-booking-form.php';

include_once BABE_PLUGIN_DIR . '/includes/widgets/class-babe-search-form.php';

include_once BABE_PLUGIN_DIR . '/includes/widgets/class-babe-search-filter-terms.php';

include_once BABE_PLUGIN_DIR . '/includes/widgets/class-babe-search-filter-price.php';

///// elementor

include_once BABE_PLUGIN_DIR . '/includes/vendors/elementor/init.php';

///// admin

if ( is_admin() ) {
    
    include_once BABE_PLUGIN_DIR . '/includes/class-babe-settings-admin.php';
    
    include_once BABE_PLUGIN_DIR . '/includes/class-babe-taxonomies-admin.php';
    
    include_once BABE_PLUGIN_DIR . '/includes/class-babe-posts-admin.php';
    
    include_once BABE_PLUGIN_DIR . '/includes/class-babe-order-admin.php';
    
   	include_once BABE_PLUGIN_DIR . '/includes/class-babe-booking-rules-admin.php';
    
    include_once BABE_PLUGIN_DIR . '/includes/class-babe-search-form-admin.php';
    
    include_once BABE_PLUGIN_DIR . '/includes/class-babe-coupons-admin.php';
}
 
    