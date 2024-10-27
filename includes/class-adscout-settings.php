<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Define all avaliable settings for the AdScout plugin.
 *
 * @link       https://adscout.io
 * @since      2.0.0
 *
 * @package    AdScout
 *
 * @updated 2.0.4
 * Added order status settings
 *
 */

class AdScout_Settings
{

    /**
     * Includes the quick registration template, if needed.
     *
     * @return void
     */
    public static function integration_settings(): void
    {

        $options = (new AdScout_Options())::all_options();
        $string = new AdScout_Settings();

        $data = array(
            'api_token' => $string->mask($options['adscout_api_token']),
            'partner_domain_hex' => $string->mask($options['adscout_partner_domain_hex']),
        );

        (new AdScout_Frontend())::include_partial('integration-settings', 'admin', $data);

        return;

    }

    public static function feed_settings(): void
    {
        $options = (new AdScout_Options())::all_options();

        $data = array(
            'as_feed_last_updated' => $options['as_feed_last_updated'] ??  false,
            'as_feed_url' => $options['as_feed_url'] ?? false,
        );


        (new AdScout_Frontend())::include_partial('feed-generator', 'admin', $data);
    }

    public static function order_status_settings(): void {
        $options = (new AdScout_Options())::all_options();

        if(!is_array($options['adscout_order_statuses_to_sync'])) {
            $statuses =  explode(',', $options['adscout_order_statuses_to_sync']);
        }
        else {
            $statuses = $options['adscout_order_statuses_to_sync'];
        }

        $data = array(
            'adscout_order_statuses_to_sync' => $statuses,
        );

        (new AdScout_Frontend())::include_partial('order-sync-status-settings', 'admin', $data);
    }

    /**
     * Returns a masked token to show in frontend
     * @param $token
     * @return string
     */
    public static function mask($token): string
    {
        if($token and strlen($token) > 10)   {
            return str_repeat('•', strlen(substr($token, -20)) - 10) . substr(substr($token, -20), -10);
        }
        return  '••••••';
    }

}