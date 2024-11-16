<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BABE_Banner_notice Class.
 *
 * @class 		BABE_Banner_notice
 * @version		1.0
 * @author 		Booking Algorithms
 */

BABE_Banner_notice::init();

class BABE_Banner_notice {

//////////////////////////////

    /**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'show_notices' ) );
	}

	//////////////////////////
	/**
	 * Show notices in admin area
	 */
	public static function show_notices() {

        if ( get_transient( 'babe-payment-banner-notice' ) ) { ?>
            <div class="notice notice-success is-dismissible">
                <a href="https://ba-booking.com/shop/"><img src="<?php echo plugins_url( "css/img/babe_payment_promo_notice.png", BABE_PLUGIN ); ?>" alt="BABE Payment pack add-on"></a>
            </div> <?php

            if ( current_user_can( 'manage_options' ) ) {
                /* Delete transient, only display this notice once. */
                delete_transient( 'babe-payment-banner-notice' );
            }
        }
	}

//////////////////
}
