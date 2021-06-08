<!DOCTYPE html>
<html>
<head>
<title><?=_('Level0 OpenStreetMap Editor') ?></title>
<meta charset="utf-8">
<meta name="generator" content="<?=GENERATOR ?>">
<link rel="stylesheet" href="https://unpkg.com/leaflet@0.7.7/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@0.7.7/dist/leaflet.js"></script>
<style>body { font-family: sans-serif; font-size: 11pt; }</style>
</head>
<body>
<h2><?=_('Level0 OpenStreetMap Editor') ?></h2>
<?php if( $error ): ?>
<p style="color: red;"><?=htmlspecialchars($error) ?></p>
<?php endif ?>
<p style="font-size: 10pt;"><?php
if( count($messages) > 10 ) {
	$cnt6 = count($messages) - 8;
	array_splice($messages, 4, $cnt6, sprintf(ngettext('...(%d message skipped)...', '...(%d messages skipped)...', $cnt6), $cnt6));
}
foreach( $messages as $message ) { echo (strlen($message) > 1 && substr($message, 0, 1) == '!' ? substr($message, 1) : htmlspecialchars($message)).'<br>'; }
?></p>

<form action="<?=$php_self ?>" method="post" name="f" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="500000" />
<input type="hidden" name="l0id" value="<?=$l0id ?>" />

<p><?=_('URL or group of IDs like "n54,w33"') ?>: <input type="text" name="url" size="60"><br>
<?=_('OSM or OSC file') ?>: <input type="file" name="file">
<input type="submit" name="add" value="<?=_('Add to editor') ?>" style="margin-left: 30px;">
<input type="submit" name="replace" value="<?=_('Replace data in editor') ?>">
<input type="submit" name="revert" value="<?=_('Revert changes') ?>">
<input type="submit" name="clear" value="<?=_('Clear data') ?>"></p>

<p>
<?php if( $loggedin ): ?>
<?php if( $user ) echo sprintf(_("You're %s."), $user).' ' ?><input type="submit" name="logout" value="<?=_('Log out') ?>">
<?php else: ?>
<input type="submit" name="login" value="<?=_('Log in') ?>">
<?php endif ?>
<input type="submit" name="download" value="<?=_('Download .osm') ?>">
<input type="submit" name="save" value="<?=_('Validate') ?>">
<input type="submit" name="check" value="<?=_('Check for conflicts') ?>">
<input type="submit" name="showosc" value="<?=_('Show osmChange') ?>">
<?php if( $loggedin ): ?>
<br><?=_('Changeset comment') ?>: <input type="text" name="comment" size="60" maxlength="255" value="<?php if(isset($_REQUEST['comment'])) echo htmlspecialchars($_REQUEST['comment']); ?>">
<input type="submit" name="upload" value="<?=_('Upload to OSM') ?>">
<?php endif ?>
</p>

<div style="float: right; width: 350px;">
<input type="hidden" name="center" value="<?=implode(',', $center) ?>">
<div><input type="button" id="coord2text" disabled="disabled" value="←">
<input type="text" style="width: 200px;" id="coords"></div>
<div id="map" style="width: 350px; height: 400px; margin: 1em 0;"></div>
<div><input type="button" id="downarea" disabled="disabled" value="<?=_('Edit this area') ?>"></div>
</div>

<div style="margin-right: 370px;"><textarea name="data" style="width: 100%; height: 500px; font-family: monospace;"><?php echo htmlspecialchars($text) ?></textarea></div>

</form>

<?php foreach( $validation as $v ): ?>
<p style="margin: 0; color: <?=$v[0] ? 'red' : 'blue' ?>"><?=sprintf(_('Line %d'), $v[1]) ?>: <?=htmlspecialchars($v[2]) ?></p>
<?php endforeach ?>

<p><?=_('Useful links: <a href="https://wiki.openstreetmap.org/wiki/Level0">about this editor</a>, '.
'<a href="https://wiki.openstreetmap.org/wiki/Level0L">language reference</a>, '.
'<a href="https://wiki.openstreetmap.org/wiki/Map_Features">tag reference</a>, '.
'<a href="https://taginfo.openstreetmap.org/">tag statistics</a>.') ?></p>

<?php if( strlen($osccontent) > 0 ): ?>
<h2 id="osmchange"><?=_('OsmChange contents (this will be uploaded to the server)') ?></h2>
<pre>
<?php echo htmlspecialchars($osccontent) ?>
</pre>
<?php if( DEBUG ): ?>
<h2><?=_('Debug') ?></h2>
<pre>
<?php
	echo "\$basedata = ";
	print_r($basedata);
	echo "\n\$userdata = ";
	print_r($userdata);
