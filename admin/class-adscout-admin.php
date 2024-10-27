<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    AdScout
 * @subpackage AdScout/admin
 * @author     AdScout <hello@adscout.io>
 */
class AdScout_Admin
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
     * All options, read to be used in the admin panel.
     *
     * @since    2.0.0
     * @access   private
     * @var      array $options All options of the plugin.
     */
    private array $options;


    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    2.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = $this->options();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @return   void
     * @access   public
     *
     * @since    2.0.0
     */
    public function enqueue_styles(): void
    {

        /**
         *
         * An instance of this class should be passed to the run() function
         * defined in AdScout_Loader as all the hooks are defined
         * in that particular class.
         *
         * The AdScout_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/adscout-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @return   void
     * @access   public
     *
     * @since    2.0.0
     */
    public function enqueue_scripts(): void
    {

        /**
         *
         * An instance of this class should be passed to the run() function
         * defined in AdScout_Loader as all the hooks are defined
         * in that particular class.
         *
         * The AdScout_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/adscout-admin.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'adscout_admin', array(
            'feed_generate_error' => __('Error generating feed. Try again or contact support', 'adscout'),
            'feed_generate_success' => __('Feed generated successfully', 'adscout'),
            'order_status_sync_warning' => __('Setting saved, but please, be advised issues might occur. Contact support with the following message', 'adscout') . ': ',
            'order_status_sync_error' => __('Error saving settings. Try again or contact support with the following message', 'adscout') . ': ',
            'order_status_sync_success' => __('Order sync statuses saved successfully', 'adscout'),
            'settings_sync_error' => __('Error saving settings. Please contact support with the following message', 'adscout') . ': ',
            'settings_sync_success' => __('Setting saved successfully ', 'adscout'),
        ));


    }

    /**
     * Register the additional plugin links into the Plugin menu.
     *
     * @param array $actions The current actions of the plugin.
     * @return   array The new actions of the plugin.
     * @access   public
     * @static
     *
     * @since    2.0.0
     */
    public function plugin_additional_links($actions): array
    {

        $settings = array(
            'settings' => '<a href="admin.php?page=as_integration_settings">' . __('Settings'). '</a>'
        );

        $settings = array_merge($settings, $actions);
        $settings = array_merge($settings, array('docs' => '<a href="https://adscout.io/faq" target="_blank">FAQ</a>'));

        return $settings;
    }

    /**
     * Register the settings page in wp-admin.
     *
     * @return   void
     * @access   public
     * @static
     *
     * @since    2.0.0
     */
    public function admin_page(): void
    {

        add_menu_page(
            'AdScout Integration Settings',
            'AdScout',
            'manage_options',
            'as_integration_settings',
            function () {
                require plugin_dir_path(__FILE__) . 'partials/adscout-admin-display.php';
            },
            plugin_dir_url(__FILE__) . 'img/adscout-logo.svg',
            81,
        );
    }

    /**
     * Add a body class to make use of plugin CSS.
     *
     * @param string $classes The current classes of the body.
     * @return   string The new classes of the body.
     * @access   public
     * @static
     *
     * @since    2.0.0
     */
    public function admin_page_css_class($classes): string
    {

        global $plugin_page;

        if ($plugin_page === 'as_integration_settings') {
            $classes .= ' adscout';
        }

        return $classes;
    }

    /**
     * Register the settings options for the plugin.
     *
     * @return   void
     * @access   public
     *
     * @since    2.0.0
     */
    public function admin_settings_fields(): void
    {

        add_settings_section(
            'as_integration_section',
            'Connection Settings',
            'as_integration_section_callback',
            'as_integration_settings'
        );

    }

    /**
     * Callback for the option field of the module.
     * If no options are set, will return an array with false for each option value.
     *
     * @return    array    The options of the plugin.
     * @access    private
     * @static
     *
     * @since    2.0.0
     */
    private static function options(): array
    {
        return (new AdScout_Options())::all_options();
    }


}
