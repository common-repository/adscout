<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0.0
 * @package    AdScout
 * @subpackage AdScout/includes
 * @author     AdScout <hello@adscout.io>
 */
class AdScout_Activator {

	/**
	 * What happens when we activate the plugin:
	 *
	 * - We add a daily job to generate the feed
     * - We register the job with WordPress
     * - We initiate basic settings (AdScout hash, etc.)
     *
     * @return void
	 *
	 * @since    2.0.0
	 */

	public static function activate(): void{

	}

}
