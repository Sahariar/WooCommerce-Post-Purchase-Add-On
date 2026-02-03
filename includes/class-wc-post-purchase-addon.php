<?php

/**
 * Boots the Post-Purchase Upsell plugin.
 * Loads core classes and registers WooCommerce hooks.
 */
class WC_Post_Purchase_Addon
{
	protected $plugin_name;
	protected $version;

	public function __construct()
	{
		if (defined('WC_POST_PURCHASE_ADDON_VERSION')) {
			$this->version = WC_POST_PURCHASE_ADDON_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wc-post-purchase-addon';
		$this->load_dependencies();
	}
	private function load_dependencies()
	{
		require_once WC_POST_PURCHASE_ADDON_DIR . 'includes/class-wc-post-purchase-addon-hooks.php';
		require_once WC_POST_PURCHASE_ADDON_DIR . 'includes/class-wc-post-purchase-addon-order.php';
		require_once WC_POST_PURCHASE_ADDON_DIR . 'includes/class-wc-post-purchase-addon-offer.php';
	}

	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	public function get_version()
	{
		return $this->version;
	}

	public function boot()
	{
		new WC_Post_Purchase_Addon_Hooks();
	}
}
