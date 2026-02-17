<?php

namespace WASR;

use WASR\Admin\Settings;
use WASR\Shipping_Method;
use WASR\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	/** @var Plugin|null */
	private static $instance = null;

	/** @var Logger */
	private $logger;

	/**
	 * Singleton instance.
	 */
	public static function instance() : Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->define_constants();
		$this->bootstrap();
	}

	private function define_constants() : void {
		$plugin_file = dirname( __DIR__ ) . '/woocommerce-advanced-shipping-rules.php';
		define( 'WASR_VERSION', '0.1.0' );
		define( 'WASR_PLUGIN_FILE', $plugin_file );
		define( 'WASR_PLUGIN_DIR', plugin_dir_path( $plugin_file ) );
		define( 'WASR_PLUGIN_URL', plugin_dir_url( $plugin_file ) );
		define( 'WASR_TEXT_DOMAIN', 'woocommerce-advanced-shipping-rules' );
	}

	private function bootstrap() : void {
		require_once __DIR__ . '/class-wasr-logger.php';
		require_once __DIR__ . '/class-wasr-settings.php';
		$this->logger = new Logger();

		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		add_action( 'woocommerce_shipping_init', [ $this, 'include_shipping_method' ] );
		add_filter( 'woocommerce_shipping_methods', [ $this, 'register_shipping_method' ] );

		// Admin settings page.
		if ( is_admin() ) {
			( new Settings( $this->logger ) )->register();
		}
	}

	public function load_textdomain() : void {
		load_plugin_textdomain( WASR_TEXT_DOMAIN, false, dirname( plugin_basename( WASR_PLUGIN_FILE ) ) . '/languages' );
	}

	public function include_shipping_method() : void {
		if ( class_exists( '\\WC_Shipping_Method' ) ) {
			require_once __DIR__ . '/class-wasr-shipping-method.php';
		}
	}

	/**
	 * @param array $methods
	 * @return array
	 */
	public function register_shipping_method( $methods ) {
		$methods['wasr_advanced'] = Shipping_Method::class;
		return $methods;
	}

	public function logger() : Logger {
		return $this->logger;
	}
}
