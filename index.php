<?php
/*
Plugin Name: Sepordeh Payment Gateway for WooCommerce
Version: 2.0.0
Stable tag: 2.0.0
Description:  This plugin adds <a href="https://sepordeh.com">Sepordeh</a> payment method for WooCommerce
Plugin URI: https://wordpress.org/plugins/sepordeh-woocommerce
Author: Sepordeh.com
Author URI: http://www.sepordeh.com/
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
*/

if (!defined('ABSPATH')) exit;

function init_woocommerce_sepordeh_gateway()
{
    add_filter('woocommerce_payment_gateways', 'Woocommerce_Add_sepordeh_Gateway');
    function Woocommerce_Add_sepordeh_Gateway($methods)
    {
        $methods[] = 'WC_SpDeh';
        return $methods;
    }
    require_once(plugin_dir_path(__FILE__) . 'class-woocommerce-gateway-sepordeh.php');
}
add_action('plugins_loaded', 'init_woocommerce_sepordeh_gateway', 0);

function sepordeh_gateway_blocks_compatibility()
{

    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
}

add_action('before_woocommerce_init', 'sepordeh_gateway_blocks_compatibility');
add_action('woocommerce_blocks_loaded', 'sepordeh_register_payment_gateway');

function sepordeh_register_payment_gateway()
{

    if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }

    require_once plugin_dir_path(__FILE__) . 'sepordeh-class-block.php';

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            $payment_method_registry->register(new sepordeh_Gateway_Blocks);
        }
    );
}
