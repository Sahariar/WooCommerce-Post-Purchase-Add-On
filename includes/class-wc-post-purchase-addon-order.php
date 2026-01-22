<?php
/**
 * Class WC_Post_Purchase_Addon_Order
 *
 * Handles operations related to WooCommerce orders for the Post-Purchase Upsell plugin.
 */
class WC_Post_Purchase_Addon_Order {
    protected $order;

    public function __construct( $order_id ) {
        $this->order = wc_get_order( $order_id );
    }

    public function isValid(){
        return $this->order && $this->order->has_status( 'processing' );
    }
    
    public function has_upsell_taken(){
        return (bool) $this->order->get_meta('_post_purchase_upsell_taken');
    }

}
