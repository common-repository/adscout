<?php
defined('ABSPATH') or die('No script kiddies please!');
?>
<form id="adscout_integration_settings" method="POST">

    <table class="form-table" role="presentation">
        <tbody>
        <tr>
            <th scope="row"><?php adscout_echo_text(__('API Token', 'adscout')); ?></th>
            <td><input type="text" id="adscout_api_token" data-init="<?php adscout_echo_text($api_token); ?>"
                       value="<?php adscout_echo_text($api_token); ?>" class="adscout_input"></td>
        </tr>
        <tr>
            <th scope="row"><?php adscout_echo_text(__('API Code', 'adscout')); ?></th>
            <td><input type="text" id="adscout_partner_domain_hex" data-init="<?php adscout_echo_text($partner_domain_hex); ?>"
                       value="<?php adscout_echo_text($partner_domain_hex); ?>" class="adscout_input"></td>
        </tr>
        </tbody>
    </table>

    <?php wp_nonce_field('adscout_save_integration_settings', 'adscout_nonce'); ?>

    <input type="hidden" name="action" value="<?php adscout_echo_text($api_token); ?>" id="adscout_api_token_original"></input>
    <input type="hidden" name="action" value="<?php adscout_echo_text($partner_domain_hex); ?>"
           id="adscout_partner_domain_hex_original"></input>

    <p class="submit"><input type="submit" name="submit" id="adscout_integration_settings_save" class="button button-primary"
                             value="<?php adscout_echo_text(__('Save settings', 'adscout')) ?>" disabled></p>

</form>
