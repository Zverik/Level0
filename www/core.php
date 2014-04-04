<?php
require('level0l.php');
$basedata = array();
$userdata = array();

function format_data() {
	global $userdata;
	return data_to_l0l($userdata);
}

function parse_text( $str ) {
	global $userdata;
	$userdata = l0l_to_data($str);
}

function get_cache_filename( $suffix ) {
	global $l0id;
	return DATA_DIR.'/'.$l0id.'.'.$suffix;
}

function read_base() {
	global $basedata;
	$filename = get_cache_filename('base');
	$data = @file_get_contents($filename);
	if( $data !== false )
		$basedata = @unserialize($data);
	if( !is_array($basedata) )
		$basedata = array();
}

function store_base() {
	global $basedata;
	$filename = get_cache_filename('base');
	if( $basedata && count($basedata) > 0 ) {
		@file_put_contents($filename, serialize($basedata));
	} else {
		// delete base
		@unlink($filename);
	}
}

function read_user() {
	$filename = get_cache_filename('user');
	$text = @file_get_contents($filename);
	@unlink($filename);
	return $text !== false && strlen($text) > 0 ? $text : '';
}

// saves user data to a cache.
function store_user( $text ) {
	$filename = get_cache_filename('user');
	if( strlen($text) > 0 ) {
		@file_put_contents($filename, $text);
	} else {
		// delete file
		@unlink($filename);
	}
}

function clear_data() {
	global $basedata, $userdata;
	$basedata = array();
	$userdata = array();
	store_base();
}

function update_text( $text ) {
	global $added, $modified;
	if( count($modified) )
		$text = format_data();
	elseif( count($added) )
		$text = l0l_merge($text, $added, $modified);
	$added = array();
	$modified = array();
	return $text;
}

// filters out base objects and saves them to a file
// also updated user object. Everything, basically
function update_data( $data ) {
	global $basedata, $userdata, $messages, $added, $modified;
	// arrays for merging
	if( !isset($added) || !isset($modified) ) {
		$added = array();
		$modified = array();
	}

	if( count($userdata) )
		$data = renumber_created($data, $userdata); // nb: this is already done in osmapi.php

	foreach( $data as $obj ) {
		$objnv = strip_version($obj);
		if( $obj['id'] <= 0 || (isset($obj['action']) && $obj['action'] == 'create') ) {
			// created objects go straight to userdata
			$userdata[] = $objnv;
			$added[] = $objnv;
		} else { // modify, delete or base
			if( !isset($obj['version']) ) {
				$messages[] = sprintf(_('No version for object %s %d.'), $obj['type'], $obj['id']);
				continue;
			}
			if( isset($obj['action']) && strlen($obj['action']) > 0 ) {
				// for deleted and modified save only version
				$res = array('type' => $obj['type'], 'id' => $obj['id'], 'version' => $obj['version'], 'complete' => false);
			} else {
				$res = $obj;
				$res['complete'] = true;
			}

			// $res goes to basedata, $obj should be merged with userdata
			$pk = $res['type'].$res['id'];
			$version_diff = $res['version'] - (isset($basedata[$pk]) ? $basedata[$pk]['version'] : 0);
			if( !isset($basedata[$pk]) || !$version_diff ) {
				if( !isset($basedata[$pk]) || !$basedata[$pk]['complete'] )
					$basedata[$pk] = $res;
				// userdata: if exists and modified, leave current version, otherwise replace
				$found = false;
				for( $i = 0; $i < count($userdata); $i++ ) {
					if( $userdata[$i]['type'] == $res['type'] && $userdata[$i]['id'] == $res['id'] ) {
						$found = true;
						if( !is_modified($userdata[$i]) ) {
							// should we replace it with the new version? idk.
							// $userdata[$i] = $objnv;
							// $modified[] = $objnv;
						}
					}
				}
				if( !$found ) {
					$userdata[] = $objnv;
					$added[] = $objnv;
				}
			} elseif( $version_diff < 0 ) {
				// it's really old, skip it
				$messages[] = sprintf(_('Found older version of %s %d: %d instead of %d.'), $res['type'], $res['id'], $res['version'], $basedata[$pk]['version']);
			} else {
				$basedata[$pk] = $res;
				// userdata: if exists and modified, then conflict!
				$found = false;
				for( $i = 0; $i < count($userdata); $i++ ) {
					if( $userdata[$i]['type'] == $res['type'] && $userdata[$i]['id'] == $res['id'] ) {
						$found = true;
						if( is_modified($userdata[$i]) ) {
							$objnv['conflict'] = $userdata[$i];
							$userdata[$i] = $objnv;
							$modified[] = $objnv;
						}
					}
				}
				if( !$found ) {
					$userdata[] = $objnv;
					$added[] = $objnv;
				}
			}
		}
	}
}

