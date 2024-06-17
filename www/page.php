<!DOCTYPE html>
<html>
<head>
<title><?=_('Level0 OpenStreetMap Editor') ?></title>
<meta charset="utf-8">
<meta name="generator" content="<?=GENERATOR ?>">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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

<p style="font-style: italic;"><?=_('Thank you to <a href="https://opencagedata.com/">OpenCage</a> for sponsoring work on Level0!') ?></p>

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
	var init_l0_map = <?= json_encode(['center' => $center, 'zoom' => $zoom, 'force' => $center_r]) ?>;
-->
</script>

<script type="text/javascript" src="script.js"></script>
</body>
</html>
