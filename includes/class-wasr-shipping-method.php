<?php

namespace WASR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shipping_Method extends \WC_Shipping_Method {

	public function __construct() {
		$this->id                 = 'wasr_advanced';
		$this->method_title       = __( 'Advanced Rules Shipping', WASR_TEXT_DOMAIN );
		$this->method_description = __( 'Shipping with weight/value tiers and product tag surcharges.', WASR_TEXT_DOMAIN );
		$this->enabled            = 'yes';
		$this->title              = __( 'Advanced Rules Shipping', WASR_TEXT_DOMAIN );

		$this->init();
	}

	private function init() : void {
		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields() : void {
		$this->form_fields = [
			'enabled' => [
				'title'   => __( 'Enable', WASR_TEXT_DOMAIN ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Advanced Rules Shipping', WASR_TEXT_DOMAIN ),
				'default' => 'yes',
			],
			'title' => [
				'title'       => __( 'Method title', WASR_TEXT_DOMAIN ),
				'type'        => 'text',
				'description' => __( 'Displayed to customers at checkout.', WASR_TEXT_DOMAIN ),
				'default'     => __( 'Advanced Rules Shipping', WASR_TEXT_DOMAIN ),
			],
			'base_rate' => [
				'title'       => __( 'Base rate', WASR_TEXT_DOMAIN ),
				'type'        => 'price',
				'description' => __( 'Base cost applied to every shipment.', WASR_TEXT_DOMAIN ),
				'default'     => '5',
			],
			'weight_tier_1' => [
				'title'       => __( 'Weight tier 1 (kg)', WASR_TEXT_DOMAIN ),
				'type'        => 'number',
				'description' => __( 'Max weight for tier 1.', WASR_TEXT_DOMAIN ),
				'default'     => '1',
			],
			'weight_tier_1_rate' => [
				'title'       => __( 'Tier 1 rate', WASR_TEXT_DOMAIN ),
				'type'        => 'price',
				'description' => __( 'Cost when weight under tier 1.', WASR_TEXT_DOMAIN ),
				'default'     => '0',
			],
			'weight_tier_2' => [
				'title'       => __( 'Weight tier 2 (kg)', WASR_TEXT_DOMAIN ),
				'type'        => 'number',
				'description' => __( 'Max weight for tier 2.', WASR_TEXT_DOMAIN ),
				'default'     => '5',
			],
			'weight_tier_2_rate' => [
				'title'       => __( 'Tier 2 rate', WASR_TEXT_DOMAIN ),
				'type'        => 'price',
				'description' => __( 'Cost when weight under tier 2.', WASR_TEXT_DOMAIN ),
				'default'     => '5',
			],
			'free_shipping_threshold' => [
				'title'       => __( 'Free shipping threshold', WASR_TEXT_DOMAIN ),
				'type'        => 'price',
				'description' => __( 'Cart total above this value ships free.', WASR_TEXT_DOMAIN ),
				'default'     => '100',
			],
			'fragile_tag' => [
				'title'       => __( 'Fragile tag slug', WASR_TEXT_DOMAIN ),
				'type'        => 'text',
				'description' => __( 'Products with this tag incur a surcharge.', WASR_TEXT_DOMAIN ),
				'default'     => 'fragile',
			],
			'fragile_surcharge' => [
				'title'       => __( 'Fragile surcharge', WASR_TEXT_DOMAIN ),
				'type'        => 'price',
				'description' => __( 'Extra fee when cart contains fragile items.', WASR_TEXT_DOMAIN ),
				'default'     => '3',
			],
		];
	}

	/**
	 * Calculate shipping rate based on cart weight/value and tag surcharges.
	 *
	 * @param array $package Shipping package.
	 */
	public function calculate_shipping( $package = [] ) {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$cart_weight = (float) WC()->cart->get_cart_contents_weight();
		$cart_total  = (float) WC()->cart->get_displayed_subtotal();
		$base_rate   = (float) $this->get_option( 'base_rate', 5 );
		$free_over   = (float) $this->get_option( 'free_shipping_threshold', 0 );
		$fragile_tag = sanitize_title( $this->get_option( 'fragile_tag', 'fragile' ) );
		$fragile_fee = (float) $this->get_option( 'fragile_surcharge', 0 );

		$cost = $base_rate + $this->get_weight_rate( $cart_weight );

		if ( $this->cart_has_tag( $fragile_tag ) ) {
			$cost += $fragile_fee;
		}

		if ( $free_over > 0 && $cart_total >= $free_over ) {
			$cost = 0;
		}

		$rate = [
			'id'    => $this->id,
			'label' => $this->title,
			'cost'  => max( 0, $cost ),
		];

		$this->add_rate( $rate );
	}

	private function get_weight_rate( float $weight ) : float {
		$tier1      = (float) $this->get_option( 'weight_tier_1', 1 );
		$tier1_rate = (float) $this->get_option( 'weight_tier_1_rate', 0 );
		$tier2      = (float) $this->get_option( 'weight_tier_2', 5 );
		$tier2_rate = (float) $this->get_option( 'weight_tier_2_rate', 5 );

		if ( $weight <= $tier1 ) {
			return $tier1_rate;
		}

		if ( $weight <= $tier2 ) {
			return $tier2_rate;
		}

		return $tier2_rate + ( $weight - $tier2 ) * 1.5; // Simple per-kg surcharge beyond tier 2.
	}

	private function cart_has_tag( string $tag_slug ) : bool {
		if ( empty( WC()->cart ) ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'];
			if ( has_term( $tag_slug, 'product_tag', $product->get_id() ) ) {
				return true;
			}
		}

		return false;
	}
}