function strip_version( $obj ) {
	if( isset($obj['type']) ) {
		if( isset($obj['version']) )
			unset($obj['version']);
	} elseif( is_array($obj) ) {
		for( $i = 0; $i < count($obj); $i++ )
			if( isset($obj[$i]['version']) )
				unset($obj[$i]['version']);
	}
	return $obj;
}

function update_data_array( $urls ) {
	global $messages, $error, $text, $added, $modified;
	$added = array();
	$modified = array();
	$cleared = false;
	foreach( $urls as $url ) {
		if( substr($url, 0, 4) == 'http' )
			$messages[] = sprintf(_('Reading URL %s'), $url);
		elseif( isset($_FILES['file']['name']) )
			$messages[] = sprintf(_('Reading file %s'), $_FILES['file']['name']);
		$data = parse_osm_xml($url);
		if( $data && count($data) > 0 ) {
			if( !$cleared && isset($_REQUEST['replace']) ) {
				clear_data();
				$text = '';
				$cleared = true;
			}
			update_data($data);
			if( $error )
				break;
		}
	}
	store_base();
	if( count($added) + count($modified) > 0 ) {
		$text = update_text($text);
	}
}

function update_modified() {
	global $error, $messages;
	$data = prepare_export();
	if( !is_array($data) ) {
		$error = sprintf(_('Error preparing data: %s.'), $data);
		return;
	}
	$update = array('node' => array(), 'way' => array(), 'relation' => array());
	$count = 0;
	foreach( $data as $obj ) {
		if( isset($obj['action']) && $obj['id'] > 0 ) {
			$update[$obj['type']][] = $obj['id'];
			$count++;
		}
	}
	if( !$count ) {
		$error = _('Nothing is modified, not going to update everything');
		return;
	} elseif( $count > MAX_REQUEST_OBJECTS ) {
		$messages = sprintf(ngettext('%d object was modified, can update only %d of them (repeat for more).', '%d objects were modified, can update only %d of them (repeat for more).', $count), $count, MAX_REQUEST_OBJECTS);
		$count = MAX_REQUEST_OBJECTS;
		foreach( array('relation', 'way', 'node') as $type ) {
			if( count($update[$type]) > $count )
				array_splice($update[$type], $count);
			$count = max(0, $count - count($update[$type]));
		}
	}
	$urls = array();
	foreach( $update as $type => $ids )
		if( count($ids) )
			$urls[] = OSM_API_URL.$type.'s?'.$type.'s='.implode(',', $ids);
	update_data_array($urls);
}

// checks the object's fields against base
function is_modified( $obj ) {
	global $basedata;
	if( $obj['id'] <= 0 || isset($obj['action']) )
		return true;
	$pk = $obj['type'].$obj['id'];
	if( !isset($basedata[$pk]) )
		return false;

	$base = $basedata[$pk];
	if( !$base['complete'] )
		return true;

	if( isset($obj['version']) && $obj['version'] > $base['version'] )
		return true;

	if( count($base['tags']) != count($obj['tags']) || array_diff_assoc($base['tags'], $obj['tags']) || array_diff_assoc($obj['tags'], $base['tags']) )
		return true;

	if( $obj['type'] == 'node' ) {
		if( $obj['lat'] != $base['lat'] || $obj['lon'] != $base['lon'] )
			return true;
	} elseif( $obj['type'] == 'way' ) {
		if( $obj['nodes'] != $base['nodes'] )
			return true;
	} elseif( $obj['type'] == 'relation' ) {
		for( $i = 0; $i < count($obj['members']); $i++ )
			if( $obj['members'][$i]['type'] != $base['members'][$i]['type'] || $obj['members'][$i]['id'] != $base['members'][$i]['id'] || $obj['members'][$i]['role'] != $base['members'][$i]['role'] )
				return true;
	}
	return false;
}

