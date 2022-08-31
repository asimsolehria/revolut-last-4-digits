<?php

/**
 * Plugin Name: Revolut last 4 digits
 * Description: Show last four digits of card in email
 * Version: 0.0.1
 * Author: Vladislav Khomenko
 * Author URI: https://vlad-homenko.name
 */

function vhShowLast4Digits($value, WC_Order $order) {
    
    if($order->get_payment_method() == 'revolut_cc') {
        $value = 'Via card ';
        $cardBrand = $order->get_meta('card_brand');
        if(!empty($cardBrand)) {
            $value = str_replace('card', "card $cardBrand", $value);
        }
        $last4Digits = $order->get_meta('last_4_digits');
        if(!empty($last4Digits)) {
            $value .= "ending in ***$last4Digits";
        }
    }
    
    
    return $value;
}

add_filter('woocommerce_order_get_payment_method_title', 'vhShowLast4Digits', 80, 2);

function vhSaveLast4Digits($order_id, $transaction_id) {
    $wcOrder = wc_get_order($order_id);
    if($wcOrder->get_payment_method() == 'revolut_cc') {
        $api_settings = revolut_wc()->api_settings;
        $api_client = new WC_Revolut_API_Client( $api_settings );
        $revolutOrder = $api_client->get("/orders/$transaction_id");
        $last4Digits = $revolutOrder['payments'][0]['payment_method']['card']['card_last_four'];
        $wcOrder->update_meta_data('last_4_digits', $last4Digits);
        $cardBrand = $revolutOrder['payments'][0]['payment_method']['card']['card_brand'];
        $wcOrder->update_meta_data('card_brand', $cardBrand);
        $wcOrder->save();
    }
    
}

add_action('woocommerce_payment_complete', 'vhSaveLast4Digits', 80, 2);