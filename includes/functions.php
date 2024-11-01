<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Check if cookies are accepted.
 *
 * @return boolean Whether cookies are accepted
 */
if ( ! function_exists( 'ultimate_notice_cookies_accepted' ) ) {
	function ultimate_notice_cookies_accepted() {
		return (bool) Ultimate_Notice::cookies_accepted();
	}
}

/**
 * Check if cookies are set.
 *
 * @return boolean Whether cookies are set
 */
if ( ! function_exists( 'ultimate_notice_cookies_set' ) ) {
	function ultimate_notice_cookies_set() {
		return (bool) Ultimate_Notice::cookies_set();
	}
}