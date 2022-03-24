<?php
/**
 * Tests for Transformer class.
 *
 * phpcs:disable
 */

declare(strict_types=1);

namespace Altis\Analytics\Integration\Segment\Export;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Segment_TransformerTest extends TestCase {

	use MatchesSnapshots;

	/**
	 * @dataProvider dataTransform
	 *
	 * @return void
	 */
	public function testTransform( $event ) : void {
		$transformer = new Segment_Transformer();

		// Test groups mapping.
		$transformer->maps['groups'] = [
			[
				'groupId' => 'endpoint.Attributes.DeviceType.0',
				'traits' => [
					'model' => 'endpoint.Attributes.DeviceModel.0',
				],
			],
		];

		$this->assertMatchesJsonSnapshot( $transformer->transform( $event ) );
	}

	public static function dataTransform() : array {
		$events = array_filter( explode( "\n", <<<EOD
{"event_type":"_session.start","event_timestamp":1643673911400,"arrival_timestamp":1643673912482,"event_version":"3.1","application":{"app_id":"XXXXX","cognito_identity_pool_id":"XXXXX:YYYYY","sdk":{},"version_name":""},"client":{"client_id":"XXXXX-XXXX-XXXX-XXXXX","cognito_id":"XX"},"device":{"locale":{"code":"en_gb","country":"GB","language":"en"},"make":"WebKit","model":"Mobile Safari","platform":{"name":"ios","version":"14.4.1"}},"session":{"session_id":"XXXXX-XXXX-XXXX-XXXXX","start_timestamp":1643673911395},"attributes":{"date":"2021-10-12T08:33:00+01:00","referer":"XX","session":"XXXXX-XXXX-XXXX-XXXXX","blog":"https://site.com","title":"Some title","pageSession":"XXXXX-XXXX-XXXX-XXXXX","network":"https://site.com/","search":"","host":"site.com","networkId":"1","postType":"deal","author":"Some author","postId":"XX","authorId":"XX","url":"https://site.com/XXXX","loggedIn":"false","blogId":"1","hash":"","sector_17":"office"},"metrics":{"elapsed":2,"scrollDepthNow":0,"hour":0,"month":2,"year":2022,"scrollDepthMax":0,"day":3},"endpoint":{"EndpointStatus":"ACTIVE","OptOut":"ALL","RequestId":"XXXXX-XXXX-XXXX-XXXXX","Location":{"Country":"GB"},"Demographic":{"Make":"WebKit","Model":"Mobile Safari","ModelVersion":"14.0.3","Locale":"en_gb","AppVersion":"","Platform":"ios","PlatformVersion":"14.4.1"},"EffectiveDate":"2022-02-01T00:05:11.400Z","Attributes":{"DeviceModel":["iPhone"],"DeviceType":["mobile"],"lastPageSession":["XXXXX-XXXX-XXXX-XXXXX"],"DeviceMake":["Apple"],"lastSession":["XXXXX-XXXX-XXXX-XXXXX"]},"Metrics":{"sessions":1.0,"pageViews":1.0},"ApplicationId":"XOXOXO","Id":"XXXXX-XXXX-XXXX-XXXXX","CohortId":"48","CreationDate":"2022-02-01T00:05:11.400Z"},"awsAccountId":"00000"}
{"event_type":"_session.stop","event_timestamp":1643674153593,"arrival_timestamp":1643674153670,"event_version":"3.1","application":{"app_id":"XXXXX","cognito_identity_pool_id":"XXXXX:YYYYY","sdk":{},"version_name":""},"client":{"client_id":"XXXXX-XXXX-XXXX-XXXXX","cognito_id":"XX"},"device":{"locale":{"code":"en_us","country":"US","language":"en"},"make":"Blink","model":"Chrome","platform":{"name":"windows","version":"10"}},"session":{"session_id":"XXXXX-XXXX-XXXX-XXXXX","start_timestamp":1643674149701,"stop_timestamp":1643674153593},"attributes":{"date":"2022-01-31T16:20:00+00:00","referer":"","Email":"XXXX@XXX.XXX","session":"XXXXX-XXXX-XXXX-XXXXX","blog":"https://site.com","title":"Some title","pageSession":"XXXXX-XXXX-XXXX-XXXXX","network":"https://site.com/","search":"search?prop=value","host":"site.com","networkId":"1","IsExclusive":"false","qv_utm_source":"XX","loggedIn":"true","qv_utm_medium":"email","UserSubscriptionLevel":"Standard","blogId":"1","hash":"","qv_mc_eid":"XXXXX"},"metrics":{"elapsed":3891,"scrollDepthNow":0,"hour":0,"month":2,"year":2022,"scrollDepthMax":0,"day":3},"endpoint":{"EndpointStatus":"ACTIVE","OptOut":"ALL","RequestId":"XXXXX-XXXX-XXXX-XXXXX","Location":{"Country":"GB"},"Demographic":{"Make":"Blink","Model":"Chrome","ModelVersion":"96.0.4664.110","Locale":"en_us","AppVersion":"","Platform":"windows","PlatformVersion":"10"},"EffectiveDate":"2022-02-01T00:09:09.860Z","Attributes":{"utm_term":["XX"],"initial_utm_source":["XX"],"initial_utm_medium":["email"],"utm_campaign":["XX"],"utm_medium":["email"],"lastPageSession":["XXXXX-XXXX-XXXX-XXXXX"],"initial_utm_campaign":["XX"],"initial_utm_term":["XX"],"lastSession":["XXXXX-XXXX-XXXX-XXXXX"],"utm_source":["XX"]},"Metrics":{"sessions":20.0,"pageViews":24.0},"ApplicationId":"XOXOXO","Id":"XXXXX-XXXX-XXXX-XXXXX","CohortId":"47","CreationDate":"2022-01-18T10:18:45.756Z"},"awsAccountId":"00000"}
{"event_type":"pageView","event_timestamp":1643673987555,"arrival_timestamp":1643673988481,"event_version":"3.1","application":{"app_id":"XXXXX","cognito_identity_pool_id":"XXXXX:YYYYY","sdk":{},"version_name":""},"client":{"client_id":"XXXXX-XXXX-XXXX-XXXXX","cognito_id":"XX"},"device":{"locale":{"code":"en_gb","country":"GB","language":"en"},"make":"WebKit","model":"Mobile Safari","platform":{"name":"ios","version":"14.1"}},"session":{"session_id":"XXXXX-XXXX-XXXX-XXXXX","start_timestamp":1643673987549},"attributes":{"date":"2022-01-31T15:47:25+00:00","referer":"","Email":"XXXX@XXX.XXX","session":"XXXXX-XXXX-XXXX-XXXXX","blog":"https://site.com","title":"Some title","pageSession":"XXXXX-XXXX-XXXX-XXXXX","network":"https://site.com/","search":"search?prop=value","host":"site.com","networkId":"1","IsExclusive":"false","qv_mc_cid":"XX","postType":"article","author":"Some author","qv_utm_term":"XXOOXX","postId":"XX","UserSubscriptionStatus":"active","authorId":"XX","url":"https://site.com/XXXX","qv_utm_source":"XX","loggedIn":"true","qv_utm_medium":"email","UserSubscriptionLevel":"Standard","blogId":"1","hash":"","qv_mc_eid":"XXXXX"},"metrics":{"elapsed":3,"scrollDepthNow":0,"hour":2,"month":2,"year":2022,"scrollDepthMax":0,"day":3},"endpoint":{"EndpointStatus":"ACTIVE","OptOut":"ALL","RequestId":"XXXXX-XXXX-XXXX-XXXXX","Location":{"Country":"RO"},"Demographic":{"Make":"WebKit","Model":"Mobile Safari","ModelVersion":"14.0","Locale":"en_gb","AppVersion":"","Platform":"ios","PlatformVersion":"14.1"},"EffectiveDate":"2022-02-01T00:06:27.555Z","Attributes":{"DeviceModel":["iPhone"],"initial_utm_medium":["email"],"utm_campaign":["XX"],"utm_medium":["email"],"initial_utm_campaign":["XX"],"utm_term":["XX"],"DeviceType":["mobile"],"initial_utm_source":["XX"],"lastPageSession":["XXXXX-XXXX-XXXX-XXXXX"],"DeviceMake":["Apple"],"initial_utm_term":["XX"],"lastSession":["XXXXX-XXXX-XXXX-XXXXX"],"utm_source":["XX"]},"Metrics":{"sessions":22.0,"pageViews":26.0},"ApplicationId":"XOXOXO","Id":"XXXXX-XXXX-XXXX-XXXXX","CohortId":"92","CreationDate":"2022-01-31T11:22:59.386Z"},"awsAccountId":"00000"}
{"event_type":"pageView","event_timestamp":1643674010377,"arrival_timestamp":1643674014161,"event_version":"3.1","application":{"app_id":"XXXXX","cognito_identity_pool_id":"XXXXX:YYYYY","sdk":{},"version_name":""},"client":{"client_id":"XXXXX-XXXX-XXXX-XXXXX","cognito_id":"XX"},"device":{"locale":{"code":"en_us","country":"US","language":"en"},"make":"Blink","model":"Chrome","platform":{"name":"windows","version":"10"}},"session":{"session_id":"XXXXX-XXXX-XXXX-XXXXX","start_timestamp":1643674010361},"attributes":{"date":"2021-08-16T15:36:41+01:00","referer":"XX","session":"XXXXX-XXXX-XXXX-XXXXX","blog":"https://site.com","title":"Some title","pageSession":"XXXXX-XXXX-XXXX-XXXXX","network":"https://site.com/","search":"","host":"site.com","networkId":"1","IsExclusive":"false","postType":"article","author":"Some author","postId":"XX","authorId":"XX","url":"https://site.com/XXXX","loggedIn":"false","blogId":"1","hash":""},"metrics":{"elapsed":15,"scrollDepthNow":0,"hour":19,"month":1,"year":2022,"scrollDepthMax":0,"day":2},"endpoint":{"EndpointStatus":"ACTIVE","OptOut":"ALL","RequestId":"XXXXX-XXXX-XXXX-XXXXX","Location":{"Country":"US"},"Demographic":{"Make":"Blink","Model":"Chrome","ModelVersion":"97.0.4692.71","Locale":"en_us","AppVersion":"","Platform":"windows","PlatformVersion":"10"},"EffectiveDate":"2022-02-01T00:06:50.377Z","Attributes":{"lastPageSession":["XXXXX-XXXX-XXXX-XXXXX"],"lastSession":["XXXXX-XXXX-XXXX-XXXXX"]},"Metrics":{"sessions":1.0,"pageViews":1.0},"ApplicationId":"XOXOXO","Id":"XXXXX-XXXX-XXXX-XXXXX","CohortId":"86","CreationDate":"2022-02-01T00:06:50.377Z"},"awsAccountId":"00000"}
EOD ));

		$events = array_map( function( $event ) { return [ json_decode( $event, true ) ]; }, $events );
		return $events;
	}
}
