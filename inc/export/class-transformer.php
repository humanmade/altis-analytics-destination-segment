<?php
/**
 * Map-based data transformation.
 *
 * @package altis-analytics-integration-segment
 */

namespace Altis\Analytics\Integration\Segment\Export;

/**
 * Map-based data transformation.
 */
class Transformer {

	/**
	 * Map between source and destination formats.
	 *
	 * @var array
	 */
	public $map = [];

	/**
	 * Construct a new instance.
	 *
	 * @param array $map Map between source and destination formats.
	 */
	function __construct( array $map ) {
		$this->map = $map;
	}

	/**
	 * Transform source data to expected format based on provided map.
	 *
	 * @param array|object $source  Source JSON object.
	 * @param array        $map     Transformation map.
	 *
	 * @return array
	 */
	function transform( $source, array $map = null ) : array {
		$destination = [];
		if ( ! isset( $map ) ) {
			$map = $this->map;
		}

		foreach ( $map as $key => $branch ) {

			if ( empty( $branch ) ) { // No map branch, skip this field.
				continue;

			} elseif ( is_numeric( $key ) ) { // No key, merge the full array.
				$value = $this->fetch( $source, $branch );
				if ( empty( $value ) ) {
					continue;
				}

				$destination = array_merge( $destination, $value );
				continue;

			} elseif ( is_array( $branch ) ) { // Nested array, map branch recursively.
				$value = $this->transform( $source, $branch );

			} elseif ( is_string( $branch ) ) { // Textual path, fetch single value.
				$value = $this->fetch( $source, $branch );
			}

			// Add to the destination object, unless value is null (not found).
			if ( isset( $value ) && ! ( is_array( $value ) && empty( $value ) ) ) {
				$destination[ $key ] = $value;
			}
		}

		return $destination;
	}

	/**
	 * Return a single value, or multiple values, from an object, using a map.
	 *
	 * @param object $object JSON object.
	 * @param string $path   Path to the value to return, eg:
	 *                       'endpoint.Attribute.X' - Returns a single value or associative array
	 *                       'source_timestamp|format_date' - Returns a value, transformed by a callback, here `format_date`
	 *
	 * @return mixed
	 */
	function fetch( $object, string $path ) {
		$value = $object;

		if ( false !== strpos( $path, '|' ) ) {
			list( $path, $callback ) = explode( '|', $path );
		}

		// Allow returning static strings without parsing, eg: 'Value|static'.
		if ( isset( $callback ) && $callback === 'static' ) {
			return $path;
		}

		$path = explode( '.', $path );

		foreach ( $path as $segment ) {
			if ( ! isset( $value[ $segment ] ) ) {
				return null;
			}
			$value = $value[ $segment ];
		}

		if ( isset( $callback ) ) {
			$value = $callback( $value );
		}

		return $value;
	}
}
