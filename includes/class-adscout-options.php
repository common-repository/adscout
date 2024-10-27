<?php
defined('ABSPATH') or die('No script kiddies please!');

class AdScout_Options
{

    /**
     * Get all options in an array.
     * If no options are set, will return an array with false for each option value.
     *
     * @return false[]
     * @access private
     * @static
     *
     * @since 2.0.0
     *
     * @updated 2.1.0
     * Added the adscout_order_statuses_to_sync option
     *
     * @updated 2.2.0
     * Added the order_status_synced_to_adscout, order_status_synced_to_adscout_timestamp option
     * which allows us to track when and what order status was synced to AdScout
     *
     */
    public static function all_options(): array
    {
        $required_options = [
            'adscout_api_token',
            'adscout_partner_domain_hex',
            'as_feed_last_updated',
            'as_feed_last_updated_timestamp',
            'as_feed_url',
            'as_feed_file_path',
            'adscout_hash',
            'adscout_order_statuses_to_sync',
            'order_status_synced_to_adscout',
            'order_status_synced_to_adscout_timestamp',
        ];

        if(get_option('as_integration_options')) {
            $options = get_option('as_integration_options');
            delete_option('as_integration_options');
            add_option('adscout_integration_options', $options);
        }

        $options = get_option('adscout_integration_options');
        $decrypt = new AdScout_Encrypt_Decrypt();

        if (is_array($options) or !str_contains($options, 'as__')) {

            if ($adscout_api_token = get_option('adscout_api_token')) {
                $options['adscout_api_token'] = $adscout_api_token;
                delete_option('adscout_api_token');
            }

            if ($adscout_partner_domain_hex = get_option('adscout_partner_domain_hex')) {
                $options['adscout_partner_domain_hex'] = $adscout_partner_domain_hex;
                delete_option('adscout_partner_domain_hex');
            }

            if ($as_feed_last_updated = get_option('as_feed_last_updated')) {
                $options['as_feed_last_updated'] = $as_feed_last_updated;
                delete_option('as_feed_last_updated');
            }

            if ($adscout_hash = get_option('adscout_hash')) {
                $options['adscout_hash'] = $adscout_hash;
                delete_option('adscout_hash');
            } else {
                $options['adscout_hash'] = md5(get_home_url());
            }

            $options = update_option('adscout_integration_options', 'as__' . $decrypt->encrypt(base64_encode(maybe_serialize($options))));
            maybe_unserialize($options);
        }

        if (substr($options, 0, strlen('as__')) == 'as__') {
            $options = maybe_unserialize(base64_decode($decrypt->decrypt(substr($options, strlen('as__')))));
        }

        if (!is_array($options) or empty($options)) {
            $options = array(
                'adscout_api_token' => false,
                'adscout_partner_domain_hex' => false,
                'as_feed_last_updated' => false,
                'as_feed_last_updated_timestamp' => false,
                'as_feed_url' => false,
                'as_feed_file_path' => false,
                'adscout_hash' => false,
                'adscout_order_statuses_to_sync' => ['wc-completed', 'wc-processing'],
                'order_status_synced_to_adscout' => null,
                'order_status_synced_to_adscout_timestamp' => null
            );
        }

        foreach ($required_options as $option) {
            if (is_array($options) and !array_key_exists($option, $options)) {
                $options[$option] = false;
            }
        }

        if (!$options['adscout_hash']) {
            $options['adscout_hash'] = md5(get_home_url());
        }

        return $options;
    }

    /**
     * Get an option value by name.
     * If the option does not exist, will return false.
     * If the option exists, will return the decrypted value.
     *
     * @param $name
     * @return string|bool The option value or false if option does not exist.
     *
     * @since 2.0.0
     */
    public static function get_option($name)
    {
        $options = self::all_options();
        return array_key_exists($name, $options) ? $options[$name] : false;
    }

    /**
     * Set an option value by name.
     *
     * @param $name
     * @param $value
     * @return bool
     *
     * @since 2.0.0
     * @updated 2.0.1
     */
    public static function set_option($name, $value): bool
    {
        $return = false;

        $options = self::all_options();
        $options[$name] = $value;
        $encrypt = new AdScout_Encrypt_Decrypt();
        $options = 'as__' . $encrypt->encrypt(base64_encode(maybe_serialize($options)));
        $options = update_option('adscout_integration_options', $options);

        if (!is_wp_error($options)) {
            $return = true;
        }

        return $return;

    }

    /**
     * Update multiple options at once.
     *
     * @param $options: array
     * @return bool
     * @access private
     * @static
     *
     * @since 2.0.0
     */
    public static function update_options(array $options): bool
    {
        foreach ($options as $key => $value) {

            $update = self::set_option($key, $value);

            if (is_wp_error($update)) {
                return false;
            }

        }

        return true;
    }

    public static function delete_options(): void
    {
        delete_option('adscout_integration_options');
    }


}