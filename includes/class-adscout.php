<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * The core AdScout class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks. Defines custom encryption/decryption, logging,
 * HTTP helpers and other functionality.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin and additional constants, used in the plugin.
 *
 * @since      2.0.0
 * @package    AdScout
 * @subpackage AdScout/includes
 * @link       https://adscout.io
 * @author     AdScout <hello@adscout.io>
 *
 * @updated   2.1.0
 * Added the order status sync settings.
 * Added action to add AdScout cookie value to order meta.
 *
 */
class AdScout
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    2.0.0
     * @access   protected
     * @var      AdScout_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected AdScout_Loader $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    2.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected string $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    2.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected string $version;

    /**
     * Defines whether we should load WooCommcerce realted functions.
     *
     * @since    2.0.0
     * @access   public
     * @var      boolean    This is how we know if we need to run the WooCommerce related functionalities.
     */
    public bool $woocommerce;

    /**
     * The unique installer has of the website.
     *
     * @since    2.0.0
     * @access   public
     * @var      string    The unique hash we use for the website.
     */
    public string $adscout_encryption_key;

    /**
     * The root directory of the plugin.
     *
     * @since    2.2.0
     * @access   public
     * @var      string    The root directory of the plugin.
     */
    public string $adscout_plugin_root = __DIR__ . '/';

    /**
     * The root directory of the plugin as a URL.
     *
     * @since    2.2.0
     * @access   public
     * @var      string    The root directory of the plugin as a URL.
     */
    public string $adscout_plugin_root_url;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @throws Exception
     * @since    2.0.0
     */
    public function __construct()
    {
        if (defined('ADSCOUT_VERSION')) {
            $this->version = ADSCOUT_VERSION;
        } else {
            $this->version = '2.0.0';
        }
        $this->plugin_name = 'adscout';
        $this->woocommerce = $this->woocommerce();
        $this->adscout_encryption_key = $this->adscout_encryption_key();
        $this->adscout_plugin_root_url = $this->adscout_plugin_root_url();

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - AdScout_Loader. Orchestrates the hooks of the plugin.
     * - AdScout_i18n. Define internationalization functionality.
     * - AdScout_Admin. Define all hooks for the admin area.
     * - Adscout_Public. Define all hooks for the public side of the site.
     * - AdScout_Logger. Define all hooks for the logging functionality.
     * - AdScout_Request. Define all hooks for the HTTP requests.
     * - AdScout_Options. Define all hooks for the plugin options.
     * - AdScout_Encrypt_Decrypt. Define all hooks for the encryption/decryption functionality.
     * - AdScout_Order_Processor. Define all hooks for the order processing functionality.
     * - AdScout_Settings. Define all hooks for the settings pages/forms.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @access   private
     * @return    void
     * @throws    Exception    If any of the dependencies are missing.
     *
     * @since    2.0.0
     */
    private function load_dependencies(): void
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-adscout-admin.php';

        /**
         * Loads everything needed for the public side of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-adscout-public.php';

        /**
         * Loads everything needed for the public side of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-logger.php';

        /**
         * Loads everything needed for the HTTP requests for the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-request.php';

        /**
         * Loads the plugin Options clss.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-options.php';

        /**
         * Loads everything needed for the custom encrypt/decrypt helper.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-encrypt-decrypt.php';

        /**
         * Loads the settings pages/forms.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-settings.php';

        /**
         * Loads the AJAX methods for the admin panel.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-ajax.php';

        /**
         * Loads the frontend helpers.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-frontend.php';

        /**
         * Loads the WooCommerce helpers for AdScout.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-woocommerce.php';

        /**
         * Checks is WooCommerce is installed and activated, and if so, loads the frontend.
         */
        if ($this->woocommerce) {

            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-jobs.php';
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adscout-order-processor.php';

        }

        $this->loader = new AdScout_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the AdScout_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @see AdScout_i18n::load_plugin_textdomain()
     *
     * @access   private
     * @since    2.0.0
     */
    private function set_locale(): void
    {

        $plugin_i18n = new AdScout_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all the hooks related to the admin area functionality
     * of the plugin.
     *
     * Also, if WooCommerce is installed and activated, we register the hooks for the WooCommerce functionality.
     *
     * @since    2.0.0
     * @access   private
     */
    private function define_admin_hooks(): void
    {

        $plugin_admin = new AdScout_Admin($this->get_plugin_name(), $this->get_version());
        $plugin_ajax = new Adscout_Ajax();
        $woocommerce = new AdScout_WooCommerce();

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'admin_page');
        $this->loader->add_action('admin_init', $plugin_admin, 'admin_settings_fields');
        $this->loader->add_filter('plugin_action_links_adscout/adscout.php', $plugin_admin, 'plugin_additional_links');
        $this->loader->add_filter('admin_body_class', $plugin_admin, 'admin_page_css_class');

        if ($this->woocommerce) {

            $order_processor = new AdScout_Order_Processor();
            $this->loader->add_action('woocommerce_order_status_changed', $order_processor, 'sync_order');
            $this->loader->add_action('woocommerce_order_status_changed', $order_processor, 'change_order_status');
            $this->loader->add_action('woocommerce_applied_coupon', $order_processor, 'check_coupon');
            $this->loader->add_action('woocommerce_new_order', $order_processor, 'update_order_meta_with_adscout_cookie_value', 100);
            $this->loader->add_action('woocommerce_new_order', $order_processor, 'sync_order', 101);


            //WooCommerce feed generator
            $this->loader->add_action('wp_ajax_adscout_generate_feed', $plugin_ajax, 'adscout_generate_feed');
            $this->loader->add_action('wp_ajax_adscout_save_order_status_sync_settings', $plugin_ajax, 'adscout_save_order_status_sync_settings');

            //Ajax checker for cuoupons for WC BLocks
            $this->loader->add_action('wp_ajax_adscout_apply_coupon', $plugin_ajax, 'adscout_apply_coupon');
            $this->loader->add_action('wp_ajax_nopriv_adscout_apply_coupon', $plugin_ajax, 'adscout_apply_coupon');

            //WooCommerce product data echo on single product page
            $this->loader->add_action('wp_footer', $woocommerce, 'product_data');

        }

        $this->loader->add_action('wp_ajax_adscout_save_integration_settings', $plugin_ajax, 'adscout_save_integration_settings');

    }

    /**
     * Register all the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    2.0.0
     * @access   private
     */
    private function define_public_hooks(): void
    {

        $plugin_public = new Adscout_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Run the loader to execute all the hooks with WordPress.
     *
     * @since    2.0.0
     */
    public function run(): void
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     2.0.0
     */
    public function get_plugin_name(): string
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    AdScout_Loader    Orchestrates the hooks of the plugin.
     * @since     2.0.0
     */
    public function get_loader(): AdScout_Loader
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     2.0.0
     */
    public function get_version(): string
    {
        return $this->version;
    }

    /**
     * Retrieve WooCommerce existense.
     *
     * @return    boolean    Whether WooCommerce is installed and activated.
     * @since     2.0.0
     */
    public function woocommerce(): bool
    {
        //check if woocommerce is installed and activeted and if so, set $woocommerce to true
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }


    /**
     * Use NONCE_KEY as the key with which to encrypt all sensitive plugin data.
     *
     * @return    string    The NONCE_KEY.
     * @since     2.0.0
     */
    public function adscout_encryption_key(): string
    {
        return NONCE_KEY;
    }

    /**
     * Return the public URL of the plugin.
     *
     * @return    string    The root URL of the plugin.
     * @since     2.2.0
     */
    public function adscout_plugin_root_url(): string
    {
        return plugin_dir_url(ADSCOUT_ROOT_FILE);
    }


}
