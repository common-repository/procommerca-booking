<?php

add_action('admin_menu', 'procommerca_add_admin_menu');
add_action('admin_init', 'procommerca_settings_init');

function procommerca_add_admin_menu()
{
    add_options_page('Procommerca', 'Procommerca', 'manage_options', 'procommerca-options-page', 'procommerca_options_page');
}

function procommerca_settings_init()
{
    register_setting('procommerca', 'procommerca_settings');
}

function procommerca_options_page()
{
    /* INITIALIZATION LOGIC */
    $test_mode = boolval(sanitize_text_field($_POST['test-mode']));
    $api_key = sanitize_text_field($_POST['api-key']);


    if (isset($api_key) && !empty($api_key)) {
        check_admin_referer('procommerca-setup');

        if (isset($test_mode) && !empty($test_mode)) {
            update_option('procommerca-test-mode', $test_mode);
        } else {
            update_option('procommerca-test-mode', false);
        }

        $result = procommerca_api('/api/external/api-tokens/generate', 'POST', array('apiKey' => $api_key, 'description' => 'Procommerca Booking Wordpress Plugin'));

        if (!empty($result) && !empty($result['message'])) {
            $notice = $result['message'];
        }

        if (!empty($result) && !empty($result['apiToken'])) {
            update_option('procommerca-api-token', $result['apiToken']);
            update_option('procommerca-api-client-id', $result['clientId']);
        }
    }

    /* DISCONNECT LOGIC */
    $disconnect = boolval(sanitize_text_field($_POST['disconnect']));
    if (isset($disconnect) && $disconnect) {
        check_admin_referer('procommerca-disconnect');
        delete_option('procommerca-api-token');
        delete_option('procommerca-api-client-id');
        delete_option('procommerca-test-mode');
    }


    /* ENABLE SIDE-CART LOGIC */
    $enable_side_cart = sanitize_text_field($_POST['enable-side-cart']);
    if (isset($enable_side_cart) && !empty($enable_side_cart)) {
        check_admin_referer('procommerca-settings');
        update_option('procommerca-enable-side-cart', boolval($enable_side_cart));
    }


    /* VIEW LOGIC */
    $api_token = get_option('procommerca-api-token');
    $test_mode_setting = get_option('procommerca-test-mode');
    $enable_side_cart_setting = get_option('procommerca-enable-side-cart');
    if (isset($api_token) && !empty($api_token)) {
        $api_check = procommerca_api('/api/external/v1/check', 'GET');
        $booking_forms = procommerca_api('/api/external/v1/clients/' . get_option('procommerca-api-client-id') . '/booking-forms', 'GET');

?>
        <h2>Site is connected to <?php echo $test_mode_setting ? 'test' : 'production'; ?> environment.</h2>
        <h4>Connection status: <?php echo (!empty($api_check) && $api_check['succeeded'] ? '<span style="color:#429c00">OK</span>' : '<span style="color:#9c0000">ERROR</span>'); ?></h4>
        <br />
        <h2>Booking forms</h2>
        <div>
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <th>Name</th>
                    <th style="text-align:right;">Shortcode</th>
                </tr>
                <?php foreach ($booking_forms as $booking_form) { ?>
                    <tr>
                        <th><?php esc_html_e($booking_form['name']); ?></th>
                        <td style="text-align:right;">
                            <pre>[booking-form id="<?php esc_html_e($booking_form['id']); ?>"]</pre>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
        <h2>Voucher store</h2>
        <div>
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <th style="text-align:right;">Shortcode</th>
                </tr>
                <tr>
                    <td style="text-align:right;">
                        <pre>[voucher-store]</pre>
                    </td>
                </tr>
            </table>
        </div>
        <br />
        <h2>Testimonials</h2>
        <div>
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <th style="text-align:right;">Shortcode</th>
                </tr>
                <tr>
                    <td style="text-align:right;">
                        <pre>[testimonials]</pre>
                    </td>
                </tr>
            </table>
        </div>
        <br />
        <h2>Settings</h2>
        <form method='post'>
            <?php wp_nonce_field('procommerca-settings'); ?>
            <table class="form-table">
                <tr>
                    <th>Enable side-cart</th>
                    <td><label><input type="checkbox" name="enable-side-cart" <?php echo isset($enable_side_cart_setting) && $enable_side_cart_setting ? 'checked' : ''; ?> /></label></td>
                </tr>
            </table>
            <br />
            <div><input type="submit" class="button button-primary" value="Save" /></div>
        </form>
        <br />
        <h2>Disconnect</h2>
        <form method='post'>
            <?php wp_nonce_field('procommerca-disconnect'); ?>
            <table class="form-table">
                <tr>
                    <th>Disconnect</th>
                    <td><label><input type="checkbox" name="disconnect" /></label></td>
                </tr>
            </table>
            <br />
            <div><input type="submit" class="button button-primary" value="Disconnect" /></div>
            <?php

            ?>

        </form>
    <?php
        /* INITIALIZATION FORM */
    } else {

    ?>
        <form method='post'>
            <?php wp_nonce_field('procommerca-setup'); ?>
            <h2>Procommerca settings</h2>
            <table class="form-table">
                <tr>
                    <th>Test mode</th>
                    <td><label><input type="checkbox" name="test-mode" /></label></td>
                </tr>
                <tr>
                    <th>API key</th>
                    <td><input type="text" name="api-key" placeholder="API key" /></td>
                </tr>
            </table>
            <br />
            <div><input type="submit" class="button button-primary" value="Connect" /></div>
            <div><?php esc_html_e($notice); ?></div>
            <?php

            ?>

        </form>
<?php
    }
}
