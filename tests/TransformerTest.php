<?php
/**
 * Tests for Transformer class.
 *
 * phpcs:disable
 */

declare(strict_types=1);

namespace Altis\Analytics\Integration\Segment\Export;

use PHPUnit\Framework\TestCase;

final class TransformerTest extends TestCase {

	/**
	 * @dataProvider dataTransform
	 *
	 * @return void
	 */
	public function testTransform( $source, $map, $expected ) : void {
		$transformer = new Transformer( $map );
		$this->assertEquals( $expected, $transformer->transform( $source ) );
	}

	public static function dataTransform() : array {
		$source = [
			'first_name' => 'shady',
			'last_name' => 'sharaf',
			'address' => [
				'building' => 47,
				'street' => 47,
				'city' => 'fortyseven',
				'country' => 47,
			],
			'favorites' => [
				'sports' => [
					'Table tennis',
				],
				'books' => [
					'Time machine',
				],
			],
			'registered' => 1646014407,
		];

		return [
			'Empty branch skips key' => [
				$source,
				[
					'age' => '',
				],
				[

				],
			],
			'Numerical index merges array' => [
				$source,
				[
					'favorites' => [
						'favorites.sports',
						'favorites.books',
					],
				],
				[
					'favorites' => [
						'Table tennis',
						'Time machine',
					],
				],
			],
			'Nested branch traversed' => [
				$source,
				[
					'city' => 'address.city',
					'favoriteBook' => 'favorites.books.0',
				], [
					'city' => 'fortyseven',
					'favoriteBook' => 'Time machine',
				],
			],
			'Basic string branch' => [
				$source,
				[
					'firstName' => 'first_name',
				],
				[
					'firstName' => 'shady',
				],
			],
			'Not found reference' => [
				$source,
				[
					'age' => 'age',
				],
				[

				],
			],
			'Empty nested branch' => [
				$source,
				[
					'tracks' => 'favorite.tracks',
				],
				[

				],
			],
		];
	}

	/**
	 * @dataProvider dataFetch
	 *
	 * @return void
	 */
	public function testFetch( $source, $path, $expected ) : void {
		$transformer = new Transformer( [] );
		$this->assertEquals( $expected, $transformer->fetch( $source, $path ) );
	}

	public static function dataFetch() : array {
		$source = [
			'first_name' => 'shady',
			'last_name' => 'sharaf',
			'registered' => 1646014407,
		];

		return [
			'Basic' => [
				$source,
				'first_name',
				'shady',
			],
			'Callbacks' => [
				$source,
				'first_name|ucfirst',
				'Shady',
			],
			'Complex callbacks' => [
				$source,
				'registered|' . __NAMESPACE__ . '\TransformerTest::dateFromEpoch',
				'2022-02-28T02:13:27+00:00',
			],
			'Empty reference' => [
				$source,
				'age',
				null,
			],
			'Static string' => [
				$source,
				'Something something|static',
				'Something something',
			],
		];
	}

	public static function dateFromEpoch( int $epoch ) : string {
		return date( 'c', $epoch );
	}

}
