document.addEventListener('DOMContentLoaded', () => {

	if( document.getElementById('osmchange') )
		document.getElementById('osmchange').scrollIntoView();

	var comment_el = document.querySelector('form input[name="comment"]');
	var upload_button_el = document.querySelector('form input[name="upload"]');

	if (comment_el) {
		comment_el.addEventListener('keypress', function (e) {
			if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
				upload_button_el.click();
				return false;
			}
			return true;
		});
	}
	
	var center_el = document.querySelector('form input[name="center"]'); // input type=hidden

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
	if (init_l0_map.force || !map.restoreView()) {
		map.setView(init_l0_map.center.split(', '), init_l0_map.zoom);
	}

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
		document.querySelector('form input[name="center"]').value = map.getZoom() < 13 ? '' : getCenter(',');
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
		document.querySelector('form input[name="url"]').value = 'map=17/' + getCenter('/');
	}

	var textarea = document.querySelector('form textarea[name="data"]'),
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

		var coord2text_el = document.getElementById('coord2text');
		coord2text_el.disabled = false;
		coord2text_el.addEventListener('click', function() {
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
		});

		textarea.addEventListener('click', text2coord);
		textarea.addEventListener('keyup', text2coord);
	}
});
