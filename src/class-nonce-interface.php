<?php

namespace Eckel\Nonces;

/**
 * Class Nonce_Interface
 *
 * @author Thomas Eckel <thomas.eckel@eckel-software.de>
 * @package Nonce
 */
interface Nonce_Interface {
	
	/**
	 * Display "Are You Sure" message to confirm the action being taken.
	 *
	 * If the action has the nonce explain message, then it will be displayed
	 * along with the "Are you sure?" message.
	 *
	 * @param string $action
	 *        	The nonce action.
	 */
	public static function wp_nonce_ays( $action );
	
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
	 * @since 2.0.4
	 *       
	 * @param int|string $action
	 *        	Optional. Action name. Default -1.
	 * @param string $name
	 *        	Optional. Nonce name. Default '_wpnonce'.
	 * @param bool $referer
	 *        	Optional. Whether to set the referer field for validation. Default true.
	 * @param bool $echo
	 *        	Optional. Whether to display or return hidden form field. Default true.
	 * @return string Nonce field HTML markup.
	 */
	public static function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $echo = true );
	
	/**
	 * Retrieve URL with nonce added to URL query.
	 *
	 * @param string $actionurl
	 *        	URL to add nonce action.
	 * @param int|string $action
	 *        	Optional. Nonce action name. Default -1.
	 * @param string $name
	 *        	Optional. Nonce name. Default '_wpnonce'.
	 * @return string Escaped URL with nonce action added.
	 */
	public static function wp_nonce_url( $actionurl, $action = -1, $name = '_wpnonce' );
	
	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 * The user is given an amount of time to use the token, so therefore, since the
	 * UID and $action remain the same, the independent variable is the time.
	 *
	 * @param string $nonce
	 *        	Nonce that was used in the form to verify
	 * @param string|int $action
	 *        	Should give context to what is taking place and be the same when nonce was created.
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between
	 *         0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
	 */
	public static function wp_verify_nonce( $nonce, $action = -1 );
	
	/**
	 * Creates a cryptographic token tied to a specific action, user, user session,
	 * and window of time.
	 *
	 * @param string|int $action
	 *        	Scalar value to add context to the nonce.
	 * @return string The token.
	 */
	public static function wp_create_nonce( $action = -1 );
	
	/**
	 * Makes sure that a user was referred from another admin page.
	 *
	 * To avoid security exploits.
	 *
	 * @param int|string $action
	 *        	Action nonce.
	 * @param string $query_arg
	 *        	Optional. Key to check for nonce in `$_REQUEST` (since 2.5).
	 *        	Default '_wpnonce'.
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between
	 *         0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
	 */
	public static function check_admin_referer( $action = -1, $query_arg = '_wpnonce' );
	
	/**
	 * Verifies the AJAX request to prevent processing requests external of the blog.
	 *
	 * @param int|string $action
	 *        	Action nonce.
	 * @param false|string $query_arg
	 *        	Optional. Key to check for the nonce in `$_REQUEST` (since 2.5). If false,
	 *        	`$_REQUEST` values will be evaluated for '_ajax_nonce', and '_wpnonce'
	 *        	(in that order). Default false.
	 * @param bool $die
	 *        	Optional. Whether to die early when the nonce cannot be verified.
	 *        	Default true.
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between
	 *         0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
	 */
	public static function check_ajax_referer( $action = -1, $query_arg = false, $die = true );
	
	/**
	 * Retrieve or display referer hidden field for forms.
	 *
	 * The referer link is the current Request URI from the server super global. The
	 * input name is '_wp_http_referer', in case you wanted to check manually.
	 *
	 * @param bool $echo
	 *        	Optional. Whether to echo or return the referer field. Default true.
	 * @return string Referer field HTML markup.
	 */
	public static function wp_referer_field( $echo = true );
}