<?php
function oauth_make() {
  $redirect = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
  return new \JBelien\OAuth2\Client\Provider\OpenStreetMap([
      'clientId'     => CLIENT_ID,
      'clientSecret' => CLIENT_SECRET,
      'redirectUri'  => $redirect.'?action=callback',
      'dev'          => strpos(OSM_API_URL, 'dev.openstreetmap') !== false
  ]);
}

function oauth_login() {
  $oauth = oauth_make();
  $options = ['scope' => 'read_prefs write_api'];
  $auth_url = $oauth->getAuthorizationUrl($options);

  $_SESSION['oauth2state'] = $oauth->getState();
  header('Location: '.$auth_url);
  exit;
}

function oauth_logout() {
	unset($_SESSION['osm_user']);
	unset($_SESSION['osm_langs']);
	unset($_SESSION['osm_token']);
}

function oauth_callback() {
	global $php_self;

	if(empty($_GET['code'])) {
		echo "Error: there is no OAuth code.";
	} elseif(empty($_SESSION['oauth2state'])) {
		echo "Error: there is no OAuth state.";
    print_r($_SESSION);
  } elseif(empty($_GET['state']) || $_GET['state'] != $_SESSION['oauth2state']) {
    echo "Error: invalid state.";
	} else {
    unset($_SESSION['oauth2state']);
		try {
      $oauth = oauth_make();
      $accessToken = $oauth->getAccessToken(
        'authorization_code', ['code' => $_GET['code']]
      );
      $_SESSION['osm_token'] = $accessToken;

      $resourceOwner = $oauth->getResourceOwner($accessToken);
      $osm_user = $resourceOwner->getDisplayName();
      $langs = $resourceOwner->getLanguages();
      $_SESSION['osm_user'] = $osm_user;
      $_SESSION['osm_langs'] = $langs;

			header("Location: ".$php_self.'?action=remember');
    } catch (Exception $e) {
			echo("<pre>Exception:\n");
			print_r($e);
			echo '</pre>';
    }
	}
	exit;
}

function oauth_upload( $comment, $data ) {
	global $messages, $error;
	if(empty($_SESSION['osm_token'])) {
		$error = _('OAuth token was lost, please log in again.');
		oauth_logout();
		return false;
	}
  $token = $_SESSION['osm_token'];

	try {
		$stage = 'login';
    $oauth = oauth_make();

		$stage = 'create';
		$xml_content = array('Content-Type' => 'application/xml');
    $ch_header = create_changeset($data, $comment);
    $response = $oauth->getResponse($oauth->getAuthenticatedRequest(
      'PUT', OSM_API_URL.'changeset/create', $token,
      ['body' => $ch_header, 'headers' => $xml_content]
    ));
    if ($response->getStatusCode() != 200) {
      $error = 'Failed to create a changeset: '.$response->getBody();
      return false;
    }

    // TODO: update everything from below.
		if( !preg_match('/\\d+/', $response->getBody(), $m) ) {
			$error = _('Could not aquire changeset id for a new changeset.');
			return false;
		}
		$changeset = $m[0];

		$stage = 'upload';
		$osc = create_osc($data, $changeset);
    $response = $oauth->getResponse($oauth->getAuthenticatedRequest(
      'POST', OSM_API_URL.'changeset/'.$changeset.'/upload', $token,
      ['body' => $osc, 'headers' => $xml_content]
    ));
    if ($response->getStatusCode() == 409) {
      $error = sprintf(
        _('Conflict while uploading changeset %d: %s.'),
        $changeset, $response->getBody());

			// todo: process conflict
			// http://wiki.openstreetmap.org/wiki/API_0.6#Error_codes_9
      return false;
    } else if ($response->getStatusCode() != 200) {
      $error = 'Failed to create a changeset: '.$response->getBody();
      return false;
    }
		// todo: parse response and renumber created objects?

		$stage = 'close';
    $oauth->getResponse($oauth->getAuthenticatedRequest(
      'PUT', OSM_API_URL.'changeset/'.$changeset.'/close', $token));
		$chlink = '<a href="https://www.openstreetmap.org/changeset/'.$changeset.'" target="_blank">'.$changeset.'</a>';
		// todo: replace %d with %s and $chlink, removing str_replace
		$messages[] = '!'.str_replace($changeset, $chlink, sprintf(_('Changeset %d was uploaded successfully.'), $changeset));
		return true;
	} catch(Exception $E) {
    print_r($E);
    $error = sprintf(_('OAuth error %d at stage "%s": %s.'), $E->getCode(), $stage, $E->getMessage());
	}
	return false;
}

function create_changeset( $data, $comment ) {
	foreach( $data as $obj ) {
		if( $obj['type'] == 'changeset' && $obj['id'] <= 0 ) {
			$chdata = $obj;
			break;
		}
	}
	if( !isset($chdata) ) {
		$chdata = array('tags' => array());
	}
	if( strlen($comment) > 0 )
		$chdata['tags']['comment'] = $comment;
	$chdata['tags']['created_by'] = GENERATOR;

	$change_data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<osm>\n  <changeset>\n";
	foreach( $chdata['tags'] as $k => $v ) {
		if (trim($v) != "") $change_data .= '    <tag k=\''.htmlspecialchars($k, ENT_QUOTES)."' v='".htmlspecialchars($v, ENT_QUOTES)."' />\n";
	}
	$change_data .= "  </changeset>\n</osm>";
	return $change_data;
}

