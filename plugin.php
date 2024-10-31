<?php
/*
Plugin Name: Funbutler Booking
Plugin URI: https://business.funbutler.com
Description: Connecting your site to the Funbutler Booking System
Version: 0.14
Author: Emil Isaksson
*/

require_once 'inc/api.inc.php';
require_once 'inc/settings.inc.php';
require_once 'settings-page.php';
require_once 'shortcodes.php';
require_once 'menu-item.php';




add_action('wp_enqueue_scripts', 'procommerca_scripts');
function procommerca_scripts()
{
  wp_enqueue_style('procommerca-booking-style', procommerca_get_api_base_url() . '/apps/public-app/styles.css', array(), false, false);

  wp_enqueue_script('procommerca-booking-runtime-script', procommerca_get_api_base_url() . '/apps/public-app/runtime-es5.js', array(), false, true);
  wp_enqueue_script('procommerca-booking-polyfills-script', procommerca_get_api_base_url() . '/apps/public-app/polyfills-es5.js', array(), false, true);
  wp_enqueue_script('procommerca-booking-styles-script', procommerca_get_api_base_url() . '/apps/public-app/styles-es5.js', array(), false, true);
  wp_enqueue_script('procommerca-booking-vendor-script', procommerca_get_api_base_url() . '/apps/public-app/vendor-es5.js', array(), false, true);
  wp_enqueue_script('procommerca-booking-main-script', procommerca_get_api_base_url() . '/apps/public-app/main-es5.js', array(), false, true);

  wp_enqueue_style('procommerca-booking-local-style', plugin_dir_url(__FILE__) . '/style.css');
}

add_action('wp_head', 'procommerca_head');
function procommerca_head()
{
?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
}


add_action('wp_footer', 'procommerca_footer');
function procommerca_footer()
{
  $settings = procommerca_get_settings();
  $client_id = $settings['default_client_id'];

  if (isset($client_id) && count($settings['clients']) == 1) {
      echo '<side-cart clientId="' . $client_id . '"></side-cart>';
  }
}


?>