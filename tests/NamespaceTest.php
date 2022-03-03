<?php /* phpcs:disable */

declare(strict_types=1);

namespace Altis\Analytics\Integration\Segment\Export;

use PHPUnit\Framework\TestCase;

final class NamespaceTest extends TestCase {

	function testFormatBasic() : void {
		$expected = [
			[
				[
					'type' => 'identify',
					'anonymousId' => '11954862-0adf-46cf-853f-ac46d8e74bc9',
					'messageId' => '7e91ec4f-edc1-4a46-ae4b-f7af21b2cdf5',
					'timestamp' => '2022-02-01T00:05:11+00:00',
				],
				[
					'type' => 'track',
					'anonymousId' => '11954862-0adf-46cf-853f-ac46d8e74bc9',
					'messageId' => '7e91ec4f-edc1-4a46-ae4b-f7af21b2cdf5',
					'timestamp' => '2022-02-01T00:05:11+00:00',
					'context' => [
						'active' => 'ACTIVE',
						'device' => [
							'manufacturer' => 'Apple',
							'model' => 'iPhone',
							'type' => 'mobile',
						],
						'locale' => 'en_gb',
						'location' => [
							'country' => 'GB',
						],
						'os' => [
							'name' => 'ios',
							'version' => '14.4.1',
						],
						'page' => [
							'search' => '',
							'title' => 'Page title',
							'url' => 'https://site.com/path/to/page/',
						],
						'traits' => [
							'sessions' => 1.0,
							'pageViews' => 1.0,
							'DeviceModel' => [
								'iPhone',
							],
							'DeviceType' => [
								'mobile',
							],
							'lastPageSession' => [
								'70dcbd86-2062-4b22-b9ed-88d6ee109107',
							],
							'DeviceMake' => [
								'Apple',
							],
							'lastSession' => [
								'2f016215-6e0c-453b-a88a-a74e97d71195',
							],
						],
					],
					'event' => '_session.start',
					'properties' => [
						'DeviceModel' => [
							'iPhone',
						],
						'DeviceType' => [
							'mobile',
						],
						'lastPageSession' => [
							'70dcbd86-2062-4b22-b9ed-88d6ee109107',
						],
						'DeviceMake' => [
							'Apple',
						],
						'lastSession' => [
							'2f016215-6e0c-453b-a88a-a74e97d71195',
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, format( '
{"event_type":"_session.start","event_timestamp":1643673911400,"arrival_timestamp":1643673912482,"event_version":"3.1","application":{"app_id":"095892d722ad4821b3e8619c643de894","cognito_identity_pool_id":"eu-west-1:771a8edc-6ef9-4e27-b4ec-1e39290a1a37","sdk":{},"version_name":""},"client":{"client_id":"11954862-0adf-46cf-853f-ac46d8e74bc9","cognito_id":"eu-west-1:11954862-0adf-46cf-853f-ac46d8e74bc9"},"device":{"locale":{"code":"en_gb","country":"GB","language":"en"},"make":"WebKit","model":"Mobile Safari","platform":{"name":"ios","version":"14.4.1"}},"session":{"session_id":"553036e4-548f-4279-bb19-826e70e59ea0","start_timestamp":1643673911395},"attributes":{"date":"2021-10-12T08:33:00+01:00","referer":"https://www.google.co.uk","session":"2f016215-6e0c-453b-a88a-a74e97d71195","blog":"https://reactnews.com","title":"Page title","pageSession":"70dcbd86-2062-4b22-b9ed-88d6ee109107","network":"https://reactnews.com/","search":"","host":"reactnews.com","networkId":"1","sector_48":"occupier","postType":"deal","author":"jamesbuckley","postId":"118715","authorId":"92","url":"https://site.com/path/to/page/","region_32046":"united-kingdom-ireland","region_6":"london","loggedIn":"false","blogId":"1","hash":"","sector_17":"office"},"metrics":{"elapsed":2,"scrollDepthNow":0,"hour":0,"month":2,"year":2022,"scrollDepthMax":0,"day":3},"endpoint":{"EndpointStatus":"ACTIVE","OptOut":"ALL","RequestId":"7e91ec4f-edc1-4a46-ae4b-f7af21b2cdf5","Location":{"Country":"GB"},"Demographic":{"Make":"WebKit","Model":"Mobile Safari","ModelVersion":"14.0.3","Locale":"en_gb","AppVersion":"","Platform":"ios","PlatformVersion":"14.4.1"},"EffectiveDate":"2022-02-01T00:05:11.400Z","Attributes":{"DeviceModel":["iPhone"],"DeviceType":["mobile"],"lastPageSession":["70dcbd86-2062-4b22-b9ed-88d6ee109107"],"DeviceMake":["Apple"],"lastSession":["2f016215-6e0c-453b-a88a-a74e97d71195"]},"Metrics":{"sessions":1.0,"pageViews":1.0},"ApplicationId":"095892d722ad4821b3e8619c643de894","Id":"11954862-0adf-46cf-853f-ac46d8e74bc9","CohortId":"48","CreationDate":"2022-02-01T00:05:11.400Z"},"awsAccountId":"577418818413"}
' ) );
	}

}
