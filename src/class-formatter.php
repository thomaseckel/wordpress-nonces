<?php

namespace Eckel\Nonces;

/**
 *
 * @author thomas
 *        
 */
class Formatter {

	public $environment;

	/**
	 * Escaping for HTML attributes.
	 *
	 * @since 2.8.0
	 *       
	 * @param string $text
	 * @return string
	 */
	public function esc_attr( $text ) {

		$safe_text = $this->wp_check_invalid_utf8( $text );
		$safe_text = $this->_wp_specialchars( $safe_text, ENT_QUOTES );
		
		return $safe_text;
	}
	
	/**
	 * Escaping for HTML blocks.
	 *
	 * @since 2.8.0
	 *
	 * @param string $text
	 * @return string
	 */
	function esc_html( $text ) {
		$safe_text = $this->wp_check_invalid_utf8( $text );
		$safe_text = $this->_wp_specialchars( $safe_text, ENT_QUOTES );
		
		return $safe_text;
	}

	/**
	 * Checks for invalid UTF8 in a string.
	 *
	 * @since 2.8.0
	 *       
	 * @staticvar bool $is_utf8
	 * @staticvar bool $utf8_pcre
	 *           
	 * @param string $string The text which is to be checked.
	 * @param bool $strip Optional. Whether to attempt to strip out invalid UTF8. Default is false.
	 * @return string The checked text.
	 */
	public function wp_check_invalid_utf8( $string, $strip = false ) {

		$string = ( string ) $string;
		
		if ( 0 === strlen( $string ) ) {
			return '';
		}
		
		// Store the site charset as a static to avoid multiple calls to get_option()
		static $is_utf8 = null;
		if ( ! isset( $is_utf8 ) ) {
			$is_utf8 = in_array( $this->environment->get_option( 'blog_charset' ), array (
					'utf8',
					'utf-8',
					'UTF8',
					'UTF-8' 
			) );
		}
		if ( ! $is_utf8 ) {
			return $string;
		}
		
		// Check for support for utf8 in the installed PCRE library once and store the result in a static
		static $utf8_pcre = null;
		if ( ! isset( $utf8_pcre ) ) {
			$utf8_pcre = @preg_match( '/^./u', 'a' );
		}
		// We can't demand utf8 in the PCRE installation, so just return the string in those cases
		if ( ! $utf8_pcre ) {
			return $string;
		}
		
		// preg_match fails when it encounters invalid UTF8 in $string
		if ( 1 === @preg_match( '/^./us', $string ) ) {
			return $string;
		}
		
		// Attempt to strip the bad chars if requested (not recommended)
		if ( $strip && function_exists( 'iconv' ) ) {
			return iconv( 'utf-8', 'utf-8', $string );
		}
		
		return '';
	}

