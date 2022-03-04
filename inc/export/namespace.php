<?php
/**
 * Altis Analytics Integration for Segment, Data export integration.
 *
 * @package altis-analytics-integration-segment
 */

namespace Altis\Analytics\Integration\Segment\Export;

use Requests;

const SEGMENT_API_BATCH_URL = 'https://api.segment.io/v1/batch';

/**
 * Bootstrap the functionality.
 *
 * @return void
 */
function setup() : void {
	if ( ! get_segment_api_write_key() ) {
		return;
	}

	add_action( 'altis.analytics.export.data.process', __NAMESPACE__ . '\process' );
}

/**
 * Returns the Segment API write key.
 *
 * @return string
 */
function get_segment_api_write_key() : string {
	/**
	 * Filters the Write API key for Segment integration.
	 *
	 * @param string Segment Write API key.
	 */
	return apply_filters( 'altis.analytic.segment.api_write_key', SEGMENT_API_WRITE_KEY );
}

/**
 * Process the raw analytics data coming from Altis Analytics cron job.
 *
 * @param string $data Raw NDJSON analytics data.
 *
 * @return void
 */
function process( string $data ) : void {
	// Format events to expected target format.
	do_action( 'altis.analytics.segment.before_format', $data );
	$events = format( $data );
	do_action( 'altis.analytics.segment.after_format', $data );

	// Prepare data and organize into batches.
	$batches = prepare( $events );

	// Send the data to the destination.
	do_action( 'altis.analytics.segment.before_send', $events );
	$results = send( $batches );
	array_map( __NAMESPACE__ . '\log_response', $results );
	do_action( 'altis.analytics.segment.after_send', $results, $batches );
}

/**
 * Format raw events data into expected format by Segment service.
 *
 * @param string $data Raw NDJSON analytics data.
 *
 * @return array       Array of formatted objects.
 */
function format( string $data ) {
	// Convert the data into JSON objects.
	$events = array_map( function( $line ) {
		return json_decode( $line, true );
	}, explode( "\n", $data ) );

	// Preload the transformer.
	$transformer = new SegmentTransformer;

	// Format the data to the expected format by the destination API.
	$formatted = array_map( [ $transformer, 'transform' ], array_values( array_filter( $events ) ) );

	/**
	 * Filter the formatted events data before sending to Segment.
	 *
	 * Note: Any data dedup can be done here.
	 *
	 * @param array Array of array of JSON objects.
	 */
	return apply_filters( 'altis.analytics.segment.formatted_data', $formatted );
}

/**
 * Prepares Segment-formatted data into batches of JSON-formatted strings, each with a maximum of 500KB.
 *
 * The function intentionally works by stitching JSON string together so we can calculate the limit of each payload.
 *
 * @param array $data Array of arrays of Segment-formatted method calls.
 *
 * @return array      Array of JSON-formatted strings to serve as the POST body for Segment batch requests.
 */
function prepare( array $data ) : array {
	// JSON-string template, where we'll add all the method calls.
	$template = '{"batch":[%s]}';

	// Batch limit should be maximu of 500KB, so we subtract the template length.
	$batch_limit = 500 * 1024 - strlen( $template ); // 500kb

	// Stringify and chunk our data.
	$batches = array_reduce( $data, function( $batches, $event ) use ( $batch_limit ) {
		$current_index = count( $batches ) - 1;
		$current_batch_size = strlen( $batches[ $current_index ] ?? '' );

		// Merge JSON-formatted calls into a single string.
		$event_calls = implode( ',', array_map( 'json_encode', $event ) );
		if ( empty( $event_calls ) ) {
			return $batches;
		}

		// Will adding this exceed our batch limit ? Create a new batch.
		if ( ! $current_batch_size || ( $current_batch_size + strlen( $event_calls ) + 1 ) > $batch_limit ) {
			$batches[] = $event_calls;
		} else {
			$batches[ $current_index ] .= ',' . $event_calls;
		}

		return $batches;
	}, [] );

	foreach ( $batches as $index => $batch ) {
		$batches[ $index ] = sprintf( $template, $batch );
	}

	return $batches;
}

/**
 * Sends batches to Segment in parallel.
 *
 * @param array $batches Batches of Segment method calls in JSON-formatted strings.
 *
 * @return array         Results of requests, array of Requests_Response or Requests_Exception.
 */
function send( array $batches ) : array {
	// Prepare the requests.
	foreach ( $batches as $batch ) {
		$requests[] = [ 'data' => $batch ];
	}

	// Fire all requests in parallel!
	$results = Requests::request_multiple( $requests, [
		'type' => 'POST',
		'url' => SEGMENT_API_BATCH_URL,
		'headers' => [
			'content-type' => 'application/json',
		],

		// Authentication is done using the WRITE key as a username and an empty string as a password.
		'auth' => [
			get_segment_api_write_key(),
			'',
		],
	] );

	return $results;
}

/**
 * Log request failures.
 *
 * @param Requests_Response|Requests_Exception $response Result of a request.
 *
 * @return void
 */
function log_response( $response ) : void {
	if ( is_a( $response, 'Requests_Exception' ) ) {
		trigger_error( "Error delivering payload to Segment, got exception: $response->getMessage()", E_USER_ERROR );

		do_action( 'altis.analytics.segment.request_failure', $response );
		return;
	} elseif ( $response->status_code === 200 ) {
		do_action( 'altis.analytics.segment.request_success', $response );
	} elseif ( $response->status_code === 400 ) {
		trigger_error( 'Error delivering payload to Segment, request too large / JSON is invalid.', E_USER_ERROR );

		do_action( 'altis.analytics.segment.request_failure', $response );
	} else {
		trigger_error( "Error delivering payload to Segment, got [$response->status_code] [$response->body].", E_USER_ERROR );

		do_action( 'altis.analytics.segment.request_failure', $response );
	}
}

/**
 * Register a new Segment group map.
 *
 * Shorthand to the `altis.analytics.segment.groups` filter.
 *
 * @example register_segment_group_map( [ 'groupId' => 'endpoint.Attributes.AudienceId' ] );
 * @example register_segment_group_map( [
 * 								'groupId' => 'endpoint.Attributes.AudienceId', [
 * 								'traits' => [ 'country' => 'endpoint.Attributes.Country' ] ]
* 							] );
 *
 * @param array $map Transformer map.
 *
 * @return void
 */
function register_segment_group_map( array $map ) : void {
	add_filter( 'altis.analytics.segment.groups', function( $groups ) use ( $map ) {
		return $groups[] = $map;
	} );
}

/**
 * Register a new Segment group map based on a field.
 *
 * Shorthand to register_segment_group_map() function and `altis.analytics.segment.groups` filter.
 *
 * @example register_segment_group_map( 'endpoint.Attributes.AudienceId' );
 * @example register_segment_group_map( 'endpoint.Attributes.AudienceId', [
 * 										'country' => 'endpoint.Attributes.Country'
* 									] );
 *
 * @param array $map Transformer map.
 *
 * @return void
 */
function register_segment_group_field( string $field, array $traits = [] ) : void {
	$map = [
		'groupId' => $field,
		'traits' => $traits,
	];

	register_segment_group_map( $map );
}
