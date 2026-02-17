<?php

use PHPUnit\Framework\TestCase;
use WASR\Shipping_Method;

require_once __DIR__ . '/../includes/class-wasr-shipping-method.php';

class ShippingRulesTest extends TestCase {
	public function test_weight_rate_tier1() {
		$method = $this->methodWithOptions([
			'weight_tier_1'      => 1,
			'weight_tier_1_rate' => 2,
			'weight_tier_2'      => 5,
			'weight_tier_2_rate' => 5,
		]);

		$this->assertSame( 2.0, $this->invokeRate( $method, 0.8 ) );
	}

	public function test_weight_rate_tier2() {
		$method = $this->methodWithOptions([
			'weight_tier_1'      => 1,
			'weight_tier_1_rate' => 2,
			'weight_tier_2'      => 5,
			'weight_tier_2_rate' => 5,
		]);

		$this->assertSame( 5.0, $this->invokeRate( $method, 3.0 ) );
	}

	public function test_weight_rate_above_tier2() {
		$method = $this->methodWithOptions([
			'weight_tier_1'      => 1,
			'weight_tier_1_rate' => 2,
			'weight_tier_2'      => 2,
			'weight_tier_2_rate' => 4,
		]);

		$this->assertSame( 7.0, $this->invokeRate( $method, 4.0 ) ); // 4 + (4-2)*1.5 = 7
	}

	private function methodWithOptions( array $options ) : Shipping_Method {
		$method = $this->getMockBuilder( Shipping_Method::class )
			->onlyMethods( [ 'get_option' ] )
			->getMock();

		$method->method( 'get_option' )
			->willReturnCallback( function ( $key, $default = null ) use ( $options ) {
				return $options[ $key ] ?? $default;
			} );

		return $method;
	}

	private function invokeRate( Shipping_Method $method, float $weight ) : float {
		$reflection = new ReflectionClass( Shipping_Method::class );
		$method_ref = $reflection->getMethod( 'get_weight_rate' );
		$method_ref->setAccessible( true );
		return $method_ref->invoke( $method, $weight );
	}
}
