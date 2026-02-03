<?php

/**
 *
 * @link              https://sahariarkabir.com
 * @since             1.0.0
 * @package           Wc_Post_Purchase_Addon
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Post-Purchase Add-On
 * Plugin URI:        https://sahariarkabir.com
 * Description:       Adds a one-click post-purchase add-on to WooCommerce orders, allowing customers to add a complementary product after checkout using the original Stripe payment method.
 * Version:           1.0.0
 * Author:            Sahariar kabir
 * Author URI:        https://sahariarkabir.com/
 * Text Domain:       wc-post-purchase-addon
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WC_POST_PURCHASE_ADDON_VERSION', '1.0.0' );
define( 'WC_POST_PURCHASE_ADDON_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_POST_PURCHASE_ADDON_URL', plugin_dir_url( __FILE__ ) );
define('WC_POST_PURCHASE_ADDON_BASENAME', plugin_basename( __FILE__ ) );

// hpos compatibility check
function wc_post_purchase_addon_hpos_compitability_check() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
}
add_action( 'before_woocommerce_init', 'wc_post_purchase_addon_hpos_compitability_check' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-post-purchase-addon-activator.php
 */
function activate_wc_post_purchase_addon() {
	require_once WC_POST_PURCHASE_ADDON_DIR . 'includes/class-wc-post-purchase-addon-activator.php';
	Wc_Post_Purchase_Addon_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-post-purchase-addon-deactivator.php
 */
function deactivate_wc_post_purchase_addon() {
	require_once WC_POST_PURCHASE_ADDON_DIR . 'includes/class-wc-post-purchase-addon-deactivator.php';
	Wc_Post_Purchase_Addon_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wc_post_purchase_addon' );
register_deactivation_hook( __FILE__, 'deactivate_wc_post_purchase_addon' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WC_POST_PURCHASE_ADDON_DIR . 'includes/class-wc-post-purchase-addon.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wc_post_purchase_addon() {

	$plugin = new Wc_Post_Purchase_Addon();
	$plugin->boot();
}
run_wc_post_purchase_addon();
