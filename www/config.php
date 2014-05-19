<?php

// OpenStreetMap OAuth parameters, see http://wiki.openstreetmap.org/wiki/OAuth
const CLIENT_ID     = '';
const CLIENT_SECRET = '';

// Just some OSM paths, if you want to switch to dev server
const OSM_OAUTH_URL	= 'http://www.openstreetmap.org/oauth/';
const OSM_API_URL	= 'http://api.openstreetmap.org/api/0.6/';

// dev
//const OSM_OAUTH_URL	= 'http://api06.dev.openstreetmap.org/oauth/';
//const OSM_API_URL	= 'http://api06.dev.openstreetmap.org/api/0.6/';

// Other settings
const BBOX_RADIUS	= 0.0003; // for downloading around a point
const MAX_REQUEST_OBJECTS = 300;
const DATA_DIR = 'data';
const TEXT_DOMAIN = 'messages';

const DEBUG = false;

?>