// a function for sorting data for exporting
function export_cmp($a, $b) {
	$typea = $a['type'] == 'node' ? 0 : ($a['type'] == 'way' ? 1 : 2);
	$typeb = $b['type'] == 'node' ? 0 : ($b['type'] == 'way' ? 1 : 2);
	if( $typea < $typeb ) return -1;
	if( $typea > $typeb ) return 1;
	$idd = $a['id'] - $b['id'];
	return $idd > 0 ? 1 : ($idd < 0 ? -1 : 0);
}

// merge base and user tables
function prepare_export() {
	global $basedata, $userdata;
	$result = array();
	$cversions = array();
	foreach( $userdata as $obj ) {
		$pk = $obj['type'].$obj['id'];
		if( $obj['id'] < 0 ) {
			if( isset($cversions[$pk]) )
				return sprintf(_('Duplicate ID for %s %d'), $obj['type'], $obj['id']);
			$cversions[$pk] = true;
		}
		if( $obj['id'] <= 0 || (isset($obj['action']) && $obj['action'] != 'delete') ) {
			// check members
			if( $obj['type'] == 'way' && count($obj['nodes']) < 2 )
				return sprintf(_('Way %d has less than two nodes'), $obj['id']);
			if( $obj['type'] == 'relation' && !count($obj['members']) )
				return sprintf(_('Relation %d has no members'), $obj['id']);
		}
	}
	$nid = -1;
	foreach( $userdata as $obj ) {
		$pk = $obj['type'].$obj['id'];
		if( $obj['id'] <= 0 ) {
			if( $obj['id'] == 0 ) {
				while( isset($cversions[$nid]) )
					$nid--;
				$obj['id'] = $nid--;
			}
			$obj['version'] = 1;
			$obj['action'] = 'create';
			$result[] = $obj;
		} else {
			if( !isset($basedata[$pk]) )
				return sprintf(_('No base data for %s %d'), $obj['type'], $obj['id']);
			$base = $basedata[$pk];
			if( !isset($obj['version']) )
				$obj['version'] = $base['version'];
			if( isset($obj['action']) && $obj['action'] == 'delete' ) {
				// nothing to do
			} else {
				if( is_modified($obj) ) { // modify
					$obj['action'] = 'modify';
				} else { // store base
					$obj = $base;
				}
			}
			$result[] = $obj;
		}
	}
	usort($result, 'export_cmp');
	return $result;
}

// for an interactive map
function calculate_center() {
	global $userdata;
	$count = 0;
	$lat = 0;
	$lon = 0;
	foreach( $userdata as $obj ) {
		if( $obj['type'] == 'node' && isset($obj['lat']) && isset($obj['lon']) ) {
			$count++;
			$lat += $obj['lat'];
			$lon += $obj['lon'];
		}
	}
	return $count ? array($lat / $count, $lon / $count) : false;
}

// reverts all modified objects and updates contents of deleted
function revert() {
	global $userdata, $basedata;
	for( $i = 0; $i < count($userdata); $i++ ) {
		$pk = $userdata[$i]['type'].$userdata[$i]['id'];
		if( $userdata[$i]['id'] > 0 && isset($basedata[$pk])
			&& $basedata[$pk]['complete'] && is_modified($userdata[$i]) ) {
			$deleted = isset($userdata[$i]['action']) && $userdata[$i]['action'] == 'delete';
			$userdata[$i] = strip_version($basedata[$pk]);
			if( $deleted )
				$userdata[$i]['action'] = 'delete';
		}
	}
	return format_data();
}

?>
