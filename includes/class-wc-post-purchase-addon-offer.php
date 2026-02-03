<?php

class WC_Post_Purchase_Addon_Offer
{

    protected $product_id; // Hardcoded product ID for the upsell offer

    public function __construct($product_id)
    {
        // For demonstration, we hardcode the upsell product ID.
        $this->product_id = $product_id; // Replace with actual upsell product ID
    }

    public function is_eligible(WC_Post_Purchase_Addon_Order $order)
    {
        // Check if the upsell has already been taken
        if ($order->has_upsell_taken()) {
            return false;
        }
        // if ( $order->contains_product( $this->product_id ) ) {
        // return false;
        // }
        return true;
    }

    public function get_product_id()
    {
        return $this->product_id;
    }
    public function get_product()
    {
        return wc_get_product($this->product_id);
    }

    public function render($order_id)
    {
        $product = $this->get_product();
        if (! $product) {
            return;
        }
        wp_enqueue_script(
            'wc-ppa',
            WC_POST_PURCHASE_ADDON_URL . 'assets/js/wc-ppa.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script(
            'wc-ppa',
            'wcPpaData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('wc_ppa_accept_offer'),
                'orderId' => $order_id,
            ]
        );
        echo '<div class="wc-ppa-offer">
                <h2>Special One-Click Offer</h2>
                <p>Add this product to your order before it ships.</p>
                <h3>' . esc_html($product->get_name()) . '</h3>
                <p>' . wp_kses_post($product->get_price_html()) . '</p>
                <button id="wc-ppa-accept" class="wc-ppa-accept" data-product-id="' . esc_attr($this->product_id) . '">Add to Order</button>
                <p id="wc-ppa-message"></p>
            </div>';
    }
}
