<?php

namespace Level0;

define('GENERATOR', 'Level0 v1.2');

require_once 'www/config.php';
require_once 'www/osmapi.php';

use PHPUnit\Framework\TestCase;

class OsmApiTest extends TestCase
{


	public function test_url_to_api() {
		$this->assertFalse(url_to_api(''));
		$this->assertFalse(url_to_api('abc'));

		$this->assertEquals(url_to_api('/api/0.6/node/123'), 'https://api.openstreetmap.org/api/0.6/node/123');

		$this->assertEquals(url_to_api('overpass-api.de/api/interpreter?data=a-long-query'), 'http://overpass-api.de/api/interpreter?data=a-long-query');

		$this->assertEquals(
			url_to_api('n12,w34,r56'),
			[
				'https://api.openstreetmap.org/api/0.6/node/12',
				'https://api.openstreetmap.org/api/0.6/way/34',
				'https://api.openstreetmap.org/api/0.6/relation/56'
			]
		);

		$this->assertEquals(url_to_api('15/12.34/56.78'), 'https://api.openstreetmap.org/api/0.6/map?bbox=56.77970,12.33970,56.78030,12.34030');
	}


	public function test_create_osc() {
		$data = [
			[
				'action'    => 'change',
				'timestamp' => 'TODAY',
				'version'   => '2',
				'type'      => 'node',
				'id'        => '123',
				'lat'       => '51.12',
				'lon'       => '21.34',
				'tags'      => [
					'building' => 'yes',
					'key'      => 'value'
				]
			]
		];
		// var_dump(create_osc($data));
		$expected = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<osmChange version="0.6" generator="Level0 v1.2">
  <change>
    <node id='123' version='2' lat='51.12' lon='21.34' timestamp='TODAY'>
      <tag k='building' v='yes' />
      <tag k='key' v='value' />
    </node>
  </change>
</osmChange>
END;

		$this->assertEquals(create_osc($data), $expected);

		$expected = <<<END
<?xml version='1.0' encoding='UTF-8'?>
<osm version='0.6' upload='true' generator='Level0 v1.2'>
  <node id='123' version='2' lat='51.12' lon='21.34' action='change' timestamp='TODAY'>
    <tag k='building' v='yes' />
    <tag k='key' v='value' />
  </node>
</osm>
END;

		$this->assertEquals(create_osm($data), $expected);
	}


	public function test_hard_trim() {
		$this->assertEquals(hard_trim(' abc  def '), 'abc  def');
		$this->assertEquals(hard_trim(" \t abc  def \0 \r\n"), 'abc  def');
		$this->assertEquals(hard_trim(" \n "), '');
	}


	public function testis_pint() {
		$this->assertTrue(is_pint('12'));
		$this->assertTrue(is_pint('-12', false));
	}
}
