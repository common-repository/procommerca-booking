<?php

function procommerca_booking_form_shortcode($attributes)
{
    $settings = procommerca_get_settings();
    $client_id = $attributes['client-id'];

    if (empty($client_id)) {
        $client_id = $settings['default_client_id'];

        if (empty($client_id)) {
            $client_id = get_option('procommerca-api-client-id');
        }
    }

    if (isset($attributes) && isset($attributes['id']) && isset($client_id)) {
        $result = '<booking-form clientId="' . $client_id . '" formId="' . $attributes['id'] . '"><div id="main-loader" class="loader-wrapper"><div class="loader-icon large padded"></div></div></booking-form>';

        if(count($settings['clients']) > 1){
            $result .= '<side-cart clientId="' . $client_id . '"></side-cart>';
        }
        
        return $result;
    } else {
        return '';
    }
}

add_shortcode('booking-form', 'procommerca_booking_form_shortcode');


function procommerca_gift_card_store_shortcode($attributes)
{
    $settings = procommerca_get_settings();
    $client_id = $attributes['client-id'];
    if (empty($client_id)) {
        $client_id = $settings['default_client_id'];

        if (empty($client_id)) {
            $client_id = get_option('procommerca-api-client-id');
        }
    }

    if (!empty($client_id)) {
        $result = '<gift-card-store clientId="' . $client_id . '"><div id="main-loader" class="loader-wrapper"><div class="loader-icon large padded"></div></div></gift-card-store>';
        return $result;
    } else {
        return '';
    }
}

add_shortcode('voucher-store', 'procommerca_gift_card_store_shortcode');


function procommerca_testimonials_shortcode($attributes)
{
    $settings = procommerca_get_settings();
    $client_id = $attributes['client-id'];
    if (empty($client_id)) {
        $client_id = $settings['default_client_id'];

        if (empty($client_id)) {
            $client_id = get_option('procommerca-api-client-id');
        }
    }

    if (!empty($client_id)) {
        $result = '<testimonials clientId="' . $client_id . '"></testimonials>';
        return $result;
    } else {
        return '';
    }
}

add_shortcode('testimonials', 'procommerca_testimonials_shortcode');


function procommerca_side_cart_shortcode($attributes)
{
    $settings = procommerca_get_settings();
    $client_id = $attributes['client-id'];
    if (empty($client_id)) {
        $client_id = $settings['default_client_id'];

        if (empty($client_id)) {
            $client_id = get_option('procommerca-api-client-id');
        }
    }

    if (!empty($client_id)) {
        $result = '<side-cart clientId="' . $client_id . '"></side-cart>';
        return $result;
    } else {
        return '';
    }
}

add_shortcode('side-cart', 'procommerca_side_cart_shortcode');
