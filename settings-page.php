<?php



/* ============= SETTINGS INITIALIZATION ================ */

$settings = procommerca_get_settings();
if (!isset($settings)) {
    // Migrate old client data 
    $api_token = get_option('procommerca-api-token');
    $test_mode_setting = get_option('procommerca-test-mode');
    $enable_side_cart_setting = get_option('procommerca-enable-side-cart');
    $client_id = get_option('procommerca-api-client-id');

    if (isset($api_token) && !empty($api_token)) {
        $settings = array(
            'test_mode' => $test_mode_setting,
            'default_client_id' => $client_id,
            'clients' => array(
                $client_id => array(
                    'name' => 'Default client',
                    'api_token' => $api_token
                )
            )
        );
    } else {
        $settings = array(
            'test_mode' => false,
            'clients' => array()
        );
    }
    procommerca_set_settings($settings);
}


/* ============= ADMIN MENU  ================ */


add_action('admin_menu', 'procommerca_add_admin_menu');
add_action('admin_init', 'procommerca_settings_init');

function procommerca_add_admin_menu()
{
    add_options_page('Funbutler', 'Funbutler', 'manage_options', 'procommerca-options-page', 'procommerca_options_page');
}

function procommerca_settings_init()
{
    register_setting('procommerca', 'procommerca_settings');
}


/* ============= OPTIONS PAGE ================ */


function procommerca_options_page()
{
    $settings = procommerca_get_settings();

    /* INITIALIZATION LOGIC */
    $test_mode = boolval(sanitize_text_field($_POST['test-mode']));
    $api_key_to_process = sanitize_text_field($_POST['api-key']);

    // Add new client 
    if (isset($api_key_to_process) && !empty($api_key_to_process)) {
        check_admin_referer('procommerca-setup');

        if (count($settings['clients']) == 0) {
            if (isset($test_mode) && !empty($test_mode)) {
                $settings['test_mode'] = $test_mode;
            } else {
                $settings['test_mode'] = false;
            }
            procommerca_set_settings($settings);
        }

        $result = procommerca_api('/api/external/api-tokens/generate', 'POST', array('apiKey' => $api_key_to_process, 'description' => 'Funbutler Booking Wordpress Plugin'));

        if (!empty($result) && !empty($result['message'])) {
            $notice = $result['message'];
        }



        if (!empty($result) && !empty($result['apiToken'])) {

            if (count($settings['clients']) == 0) {
                $settings['default_client_id'] = $result['clientId'];
            }

            $settings['clients'][$result['clientId']] = array(
                'name' => $result['clientName'],
                'api_token' => $result['apiToken']
            );
        }
        procommerca_set_settings($settings);
    }

    /* DISCONNECT LOGIC */
    $disconnect = boolval(sanitize_text_field($_POST['disconnect']));
    $disconnect_client_id = sanitize_text_field($_POST['client-id']);
    if (isset($disconnect) && $disconnect && isset($disconnect_client_id)) {
        check_admin_referer('procommerca-disconnect');
        unset($settings['clients'][$disconnect_client_id]);
        procommerca_set_settings($settings);
    }

    //print_r($settings);

    /* VIEW LOGIC */
    if (count($settings['clients']) > 0) {
        $api_check = procommerca_api('/api/external/v1/check', 'GET');
?>
        <h2>Site is connected to <?php echo $settings['test_mode'] ? 'test' : 'production'; ?> environment.</h2>
        <h4>Connection status: <?php echo (!empty($api_check) && $api_check['succeeded'] ? '<span style="color:#429c00">OK</span>' : '<span style="color:#9c0000">ERROR</span>'); ?></h4>
        <br />
        <?php
        foreach ($settings['clients'] as $client_id => $client) {
            $booking_forms = procommerca_api('/api/external/v1/clients/' . $client_id . '/booking-forms', 'GET', array(), $client['api_token']);
        ?>
            <h1><?php esc_html_e($client['name']); ?></h1>
            <h2>Booking forms</h2>
            <div>
                <table class="wp-list-table widefat fixed striped">
                    <tr>
                        <th>Name</th>
                        <th style="text-align:right;">Shortcode</th>
                    </tr>
                    <?php if (!empty($booking_forms)) { ?>
                        <?php foreach ($booking_forms as $booking_form) { ?>
                            <tr>
                                <th><?php esc_html_e($booking_form['name']); ?></th>
                                <td style="text-align:right;">
                                    <pre>[booking-form client-id="<?php esc_html_e($client_id); ?>" id="<?php esc_html_e($booking_form['id']); ?>"]</pre>
                                </td>
                            </tr>
                        <?php } ?>
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
                            <pre>[voucher-store client-id="<?php esc_html_e($client_id); ?>"]</pre>
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
                            <pre>[testimonials client-id="<?php esc_html_e($client_id); ?>"]</pre>
                        </td>
                    </tr>
                </table>
            </div>
            <br />
            <?php if (count($settings['clients']) > 1) { ?>
                <h2>Side-cart (for pages without booking-forms)</h2>
                <div>
                    <table class="wp-list-table widefat fixed striped">
                        <tr>
                            <th style="text-align:right;">Shortcode</th>
                        </tr>
                        <tr>
                            <td style="text-align:right;">
                                <pre>[side-cart client-id="<?php esc_html_e($client_id); ?>"]</pre>
                            </td>
                        </tr>
                    </table>
                </div>
                <br />
            <?php } ?>
            <h2>Disconnect</h2>
            <form method='post'>
                <?php wp_nonce_field('procommerca-disconnect'); ?>
                <input type="hidden" name="client-id" value="<?php esc_html_e($client_id); ?>" />
                <table class="form-table">
                    <tr>
                        <th>Disconnect</th>
                        <td><label><input type="checkbox" name="disconnect" /></label></td>
                    </tr>
                </table>
                <br />
                <div><input type="submit" class="button button-primary" value="Disconnect" /></div>
            </form>
            <br />
            <hr>
        <?php
        }
        ?>
        <form method='post'>
            <?php wp_nonce_field('procommerca-setup'); ?>
            <h2>Add client</h2>
            <table class="form-table">
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

        /* INITIALIZATION FORM */
    } else {

    ?>
        <form method='post'>
            <?php wp_nonce_field('procommerca-setup'); ?>
            <h2>Funbutler settings</h2>
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
