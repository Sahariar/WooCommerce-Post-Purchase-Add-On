<?php

/**
 * Handles all WooCommerce hook registrations.
 *
 * Currently uses `woocommerce_thankyou` to display
 * post-purchase offers after order completion.
 *
 * Important: This must NOT run on failed or pending orders.
 */
class WC_Post_Purchase_Addon_Hooks
{

    public function __construct()
    {
        add_action('woocommerce_thankyou', array($this, 'display_post_purchase_offer'));
        add_action('wp_ajax_wc_ppa_accept_offer', array($this, 'ajax_accept_offer'));
        add_action('wp_ajax_nopriv_wc_ppa_accept_offer', array($this, 'ajax_accept_offer'));
    }
    /**
     * Displays a post-purchase upsell offer on the thank-you page.
     *
     * @param int $order_id WooCommerce order ID from thankyou hook.
     */
    public function display_post_purchase_offer($order_id)
    {
        error_log('Thank you hook fired. Order ID: ' . $order_id);
        $order = new WC_Post_Purchase_Addon_Order($order_id);

        if (! $order->isValid()) {
            return;
        }
        $upsells = $order->get_remaining_upsells();
        $next_upsell = $upsells[0] ?? null;

        if (! $next_upsell) {
            return;
        }

        $offer = new WC_Post_Purchase_Addon_Offer($next_upsell['product_id']);

        if (! $offer->is_eligible($order)) {
            return;
        }

        $offer->render($order->get_id());
    }

public function ajax_accept_offer() {

    // 1. Nonce check
    if (
        ! isset($_POST['nonce']) ||
        ! wp_verify_nonce($_POST['nonce'], 'wc_ppa_accept_offer')
    ) {
        wp_send_json_error(['message' => 'Invalid request']);
    }

    // 2. Sanitize input
    $order_id   = absint($_POST['order_id'] ?? 0);
    $upsell_id  = absint($_POST['upsell_product_id'] ?? 0);

    if ( ! $order_id || ! $upsell_id ) {
        wp_send_json_error(['message' => 'Missing data']);
    }

    $order = new WC_Post_Purchase_Addon_Order($order_id);

    // 3. Validate order
    if ( ! $order->isValid() ) {
        wp_send_json_error(['message' => 'Order not eligible']);
    }

    // 4. HARD LOCK (race condition prevention)
    if ( ! $order->acquire_lock() ) {
        wp_send_json_error(['message' => 'Offer already processing']);
    }

    // 5. Validate upsell eligibility
    if ( ! $order->is_upsell_available( $upsell_id ) ) {
        $order->release_lock();
        wp_send_json_error(['message' => 'Upsell not available']);
    }

    // 6. Add product to order
    if ( ! $order->add_product( $upsell_id ) ) {
        $order->release_lock();
        wp_send_json_error(['message' => 'Failed to add product']);
    }

    // 7. Mark upsell taken
    $order->mark_upsell_taken( $upsell_id );

    // 8. Unlock
    $order->release_lock();

    wp_send_json_success([
        'message' => 'Upsell added',
        'next'    => $order->get_remaining_upsells(),
    ]);
}

}
