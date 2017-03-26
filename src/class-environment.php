<?php

namespace Eckel\Nonces;

class Environment {
	
	public $salt = 'eZyT)-Naw]F8CwA*VaW#q*|.)g@o}||wf~@C-YSt}(dh_r6EbI#A,y|nU2{B#JBW';
	
	public $options = array('blog_charset' => 'utf8');
	
	public $user_id = '123456';
	
	/**
	 * set user id
	 * 
	 * @param unknown $user_id
	 */
	public function set_user_id($user_id){
		$this->user_id = $user_id;
	}
	
	/**
	 * get current user
	 * 
	 * @return StdClass
	 */
	public function wp_get_current_user(){
		$user = array('ID' => $this->user_id);
		return (object) $user;
	}
	
	/**
	 * get session token
	 * 
	 * @return string
	 */
	public function wp_get_session_token(){
		return 'dlakjfewoij';
	}
	
	/**
	 * get option
	 * 
	 * @param string $option
	 * @return string
	 */
	public function get_option($option){
		return $this->options[$option];
	}
	
	/**
	 * get request url
	 * 
	 * @return string
	 */
	public function get_request_uri(){
		return '/request/uri';
	}
	
}