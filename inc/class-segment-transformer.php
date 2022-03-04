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
class SegmentTransformer extends Transformer {
	/**
	 * Maps between source and destination formats.
	 *
	 * @var array
	 */
	public $maps = [];

	/**
	 * Construct a new instance.
	 *
	 * @param array $map Map between source and destination formats.
	 */
	public function __construct() {
		// Prepare data mapping array in advance.
		$this->maps = [
			'identify' => $this->get_segment_call_type_map( 'identify' ),
			'page' => $this->get_segment_call_type_map( 'page' ),
			'track' => $this->get_segment_call_type_map( 'track' ),
			'groups' => apply_filters( 'altis.analytics.segment.groups', [] ),
		];
	}

	/**
	 * Get transformation map based on call type.
	 *
	 * @param string $type Segment call type.
	 *
	 * @return array       Transformation map.
	 */
	public function get_segment_call_type_map( string $type ) {
		$mapping = [
			'type' => $type . '|static',
			'anonymousId' => 'endpoint.Id',
			'messageId' => 'endpoint.RequestId',
			'timestamp' => 'event_timestamp|' . __CLASS__ . '::format_date',
			'userId' => 'endpoint.User.UserId',

			'context' => [
				'active' => 'endpoint.EndpointStatus',
				'app' => [
					'name' => '',
					'build' => '',
					'version' => 'endpoint.Demographics.AppVersion',
				],
				'campaign' => [
					'name' => 'attributes.qv_utm_campaign',
					'source' => 'attributes.qv_utm_source',
					'medium' => 'attributes.qv_utm_medium',
					'term' => 'attributes.qv_utm_term',
					'content' => 'attributes.qv_utm_content',
				],
				'device' => [
					'id' => '',
					'advertisingId' => '',
					'manufacturer' => 'endpoint.Attributes.DeviceMake.0',
					'model' => 'endpoint.Attributes.DeviceModel.0',
					'name' => '',
					'type' => 'endpoint.Attributes.DeviceType.0',
					'version' => '',
				],
				'ip' => '',
				'library' => [
					'name' => '',
					'version' => '',
				],
				'locale' => 'endpoint.Demographic.Locale',
				'location' => [
					'country' => 'endpoint.Location.Country',
					'city' => 'endpoint.Location.City',
					'latitude' => 'endpoint.Location.Latitude',
					'longitude' => 'endpoint.Location.Longitude',
				],
				'os' => [
					'name' => 'endpoint.Demographic.Platform',
					'version' => 'endpoint.Demographic.PlatformVersion',
				],
				'page' => [
					'path' => '',
					'referrer' => 'attributes.referrer',
					'search' => 'attributes.search',
					'title' => 'attributes.title',
					'url' => 'attributes.url',
					'keywords' => [],
				],
				'referrer' => [
					'type' => '',
					'name' => '',
					'url' => '',
					'link' => '',
				],
				'screen' => [
					'density' => '',
					'height' => '',
					'width' => '',
				],
				'timezone' => 'endpoint.Demographic.Timezone',
				'groupId' => '', // Groups are handled separately via `group` calls.
				'traits' => [
					'sessions' => 'endpoint.Metrics.sessions',
					'pageViews' => 'endpoint.Metrics.pageViews',
					'endpoint.Attributes',
					'endpoint.User.Attributes',
				],
				'userAgent' => '',
			],
		];

		if ( $type === 'identify' ) {
			$type_mapping = array_merge( $mapping, [
				'traits' => $mapping['context']['traits'],
				'context' => null,
			] );
		} elseif ( $type === 'page' ) {
			$type_mapping = array_merge( $mapping, [
				'name' => $mapping['context']['page']['title'],
				'properties' => $mapping['context']['page'],
			] );
		} elseif ( $type === 'track' ) {
			$type_mapping = array_merge( $mapping, [
				'event' => 'event_type',
				'properties' => [
					'endpoint.Attributes', // TODO: This doesn't seem right, can it be more targetted ?
				],
			] );
		} elseif ( $type === 'group' ) {
			$type_mapping = array_merge( $mapping, [
				'context' => null,
				'messageId' => null,
			] );
		}

		/**
		 * Filter the mapping  tree.
		 *
		 * @param array  Mapping tree between Segment object and Altis Analytics event object.
		 * @param string Event type.
		 */
		$type_mapping = apply_filters( 'altis.analytics.segment.mapping', $type_mapping, $type );

		return $type_mapping;
	}

