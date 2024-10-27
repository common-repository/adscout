<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * The AdScout Request URL helper.
 * Defines all request endpoints and settings..
 *
 * @since      2.0.0
 * @package    AdScout
 * @subpackage AdScout/includes
 * @link       https://adscout.io
 * @author     AdScout <hello@adscout.io>
 *
 * @updated 2.1.1
 * Fixed a mis-logic into the header definition method.
 *
 */

/**
 * Base URL for all AdScout API requests.
 * Added '/' for ease of use.
 */

use JetBrains\PhpStorm\ArrayShape;

define('ADSCOUT_API_BASE_URL', 'https://adscout.io/api/');

/**
 * All API Endpoints.
 * Added '/' for ease of use.
 */

define('ADSCOUT_API_CREATE_PARTNER', 'create-partner/');
define('ADSCOUT_API_TRACK_ORDER', 'track-order/');
define('ADSCOUT_API_CHANGE_ORDER_STATUS', 'change-order-status/');
define('ADSCOUT_API_PARTNER_PROMOCODE_REF', 'get-partner-promo-code-ref/');
define('ADSCOUT_SYNC_ALL_ORDER_STATUSES', 'partner/statuses/');

class AdScout_Request
{

    private $bearer;

    private $code;

    private $headers;

    public function __construct()
    {
        $this->bearer = $this->bearer();
        $this->code = $this->code();
        $this->headers = $this->headers();
    }

    /**
     * Makes a GET request to the AdScout API.
     * Returns the response as an object.
     * If the request fails, returns false.
     *
     * @param $type
     * @param $params
     *
     * @since 2.0.0
     * @updated 2.0.1
     */
    public static function get($type, $params = array(), $bearer = false)
    {

        $bearer = $bearer ? $bearer : self::bearer();
        $request_url = self::request_type_url();

        if (!isset($request_url[$type])) {
            return false;
        }

        $request = wp_remote_get(
            add_query_arg($params, $request_url[$type]),
            array(
                'headers' => self::headers($bearer),
            )
        );

        if (is_wp_error($request)) {
            return false;
        }


        return $request;

    }

    /**
     * Makes a POST request to the AdScout API.
     * Returns the response as an object.
     * If the request fails, returns false.
     *
     * @param $type
     * @param $body
     *
     * @since 2.0.0
     * @updated 2.0.1
     */
    public static function post($type, $body, $bearer = false)
    {

        $request_url = self::request_type_url();
        if (!isset($request_url[$type])) {
            return false;
        }

        $request = wp_remote_post(
            $request_url[$type],
            array(
                'headers' => self::headers($bearer),
                'body' => $body,
            )
        );

        if ( wp_remote_retrieve_response_code($request) !== 200 ) {
            return false;
        }

        return $request;

    }

    /**
     * Returns the bearer token from the plugin options or
     * false if it's not set.
     *
     * @access private
     * @static
     *
     * @since 2.0.0
     * @updated 2.0.1
     *
     * @updated 2.2.0
     * Added sync_order_statuses - a method to sync all orders statuses with AdScout when the settings are saved
     */
    private static function bearer()
    {
        return (new AdScout_Options())::get_option('adscout_api_token');
    }

    /**
     * Returns the bearer token from the plugin options or
     * false if it's not set.
     *
     * @return string | bool
     * @access private
     * @static
     *
     * @since 2.0.0
     */
    private static function code()
    {
        return (new AdScout_Options())::get_option('adscout_partner_domain_hex');
    }

    /**
     * Returns the headers for the API requests.
     *
     * @return array{Content-Type: string, Accept: string, Authorization: string}
     * @access private
     * @static
     *
     * @since 2.0.0
     */
    #[ArrayShape(['Content-Type' => "string", 'Accept' => "string", 'Authorization' => "string"])]
    private static function headers($bearer = false): array
    {
        $token = 'Bearer ' . self::bearer();
        if($bearer) {
            $token = 'Bearer ' . $bearer;
        }

        return array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $token,
        );
    }

    /**
     * Returns an array with the URLs for all different request types
     *
     * @return array{create_partner: string, track_order: string, change_order_status: string, partner_promocode_ref: string}
     * @access private
     * @static
     *
     * @since 2.0.0
     */
    #[ArrayShape(['create_partner' => "string", 'track_order' => "string", 'change_order_status' => "string", 'partner_promocode_ref' => "string", 'check_coupon' => "string", 'sync_order_statuses' => "string"])]
    private static function request_type_url(): array
    {
        return array(
            'create_partner' => ADSCOUT_API_BASE_URL . ADSCOUT_API_CREATE_PARTNER,
            'track_order' => ADSCOUT_API_BASE_URL . ADSCOUT_API_TRACK_ORDER,
            'change_order_status' => ADSCOUT_API_BASE_URL . ADSCOUT_API_CHANGE_ORDER_STATUS,
            'partner_promocode_ref' => ADSCOUT_API_BASE_URL . ADSCOUT_API_PARTNER_PROMOCODE_REF,
            'check_coupon' => ADSCOUT_API_BASE_URL . ADSCOUT_API_PARTNER_PROMOCODE_REF,
            'sync_order_statuses' => ADSCOUT_API_BASE_URL . ADSCOUT_SYNC_ALL_ORDER_STATUSES,
        );
    }
}