?>
</pre>
<?php endif ?>
</div>
<?php endif ?>

<script type="text/javascript">
//<!--
if( document.getElementById('osmchange') )
	document.getElementById('osmchange').scrollIntoView();

if( 'comment' in document.forms['f'].elements ) {
	L.DomEvent.on(document.forms['f'].elements['comment'], 'keypress', function(e) {
		if( (e.which && e.which == 13) || (e.keyCode && e.keyCode == 13) ) {
			document.forms['f'].elements['upload'].click();
			return false;
		}
		return true;
	});
}

// Copy-pasted from https://github.com/makinacorpus/Leaflet.RestoreView (MIT license)
var RestoreViewMixin = {
    restoreView: function () {
        var storage = window.localStorage || {};
        if (!this.__initRestore) {
            this.on('moveend', function (e) {
                if (!this._loaded)
                    return;  // Never access map bounds if view is not set.

                var view = {
                    lat: this.getCenter().lat,
                    lng: this.getCenter().lng,
                    zoom: this.getZoom()
                };
                storage['mapView'] = JSON.stringify(view);
            }, this);
            this.__initRestore = true;
        }

        var view = storage['mapView'];
        try {
            view = JSON.parse(view || '');
            this.setView(L.latLng(view.lat, view.lng), view.zoom, true);
            return true;
        }
        catch (err) {
            return false;
        }
    }
};
L.Map.include(RestoreViewMixin);

var map = L.map('map');
<?php if( !$center_r ) { ?>if( !map.restoreView() )<?php } ?>
map.setView([<?=implode(', ', $center) ?>], <?=$zoom ?>);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',
	{ attribution: 'Map &copy; <a href="https://www.openstreetmap.org">OpenStreetMap contributors</a>' }).addTo(map);
var marker = L.marker(map.getCenter(), { draggable: true }).addTo(map);
var ways = L.layerGroup().addTo(map);

function checkZoom() {
	document.getElementById('downarea').disabled = map.getZoom() < 15;
}
map.on('moveend', checkZoom);
checkZoom();

marker.on('dragend', function() {
	map.panTo(marker.getLatLng());
});

function getCenter( delimiter ) {
	return L.Util.formatNum(map.getCenter().lat, 6) + delimiter + L.Util.formatNum(map.getCenter().lng, 6);
}

function updateCoords() {
	document.getElementById('coords').value = getCenter(', ');
	document.forms['f'].elements['center'].value = map.getZoom() < 13 ? '' : getCenter(',');
}

marker.on('move dragend', updateCoords);
updateCoords();

map.on('drag zoomend', function() {
	marker.setLatLng(map.getCenter());
});

function setCenter(latlng) {
	map.panTo(latlng);
	marker.setLatLng(latlng);
}

document.getElementById('downarea').onclick = function() {
	document.forms['f'].elements['url'].value = 'map=17/' + getCenter('/');
}

