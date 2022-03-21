<table width="100%">
	<tr>
		<td align="left" width="70">
			<strong>Segment Integration for Altis Analytics</strong><br />
			Segment integration for Altis Analytics module, exports Altis Analytics events to Segment.
		</td>
		<td align="right" width="20%">
			<a href="https://travis-ci.com/humanmade/S3-Uploads">
				<img src="https://travis-ci.com/humanmade/S3-Uploads.svg?branch=master" alt="Build status">
			</a>
		</td>
	</tr>
	<tr>
		<td>
			A <strong><a href="https://hmn.md/">Human Made</a></strong> project. Maintained by @humanmade.
		</td>
		<td align="center">
			<img src="https://hmn.md/content/themes/hmnmd/assets/images/hm-logo.svg" width="100" />
		</td>
	</tr>
</table>

Segment integration for Altis Analytics adds automatic exporting of analytics events to Segment via background cronjobs.

## Getting Set Up

### Installing

```
composer require humanmade/altis-analytics-integration-segment
```

---

Once you've installed the package, add the following constants to your `wp-config.php`:

```PHP
define( 'SEGMENT_API_WRITE_KEY', 'ADD_YOUR_SEGMENT_KEY_HERE' );
```

## Usage

Once installed the package sets up a cronjob that handles batch uploading events to Segment APIs through hooking to the event `altis.analytics.export.data.process` that is triggered by Altis Analytics.

#### Functions

**`register_segment_group_field( string $field, array $traits = [] ) : void`**

Registers a grouping field, which is sent to Segment as a group call. See [Segment docs on Group cals](https://segment.com/docs/connections/sources/catalog/libraries/server/http-api/#group) for more information.

- *`$field` is the event field to group by.
- *`$traits` is the data map to add to the group from.

```php
register_segment_group_map( 'endpoint.Attributes.AudienceId', [ 'country' => 'endpoint.Attributes.UserAttributes.country' ] )
```

#### Actions

**`altis.analytics.segment.request_failure : Requests_Exception|Requests_Response $response,  Array $batch`**

Triggered when a batch request to Segment APIs fails.

**`altis.analytics.segment.request_success  : Requests_Response $response,  Array $batch`**

Triggered when a batch request to Segment APIs succeeds.

**`altis.analytics.segment.after_send : Array<Requests_Exception|Requests_Response> $results, Array $batches, Array<Array> $events`**

Triggered after sending the batch request to Segment APIs.

#### Filters

**`altis.analytic.segment.api_write_key : String`**

Filters the Segment API key, for instances where different keys are needed for different sites. Use the constant `SEGMENT_API_WRITE_KEY` otherwise.

**`altis.analytics.segment.mapping : Array`**

Filters the mapping tree which the transformer uses to translate the Altis Analytics event format to the format needed by Segment. Use only if you need to customize the event structure that will be logged by Segment. The filter accepts a second parameter `$type <string>` that indicates the event type, eg: `identify`, `track`, `group`, etc.

**`altis.analytics.segment.formatted_data : Array`**

Filters the formatted data, in case further customizations are needed.

**`altis.analytics.segment.groups : Array`**

Filters the group definitions, for advanced use only, use the function `register_segment_group_map()` instead for simple usage.

### Data mapping

The package uses a data mapping structure to convert between Altis Analytics and Segment respective formats.

Examples:

```
$mapping = [
    // Default format:
    'key' => 'path.to.value',

    // Basic value map:
    'country' => 'endpoint.Attributes.UserAttributes.country',

    // Use a transformative callback:
    'country' => 'endpoint.Attributes.UserAttributes.country|\SOME\NAMESPACE\timestamp_to_string',

    // Skip a specific key:
    'country' => '',

    // Merge a whole map branch (notice the absence of a key):
    'endpoint.Attributes.UserAttributes',
]
```
