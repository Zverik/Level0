<?php

namespace Level0;

$GLOBALS['validation'] = null;

require_once 'www/config.php';
require_once 'www/level0l.php';

use PHPUnit\Framework\TestCase;

class Level0lTest extends TestCase
{


	public function test_l0l_to_data()
	{
		$l0l = "node 123: 51.12, 21.34\n  building = yes\n  key = value\n";

		$data = l0l_to_data($l0l);

		$this->assertEquals(
			$data,
			[
				[
					'type' => 'node',
					'id'   => '123',
					'lat'  => '51.12',
					'lon'  => '21.34',
					'tags' => [
						'building' => 'yes',
						'key'      => 'value'
					]
				]
			]
		);

		$this->assertEquals(data_to_l0l($data), $l0l);
	}
}
