<?php


defined('ABSPATH') or die('No script kiddies please!');

if (defined('WC_VERSION')) { ?>

    <div class="settings-wrapper">
        <h2>
            <?php echo esc_html (__('Order status sync settings', 'adscout')) ?>
        </h2>
        <p>
            <?php echo esc_html (__('Please select one or more statuses for your orders. Only orders with the matching statuses will be synced with AdScout', 'adscout')) ?>
        </p>

        <form id="adscout_sync_status_settings" method="POST">


            <label for="as_order_statuses">
                <?php echo esc_html (__('Available Order Statuses', 'adscout')); ?>:
            </label>
            <br><br>

            <?php
            $order_statuses = wc_get_order_statuses();
            $adscout_order_statuses_to_sync = $adscout_order_statuses_to_sync ?? [];

            if (0 === count($adscout_order_statuses_to_sync)) {
                $adscout_order_statuses_to_sync = ['wc-completed', 'wc-processing'];
            }

            foreach ($order_statuses as $key => $value) {
                $checked = in_array($key, $adscout_order_statuses_to_sync) ? 'checked' : '';
                ?>
                <div>
                    <input type="checkbox" name="<?php adscout_echo_text ($key); ?>" id="<?php adscout_echo_text($key); ?>" value="JavaScript" <?php adscout_echo_text($checked) ?>>
                    <label for="<?php adscout_echo_text ($key); ?>"><?php adscout_echo_text ($value); ?></label>
                </div>

            <?php } ?>

            <?php wp_nonce_field('adscout_save_order_status_sync_settings', 'adscout_save_order_status_sync_settings', false); ?>
            <br>
            <?php
            submit_button( esc_html (__('Save setting', 'adscout')), 'primary', 'adscout_save_order_status_sync_settings_button', false);
            ?>

        </form>

    </div>

<?php } ?>