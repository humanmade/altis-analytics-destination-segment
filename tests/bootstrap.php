<?php /* phpcs:disable */

if ( ! function_exists( 'do_action' ) ) {
	function do_action() {};
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $filter, $value ) {
		return $value;
	};
}
