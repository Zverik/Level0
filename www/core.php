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

function open_db($readonly) {
  try {
    $mode = $readonly ? SQLITE3_OPEN_READONLY : SQLITE3_OPEN_READWRITE;
    $db = new SQLite3(SQLITE_DB, $mode);
    $db->busyTimeout(5000);
    error_log('Opened db with mode '.$mode.' (readonly: '.$readonly.')');
  } catch (Exception) {
    $db = new SQLite3(SQLITE_DB);
    $db->exec(
      "create table if not exists base ".
      "(l0id integer primary key, content text, ".
      "created datetime default current_timestamp);");
    $db->exec(
      "create table if not exists user ".
      "(l0id integer primary key, content text, ".
      "created datetime default current_timestamp);");
  }
  $db->exec('PRAGMA journal_mode = wal;');
  return $db;
}

function read_base() {
	global $basedata, $l0id, $error;
  try {
    $db = open_db(true); // should be true, but the next line invokes the ro error
    $st = $db->prepare("select content from base where l0id = :id");
    if (!$st) {
      $error = 'Failed to prepare sqlite statement';
      return;
    }
    $st->bindValue(':id', $l0id, SQLITE3_INTEGER);
    $result = $st->execute();
    $row = $result ? $result->fetchArray(SQLITE3_NUM) : null;
    if ($row) {
      $basedata = @unserialize($row[0]);
      if( !is_array($basedata) )
        $basedata = array();
    }
    $db->close();
  } catch (Exception $e) {
    // it's okay
    $error = 'Error reading stored data: '.$e;
  }
}

function store_base() {
	global $basedata, $l0id, $error;
  try {
    $db = open_db(false);
    if( $basedata && count($basedata) > 0 ) {
      $st = $db->prepare(
        "insert into base(l0id, content) values(:id, :cnt) ".
        "on conflict do update set content = :cnt");
      $st->bindValue(':id', $l0id, SQLITE3_INTEGER);
      $st->bindValue(':cnt', serialize($basedata), SQLITE3_TEXT);
      $st->execute();
      error_log('written '.count($basedata).' objects to db');
    } else {
      $st = $db->prepare("delete from base where l0id = :id");
      $st->bindValue(':id', $l0id, SQLITE3_INTEGER);
      $st->execute();
      error_log('no data written');
    }
    $db->close();
  } catch (Exception $e) {
    $error = 'Error storing data: '.$e;
  }
}

function read_user() {
	global $l0id, $error;
  $text = '';
  try {
    $db = open_db(false);
    $st = $db->prepare("select content from user where l0id = :id");
    $st->bindValue(':id', $l0id, SQLITE3_INTEGER);
    $result = $st->execute();
    $row = $result ? $result->fetchArray(SQLITE3_NUM) : null;
    if ($row) {
      $text = row[0];
      $st = $db->prepare("delete from user where l0id = :id");
      $st->bindValue(':id', $l0id, SQLITE3_INTEGER);
      $st->execute();
    }
    $db->close();
  } catch (Exception $e) {
    $error = 'Error reading stored user data: '.$e;
  }
  return $text;
}

// saves user data to a cache.
function store_user( $text ) {
	global $l0id, $error;
  try {
    $db = open_db(false);
    if(strlen($text) > 0) {
      $st = $db->prepare(
        "insert into user(l0id, content) values(:id, :cnt) ".
        "on conflict do update set content = :cnt");
      $st->bindValue(':id', $l0id, SQLITE3_INTEGER);
      $st->bindValue(':cnt', $text, SQLITE3_TEXT);
      $st->execute();
    } else {
      $st = $db->prepare("delete from user where l0id = :id");
      $st->bindValue(':id', $l0id, SQLITE3_INTEGER);
      $st->execute();
    }
    $db->close();
  } catch (Exception $e) {
    $error = 'Error storing user data: '.$e;
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
		if( $obj['type'] == 'changeset' ) {
			// check that created changeset metadata is not duplicated
			if( $obj['id'] > 0 ) {
				$found = false;
				foreach( $userdata as $obj ) {
					if( $obj['type'] == 'changeset' && $obj['id'] <= 0 ) {
						$found = true;
						break;
					}
				}
				if( $found ) {
					$messages[] = _('There can be only one changeset metadata').'.';
					continue;
				}
			}
			$userdata[] = $objnv;
			$added[] = $objnv;
		} elseif( $obj['id'] <= 0 || (isset($obj['action']) && $obj['action'] == 'create') ) {
			// created objects go straight to userdata
			$userdata[] = $objnv;
			$added[] = $objnv;
		} else { // modify, delete or base
			if( !isset($obj['version']) ) {
				$messages[] = sprintf(_('No version for object %s %s.'), $obj['type'], $obj['id']);
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
				$messages[] = sprintf(_('Found older version of %s %s: %d instead of %d.'), $res['type'], $res['id'], $res['version'], $basedata[$pk]['version']);
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
		if( count($obj['members']) != count($base['members']) )
			return true;
		for( $i = 0; $i < count($obj['members']); $i++ )
			if( $obj['members'][$i]['type'] != $base['members'][$i]['type'] || $obj['members'][$i]['id'] != $base['members'][$i]['id'] || $obj['members'][$i]['role'] != $base['members'][$i]['role'] )
				return true;
	}
	return false;
}

// The lesser is the result, the higher in the osmChange is the object
function grade_for_export($obj) {
		// 0 = node, 1 = way, 2 = relation
		$grade = $obj['type'] == 'node' ? 0 : ($obj['type'] == 'way' ? 1 : 2);
		if( isset($obj['action']) ) {
				// reverse deletion order
				if( $obj['action'] == 'delete' )
						$grade = 2 - $grade;
				// first, created objects, then modified (so deleted refs are deleted), then deleted
				if( $obj['action'] == 'modify' )
						$grade += 10;
				elseif( $obj['action'] == 'delete' )
						$grade += 20;
		}
		return $grade;
}

// a function for sorting data for exporting
function export_cmp($a, $b) {
	$typea = grade_for_export($a);
	$typeb = grade_for_export($b);
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
				return sprintf(_('Duplicate ID for %s %s'), $obj['type'], $obj['id']);
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
		if( $obj['type'] == 'changeset' && $obj['id'] <= 0 )
			$result[] = $obj;
		else if( $obj['id'] <= 0 ) {
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
				return sprintf(_('No base data for %s %s'), $obj['type'], $obj['id']);
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
