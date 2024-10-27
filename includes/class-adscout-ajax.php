<?php
defined('ABSPATH') or die('No script kiddies please!');


/**
 * The AdScout Ajax helper class.
 * Defines all required AJAX actions for the plugin.
 *
 * @since      2.0.0
 * @package    AdScout
 * @subpackage AdScout/includes
 * @link       https://adscout.io
 * @author     AdScout <hello@adscout.io>
 *
 * @updated 2.1.0
 * Added the adscout_save_order_status_sync_settings method
 *
 */
class AdScout_Ajax
{
    public static function adscout_save_integration_settings(): void
    {

        if (!array_key_exists('_wpnonce', $_POST) or !wp_verify_nonce( sanitize_text_field(wp_unslash( $_POST['_wpnonce'] )), 'adscout_save_integration_settings')) {
            wp_send_json_error([
                'message' => __('Nonce is wrong or expired. Please, try again', 'adscout') . '.',
            ], 403);
        }

        if (!array_key_exists('api_token', $_POST) or !array_key_exists('adscout_partner_domain_hex', $_POST)) {
            wp_send_json_error([
                'message' => __('Missing required fields', 'adscout'),
            ], 400);
        }

        $data = [
            'api_token' => sanitize_text_field(wp_unslash($_POST['api_token'])),
            'adscout_partner_domain_hex' => sanitize_text_field(wp_unslash($_POST['adscout_partner_domain_hex'])),
        ];

        $options = (new AdScout_Options());
        $all_options = $options::all_options();

        $api_token = $data['api_token'] === '0' ? $all_options['adscout_api_token'] : sanitize_text_field($data['api_token']);
        $adscout_partner_domain_hex = $data['adscout_partner_domain_hex'] === '0' ? $all_options['adscout_partner_domain_hex'] : sanitize_text_field($data['adscout_partner_domain_hex']);

        $request = (new AdScout_Request())::get('partner_promocode_ref', array(), $api_token);

        if ($request and wp_remote_retrieve_response_code($request) == 401) {
            wp_send_json_error([
                'message' => __('Token is wrong or expired. Please, try again', 'adscout') . '.',
            ], 401);
        }

        $options::set_option('adscout_api_token', $api_token);
        $options::set_option('adscout_partner_domain_hex', $adscout_partner_domain_hex);

        $all_options['adscout_api_token'] = $api_token;
        $all_options['adscout_partner_domain_hex'] = $adscout_partner_domain_hex;

        $string = new AdScout_Settings();

        wp_send_json_success([
            'message' => 'Settings saved',
            'adscout_api_token' => $string->mask($all_options['adscout_api_token']),
            'adscout_partner_domain_hex' => $string->mask($all_options['adscout_partner_domain_hex']),
        ]);

        wp_die();
    }

    public static function adscout_generate_feed(): void
    {

        if (!array_key_exists('_wpnonce', $_POST) or !wp_verify_nonce( sanitize_text_field(wp_unslash( $_POST['_wpnonce'] )), 'adscout_feed_generator')) {
            wp_send_json_error([
                'message' => 'Nonce is wrong or expired. Please, try again.',
            ], 403);
        }

        $options = (new AdScout_Options())::all_options();
        $feed = (new AdScout_WooCommerce())::generate_product_feed();

        if ($feed) {
            wp_send_json_success([
                'message' => 'Feed generated',
                'as_feed_last_updated' => $feed['as_feed_last_updated'],
                'as_feed_url' => $feed['as_feed_url'],
            ]);

        } else {
            wp_send_json_error([
                'message' => 'Error generating feed',
            ]);
        }

        wp_die();
    }

    public static function adscout_apply_coupon()
    {

        if (!isset($data['coupon']) or empty($data['coupon'])) {
            wp_send_json_error([
                'message' => 'Coupon code is required',
            ], 400);
        }
        $data = [
            'coupon' => isset($data['coupon']) ? sanitize_text_field($data['coupon']) : '',
        ];
        $coupon_handler = new AdScout_Order_Processor();
        $handle = $coupon_handler->check_coupon($data['coupon']);

        return $handle;

    }

    /**
     * Which order statuses to sync with AdScout
     *
     * @return void
     *
     * @since 2.1.0
     *
     * @updated 2.2.0
     * Added a method to sync all orders statuses with AdScout when the settings are saved
     *
     * @updated 2.2.1
     * Modified the error responses to include warnings and detailed feedback
     */
    public static function adscout_save_order_status_sync_settings(): void
    {

        if (!array_key_exists('_wpnonce', $_POST) or !wp_verify_nonce( sanitize_text_field(wp_unslash( $_POST['_wpnonce'] )), 'adscout_save_order_status_sync_settings')) {
            wp_send_json_error([
                'message' => 'Nonce is wrong or expired. Please, try again.',
            ], 403);
        }

        if ( !array_key_exists('as_order_statuses', $_POST) or !is_array($_POST['as_order_statuses']) or count($_POST['as_order_statuses']) < 1) {
            wp_send_json_error([
                'message' => 'No statuses selected',
            ], 400);
        }

        $data = [
            'as_order_statuses' => array_map( 'sanitize_text_field', wp_unslash( $_POST['as_order_statuses'])),
        ];

        $statuses_to_sync = '';
        $statuses_not_synced = '';

        foreach ($data['as_order_statuses'] as $status) {
            $statuses_to_sync .= $status . ',';
            if (!array_key_exists($status, wc_get_order_statuses())) {
                $statuses_not_synced .= $status . ',';
            }

        }

        $statuses_to_sync = rtrim($statuses_to_sync, ',');

        $options = (new AdScout_Options());
        $options::set_option('adscout_order_statuses_to_sync', $statuses_to_sync);

        $last_synced_statuses = $options::get_option('order_status_synced_to_adscout');

        $request = true;

        if ($last_synced_statuses !== wc_get_order_statuses()) {

            $request = (new AdScout_Request())::post('sync_order_statuses', wp_json_encode(array(
                'statuses' => wc_get_order_statuses(),
            )));



            if ($request and !is_wp_error($request) and wp_remote_retrieve_response_code($request) == 200) {
                $options::set_option('order_status_synced_to_adscout', wc_get_order_statuses());
                $options::set_option('order_status_synced_to_adscout_timestamp', current_time('timestamp'));
            }

        }

        $message = 'Settings saved';

        if ('' !== $statuses_not_synced or !$request) {

            $message .= '. Could not sync save ' . rtrim($statuses_not_synced, ',') . ' to order statuses. Allowed statuses via the Ajax call: ' . wp_json_encode(wc_get_order_statuses());

            wp_send_json_error([
                'message' => $message,
            ], 409);

        }

        wp_send_json_success([
            'message' => $message,
        ]);

        wp_die();

    }

}