<?php

function procommerca_get_api_base_url()
{
    $settings = procommerca_get_settings();
    $test_mode = $settings['test_mode'];

    if ($test_mode) {
        return 'https://booking.funbutler.dev';
    } else {
        return 'https://booking.funbutler.com';
    }
}


function procommerca_api($url, $method, $data = array(), $api_token = null)
{


    if (!isset($api_token)){

        $settings = procommerca_get_settings();

        if (count($settings['clients']) > 0) {
            foreach ($settings['clients'] as $client_id => $client) {
                $api_token = $client['api_token'];
                break;
            } 
            
        }else{
            $api_token = get_option('procommerca-api-token');
        }
    }
       


    $body = wp_remote_retrieve_body(wp_remote_request(
        procommerca_get_api_base_url() . $url,
        array(
            'method' => $method,
            'body' => json_encode($data),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Api-Token' => $api_token
            ),
            'cookies' => array()
        )
    ));

    return json_decode($body, true);
}
