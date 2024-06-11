<?php

namespace Level0;

require_once 'www/core.php';

use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{

	public function test_store_and_read_base() {
		global $l0id;
		$l0id     = mt_rand(1000, 9999999);
		$testdata = [
			'key'  => 'value',
			'tags' => [
				1,
				2
			]
		];

		$filename = get_cache_filename('base');

		global $basedata;
		clear_data();
		$this->assertFalse(file_exists($filename));

		$basedata = $testdata;
		store_base();
		$this->assertTrue(file_exists($filename));

		$basedata = [];
		read_base();
		$this->assertEquals($basedata, $testdata);

		clear_data();
		$this->assertFalse(file_exists($filename));
		$this->assertEquals($basedata, []);
	}


	public function test_strip_version() {
		$this->assertEquals(
			strip_version([]),
			[]
		);
		$this->assertEquals(
			strip_version(['a' => 'b', 'type' => 'node', 'version' => "1"]),
			['a' => 'b', 'type' => 'node']
		);

		$this->assertEquals(
			strip_version([['b' => 'c', 'type' => 'node', 'version' => "1"]]),
			[['b' => 'c', 'type' => 'node']]
		);
	}

	public function test_usort() {
		$result = [
			['action' => 'delete', 'type' => 'way', 'expected_order' => 3],
			['action' => 'delete', 'type' => 'node', 'expected_order' => 4],
			['action' => 'modify', 'type' => 'way', 'expected_order' => 2],
			['action' => 'modify', 'type' => 'node', 'expected_order' => 1]
		];

		usort($result, 'export_cmp');
		$this->assertEquals(
			array_map(fn($o) => $o['expected_order'], $result),
			[1, 2, 3, 4]
		);
	}

	public function test_calculate_center() {
		global $userdata;
		$userdata = [];
		$this->assertFalse(calculate_center());

		$userdata = [
			['type' => 'node', 'lat' => '12.0', 'lon' => '34.0'],
			['type' => 'node', 'lat' => '12.2', 'lon' => '34.2']
		];

		$this->assertEquals(
			calculate_center(),
			[12.1, 34.1]
		);

	}
}
