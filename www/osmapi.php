<?php

function oauth_login() {
	global $error;
	try {
		$oauth = new OAuth(CLIENT_ID,CLIENT_SECRET,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
		$request_token_info = $oauth->getRequestToken(OSM_OAUTH_URL.'request_token');
		$_SESSION['secret'] = $request_token_info['oauth_token_secret'];
		header('Location: '.OSM_OAUTH_URL."authorize?oauth_token=".$request_token_info['oauth_token']);
		exit;
	} catch(OAuthException $E) {
		$error = 'OAuth error '.$E->getCode().': '.$E->getMessage();
	}
}

function oauth_logout() {
	unset($_SESSION['osm_user']);
	unset($_SESSION['osm_langs']);
	unset($_SESSION['osm_token']);
	unset($_SESSION['osm_secret']);
}

function oauth_callback() {
	global $php_self;

	if(!isset($_GET['oauth_token'])) {
		echo "Error! There is no OAuth token!";
	} elseif(!isset($_SESSION['secret'])) {
		echo "Error! There is no OAuth secret!";
	} else {
		try {
			$oauth = new OAuth(CLIENT_ID, CLIENT_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
			$oauth->setToken($_GET['oauth_token'], $_SESSION['secret']);
			$access_token_info = $oauth->getAccessToken(OSM_OAUTH_URL.'access_token');
			unset($_SESSION['secret']);

			$_SESSION['osm_token'] = strval($access_token_info['oauth_token']);
			$_SESSION['osm_secret'] = strval($access_token_info['oauth_token_secret']);
			$oauth->setToken($_SESSION['osm_token'], $_SESSION['osm_secret']);

			try {
				$oauth->fetch(OSM_API_URL.'user/details');
				$user_details = $oauth->getLastResponse();

				$xml = simplexml_load_string($user_details);       
				$_SESSION['osm_user'] = strval($xml->user['display_name']);

				$langs = array();
				foreach( $xml->user->languages->lang as $lang )
					$langs[] = strval($lang);
				$_SESSION['osm_langs'] = $langs;
			} catch(OAuthException $E) {
				// well, we don't need that
			}

			header("Location: ".$php_self.'?action=remember');
		} catch(OAuthException $E) {
			echo("<pre>Exception:\n");
			print_r($E);
			echo '</pre>';
		}
	}
	exit;
}

function oauth_upload( $comment, $data ) {
	global $messages, $error;
	if( !isset($_SESSION['osm_token']) || !isset($_SESSION['osm_secret']) ) {
		$error = _('OAuth token was lost, please relogin.');
		oauth_logout();
		return false;
	}

	try {
		$stage = 'login';
		$oauth = new OAuth(CLIENT_ID, CLIENT_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
		$oauth->setToken($_SESSION['osm_token'], $_SESSION['osm_secret']);

		$change_data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<osm>\n  <changeset>\n";
		$change_data .= '    <tag k="created_by" v="'.GENERATOR."\" />\n";
		$change_data .= '    <tag k="comment" v="'.htmlspecialchars($comment)."\" />\n";
		$change_data .= "  </changeset>\n</osm>";

		$xml_content = array('Content-Type' => 'application/xml');
		$stage = 'create';
		// note: this call works instead of returning 401 because of $xml_content
		$oauth->fetch(OSM_API_URL.'changeset/create', $change_data, OAUTH_HTTP_METHOD_PUT, $xml_content);
		if( !preg_match('/\\d+/', $oauth->getLastResponse(), $m) ) {
			$error = _('Could not aquire changeset id for a new changeset.');
			return false;
		}
		$changeset = $m[0];

		$osc = create_osc($data, $changeset);
		$stage = 'upload';
			$oauth->fetch(OSM_API_URL.'changeset/'.$changeset.'/upload', $osc, OAUTH_HTTP_METHOD_POST, $xml_content);
		// todo: parse response and renumber created objects?

		$stage = 'close';
		$oauth->fetch(OSM_API_URL.'changeset/'.$changeset.'/close', array(), OAUTH_HTTP_METHOD_PUT);
		$messages[] = sprintf(_('Changeset %d was uploaded successfully.'), $changeset);
		return true;
	} catch(OAuthException $E) {
		if( $stage == 'upload' && $E->getCode() == 409 ) {
			$error = sprintf(_('Conflict while uploading changeset %d: %s.'), $changeset, $oauth->getLastResponse());
			// todo: process conflict
			// http://wiki.openstreetmap.org/wiki/API_0.6#Error_codes_9
		} else {
			print_r($E);
			$msg = $oauth->getLastResponse();
			$error = sprintf(_('OAuth error %d at stage "%s": %s.'), $E->getCode(), $stage, $msg ? $msg : $E->getMessage());
		}
	}
	return false;
}

function parse_osm_xml( $uri ) {
	$r = new XMLReader();
	$r->open($uri, 'utf-8');
	$result = array();
	$mode = false;
	$cur = array();
	while( $r->read() ) {
		if( $r->nodeType == XMLReader::ELEMENT ) {
			if( $r->name == 'modify' || $r->name == 'create' || $r->name == 'delete' ) {
				$mode = $r->name;
			} elseif( $r->name == 'node' || $r->name == 'way' || $r->name == 'relation' ) {
				$id = $r->getAttribute('id');
				if( $id === null )
					$id = 0;
				if( is_pint($id) ) {
					$cur['type'] = $r->name;
					$cur['id'] = $id;
					$action = $mode ? $mode : $r->getAttribute('action');
					if( $action )
						$cur['action'] = $action;

					// user, uid, visible, changeset, timestamp
					$version = $r->getAttribute('version');
					$user = $r->getAttribute('user');
					$uid = $r->getAttribute('uid');
					$visible = $r->getAttribute('visible');
					$changeset = $r->getAttribute('changeset');
					$timestamp = $r->getAttribute('timestamp');
					if( is_pint($version, true) )
						$cur['version'] = $version;
					if( $user !== null && strlen($user) > 0 )
						$cur['user'] = $user;
					if( is_pint($uid, true) )
						$cur['uid'] = $uid;
					if( is_pint($changeset, true) )
						$cur['changeset'] = $changeset;
					if( $timestamp != null && strlen($timestamp) > 10 )
						$cur['timestamp'] = $timestamp; // parse?
					if( $visible === 'false' )
						$cur['deleted'] = true;

					$cur['tags'] = array();
					if( $r->name == 'node' ) {
						$lat = $r->getAttribute('lat');
						$lon = $r->getAttribute('lon');
						if( is_numeric($lat) && is_numeric($lon) ) {
							$cur['lat'] = $lat;
							$cur['lon'] = $lon;
						}
					} elseif( $r->name == 'way' )
						$cur['nodes'] = array();
					elseif( $r->name == 'relation' )
						$cur['members'] = array();
				}
			} elseif( $r->name == 'tag' ) {
				$key = $r->getAttribute('k');
				$value = $r->getAttribute('v');
				if( $key !== null && $value !== null && strlen(trim($key)) > 0 && strlen(trim($value)) > 0 ) {
					$cur['tags'][trim($key)] = trim($value);
				}
			} elseif( $r->name == 'nd' ) {
				$ref = $r->getAttribute('ref');
				if( is_pint($ref) )
					$cur['nodes'][] = $ref;
			} elseif( $r->name == 'member' ) {
				$mtype = $r->getAttribute('type');
				$mref = $r->getAttribute('ref');
				$mrole = $r->getAttribute('role');
				if( $mtype !== null && preg_match('/^(?:node|way|relation)$/', $mtype) && is_pint($mref) ) {
					$member = array('type' => $mtype, 'id' => $mref);
					$member['role'] = $mrole === null ? '' : $mrole;
					$cur['members'][] = $member;
				}
			}
		}
		if( $r->nodeType == XMLReader::END_ELEMENT || $r->isEmptyElement ) {
			if( $r->name == $mode ) {
				$mode = false;
			} elseif( isset($cur['type']) && $r->name == $cur['type'] ) {
				if( isset($cur['version']) && $cur['version'] > 1 ) {
					// if $result contains older version, remove it
					for( $i = 0; $i < count($result); $i++ ) {
						if( $result[$i]['type'] == $cur['type'] && $result[$i]['id'] == $cur['id'] && (!isset($result['version']) || $result['version'] <= $cur['version']) ) {
							array_splice($result, $i, 1);
							break;
						}
					}
				}
				$result[] = $cur;
				$cur = array();
			}
		}
	}
	$r->close();
	return renumber_created($result);
}

function is_pint( $str, $positive = false ) {
	return $str !== null && preg_match($positive ? '/^[0-9]+$/' : '/^-?[0-9]+$/', $str);
}

// Finds new objects with positive ids and replaces them to negative
function renumber_created( $data, $olddata = false ) {
	// grep existing negative keys from $olddata
	$existing = array();
	if( $olddata )
		foreach( $olddata as $obj )
			if( $obj['id'] < 0 )
				$existing[$obj['id']] = true;

	// find minimal unused negative id
	$id = -1;
	foreach( $data as $obj )
		if( $obj['id'] <= $id )
			$id = $obj['id'] - 1;
	foreach( array_keys($existing) as $obj )
		if( $obj <= $id )
			$id = $obj - 1;

	// build renumbering table
	$table = array();
	foreach( $data as &$obj ) {
		if( (isset($obj['action']) && $obj['action'] == 'create' && $obj['id'] > 0) || isset($existing[$obj['id']]) ) {
			$table[$obj['type'].$obj['id']] = $id;
			$obj['id'] = $id--;
		}
	}
	unset($obj);

	// renumber references
	if( count($table) > 0 ) {
		foreach( $data as &$obj ) {
			if( $obj['type'] == 'way' ) {
				for( $i = 0; $i < count($obj['nodes']); $i++ )
					if( isset($table['node'.$obj['nodes'][$i]]) )
						$obj['nodes'][$i] = $table['node'.$obj['nodes'][$i]];
			} elseif( $obj['type'] == 'relation' ) {
				for( $i = 0; $i < count($obj['members']); $i++ )
					if( isset($table[$obj['members'][$i]['type'].$obj['members'][$i]['id']]) )
						$obj['members'][$i]['id'] = $table[$obj['members'][$i]['type'].$obj['members'][$i]['id']];
			}
		}
		unset($obj);
	}
	return $data;
}

// Checks that $url is to OSM API, converts if otherwise
// Returns false if it cannot be converted
function url_to_api( $url ) {
	# API calls
	if( preg_match('#/api/0.6/((?:node|way|relation|changeset)/\\d+(?:/[0-9a-z]+)?)$#', $url, $m) )
		return OSM_API_URL.$m[1];
	if( preg_match('#/api/0.6/((nodes|ways|relations)?\2=\\d+.*)$#', $url, $m) )
		return OSM_API_URL.$m[1];
	if( preg_match('#/api/0.6/(map\?bbox=.*)$#', $url, $m) )
		return OSM_API_URL.$m[1];
	if( preg_match('#\.org/((?:node|way|relation)/\\d+)(?:/[a-z]+)?$#', $url, $m) )
		return OSM_API_URL.$m[1];
	if( preg_match('#\.org/(changeset/\\d+)$#', $url, $m) )
		return OSM_API_URL.$m[1].'/download';

	# List of objects
	if( preg_match('#^!?\\s*[a-y]+[/\\s]*[0-9.]+[!*]?(?:\\s*,\\s*[a-y]+[/\\s]*[0-9.]+[!*]?)*$#', $url) ) {
		$urls = array();
		$objs = explode(',', $url);
		$fullAll = substr($url, 0, 1) == '!';
		if( $fullAll )
			$url = preg_replace('/^!\\s*/', '', $url);
		foreach( $objs as $obj ) {
			if( !preg_match('/^\\s*(n|nd|node|w|wy|way|r|rel|relation|c|changeset)[\\s\\/]*(\\d+)(?:\\.(\\d+))?([!*]?)\\s*$/', $obj, $m) )
				continue;
			$t = substr($m[1], 0, 1);
			$id = $m[2];
			$version = count($m) > 3 && is_pint($m[3]) ? $m[3] : 0;
			if( $t == 'c' ) {
				$urls[] = OSM_API_URL.'changeset/'.$id.'/download';
			} else {
				if( $t == 'n' ) $type = 'node';
				elseif( $t == 'w' ) $type = 'way';
				else $type = 'relation';
				$u = OSM_API_URL.$type.'/'.$id;
				if( $version > 0 )
					$u .= '/'.$version;
				elseif( count($m) > 4 && $m[4] == '*' ) {
					if( $type == 'node' )
						$urls[] = $u.'/ways';
					$u .= '/relations';
				} elseif( $type != 'node' && ($fullAll || count($m) < 4 || $m[4] != '!') ) // (count($m) > 4 && $m[4] == '!') )
					$u .= '/full';
				$urls[] = $u;
			}
		}
		return count($urls) == 0 ? false : (count($urls) == 1 ? $urls[0] : $urls);
	}

	# Web map URL. Basically, download small bbox from the center
	$lat = false; $lon = false;

	if( preg_match('#[0-9]{1,2}/(-?[0-9]{1,2}\.[0-9]+)/(-?[0-9]{1,3}\.[0-9]+)#', $url, $m) ) {
		$lat = $m[1];
		$lon = $m[2];
	}
	if( preg_match('#lat=(-[0-9]{1,2}\.[0-9]+)#i', $url, $m) )
		$lat = $m[1];
	if( preg_match('#lon=(-[0-9]{1,3}\.[0-9]+)#i', $url, $m) )
		$lon = $m[1];

	if( $lat !== false && $lon !== false )
		return sprintf(OSM_API_URL.'map?bbox=%.5f,%.5f,%.5f,%.5f',
			$lon - BBOX_RADIUS, $lat - BBOX_RADIUS, $lon + BBOX_RADIUS, $lat + BBOX_RADIUS);

	# We're out of options: no arbitrary file downloading
	return false;
}

function create_osc( $data, $changeset = false ) {
	$now = gmdate(DATE_ISO8601);
	$osc = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<osmChange version=\"0.6\" generator=\"".GENERATOR."\">\n";
	$lastmode = '';
	foreach( $data as $obj ) {
		if( !isset($obj['action']) )
			continue;
		if( $obj['action'] != $lastmode ) {
			if( $lastmode )
				$osc .= "  </$lastmode>\n";
			$osc .= "  <".$obj['action'].">\n";
			$lastmode = $obj['action'];
		}
		$osc .= '    <'.$obj['type']." id='".$obj['id']."' version='".$obj['version']."'";
		if( $obj['type'] == 'node' && isset($obj['lat']) && isset($obj['lon']) )
			$osc .= " lat='".$obj['lat']."' lon='".$obj['lon']."'";
		if( $changeset )
			$osc .= " changeset='".$changeset."'";
		$osc .= " timestamp='".(isset($obj['timestamp']) ? $obj['timestamp'] : $now)."'";
		$osc .= ">\n";
		if( $obj['type'] == 'way' ) {
			foreach( $obj['nodes'] as $node )
				$osc .= "      <nd ref='".$node."' />\n";
		} elseif( $obj['type'] == 'relation' ) {
			foreach( $obj['members'] as $member )
				$osc .= "      <member type='".$member['type']."' ref='".$member['id']."' role='".$member['role']."' />\n";
		}
		foreach( $obj['tags'] as $k => $v ) {
			$osc .= '      <tag k=\''.htmlspecialchars($k)."' v='".htmlspecialchars($v)."' />\n";
		}
		$osc .= '    </'.$obj['type'].">\n";
	}
	if( $lastmode )
		$osc .= "  </$lastmode>\n";
	return $osc."</osmChange>";
}

function create_osm( $data ) {
	$now = gmdate(DATE_ISO8601);
	$osm = "<?xml version='1.0' encoding='UTF-8'?>\n<osm version='0.6' upload='true' generator='".GENERATOR."'>\n";
	foreach( $data as $obj ) {
		$osm .= '  <'.$obj['type']." id='".$obj['id']."' version='".$obj['version']."'";
		if( $obj['type'] == 'node' && isset($obj['lat']) && isset($obj['lon']) )
			$osm .= " lat='".$obj['lat']."' lon='".$obj['lon']."'";
		foreach( array('user', 'uid', 'changeset', 'action') as $k )
			if( isset($obj[$k]) )
				$osm .= " $k='".$obj[$k]."'";
		$osm .= " timestamp='".(isset($obj['timestamp']) ? $obj['timestamp'] : $now)."'";
		$osm .= ">\n";
		if( $obj['type'] == 'way' ) {
			foreach( $obj['nodes'] as $node )
				$osm .= "    <nd ref='".$node."' />\n";
		} elseif( $obj['type'] == 'relation' ) {
			foreach( $obj['members'] as $member )
				$osm .= "    <member type='".$member['type']."' ref='".$member['id']."' role='".$member['role']."' />\n";
		}
		foreach( $obj['tags'] as $k => $v ) {
			$osm .= '    <tag k=\''.htmlspecialchars($k)."' v='".htmlspecialchars($v)."' />\n";
		}
		$osm .= '  </'.$obj['type'].">\n";
	}
	return $osm."</osm>";
}

?>
