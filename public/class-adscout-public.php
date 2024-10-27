<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://adscout.io
 * @since      2.0.0
 *
 * @package    AdScout
 * @subpackage AdScout/public
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * At AdScout, we utilize an alternative domain, scoutefy.com, for our integrations to enhance user experience and avoid common obstacles faced by digital advertising:
 * 1. AdBlocker Evasion: The absence of the word "ad" in our alternative domain helps us circumvent ad-blocking software. Many users employ ad blockers to enhance their browsing experience by filtering out intrusive advertisements. By using a domain that does not trigger these filters, we ensure that our essential scripts and resources are delivered seamlessly.
 * 2. User Engagement: Our primary goal is to foster engagement with potential customers. By avoiding ad blockers, we can maintain visibility and accessibility to our services, which directly impacts our conversion rates and overall user experience.
 * 3. Future Considerations: While we plan to continue using scoutefy.com for the time being, we are actively monitoring the statistics related to ad-blocker usage. Should there be significant changes in these metrics, we may reconsider our approach and potentially shift away from this alternative domain.
 * In summary, the use of **scoutefy.com** is a strategic decision aimed at optimizing our outreach efforts while keeping a close eye on user preferences and technology trends.
**/

define('ADSCOUT_SCRIPTS_ROOT', 'https://scoutefy.com/script/');

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    AdScout
 * @subpackage AdScout/public
 * @author     AdScout <hello@adscout.io>
 */
class Adscout_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    2.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private string $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    2.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private string $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    2.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     * As of version 2.0.0. this is just a placeholder for future development,
     * should one be needed.
     *
     * @since    2.0.0
     */
    public function enqueue_styles(): void
    {

        /**
         *
         * An instance of this class should be passed to the run() function
         * defined in AdScout_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The AdScout_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        // wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/adscout-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     * As of version 2.0.0. this is just a placeholder for future development,
     * should one be needed.
     *
     * @since    2.0.0
     */
    public function enqueue_scripts(): void
    {

        /**
         *
         * An instance of this class should be passed to the run() function
         * defined in AdScout_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The AdScout_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        $plugin = new AdScout();
        if ($plugin->woocommerce) {

            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/adscout-public.js', array('jquery'), $this->version, array(
                'strategy' => 'defer',
                'in_footer' => true,
            ));
            wp_localize_script($this->plugin_name, 'adscout_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

        }

        $hex = (new AdScout_Options())::get_option('adscout_partner_domain_hex');

        if ($hex) {
            $url = ADSCOUT_SCRIPTS_ROOT . $hex;
            wp_enqueue_script('adscout-main', $url, array(), ADSCOUT_VERSION, array('strategy' => 'async', 'in_footer' => true));
        }

        if ($plugin->woocommerce and !is_admin() and is_wc_endpoint_url('order-received')) {
            wp_enqueue_script('adscout-order-received', plugin_dir_url(__FILE__) . 'js/adscout-order-received.js', array('adscout-main'), ADSCOUT_VERSION, array('strategy' => 'async', 'in_footer' => true));
        }
    }


}
