<?php
require('config.php');
require('osmapi.php');
require('core.php');

// Constants and session management
const GENERATOR = 'Level0 v1.0';
$php_self = htmlentities(substr($_SERVER['PHP_SELF'], 0,  strcspn($_SERVER['PHP_SELF'], "\n\r")), ENT_QUOTES);
header('Content-type: text/html; charset=utf-8');
ini_set('session.gc_maxlifetime', 7776000);
ini_set('session.cookie_lifetime', 7776000);
session_set_cookie_params(7776000);
session_start();

// Determine the locale
$directory = dirname(__FILE__).'/locale';
if( isset($_REQUEST['lang']) && preg_match('/^[a-z]{2,3}[A-Z-_]*$/', $_REQUEST['lang']) )
	$_SESSION['lang'] = $_REQUEST['lang'];
$locale = isset($_SESSION['lang']) ? array($_SESSION['lang']) : array();
if( isset($_SESSION['osm_langs']) && is_array($_SESSION['osm_langs']) )
	$locale = array_merge($locale, $_SESSION['osm_langs']);
if( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('/^[a-z_,;0-9=.-]+$/i', $_SERVER['HTTP_ACCEPT_LANGUAGE']) )
	$locale = array_merge($locale, preg_replace('/;.+$/', '', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])));
$locale = preg_replace('/^([a-z]{2})-([A-Z]+)/', '$1_$2', $locale);
$locale[] = 'en_US';

// Expand two-letter locales to fully specified
$default_locales = array('en_US', 'ru_RU', 'de_DE', 'ja_JP', 'it_IT', 'hr_HR', 'fr_FR', 'uk_UA', 'vi_VN');
$loclist = array();
foreach( $default_locales as $dl )
	$loclist[] = '/^'.substr($dl, 0, 2).'$/';
$locale = preg_replace($loclist, $default_locales, $locale);

// Finally, setlocale
setlocale(LC_MESSAGES, $locale);
bindtextdomain(TEXT_DOMAIN, $directory);
bind_textdomain_codeset(TEXT_DOMAIN, 'UTF-8');
textdomain(TEXT_DOMAIN);

// Generate an (reasonably) unique identifier for the session
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if( !isset($_REQUEST['l0id']) && $action == 'remember' && isset($_SESSION['l0id']) ) {
	$l0id = $_SESSION['l0id'];
	unset($_SESSION['l0id']);
	$text = read_user(); // removes the file
}
if( !isset($l0id) || strlen($l0id) == 0 )
	$l0id = isset($_REQUEST['l0id']) && preg_match('/^\\d{1,10}$/', $_REQUEST['l0id']) ? $_REQUEST['l0id'] : mt_rand(1000, 9999999);

// Check logged in user
$user = isset($_SESSION['osm_user']) ? $_SESSION['osm_user'] : false;
$loggedin = isset($_SESSION['osm_token']);

// Read edited data
if( !isset($text) || !$text )
	$text = isset($_REQUEST['data']) ? $_REQUEST['data'] : '';

// Generate $basedata and $userdata arrays
$error = false;
$messages = array();
$validation = array(); // of (severe?, line, description)
read_base();
parse_text($text);

// Now process actions
if( $action == 'login' || isset($_REQUEST['login']) ) {
	if( $loggedin )
		$error = _('Yor are already logged in.');
	else {
		if( count($userdata) || count($basedata) ) {
			$_SESSION['l0id'] = $l0id;
			store_user($text);
		}
		oauth_login();
	}
} elseif( $action == 'callback' ) {
	oauth_callback();
} elseif( $action == 'logout' || isset($_REQUEST['logout']) ) {
	oauth_logout();
	$loggedin = false;
} elseif( isset($_REQUEST['add']) || isset($_REQUEST['replace']) || (isset($_REQUEST['url']) && strlen($_REQUEST['url']) > 0) ) {
	if( isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name']) ) {
		if( $_FILES['file']['error'] > 0 ) {
			$errors = array('OK', _('too big'), _('bigger than MAX_FILE_SIZE'), _('partial upload'),
				_('no file'), '', _('nowhere to store'), _('failed to write'), _('extension error'));
			$error = sprintf(_('Error uploading file: %s.'), $errors[$_FILES['file']['error']]);
		} else {
			$url = $_FILES['file']['tmp_name'];
		}
	} elseif( isset($_REQUEST['url']) && strlen($_REQUEST['url']) > 0 ) {
		$url = url_to_api($_REQUEST['url']);
		if( $url === false )
			$error = _('Could not parse the URL.');
	} else {
		$error = isset($_REQUEST['replace']) ? _('Replace with what?') : _('Add what?');
	}
	if( isset($url) && $url ) {
		$validation = array();
		update_data_array(is_array($url) ? $url : array($url));
	}
} elseif( isset($_REQUEST['clear']) ) {
	clear_data();
	$text = '';
} elseif( isset($_REQUEST['revert']) ) {
	$text = revert();
	$validation = array();
} elseif( isset($_REQUEST['download']) ) {
	$e = prepare_export();
	if( is_array($e) ) {
		$osm = create_osm($e);
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header('Content-Type: application/x-openstreetmap+xml');
		header('Content-Disposition: attachment; filename=level0_export.osm');
		header('Content-Length: '.mb_strlen($osm, '8bit'));
		echo $osm;
		exit;
	} else
		$error = sprintf(_('Error preparing data: %s.'), $e);
} elseif( isset($_REQUEST['upload']) ) {
	$e = prepare_export();
	$severe = false;
	foreach( $validation as $v )
		if( $v[0] )
			$severe = true;
	$empty_ = true;
	if( is_array($e) ) {
		foreach( $e as $obj ) {
			if( isset($obj['action']) ) {
				$empty_ = false;
				break;
			}
		}
	}
	if( $severe )
		$error = _('There are severe validation errors, please fix them.');
	elseif( !is_array($e) )
		$error = sprintf(_('Error preparing data: %s.'), $e);
	elseif( $empty_ )
		$error = _('Nothing to upload.');
	elseif( !isset($_REQUEST['comment']) || strlen(trim($_REQUEST['comment'])) == 0 )
		$error = _('Please enter changeset comment.');
	else {
		if( oauth_upload(trim($_REQUEST['comment']), $e) ) {
			clear_data();
			$text = '';
			$validation = array();
		}
		$loggedin = isset($_SESSION['osm_token']);
	}
} elseif( isset($_REQUEST['check']) ) {
	update_modified();
}

// This is for when DEBUG constant is true
function print_debug() {
	global $basedata, $userdata;
	$e = prepare_export();
	if( is_array($e) ) {
		echo htmlspecialchars(create_osc($e, 1234));
//		echo "\n\n";
//		echo htmlspecialchars(create_osm($e));
	} else
		echo htmlspecialchars(sprintf(_('Error preparing data: %s.'), $e));
	echo "\n\n\$basedata = ";
	print_r($basedata);
	echo "\n\$userdata = ";
	print_r($userdata);
}

// Restore map parameters
$center = calculate_center();
$center_r = false;
if( !$center && isset($_REQUEST['center']) && preg_match('/^-?\\d{1,2}(?:\\.\\d+)?,-?\\d{1,3}(?:\\.\\d+)?$/', $_REQUEST['center']) ) {
	$center = explode(',', $_REQUEST['center']);
	$center_r = true;
}
$zoom = $center ? 17 : ($center_r ? 15 : 2);
if( !$center )
	$center = array(30, 0);

require('page.php');
?>
