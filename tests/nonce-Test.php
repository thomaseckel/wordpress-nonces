<?php

namespace Eckel\Nonces\Test;

require_once 'src/class-environment.php';
require_once 'src/class-nonce.php';

use PHPUnit\Framework\TestCase;
use Eckel\Nonces\Environment;
use Eckel\Nonces\Nonce;

class nonce_test extends TestCase {

	/**
	 * Test for wp_create_nonce
	 *
	 * @return void
	 */
	public function test_wp_create_nonce() {

		$environment = new Environment();
		$nonce_object = new Nonce();
		$nonce_object->set_environment( $environment );
		
		$nonce = $nonce_object->wp_create_nonce();
		$this->assertNotNull( $nonce );
	}

	/**
	 * Test for wp_verify_nonce
	 *
	 * @return void
	 */
	public function test_wp_verify_nonce() {

		$environment = new Environment();
		$nonce_object = new Nonce();
		
		// User 9999
		$environment->set_user_id( '9999' );
		$nonce_object->set_environment( $environment );
		
		// Create nonce
		$nonce = $nonce_object->wp_create_nonce( 'action-1' );
		
		// verify with correct user and correct action
		$verify_true = $nonce_object->wp_verify_nonce( $nonce, 'action-1' );
		$this->assertEquals( $verify_true, 1 );
		
		// verify with correct user and wrong action
		$verify_false_action = $nonce_object->wp_verify_nonce( $nonce, 'action-2' );
		// $this->assertEquals ( $verify_false_action, 0 );
		$this->assertFalse( $verify_false_action );
		
		// change to user 7777
		$environment->set_user_id( '7777' );
		
		// verify with wrong user and correct action
		$verify_false_user = $nonce_object->wp_verify_nonce( $nonce, 'action-1' );
		$this->assertFalse( $verify_false_user );
	}

	/**
	 * Test for wp_nonce_field
	 *
	 * @return void
	 */
	public function test_wp_nonce_field() {
		
		// set environment
		$environment = new Environment();
		$nonce_object = new Nonce();
		$nonce_object->set_environment( $environment );
		
		// create nonce for comparison
		$nonce_comparison = $nonce_object->wp_create_nonce( 'action-1' );
		
		// create field for comparison
		$field_comparison = '<input type="hidden" id="_wpnonce" name="_wpnonce" value="' . $nonce_comparison . '" />';
		
		// create field
		$field = $nonce_object->wp_nonce_field( 'action-1', '_wpnonce', false, false );
		
		// Test
		$this->assertEquals( $field, $field_comparison );
		
		// create field
		$referer_field = $nonce_object->wp_nonce_field( 'action-1', '_wpnonce', true, false );
		
		// test
		$this->assertRegExp('/.*'.$nonce_comparison.'.*/', $referer_field);
		
	}
	
	public function test_wp_nonce_url(){
		
		// set environment
		$environment = new Environment();
		$nonce_object = new Nonce();
		$nonce_object->set_environment( $environment );
		
		// create nonce for comparison
		$nonce_comparison = $nonce_object->wp_create_nonce( 'action-1' );
		
		// create nonce url
		$nonce_url = $nonce_object->wp_nonce_url('http://action-1', 'action-1');
		
		// test
		$this->assertRegExp('/.*'.$nonce_comparison.'.*/', $nonce_url);
		
	}
}