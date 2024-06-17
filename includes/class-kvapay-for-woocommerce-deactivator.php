<?php
/**
 * Fired during plugin deactivation.
 *
 * @link       https://kvapay.com
 * @since      1.0.0
 *
 * @package    Kvapay_For_Woocommerce
 * @subpackage Kvapay_For_Woocommerce/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Kvapay_For_Woocommerce
 * @subpackage Kvapay_For_Woocommerce/includes
 * @author     KvaPay <support@kvapay.com>
 */
class Kvapay_For_Woocommerce_Deactivator {

	/**
	 * Delete plugin settings.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		delete_option( 'woocommerce_kvapay_settings' );
	}

}
