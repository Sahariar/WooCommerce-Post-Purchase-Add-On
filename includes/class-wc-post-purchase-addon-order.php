<?php

/**
 * Class WC_Post_Purchase_Addon_Order
 *
 * Handles operations related to WooCommerce orders for the Post-Purchase Upsell plugin.
 */
class WC_Post_Purchase_Addon_Order
{
    protected $order;

    public function __construct($order_id)
    {
        $this->order = wc_get_order($order_id);
    }

    public function isValid()
    {
        if (! $this->order) {
            error_log('Order not found');
            return false;
        }

        error_log('Order status: ' . $this->order->get_status());

        return $this->order->has_status(array('processing', 'completed'));
    }

    public function get_taken_upsells(): array
    {
        $taken = $this->order->get_meta('_post_purchase_taken_upsells');
        return is_array($taken) ? array_map('intval', $taken) : [];
    }


    public function contains_product($product_id)
    {
        foreach ($this->order->get_items() as $item) {
            if ((int) $item->get_product_id() === (int) $product_id) {
                return true;
            }
        }
        return false;
    }

    public function get_applicable_upsell_product_ids(): array
    {
        $upsell_product_ids = [];
        foreach ($this->order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $upsell_id = get_post_meta($product_id, '_post_purchase_upsell_product_id', true);

            if ($upsell_id) {
                $upsell_product_ids[] = (int) $upsell_id;
            }
        }
        return $upsell_product_ids;
    }


    public function add_product($product_id)
    {
        $product = wc_get_product($product_id);
        if (! $product) {
            error_log('Product not found: ' . $product_id);
            return false;
        }

        $item_id = $this->order->add_product($product, 1); // Add one quantity of the product
        if (! $item_id) {
            error_log('Failed to add product to order: ' . $product_id);
            return false;
        }

        $this->order->calculate_totals();
        $this->order->save();

        return true;
    }

    public function mark_upsell_taken(int $upsell_product_id): void
    {
        $taken = $this->get_taken_upsells();

        if (! in_array($upsell_product_id, $taken, true)) {
            $taken[] = $upsell_product_id;
            $this->order->update_meta_data('_post_purchase_taken_upsells', $taken);
            $this->order->save();
        }
    }

    public function get_id()
    {
        return $this->order->get_id();
    }
    public function is_locked_order()
    {
        return (bool) $this->order->get_meta('_ppa_lock');
    }
    public function lock_order()
    {
        $this->order->update_meta_data('_ppa_lock', true);
        $this->order->save();
    }
    public function unlock_order()
    {
        $this->order->delete_meta_data('_ppa_lock');
        $this->order->save();
    }
    public function is_valid_upsell($upsell_product_id)
    {
        foreach ($this->order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $mapped = get_post_meta($product_id, '_post_purchase_upsell_product_id', true);

            if ((int) $mapped === (int) $upsell_product_id) {
                return true;
            }
        }
        return false;
    }

    public function get_remaining_upsells(): array
    {
        $upsells = [];
        $taken   = $this->get_taken_upsells();

        foreach ($this->order->get_items() as $item) {
            $product_id = $item->get_product_id();

            $upsell_id = (int) get_post_meta(
                $product_id,
                '_post_purchase_upsell_product_id',
                true
            );

            if (! $upsell_id || in_array($upsell_id, $taken, true)) {
                continue;
            }

            $priority = (int) get_post_meta(
                $product_id,
                '_post_purchase_upsell_priority',
                true
            );

            $upsells[] = [
                'product_id' => $upsell_id,
                'priority'   => $priority ?: 10,
            ];
        }

        usort($upsells, fn($a, $b) => $a['priority'] <=> $b['priority']);

        return $upsells;
    }
    public function acquire_lock(): bool
    {
        $lock = $this->order->get_meta('_wc_ppa_lock');

        if ($lock) {
            return false;
        }

        $this->order->update_meta_data('_wc_ppa_lock', time());
        $this->order->save();

        return true;
    }

    public function release_lock(): void
    {
        $this->order->delete_meta_data('_wc_ppa_lock');
        $this->order->save();
    }
    public function is_upsell_available($upsell_product_id): bool
    {
        $remaining_upsells = $this->get_remaining_upsells();
        foreach ($remaining_upsells as $upsell) {
            if ((int) $upsell['product_id'] === (int) $upsell_product_id) {
                return true;
            }
        }
        return false;
    }
}
