<?php
defined('ABSPATH') or die('No script kiddies please!');

if (defined('WC_VERSION')) { ?>

    <div class="settings-wrapper">
        <h2>
            <?php adscout_echo_text(__('Product Feed Settings', 'adscout')); ?>
        </h2>

        <form id="adscout_feed_generator" method="POST">


            <div>
                <?php adscout_echo_text(__('Feed URL', 'adscout')); ?>:
                <input type="text" value="<?php $as_feed_url ? adscout_echo_text($as_feed_url)  : __('Feed not avaliable', 'adscout') ?>"
                       id="adscout_feed_url" readonly>
            </div>

                <p id="adscout_last_updated">
                    <?php adscout_echo_text(__('The feed is automatically updated every 24 hours. Last Update', 'adscout')); ?>:
                    <span id="adscout_last_updated__time">
                        <?php if($as_feed_last_updated){ ?>
                            <?php adscout_echo_text($as_feed_last_updated) ?>
                        <?php } ?>
                    </span>
                </p>


            <?php wp_nonce_field('adscout_feed_generator', 'adscout_feed_nonce', false); ?>

            <?php
            submit_button(__('Generate Product Feed', 'adscout'), 'primary', 'generate_feed_button', false);
            ?>

        </form>

    </div>

<?php } ?>