	/**
	 * Get event main map based on raw event type.
	 *
	 * @param array $event Raw Altis Analytics source event.
	 *
	 * @return array       Transformation map.
	 */
	public function get_main_event_map( array $event ) : array {
		$type = $event['event_type'] === 'pageView' ? 'page' : 'track';

		return $this->maps[ $type ];
	}

	/**
	 * Returns the transformed call(s) from raw source event.
	 *
	 * Iterates over defined maps if no map is passed explicitly, or proxies to parent::transform() if one is passed.
	 *
	 * @param array $source Raw Altis Analytics source event.
	 * @param array $map    Transformation map.
	 *
	 * @return array		Returns an array of call arrays if no map is specified, otherwise the single mapped call array.
	 */
	public function transform( $source, array $map = null ) : array {
		// If no map is passed, execute the three methods, identify, group, and track for the source event.
		if ( ! isset( $map ) ) {
			return [
				$this->identify( $source ),
				...$this->groups( $source ),
				$this->track( $source ),
			];
		}

		return parent::transform( $source, $map );
	}

	/**
	 * Returns the main event call associated with the raw source event.
	 *
	 * @see https://segment.com/docs/connections/sources/catalog/libraries/server/http-api/#track
	 * @see https://segment.com/docs/connections/sources/catalog/libraries/server/http-api/#page
	 *
	 * @param array $source Raw Altis Analytics source event.
	 *
	 * @return array
	 */
	public function track( array $source ) : array {
		// Get target map for this event.
		$map = $this->get_main_event_map( $source );
		return $this->transform( $source, $map );
	}

	/**
	 * Returns the `identify` call associated with the raw source event.
	 *
	 * @see https://segment.com/docs/connections/sources/catalog/libraries/server/http-api/#identify
	 *
	 * @todo Try to deduplicate data by keeping a log of userId/anonymousId pairs.
	 *
	 * @param array $source Raw Altis Analytics source event.
	 *
	 * @return array
	 */
	public function identify( array $source ) : array {
		return $this->transform( $source, $this->maps['identify'] );
	}

	/**
	 * Returns the `group` calls associated with the raw source event.
	 *
	 * Uses group maps registered through the `altis.analytics.segment.groups` filter.
	 *
	 * @see https://segment.com/docs/connections/sources/catalog/libraries/server/http-api/#group
	 *
	 * @todo Try to deduplicate data by keeping a log of groupId-userId/anonymousId pairs.
	 *
	 * @param array $source Raw Altis Analytics source event.
	 *
	 * @return array
	 */
	public function groups( array $source ) : array {
		// Iterate over registered groups and only add ones where transformed groupId isn't empty.
		$groups = array_map( function( $group_map ) use ( $source ) {
			// Merge the registered group map with the default group map.
			$default_map = $this->get_segment_call_type_map( 'group' );
			$map = array_merge( $default_map, $group_map );

			// Get the transformed group call based on the map.
			$call = $this->transform( $source, $map );

			// Skip this group if we did not match a non-empty groupId.
			if ( empty( $call['groupId'] ) ) {
				return null;
			}

			return $call;
		}, $this->maps['groups'] );

		return array_values( array_filter( $groups ) );
	}

	/**
	 * Convert a numerical timestamp into an ISO 8601 date string.
	 *
	 * @param integer $timestamp Numerical timestamp, in seconds or milliseconds.
	 *
	 * @return string ISO 8601 date string.
	 */
	public static function format_date( int $timestamp ) : string {
		// If in milliseconds, convert to seconds.
		if ( strlen( $timestamp ) > 10 ) {
			$timestamp /= 1000;
		}

		return date( 'c', $timestamp );
	}

}
