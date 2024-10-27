<?php
defined('ABSPATH') or die('No script kiddies please!');

class AdScout_Order_Processor
{

    /**
     * @param $order_id
     * @return bool|void
     *
     * @since 2.0.0
     *
     * @updated 2.0.2
     * Added notes to order
     *
     * @updated 2.0.3
     * Added number_format to limit currency decimals
     *
     * @updated 2.0.4
     * Added additional log for order processing
     *
     * @updated 2.1.0
     * Added get_order_status_to_sync method.
     * Added check for the AdScout cookie which is stored in the order meta.
     * Added method to update order meta when order is created and the user has the AdScout cookie.
     *
     * @updated 2.1.1
     * Added required 'status' key to the order data array.
     *
     */
    public function sync_order($order_id)
    {

        $order = wc_get_order($order_id);

        if(!$order->get_meta('_adscout_cookie_value')) {
            return;
        }

        $logger = new AdScout_Logger();
        $request = new AdScout_Request();

        $logger->add('Processing order ID ' . $order_id);
        $order = wc_get_order($order_id);

        // Check if the function has already been executed for this order
        if ($order->get_meta('_adscout_api_order_placed')) {
            $logger->add('Stopped processing order ID ' . $order_id . ', order already synced');
            return; // Stop if the function has already been executed for this order
        }

        $process_statuses = self::get_order_status_to_sync();

        // Check if the function has already been executed for this order
        if ( !in_array($order->get_status(), $process_statuses) ) {
            $logger->add('Stopped processing order ID ' . $order_id . ', status is not for sync.');
            return; // Stop if the function has already been executed for this order
        }

        $logger->add('Started processing order ID ' . $order_id );

        $discount_order_total = 0;

        $order_total = number_format($order->get_total(), 2); // Get Order Total (including shipping)
        $shipping_cost = number_format($order->get_shipping_total(), 2); // Get Shipping Cost
        $order_total_excl_shipping = max($order_total - $shipping_cost, 0); // Get Order Total (excluding shipping)
        $order_currency = get_woocommerce_currency();

        if (!empty($order->get_discount_total())) {
            $discount_order_total = number_format($order->get_discount_total(), 2);
        }

        $order_products = array();

        $products_total_price = 0;
        
        foreach ($order->get_items() as $item_id => $item) {
            $price_for_calculations = 0;
            $product = $item->get_product();
            $product_id = $product->get_id();
            $variation_id = $item->get_variation_id();

            if ($variation_id) {
                $variation = wc_get_product($variation_id);
                $sku = $variation->get_sku();
                $regular_price = $variation->get_regular_price();
                $sale_price = $variation->get_sale_price();
            } else {
                $sku = $product->get_sku();
                $regular_price = $product->get_regular_price();
                $sale_price = $product->get_sale_price();
            }

            $price_for_calculations = !empty($sale_price) ? $sale_price : $regular_price;

            $products_total_price += ($price_for_calculations * $item->get_quantity());

            $product_info = array(
                'id' => $variation_id ? $variation_id : $product_id,
                'qty' => $item->get_quantity(),
                'sku' => $sku,
                'p' => $regular_price,
                'sp' => $sale_price,
            );

            $order_products[] = $product_info;
        }

        $discounted_total_percent = 0;

        if ($products_total_price > $order_total_excl_shipping) {
            $discounted_total_percent = 100 - (($order_total_excl_shipping / $products_total_price) * 100);
        }

        foreach ($order_products as $key => $product) {
            if (!empty($product['sp'])) {
                $price_for_calculations = $product['sp'];
            } else {
                $price_for_calculations = $product['p'];
            }

            $order_products[$key]['sp'] = $price_for_calculations * ((100 - $discounted_total_percent) / 100);
        }

        $order_data = wp_json_encode([
            'adsRef' => $order->get_meta('_adscout_cookie_value'),
            'amount' => $order_total_excl_shipping,
            'discount' => $discount_order_total,
            'currency' => $order_currency,
            'orderNumber' => strval($order_id),
            'orderType' => 'n',
            'products' => $order_products,
        ]);

        $response = $request->post('track_order', $order_data);

        if ($response and wp_remote_retrieve_response_code($response) === 401) {
            $note = 'Order ID ' . $order_id . ' has NOT been synced with AdScout. Order data: ' . wp_json_encode($order_data) . '. Reason: request unauthenticated';
            $order->add_order_note($note);
            $order->save();
            $logger->add($note, 'error');
            return false;
        }

        if ($response and wp_remote_retrieve_response_code($response) === 200) {
            $note = 'Order ID ' . $order_id . ' has been synced with AdScout. Order data: ' . wp_json_encode($order_data);
            $order->add_order_note($note);
            $order->update_meta_data('_adscout_api_order_placed', gmdate('timestamp'));
            $order->save();
            $logger->add($note);
            return true;
        }

        $note = 'Order ID ' . $order_id . ' has NOT been synced with AdScout. Order data: ' . wp_json_encode($order_data) . ' Response: ' . wp_json_encode($response);
        $order->add_order_note($note);
        $order->save();
        $logger->add($note, 'error');

        return false;
    }


