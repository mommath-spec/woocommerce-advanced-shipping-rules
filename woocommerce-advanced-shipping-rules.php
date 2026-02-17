<?php
/**
 * Plugin Name: WooCommerce Advanced Shipping Rules
 * Plugin URI: https://github.com/krzysztofslonina/woocommerce-advanced-shipping-rules
 * Description: Adds an "Advanced Rules Shipping" method with weight, cart value, zone, and tag-based pricing.
 * Version: 0.1.0
 * Author: Krzysztof Slonina
 * Author URI: https://github.com/krzysztofslonina
 * License: GPL-2.0-or-later
 * Text Domain: woocommerce-advanced-shipping-rules
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

if ( ! class_exists( '\\WASR\\Plugin' ) ) {
	require_once __DIR__ . '/includes/class-wasr-plugin.php';
}

WASR\Plugin::instance();