var textarea = document.forms['f'].elements['data'],
	headerRE = /^!?-?(node|way|relation)(?:\s+(-?\d+))?(?:\.\d+)?(?:\s*:\s*(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?))?\s*(?:#.*)?$/,
	nodeSetRE = /^(!?-?node(?:\s+(-?\d+))?\s*)(\s*:\s*)?(-?\d{1,2}(?:\.\d+)?\s*,\s*-?\d{1,3}(?:\.\d+)?)?(\s*#.*)?\s*$/,
	ndRE = /^\s*nd\s+(-?\d+)\s*$/,
	memberRE = /^\s*(nd|wy|rel)\s+(-?\d+)/;

function findNodeCoords( lines, id ) {
	var i, m, re = /^!?-?node\s+(-?\d+)(?:\.\d+)?\s*:\s*(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*(?:#.*)?$/;
	for( i = 0; i < lines.length; i++ ) {
		m = re.exec(lines[i]);
		if( m && m[1] == id )
			return [+m[2], +m[3]];
	}
	return false;
}

if( 'selectionStart' in textarea ) {
	function text2coord(event, memberObjectRow, highlight) {
		if( memberObjectRow === undefined ) ways.clearLayers();
		var lines = textarea.value.split('\n'),
			selectionRow = textarea.value.substr(0, textarea.selectionStart).split('\n').length - 1,
			row = memberObjectRow === undefined ? selectionRow : memberObjectRow;

		if( row < lines.length ) {
			var headerRow = row;
			while( headerRow >= 0 && !headerRE.test(lines[headerRow]) )
				headerRow--;
			if( row === headerRow ) row++;
			if( headerRow >= 0 ) {
				var header = headerRE.exec(lines[headerRow]);
				if( header[1] == 'node' ) {
					if( header[3] !== '' && header[4] !== '' )
						setCenter([+header[3], +header[4]]);
				} else if( header[1] == 'way' ) {
					var nodeRow = row, nd = null;
					while( nodeRow < lines.length && !headerRE.test(lines[nodeRow]) ) {
						nd = ndRE.exec(lines[nodeRow]);
						if( nd !== null )
							break;
						nodeRow++;
					}
					if( nd !== null ) {
						// find relevant node coords and put marker
						var coords = findNodeCoords(lines, nd[1]);
						if( coords ) {
							if( highlight !== false )
								setCenter(coords);
							// build array of nodes and draw line
							var wayRow = headerRow + 1, nodes = [];
							while( wayRow < lines.length && !headerRE.test(lines[wayRow]) ) {
								nd = ndRE.exec(lines[wayRow++]);
								if( nd !== null ) {
									coords = findNodeCoords(lines, nd[1]);
									if( coords )
										nodes.push(coords);
									else {
										if( nodes.length >= 2 )
											ways.addLayer(L.polyline(nodes));
										if( nodes.length )
											nodes = [];
									}
								}
							}
							if( nodes.length >= 2 )
								ways.addLayer(L.polyline(nodes, { color: highlight ? '#f30' : '#03f' }));
						}
					}
				} /*else if( header[1] == 'relation' ) {
					var memberRow = headerRow+1, member = null;
					for( ; memberRow < lines.length && !headerRE.test(lines[memberRow]) ; memberRow++ ) {
						member = memberRE.exec(lines[memberRow]);
						if( member === null ) continue;
						if( member[1] === 'nd' ) {
							var coords = findNodeCoords(lines, member[2]);
							if( coords ) {
								var nodeMemberMarker = L.marker(coords);
								ways.addLayer(nodeMemberMarker);
								if( selectionRow === memberRow ) {
									setCenter(coords);
									nodeMemberMarker._icon.style.filter = "hue-rotate(180deg)";
									nodeMemberMarker._icon.style.webkitFilter = "hue-rotate(180deg)";
								}
							}
						} else if( member[1] === 'wy' ) {
							var memberHeaderRow = textarea.value.substr(0, textarea.value.search(new RegExp('^!?-?way\\s+' + member[2], 'm'))).split('\n').length - 1;
							text2coord(event, memberHeaderRow, selectionRow === memberRow);
						} else {
							// nested relations –> ??
						}
					}
					// hack: hide way-node marker by placing it somewhere far away
					marker.setLatLng([0, -999]);
                               } */
			}
		}
	}

	document.getElementById('coord2text').disabled = false;
	document.getElementById('coord2text').onclick = function() {
		ways.clearLayers();
		var lines = textarea.value.split('\n'),
			row = textarea.value.substr(0, textarea.selectionStart).split('\n').length - 1,
			coords = document.getElementById('coords').value
		if( coords != '' && row < lines.length ) {
			var headerRow = row, m;
			while( headerRow >= 0 && !headerRE.test(lines[headerRow]) )
				headerRow--;
			if( headerRow >= 0 ) {
				var header = headerRE.exec(lines[headerRow]);
				if( header[1] == 'node' ) {
					m = nodeSetRE.exec(lines[headerRow]);
					if( m ) {
						lines[headerRow] = m[1] + (m[3] ? m[3] : ': ') + coords + (m[5] || '');
						var ss = textarea.selectionStart;
						textarea.value = lines.join('\n');
						textarea.setSelectionRange(ss, ss);
					}
				} else if( header[1] == 'way' ) {
					var nd = ndRE.exec(lines[row]);
					if( nd !== null ) {
						for( var i = 0; i < lines.length; i++ ) {
							m = nodeSetRE.exec(lines[i]);
							if( m && m[2] == nd[1] ) {
								lines[i] = m[1] + (m[3] ? m[3] : ': ') + coords + (m[5] || '');
								var ss = textarea.selectionStart;
								textarea.value = lines.join('\n');
								textarea.setSelectionRange(ss, ss);
								break;
							}
						}
					}
				}
			}
		}
	};

	L.DomEvent.on(textarea, 'click', text2coord);
	L.DomEvent.on(textarea, 'keyup', text2coord);
}
-->
</script>
</body>
</html>
