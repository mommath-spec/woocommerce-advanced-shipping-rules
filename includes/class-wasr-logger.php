<?php

namespace WASR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Logger {
	/** @var \WC_Logger */
	private $logger;

	public function __construct() {
		$this->logger = wc_get_logger();
	}

	public function info( string $message, array $context = [] ) : void {
		$this->logger->info( $message, $this->context( $context ) );
	}

	public function warning( string $message, array $context = [] ) : void {
		$this->logger->warning( $message, $this->context( $context ) );
	}

	private function context( array $context ) : array {
		$context['source'] = 'wasr';
		return $context;
	}
}
