<?php
/**
 * Class WC_Post_Purchase_Addon_Hooks
 *
 * Registers WooCommerce hooks for the Post-Purchase Upsell plugin.
 */
class WC_Post_Purchase_Addon_Hooks {

    public function __construct() {
        add_action( 'woocommerce_thankyou', array( $this, 'display_post_purchase_offer' ) );
    }

    public function display_post_purchase_offer( $order_id ) {
        $order = new WC_Post_Purchase_Addon_Order( $order_id );

        if ( ! $order->isValid() || $order->has_upsell_taken() ) {
            return;
        }
    }

}
