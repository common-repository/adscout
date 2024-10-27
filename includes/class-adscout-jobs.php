<?php
defined('ABSPATH') or die('No script kiddies please!');

class AdScout_Jobs
{

    public static function generate_feed_job_new()
    {
        (new AdScout_WooCommerce())::generate_product_feed();
    }

}