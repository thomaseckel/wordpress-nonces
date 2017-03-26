<?php

namespace Eckel\Nonces;

require_once 'src/class-formatter.php';

use Eckel\Nonces\Environment;
use Eckel\Nonces\Formatter;

/**
 * Class Nonce
 *
 * implements the Nonce
 *
 * @author Thomas Eckel <thomas.eckel@eckel-software.de>
 * @package Nonce
 */
class Nonce {

	private $environment;

	private $nonce_lifetime = 86400;

	private $formatter;

	/**
	 * constructor
	 */
	function __construct() {

		$this->formatter = new Formatter();
	}

	/**
	 * Init
	 *
	 * @param environment object
	 */
	public function set_environment( $param_environment ) {

		$this->environment = $param_environment;
		$this->formatter->environment = $param_environment;
	}

	/**
	 * Sets the nonce lifetime
	 *
	 * @param unknown $lifetime
	 */
	public function set_nonce_lifetime( $lifetime ) {

		$this->nonce_lifetime = $lifetime;
	}

	/**
	 * Creates a cryptographic token tied to a specific action, user, user session,
	 * and window of time.
	 *
	 * @param string|int $action Scalar value to add context to the nonce.
	 * @return string The token.
	 */
	public function wp_create_nonce( $action = -1 ) {

		$user = $this->environment->wp_get_current_user();
		$uid = ( int ) $user->ID;
		
		$token = $this->environment->wp_get_session_token();
		$i = $this->wp_nonce_tick();
		
		return substr( $this->wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), - 12, 10 );
	}

	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 * The user is given an amount of time to use the token, so therefore, since the
	 * UID and $action remain the same, the independent variable is the time.
	 *
	 * @param string $nonce
	 * @param string|int $action
	 * @return boolean|number
	 */
	public function wp_verify_nonce( $nonce, $action = -1 ) {

		$nonce = ( string ) $nonce;
		$user = $this->environment->wp_get_current_user();
		$uid = ( int ) $user->ID;
		
		if ( empty( $nonce ) ) {
			return false;
		}
		
		$token = $this->environment->wp_get_session_token();
		$i = $this->wp_nonce_tick();
		
		// Nonce generated 0-12 hours ago
		$expected = substr( $this->wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), - 12, 10 );
		if ( $this->hash_equals( $expected, $nonce ) ) {
			return 1;
		}
		
		// Nonce generated 12-24 hours ago
		$expected = substr( $this->wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), - 12, 10 );
		if ( $this->hash_equals( $expected, $nonce ) ) {
			return 2;
		}
		
		// Invalid nonce
		return false;
	}

	/**
	 * Retrieve or display nonce hidden field for forms.
	 *
	 * The nonce field is used to validate that the contents of the form came from
	 * the location on the current site and not somewhere else. The nonce does not
	 * offer absolute protection, but should protect against most cases. It is very
	 * important to use nonce field in forms.
	 *
	 * The $action and $name are optional, but if you want to have better security,
	 * it is strongly suggested to set those two parameters. It is easier to just
	 * call the function without any parameters, because validation of the nonce
	 * doesn't require any parameters, but since crackers know what the default is
	 * it won't be difficult for them to find a way around your nonce and cause
	 * damage.
	 *
	 * The input name will be whatever $name value you gave. The input value will be
	 * the nonce creation value.
	 *
	 * @param int|string $action Optional. Action name. Default -1.
	 * @param string $name Optional. Nonce name. Default '_wpnonce'.
	 * @param bool $referer Optional. Whether to set the referer field for validation. Default true.
	 * @param bool $echo Optional. Whether to display or return hidden form field. Default true.
	 * @return string Nonce field HTML markup.
	 */
	public function wp_nonce_field( $action = -1, $name = "_wpnonce", $referer = true, $echo = true ) {

		$name = $this->formatter->esc_attr( $name );
		$nonce_field = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . $this->wp_create_nonce( $action ) . '" />';
		
		if ( $referer )
			$nonce_field .= $this->wp_referer_field( false );
		
		if ( $echo )
			echo $nonce_field;
		
		return $nonce_field;
	}

	/**
	 * Retrieve or display referer hidden field for forms.
	 *
	 * The referer link is the current Request URI from the server super global. The
	 * input name is '_wp_http_referer', in case you wanted to check manually.
	 *
	 * @param bool $echo Optional. Whether to echo or return the referer field. Default true.
	 * @return string Referer field HTML markup.
	 */
	public function wp_referer_field( $echo = true ) {

		$referer_field = '<input type="hidden" name="_wp_http_referer" value="' . $this->formatter->esc_attr( $this->formatter->wp_unslash( $this->environment->get_request_uri() ) ) . '" />';
		
		if ( $echo )
			echo $referer_field;
		
		return $referer_field;
	}

	/**
	 * Retrieve URL with nonce added to URL query.
	 *
	 * @param string $actionurl URL to add nonce action.
	 * @param int|string $action Optional. Nonce action name. Default -1.
	 * @param string $name Optional. Nonce name. Default '_wpnonce'.
	 * @return string Escaped URL with nonce action added.
	 */
	public function wp_nonce_url( $actionurl, $action = -1, $name = '_wpnonce' ) {

		$actionurl = str_replace( '&amp;', '&', $actionurl );
		return $this->formatter->esc_html( $this->add_query_arg( $name, $this->wp_create_nonce( $action ), $actionurl ) );
	}

	/**
	 * Get the time-dependent variable for nonce creation.
	 *
	 * @return number Float value rounded up to the next highest integer.
	 */
	private function wp_nonce_tick() {

		return ceil( time() / ( $this->nonce_lifetime / 2 ) );
	}

	/**
	 * Get hash of given string.
	 *
	 * @param string $data Plain text to hash
	 * @param string $scheme Authentication scheme (auth, secure_auth, logged_in, nonce)
	 * @return string Hash of $data
	 */
	private function wp_hash( $data, $scheme = 'auth' ) {

		$salt = $this->environment->salt;
		return hash_hmac( 'md5', $data, $salt );
	}

	/**
	 * Compat function to mimic hash_hmac().
	 *
	 * @param string $algo Hash algorithm. Accepts 'md5' or 'sha1'.
	 * @param string $data Data to be hashed.
	 * @param string $key Secret key to use for generating the hash.
	 * @param bool $raw_output Optional. Whether to output raw binary data (true),
	 *        or lowercase hexits (false). Default false.
	 * @return string|false The hash in output determined by `$raw_output`. False if `$algo`
	 *         is unknown or invalid.
	 */
	private function hash_hmac( $algo, $data, $key, $raw_output = false ) {

		$packs = array (
				'md5' => 'H32',
				'sha1' => 'H40' 
		);
		
		if ( ! isset( $packs[ $algo ] ) )
			return false;
		
		$pack = $packs[ $algo ];
		
		if ( strlen( $key ) > 64 )
			$key = pack( $pack, $algo( $key ) );
		
		$key = str_pad( $key, 64, chr( 0 ) );
		
		$ipad = ( substr( $key, 0, 64 ) ^ str_repeat( chr( 0x36 ), 64 ) );
		$opad = ( substr( $key, 0, 64 ) ^ str_repeat( chr( 0x5C ), 64 ) );
		
		$hmac = $algo( $opad . pack( $pack, $algo( $ipad . $data ) ) );
		
		if ( $raw_output )
			return pack( $pack, $hmac );
		return $hmac;
	}

	/**
	 * Timing attack safe string comparison
	 *
	 * Compares two strings using the same time whether they're equal or not.
	 *
	 * This function was added in PHP 5.6.
	 *
	 * Note: It can leak the length of a string when arguments of differing length are supplied.
	 *
	 * @param string $a Expected string.
	 * @param string $b Actual, user supplied, string.
	 * @return bool Whether strings are equal.
	 */
	private function hash_equals( $a, $b ) {

		$a_length = strlen( $a );
		if ( $a_length !== strlen( $b ) ) {
			return false;
		}
		$result = 0;
		
		// Do not attempt to "optimize" this.
		for( $i = 0; $i < $a_length; $i ++ ) {
			$result |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
		}
		
		return $result === 0;
	}

	/**
	 * Retrieves a modified URL query string.
	 *
	 * You can rebuild the URL and append query variables to the URL query by using this function.
	 * There are two ways to use this function; either a single key and value, or an associative array.
	 *
	 * Using a single key and value:
	 *
	 * add_query_arg( 'key', 'value', 'http://example.com' );
	 *
	 * Using an associative array:
	 *
	 * add_query_arg( array(
	 * 'key1' => 'value1',
	 * 'key2' => 'value2',
	 * ), 'http://example.com' );
	 *
	 * Omitting the URL from either use results in the current URL being used
	 * (the value of `$_SERVER['REQUEST_URI']`).
	 *
	 * Values are expected to be encoded appropriately with urlencode() or rawurlencode().
	 *
	 * Setting any query variable's value to boolean false removes the key (see remove_query_arg()).
	 *
	 * Important: The return value of add_query_arg() is not escaped by default. Output should be
	 * late-escaped with esc_url() or similar to help prevent vulnerability to cross-site scripting
	 * (XSS) attacks.
	 *
	 * @param string|array $key Either a query variable key, or an associative array of query variables.
	 * @param string $value Optional. Either a query variable value, or a URL to act upon.
	 * @param string $url Optional. A URL to act upon.
	 * @return string New URL query string (unescaped).
	 */
	private function add_query_arg() {

		$args = func_get_args();
		if ( is_array( $args[ 0 ] ) ) {
			if ( count( $args ) < 2 || false === $args[ 1 ] )
				$uri = $this->environment->get_request_uri();
			else
				$uri = $args[ 1 ];
		} else {
			if ( count( $args ) < 3 || false === $args[ 2 ] )
				$uri = $this->environment->get_request_uri();
			else
				$uri = $args[ 2 ];
		}
		
		if ( $frag = strstr( $uri, '#' ) )
			$uri = substr( $uri, 0, - strlen( $frag ) );
		else
			$frag = '';
		
		if ( 0 === stripos( $uri, 'http://' ) ) {
			$protocol = 'http://';
			$uri = substr( $uri, 7 );
		} elseif ( 0 === stripos( $uri, 'https://' ) ) {
			$protocol = 'https://';
			$uri = substr( $uri, 8 );
		} else {
			$protocol = '';
		}
		
		if ( strpos( $uri, '?' ) !== false ) {
			list ( $base, $query ) = explode( '?', $uri, 2 );
			$base .= '?';
		} elseif ( $protocol || strpos( $uri, '=' ) === false ) {
			$base = $uri . '?';
			$query = '';
		} else {
			$base = '';
			$query = $uri;
		}
		
		$this->formatter->wp_parse_str( $query, $qs );
		$qs = $this->formatter->urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string
		if ( is_array( $args[ 0 ] ) ) {
			foreach ( $args[ 0 ] as $k => $v ) {
				$qs[ $k ] = $v;
			}
		} else {
			$qs[ $args[ 0 ] ] = $args[ 1 ];
		}
		
		foreach ( $qs as $k => $v ) {
			if ( $v === false )
				unset( $qs[ $k ] );
		}
		
		$ret = $this->build_query( $qs );
		$ret = trim( $ret, '?' );
		$ret = preg_replace( '#=(&|$)#', '$1', $ret );
		$ret = $protocol . $base . $ret . $frag;
		$ret = rtrim( $ret, '?' );
		return $ret;
	}

	/**
	 * Build URL query based on an associative and, or indexed array.
	 *
	 * This is a convenient function for easily building url queries. It sets the
	 * separator to '&' and uses _http_build_query() function.
	 *
	 * @see _http_build_query() Used to build the query
	 * @link https://secure.php.net/manual/en/function.http-build-query.php for more on what
	 *       http_build_query() does.
	 *      
	 * @param array $data URL-encode key/value pairs.
	 * @return string URL-encoded string.
	 */
	private function build_query( $data ) {

		return $this->_http_build_query( $data, null, '&', '', false );
	}

	/**
	 * From php.net (modified by Mark Jaquith to behave like the native PHP5 function).
	 *
	 * @access private
	 *        
	 * @see https://secure.php.net/manual/en/function.http-build-query.php
	 *
	 * @param array|object $data An array or object of data. Converted to array.
	 * @param string $prefix Optional. Numeric index. If set, start parameter numbering with it.
	 *        Default null.
	 * @param string $sep Optional. Argument separator; defaults to 'arg_separator.output'.
	 *        Default null.
	 * @param string $key Optional. Used to prefix key name. Default empty.
	 * @param bool $urlencode Optional. Whether to use urlencode() in the result. Default true.
	 *       
	 * @return string The query string.
	 */
	private function _http_build_query( $data, $prefix = null, $sep = null, $key = '', $urlencode = true ) {

		$ret = array ();
		
		foreach ( ( array ) $data as $k => $v ) {
			if ( $urlencode )
				$k = urlencode( $k );
			if ( is_int( $k ) && $prefix != null )
				$k = $prefix . $k;
			if ( ! empty( $key ) )
				$k = $key . '%5B' . $k . '%5D';
			if ( $v === null )
				continue;
			elseif ( $v === false )
				$v = '0';
			
			if ( is_array( $v ) || is_object( $v ) )
				array_push( $ret, _http_build_query( $v, '', $sep, $k, $urlencode ) );
			elseif ( $urlencode )
				array_push( $ret, $k . '=' . urlencode( $v ) );
			else
				array_push( $ret, $k . '=' . $v );
		}
		
		if ( null === $sep )
			$sep = ini_get( 'arg_separator.output' );
		
		return implode( $sep, $ret );
	}
}