<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Provide an admin area view for the plugin
 *
 * This file is used to mark up the admin-facing aspects of the plugin.
 *
 * @link       https://adscout.io
 * @since      2.0.0
 *
 * @package    AdScout
 * @subpackage AdScout/admin/partials
 */
?>

<div class="wrap">
    <h1 class="plugin-heading" aria-label="AdScout.io Integration Settings" aria-hidden="true"></h1>
    <div class="adscout__header">
        <img src="<?php echo esc_url(plugins_url('/admin/img/adscout-logo.png', ADSCOUT_ROOT_FILE)); ?>"
             alt="AdScout Logo">
        <h3 class="adscout__header-heading"><?php adscout_echo_text(__('AdScout.io Integration Settings', 'adscout')) ?></h3>
    </div>
    <div class="adscout__wrapper">

        <div>
            <div class="settings-wrapper">
                <h2>
                    <?php adscout_echo_text(__('Integration Credentials', 'adscout')) ?>
                </h2>

                <?php

                $sections = new AdScout_Settings();
                $sections->integration_settings();

                ?>

            </div>

            <?php

            if (defined('WC_VERSION')) {
                $sections->feed_settings();
                $sections->order_status_settings();
            }

            ?>


        </div>

        <div>
            <?php
            $current_locale = get_locale();

            if ('bg_BG' === $current_locale) {
                echo '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/nAp0JoGHRow" title="Какво представлява AdScout" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen rel="0"></iframe>';
            } else {
                echo '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/YWKRmdcvtBk" title="AdScout Introduction" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen rel="0"></iframe>';
            }
            ?>

        </div>
    </div>
</div>