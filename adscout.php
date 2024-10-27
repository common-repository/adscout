<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 *
 * @link              https://adscout.io
 * @link              http://scoutefy.com
 * @since             2.0.0
 * @package           AdScout / ux2.dev
 *
 * @wordpress-plugin
 * Plugin Name:       AdScout Integration
 * Plugin URI:        https://adscout.io / http://scoutefy.com
 * Description:       WordPress / WooCommerce integration for AdScout
 * Version:           2.2.6
 * Author:            AdScout / ux2.dev
 * Author URI:        https://ux2.dev/plugins/adscout
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       adscout
 * Domain Path:       /languages
 * Requires at least: 4.7
 * Tested up to:      6.6
 * Requires PHP:      7.4
 * Contributors:      druf
 *
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * A string, used as a generic identifier ot text domain, plugin, etc.
 */
define('ADSCOUT_IDENTIFIER', 'adscout');

/**
 * Current plugin version.
 * Start at version 2.0.0 and use SemVer - https://semver.org
 */
define('ADSCOUT_VERSION', '2.2.6');

/**
 * The root file of the plugin. User for different functions and calls.
 */
define('ADSCOUT_ROOT_FILE', WP_PLUGIN_DIR . '/' . ADSCOUT_IDENTIFIER . '/' . ADSCOUT_IDENTIFIER . '.php');

/**
 * Default name of the AdScout cookie we use on the website
 */
define('ADSCOUT_COOKIE', 'ScoutSRef');

/**
 * The helpers to make all cronjob initiators available during runtime.
 */
function adscout_run_job_queue(): void
{
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        (new AdScout_WooCommerce())::generate_product_feed();
    }
}

function adscout_echo_text($text): void
{
    echo esc_html($text);
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-adscout-activator.php
 */
function adscout_activate()
{

    if (wp_next_scheduled('as_generate_product_feed_daily')) {
        wp_clear_scheduled_hook('as_generate_product_feed_daily');
    }
    if (wp_next_scheduled('run_adscout_job_queue')) {
        wp_clear_scheduled_hook('run_adscout_job_queue');
    }

    require_once plugin_dir_path(__FILE__) . 'includes/class-adscout-activator.php';
    AdScout_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-adscout-deactivator.php
 */
function adscout_deactivate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-adscout-deactivator.php';
    AdScout_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'adscout_activate');
register_deactivation_hook(__FILE__, 'adscout_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-adscout.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function adscout_run()
{

    $plugin = new AdScout();
    $plugin->run();

}

adscout_run();

$plugin = new AdScout();
if($plugin->woocommerce) {
    add_action('adscout_run_job_queue', 'adscout_run_job_queue');
    if (!wp_next_scheduled('adscout_run_job_queue')) {
        wp_schedule_event(
            strtotime('today midnight'),
            'daily',
            'adscout_run_job_queue');
    }
}
