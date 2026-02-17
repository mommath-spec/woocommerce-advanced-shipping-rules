<?php

namespace WASR\Admin;

use WASR\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	/** @var Logger */
	private $logger;

	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	public function register() : void {
		add_filter( 'woocommerce_get_settings_pages', [ $this, 'add_settings_page' ] );
	}

	public function add_settings_page( $settings ) {
		$settings[] = new Settings_Page( $this->logger );
		return $settings;
	}
}

class Settings_Page extends \WC_Settings_Page {
	/** @var Logger */
	private $logger;

	public function __construct( Logger $logger ) {
		$this->id    = 'wasr';
		$this->label = __( 'Advanced Shipping Rules', WASR_TEXT_DOMAIN );
		$this->logger = $logger;

		add_filter( 'woocommerce_settings_tabs_array', [ $this, 'add_settings_page' ], 20 );
		add_action( 'woocommerce_settings_' . $this->id, [ $this, 'output' ] );
		add_action( 'woocommerce_settings_save_' . $this->id, [ $this, 'save' ] );
	}

	public function get_settings() {
		return [
			[ 'title' => __( 'Advanced Shipping Rules', WASR_TEXT_DOMAIN ), 'type' => 'title', 'id' => 'wasr_options' ],
			[
				'title'    => __( 'Free shipping threshold', WASR_TEXT_DOMAIN ),
				'id'       => 'wasr_free_shipping_threshold',
				'default'  => '100',
				'type'     => 'price',
				'desc_tip' => __( 'Orders above this total ship free.', WASR_TEXT_DOMAIN ),
			],
			[
				'title'    => __( 'Fragile tag slug', WASR_TEXT_DOMAIN ),
				'id'       => 'wasr_fragile_tag',
				'default'  => 'fragile',
				'type'     => 'text',
				'desc_tip' => __( 'Apply surcharge when cart contains products with this tag.', WASR_TEXT_DOMAIN ),
			],
			[
				'title'    => __( 'Fragile surcharge', WASR_TEXT_DOMAIN ),
				'id'       => 'wasr_fragile_surcharge',
				'default'  => '3',
				'type'     => 'price',
				'desc_tip' => __( 'Extra cost added for fragile items.', WASR_TEXT_DOMAIN ),
			],
			[ 'type' => 'sectionend', 'id' => 'wasr_options' ],
		];
	}

	public function save() {
		$settings = $this->get_settings();
		\WC_Admin_Settings::save_fields( $settings );
		$this->logger->info( 'WASR settings saved.' );
	}
}
