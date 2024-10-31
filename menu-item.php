<?php

add_filter('wp_nav_menu_items', 'procommerca_booking_menu_item', 10, 2);
function procommerca_booking_menu_item($items, $args)
{
    /*
    $client_id = get_option('procommerca-api-client-id');
    $enable_side_cart = boolval(get_option('procommerca-enable-side-cart'));
    if (isset($client_id) && !$enable_side_cart) {
        $items .= '<li><booking-cart clientId="' . $client_id . '"></booking-cart></li>';
    }
    */
    return $items;
}
