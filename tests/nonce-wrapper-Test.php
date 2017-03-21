<?php

namespace Eckel\Nonces\Test;

require_once 'bootstrap.php';

require_once ( 'src/class-nonce-interface.php' );
require_once ( 'src/class-nonce-wrapper.php' );

use Wordpress_Tests\WP_UnitTestCase;
use Eckel\Nonces\Nonce_Wrapper;

class Nonce_Wrapper_Test extends WP_UnitTestCase {
	
	/**
	 * Simple Test
	 */
	public function testFooBar() {
		$this->assertNotNull ( "notNull" );
	}
	
	/**
	 * Test for wp_create_nonce
	 *
	 * @return void
	 */
	public function testWpCreateNonce() {
		$nonce = Nonce_Wrapper::wp_create_nonce ();
		$this->assertNotNull ( $nonce );
	}
}