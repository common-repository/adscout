<?php
defined('ABSPATH') or die('No script kiddies please!');

//create a new class called AdScout_Logger
class AdScout_Logger
{

    private string $file;

    public function __construct()
    {
        $hash = (new AdScout_Options())::get_option('adscout_hash');
        $this->file = $this->set_file($hash);
    }

    public function get_file(): string
    {
        return $this->file;
    }

    public function set_file($hash): string {
        return defined('WP_LOG_DIR') ? WP_LOG_DIR . '/adscout-' . $hash . '.log' : WP_CONTENT_DIR . '/adscout-' . $hash . '.log';
    }

    /**
     * Add a new log entry to the custom log file
     *
     * @param $message
     * @param $level
     * @return void
     *
     * @since 2.0.0
     */
    public static function add($message, $level = 'debug'): void
    {
        //if the message is an array or an object, convert it to a string
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        //create a custom log file in the wp_log_dir so that the log file is not overwritten

        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        $log = '';

        if ($wp_filesystem->exists((new AdScout_Logger)->get_file())) {
            $log = $wp_filesystem->get_contents((new AdScout_Logger)->get_file());
        }

        $log .= gmdate('Y-m-d H:i:s') . ' [' . strtoupper($level) . '] ' . $message . PHP_EOL;

        $wp_filesystem->put_contents((new AdScout_Logger)->get_file(), $log , 0664);
        return;
    }

    public static function delete(): void {
        $file = defined('WP_LOG_DIR') ? WP_LOG_DIR . '/adscout-' . $hash . '.log' : WP_CONTENT_DIR . '/adscout-' . $hash . '.log';
        if (file_exists($file)) {
            wp_delete_file($file);
        }
    }

}
