<?php
defined('ABSPATH') or die('No script kiddies please!');

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

//clear job queues
if (wp_next_scheduled('adscout_run_job_queue')) {
    wp_clear_scheduled_hook('adscout_run_job_queue');
}

//clear all options
delete_option('as_integration_options');
delete_option('adscout_integration_options');