function parse_osm_xml( $uri ) {
	global $messages;
	$r = new XMLReader();
	if( !$r->open($uri, 'utf-8') ) {
		$messages[] = _('Failed to open XML stream');
		return;
	}
	$result = array();
	$mode = false;
	$cur = array();
	while( $r->read() ) {
		if( $r->nodeType == XMLReader::ELEMENT ) {
			if( $r->name == 'modify' || $r->name == 'create' || $r->name == 'delete' ) {
				$mode = $r->name;
			} elseif( $r->name == 'node' || $r->name == 'way' || $r->name == 'relation' || $r->name == 'changeset' ) {
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
				if( $key !== null && $value !== null ) {
					$tkey = hard_trim($key);
					$tvalue = hard_trim($value);
					if( strlen($tkey) > 0 && strlen($tvalue) > 0 )
						$cur['tags'][$tkey] = $tvalue;
					if( !isset($cur['action']) && ($key !== $tkey || $value !== $tvalue) )
						$cur['action'] = 'modify';
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
		if( count($result) == MAX_REQUEST_OBJECTS ) {
			$messages[] = sprintf(_('Download is incomplete, maximum of %d objects has been reached'), MAX_REQUEST_OBJECTS);
			break;
		}
	}
	$r->close();
	return renumber_created($result);
}

function hard_trim( $str ) {
	return str_replace("\0", '', str_replace("\t", ' ', str_replace("\n", ' ', str_replace("\r", '', trim($str)))));
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
	# Remove all commas from start, end and any duplicate commas
	$url = trim(preg_replace('/,+/', ',', $url), ',');

	# API calls
	if( preg_match('#/api/0.6/((?:node|way|relation|changeset)/\\d+(?:/[0-9a-z]+)?)$#', $url, $m) )
		return OSM_API_URL.$m[1];
	if( preg_match('#/api/0.6/((nodes|ways|relations)\?\2=\\d+.*)$#', $url, $m) )
		return OSM_API_URL.$m[1];
	if( preg_match('#/api/0.6/(map\?bbox=.*)$#', $url, $m) )
		return OSM_API_URL.$m[1];
	if( preg_match('!\.org/(?:browse/)?((node|way|relation)/\\d+)(?:/[a-z]+)?(?:#.*)?$!', $url, $m) )
		return OSM_API_URL.$m[1].($m[2] == 'way' ? '/full' : '');
	if( preg_match('!\.org/(?:browse/)?(changeset/\\d+)(?:#.*)?$!', $url, $m) )
		return OSM_API_URL.$m[1].'/download';

	# Overpass API
	$overpass_re = str_replace('.', '\\.', implode('|', array(
		'overpass.osm.rambler.ru/cgi', 'overpass-api.de/api', 'api.openstreetmap.fr/oapi',
		'overpass.openstreetmap.ie/api', 'dev.overpass-api.de/[a-z0-9_]+'
	)));
	if( preg_match('!(?:'.$overpass_re.')/interpreter\?data=.+$!', $url, $m) )
		return 'http://'.$m[0];
	# Overpass API
	$overpass_re_https = str_replace('.', '\\.', implode('|', array(
		'overpass.private.coffee/api' , 'overpass.osm.jp/api', 'maps.mail.ru/osm/tools/overpass/api'
	)));
	if( preg_match('!(?:'.$overpass_re_https.')/interpreter\?data=.+$!', $url, $m) )
		return 'https://'.$m[0];


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
				} elseif( $type != 'node' && count($m) > 4 && $m[4] == '!' )
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
	$now = gmdate(DATE_ATOM);
	$osc = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<osmChange version=\"0.6\" generator=\"".GENERATOR."\">\n";
	$lastmode = '';
	foreach( $data as $obj ) {
		if( !isset($obj['action']) || $obj['type'] == 'changeset' )
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
				$osc .= "      <member type='".$member['type']."' ref='".$member['id']."' role='".htmlspecialchars($member['role'], ENT_QUOTES)."' />\n";
		}
		foreach( $obj['tags'] as $k => $v ) {
			$osc .= '      <tag k=\''.htmlspecialchars($k, ENT_QUOTES)."' v='".htmlspecialchars($v, ENT_QUOTES)."' />\n";
		}
		$osc .= '    </'.$obj['type'].">\n";
	}
	if( $lastmode )
		$osc .= "  </$lastmode>\n";
	return $osc."</osmChange>";
}

function create_osm( $data ) {
	$now = gmdate(DATE_ATOM);
	$osm = "<?xml version='1.0' encoding='UTF-8'?>\n<osm version='0.6' upload='true' generator='".GENERATOR."'>\n";
	foreach( $data as $obj ) {
		if( $obj['type'] == 'changeset' )
			continue;
		$osm .= '  <'.$obj['type']." id='".$obj['id']."' version='".$obj['version']."'";
		if( $obj['type'] == 'node' && isset($obj['lat']) && isset($obj['lon']) )
			$osm .= " lat='".$obj['lat']."' lon='".$obj['lon']."'";
		foreach( array('user', 'uid', 'changeset', 'action') as $k )
			if( isset($obj[$k]) )
				$osm .= " $k='".htmlspecialchars($obj[$k], ENT_QUOTES)."'";
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
			$osm .= '    <tag k=\''.htmlspecialchars($k, ENT_QUOTES)."' v='".htmlspecialchars($v, ENT_QUOTES)."' />\n";
		}
		$osm .= '  </'.$obj['type'].">\n";
	}
	return $osm."</osm>";
}

?>