    /**
     * Sync order status change with AdScout
     *
     * @param $order_id
     * @return bool
     *
     * @since 2.0.0
     *
     * @updated 2.0.4
     * Fixed an odd logic issue with the order status sync check,
     * inherited from a previous plugin version.
     *
     */
    public function change_order_status($order_id): bool {

        $logger = new AdScout_Logger();
        $request = new AdScout_Request();

        $order = wc_get_order($order_id);

        if(!$order->get_meta('_adscout_api_order_placed') ) {
            return false;
        }

        $logger->add('Re-sync of order ID ' . $order_id . ' because of status change to ' . $order->get_status() . ' started.');


        $order_data = [
            'orderNumber' => strval($order_id),
            'status' => $order->get_status()
        ];
        $order_data = wp_json_encode($order_data);

        $response = $request->post('change_order_status', $order_data);

        if ($response and wp_remote_retrieve_response_code($response) === 401) {
            $logger->add('Status of order ID ' . $order_id . ' has NOT been updated with AdScout. Order data: ' . $order_data . '. Reason: request unauthenticated', 'error');
            return false;
        }

        if ($response and wp_remote_retrieve_response_code($response) === 200) {
            $logger->add('Status of order ID ' . $order_id . ' has been updated with AdScout. Order data: ' . $order_data);
            return true;
        }

        $logger->add('Status of order ID ' . $order_id . ' has NOT been re-synced with AdScout. Order data: ' . $order_data . ' Response: ' . wp_json_encode($response), 'error');
        return false;

    }


    public function check_coupon($coupon_code): bool {

        $logger = new AdScout_Logger();
        $request = new AdScout_Request();

        $data = [
            'promo_code' => (string) $coupon_code,
        ];

        $response = $request->get('partner_promocode_ref', $data);

        if(!$response or wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }
        $response = json_decode(wp_remote_retrieve_body($response), true);

        if($response['success'] === false) {
            return false;
        }

        if (isset($_COOKIE['ScoutSRef'])) {
            $current_cookie = sanitize_text_field(wp_unslash($_COOKIE['ScoutSRef']));
            $cookie = $current_cookie . ',' . $response['data']['ref'];
        } else {
            $cookie = $response['data']['ref'];
        }

        setcookie('ScoutSRef', $cookie, time() + 3600 * 24 * 30, "/");
        return true;

    }

    public static function get_order_status_to_sync(): array
    {
        $options = (new AdScout_Options())::all_options();
        $raw_statuses = $options['adscout_order_statuses_to_sync'] ? explode(',', $options['adscout_order_statuses_to_sync']) : ['wc-completed', 'wc-processing'];
        $statuses = [];
        foreach ($raw_statuses as $status) {
            $statuses[] = str_replace('wc-', '', $status);
        }

        return $statuses;
    }

    public function update_order_meta_with_adscout_cookie_value($order_id): void {

        if(!isset($_COOKIE[ADSCOUT_COOKIE])) {
            return;
        }

        if(is_user_logged_in() and (current_user_can( 'manage_options' ) or current_user_can( 'edit_shop_orders' ))) {
            return;
        }

        $order = wc_get_order($order_id);
        $order->update_meta_data('_adscout_cookie_value', sanitize_text_field(wp_unslash($_COOKIE[ADSCOUT_COOKIE])));
        $order->save();

    }



}