	/**
	 * Converts a number of special characters into their HTML entities.
	 *
	 * Specifically deals with: &, <, >, ", and '.
	 *
	 * $quote_style can be set to ENT_COMPAT to encode " to
	 * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
	 *
	 * @since 1.2.2
	 * @access private
	 *        
	 * @staticvar string $_charset
	 *           
	 * @param string $string The text which is to be encoded.
	 * @param int|string $quote_style Optional. Converts double quotes if set to ENT_COMPAT,
	 *        both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES.
	 *        Also compatible with old values; converting single quotes if set to 'single',
	 *        double if set to 'double' or both if otherwise set.
	 *        Default is ENT_NOQUOTES.
	 * @param string $charset Optional. The character encoding of the string. Default is false.
	 * @param bool $double_encode Optional. Whether to encode existing html entities. Default is false.
	 * @return string The encoded text with HTML entities.
	 */
	public function _wp_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ) {

		$string = ( string ) $string;
		
		if ( 0 === strlen( $string ) )
			return '';
			
			// Don't bother if there are no specialchars - saves some processing
		if ( ! preg_match( '/[&<>"\']/', $string ) )
			return $string;
			
			// Account for the previous behaviour of the function when the $quote_style is not an accepted value
		if ( empty( $quote_style ) )
			$quote_style = ENT_NOQUOTES;
		elseif ( ! in_array( $quote_style, array (
				0,
				2,
				3,
				'single',
				'double' 
		), true ) )
			$quote_style = ENT_QUOTES;
			
			// Store the site charset as a static to avoid multiple calls to wp_load_alloptions()
		if ( ! $charset ) {
			static $_charset = null;
			if ( ! isset( $_charset ) ) {
				$alloptions = wp_load_alloptions();
				$_charset = isset( $alloptions[ 'blog_charset' ] ) ? $alloptions[ 'blog_charset' ] : '';
			}
			$charset = $_charset;
		}
		
		if ( in_array( $charset, array (
				'utf8',
				'utf-8',
				'UTF8' 
		) ) )
			$charset = 'UTF-8';
		
		$_quote_style = $quote_style;
		
		if ( $quote_style === 'double' ) {
			$quote_style = ENT_COMPAT;
			$_quote_style = ENT_COMPAT;
		} elseif ( $quote_style === 'single' ) {
			$quote_style = ENT_NOQUOTES;
		}
		
		if ( ! $double_encode ) {
			// Guarantee every &entity; is valid, convert &garbage; into &amp;garbage;
			// This is required for PHP < 5.4.0 because ENT_HTML401 flag is unavailable.
			$string = wp_kses_normalize_entities( $string );
		}
		
		$string = @htmlspecialchars( $string, $quote_style, $charset, $double_encode );
		
		// Back-compat.
		if ( 'single' === $_quote_style )
			$string = str_replace( "'", '&#039;', $string );
		
		return $string;
	}

	/**
	 * Remove slashes from a string or array of strings.
	 *
	 * This should be used to remove slashes from data passed to core API that
	 * expects data to be unslashed.
	 *
	 * @param string|array $value String or array of strings to unslash.
	 * @return string|array Unslashed $value
	 */
	public function wp_unslash( $value ) {

		return $this->stripslashes_deep( $value );
	}
	
	/**
	 * Navigates through an array, object, or scalar, and removes slashes from the values.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value The value to be stripped.
	 * @return mixed Stripped value.
	 */
	public function stripslashes_deep( $value ) {
		return $this->map_deep( $value, 'stripslashes_from_strings_only' );
	}
	
	/**
	 * Maps a function to all non-iterable elements of an array or an object.
	 *
	 * This is similar to `array_walk_recursive()` but acts upon objects too.
	 *
	 * @since 4.4.0
	 *
	 * @param mixed    $value    The array, object, or scalar.
	 * @param callable $callback The function to map onto $value.
	 * @return mixed The value with the callback applied to all non-arrays and non-objects inside it.
	 */
	public function map_deep( $value, $callback ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
				$value[ $index ] = map_deep( $item, $callback );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );
			foreach ( $object_vars as $property_name => $property_value ) {
				$value->$property_name = map_deep( $property_value, $callback );
			}
		} else {
			$value = call_user_func(array($this, $callback) , $value );
		}
	
		return $value;
	}
	
	/**
	 * Callback function for `stripslashes_deep()` which strips slashes from strings.
	 *
	 * @since 4.4.0
	 *
	 * @param mixed $value The array or string to be stripped.
	 * @return mixed $value The stripped value.
	 */
	public function stripslashes_from_strings_only( $value ) {
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
	
	/**
	 * Parses a string into variables to be stored in an array.
	 *
	 * Uses {@link https://secure.php.net/parse_str parse_str()} and stripslashes if
	 * {@link https://secure.php.net/magic_quotes magic_quotes_gpc} is on.
	 *
	 * @since 2.2.1
	 *
	 * @param string $string The string to be parsed.
	 * @param array  $array  Variables will be stored in this array.
	 */
	public function wp_parse_str( $string, &$array ) {
		parse_str( $string, $array );
		if ( get_magic_quotes_gpc() )
			$array = stripslashes_deep( $array );
	}
	
	/**
	 * Navigates through an array, object, or scalar, and encodes the values to be used in a URL.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed $value The array or string to be encoded.
	 * @return mixed $value The encoded value.
	 */
	public function urlencode_deep( $value ) {
		return $this->map_deep( $value, 'urlencode' );
	}
}