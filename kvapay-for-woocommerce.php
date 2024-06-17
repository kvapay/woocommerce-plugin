<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://kvapay.com
 * @since             1.0.0
 * @package           Kvapay_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Payment Gateway - Kvapay
 * Plugin URI:        https://kvapay.com
 * Description:       Accept Bitcoin and Altcoins via KvaPay in your WooCommerce store.
 * Version:           1.0.2
 * Author:            KvaPay
 * Author URI:        https://kvapay.com
 * License:           MIT License
 * License URI:       https://github.com/kvapay/woocommerce-plugin/blob/master/LICENSE
 * Github Plugin URI: https://github.com/kvapay/woocommerce-plugin
 * Text Domain:       kvapay-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'vendor/autoload.php';

/**
 * Currently plugin version.
 */
define( 'KVAPAY_FOR_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * Currently plugin URL.
 */
define( 'KVAPAY_FOR_WOOCOMMERCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-kvapay-for-woocommerce-activator.php
 */
function activate_kvapay_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kvapay-for-woocommerce-activator.php';
	Kvapay_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-kvapay-for-woocommerce-deactivator.php
 */
function remove_kvapay_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kvapay-for-woocommerce-deactivator.php';
	Kvapay_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_kvapay_for_woocommerce' );
register_uninstall_hook( __FILE__, 'remove_kvapay_for_woocommerce' );
register_deactivation_hook( __FILE__, 'remove_kvapay_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-kvapay-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_kvapay_for_woocommerce() {

	$plugin = new Kvapay_For_Woocommerce();
	$plugin->run();

}
run_kvapay_for_woocommerce();

add_action( 'woocommerce_blocks_loaded', 'kvapay_woocommerce_blocks_support' );

function kvapay_woocommerce_blocks_support() {
    if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        require_once dirname( __FILE__ ) . '/includes/class-kvapay-for-woocommerce-blocks.php';
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                $container = Automattic\WooCommerce\Blocks\Package::container();
                // registers as shared instance.
                $container->register(
                    WC_Kvapay_Blocks_Support::class,
                    function() {
                        return new WC_Kvapay_Blocks_Support();
                    }
                );
                $payment_method_registry->register(
                    $container->get( WC_Kvapay_Blocks_Support::class )
                );
            },
            5
        );
    }
}
