<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Fired during plugin deactivation
 *
 * @link       https://adscout.io
 * @since      2.0.0
 *
 * @package    AdScout
 * @subpackage AdScout/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      2.0.0
 * @package    AdScout
 * @subpackage AdScout/includes
 * @author     AdScout <hello@adscout.io>
 */
class AdScout_Deactivator {

	/**
	 * Actions fire upon deactivating the plugin:
     * - clear job queues
	 *
	 * Long Description.
	 *
	 * @since    2.0.0
	 */
	public static function deactivate(): void
    {

        //clear job queues
        if (wp_next_scheduled('adscout_run_job_queue')) {
            wp_clear_scheduled_hook('adscout_run_job_queue');
        }

        //delete feed if exists and the respective directory
        $option = (new AdScout_Options())::all_options();

        if($option['as_feed_file_path'] and file_exists($option['as_feed_file_path'])) {
            //delete the feed file
            wp_delete_file($option['as_feed_file_path']);
            $file = new WP_Filesystem_Direct(false);


            //delete the feed directory
            $upload_dir = wp_upload_dir();
            $feed_dir = trailingslashit($upload_dir['basedir']) . 'as-product-feed';
            $file->rmdir($feed_dir, true);
        }

        //delete the log file
        if(file_exists((new AdScout_Logger())->get_file())) {
            wp_delete_file((new AdScout_Logger())->get_file());
        }

    }

}
