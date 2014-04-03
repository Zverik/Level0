<?php

function data_to_l0l( $data ) {
	usort($data, 'userdata_cmp');
	$str = '';
	$needNewline = false;
	$tmpid = 0;
	foreach( $data as $obj ) {
		if( !isset($obj['type']) || !isset($obj['id']) ) {
			echo "fail object $tmpid: ";
			print_r($obj);
		}
		$tmpid++;
		if( $needNewline || $obj['type'] != 'node' )
			$str .= "\n";
		$needNewline = $obj['type'] != 'node' || count($obj['tags']) > 0 || isset($obj['conflict']);
		if( isset($obj['conflict']) ) {
			$conflict = data_to_l0l(array($obj['conflict']));
			$conflict = _('Conflict! Your edits to the old version are saved in this comment.\nPlease make appropriate changes and remove \'!\' character from the entity header.')."\n".$conflict;
			$conflict = preg_replace('/^/m', '# ', $conflict);
			$str .= $conflict.'!';
		}
		if( isset($obj['action']) && $obj['action'] == 'delete' )
			$str .= '-';
		$str .= $obj['type'];
		if( $obj['id'] != 0 ) {
			$str .= ' '.$obj['id'];
			if( isset($obj['version']) )
				$str .= '.'.$obj['version'];
		}
		if( $obj['type'] == 'node' && isset($obj['lat']) && isset($obj['lon']) )
			$str .= ': '.$obj['lat'].', '.$obj['lon'];
		$str .= "\n";
		foreach( $obj['tags'] as $k => $v ) {
			$str .= sprintf("  %s = %s\n", str_replace('=', '\\=', $k), $v);
		}
		if( $obj['type'] == 'way' && count($obj['nodes']) > 0 )
			foreach( $obj['nodes'] as $nd )
				$str .= '  nd '.$nd."\n";
		if( $obj['type'] == 'relation' && count($obj['members']) > 0 )
			foreach( $obj['members'] as $m )
				$str .= sprintf("  %s %d%s\n", $m['type'] == 'node' ? 'nd' : ($m['type'] == 'way' ? 'wy' : 'rel'), $m['id'], strlen($m['role']) > 0 ? ' '.$m['role'] : '');
	}
	return $str;
}

// Parses level0l into $userdata
function l0l_to_data( $str ) {
	global $validation;
	$lines = explode("\n", $str);
	$data = array();
	$cur = false;
	$ln = 0;
	foreach( $lines as $line ) {
		$ln++;
		if( !strlen(trim($line)) || substr($line, 0, 1) == '#' )
			continue;
		if( preg_match('/^(!)?(-)?(node|way|relation)(?:\\s+(-?[0-9]+)(?:\\.([0-9]+))?)?(?:\\s*:\\s*(-?\\d{1,2}(?:\.\\d+)?)\\s*,\\s*(-?\\d{1,3}(?:\\.\\d+)?))?\\s*(?:#.*)?$/', $line, $m) ) {
			if( $cur ) {
				validate_entity($cur, $ln - 1);
				$data[] = $cur;
			}
			$cur = array('type' => $m[3], 'id' => (count($m) > 4 && strlen($m[4]) > 0 ? $m[4] : 0));
			if( $m[1] === '!' )
				$validation[] = array(true, $ln, sprintf(_('Please resolve conflict of %s %d'), $cur['type'], $cur['id']));
			if( $m[2] === '-' ) {
				if( $cur['id'] > 0 )
					$cur['action'] = 'delete';
				else
					$validation[] = array(true, $ln, _('Deleting an unsaved object'));
			}
			if( count($m) > 5 && strlen($m[5]) > 0 )
				$cur['version'] = $m[5];
			if( count($m) > 7 && strlen($m[6]) > 0 && strlen($m[7]) > 0 ) {
				if( $cur['type'] == 'node' ) {
					$cur['lat'] = $m[6];
					$cur['lon'] = $m[7];
				} else
					$validation[] = array(false, $ln, sprintf(_('Coordinates specified for %s %d'), $cur['type'], $cur['id']));
			} elseif( $cur['type'] == 'node' && $m[2] !== '-' )
				$validation[] = array(true, $ln, _('Node without coordinates'));
			$cur['tags'] = array();
			if( $cur['type'] == 'way' )
				$cur['nodes'] = array();
			elseif( $cur['type'] == 'relation' )
				$cur['members'] = array();
		} elseif( $cur ) {
			if( preg_match('/^\\s*(nd|wy|rel)\\s+(-?\\d+)(?:\\s+(.+?))?\\s*$/', $line, $m) ) {
				if( $cur['type'] == 'node' ) {
					$validation[] = array(true, $ln, _('A node cannot have member objects'));
				} elseif( $cur['type'] == 'way' ) {
					if( $m[1] == 'nd' ) {
						$cur['nodes'][] = $m[2];
						if( count($m) > 3 && strlen($m[3]) > 0 )
							$validation[] = array(false, $ln, _('Role name specified for a way node'));
					} else
						$validation[] = array(true, $ln, _('Ways cannot have members besides nodes'));
				} elseif( $cur['type'] == 'relation' ) {
					$cur['members'][] = array('type' => ($m[1] == 'nd' ? 'node' : ($m[1] == 'wy' ? 'way' : 'relation')), 'id' => $m[2], 'role' => (count($m) > 3 ? $m[3] : ''));
				}
			} elseif( preg_match('/^\\s*([^=]*?(?:\\\\=[^=]*?)*)\\s*=\\s*(.+?)\\s*$/', $line, $m) ) {
				if( !isset($cur['tags'][$m[1]]) )
					$cur['tags'][$m[1]] = $m[2];
				else
					$validation[] = array(true, $ln, _('Duplicated tag'));
			} else
				$validation[] = array(false, $ln, sprintf(_('Unknown content while parsing %s %d'), $cur['type'], $cur['id']));
		} else
			$validation[] = array(false, $ln, _('Unknown and unparsed content found'));
	}
	if( $cur ) {
		validate_entity($cur, $ln);
		$data[] = $cur;
	}
	return $data;
}

function validate_entity( $obj, $ln ) {
	global $validation;
	$is_new = $obj['id'] <= 0;
	$is_deleted = isset($obj['action']) && $obj['action'] == 'delete';
	if( $is_new || !$is_deleted ) {
		if( $obj['type'] == 'way' && count($obj['nodes']) < 2 )
			$validation[] = array(true, $ln, sprintf(_('Way %d has less than two nodes'), $obj['id']));
		elseif( $obj['type'] == 'relation' && !count($obj['members']) )
			$validation[] = array(true, $ln, sprintf(_('Relation %d has no members'), $obj['id']));
	}
}

// Merges changes into l0l text
function l0l_merge( $str, $added, $modified ) {
	$str = data_to_l0l($added)."\n".$str;
	// todo: modify modified
	return $str;
}

// a function to be used in usort($userdata, 'userdata_cmp')
function userdata_cmp($a, $b) {
	$typea = $a['type'] == 'node' ? (count($a['tags']) ? 0 : 4) : ($a['type'] == 'way' ? 1 : 2);
	$typeb = $b['type'] == 'node' ? (count($b['tags']) ? 0 : 4) : ($b['type'] == 'way' ? 1 : 2);
	if( $typea < $typeb ) return -1;
	if( $typea > $typeb ) return 1;
	$idd = $a['id'] - $b['id'];
	return $idd > 0 ? 1 : ($idd < 0 ? -1 : 0);
}

?>
