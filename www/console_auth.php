<?php
require('config.php');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$db = new SQLite3(CONSOLE_DB);

if ($action == 'login' && isset($_REQUEST['rid']) && strlen($_REQUEST['rid']) > 8) {
  // Create a table, or remove any old tokens for rid.
  $db->exec('CREATE TABLE IF NOT EXISTS l0auth (rid STRING PRIMARY KEY, token STRING, secret STRING, when INTEGER');
  $st = $db->prepare('DELETE FROM l0auth WHERE rid = :rid');
  $st->bindValue(':rid', $_REQUEST['rid'], SQLITE3_TEXT);
  $st->execute();

  try {
    $oauth = new OAuth(CLIENT_ID, CLIENT_SECRET,
      OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
    $request_token_info = $oauth->getRequestToken(OSM_OAUTH_URL.'request_token');
    $st = $db->prepare('INSERT INTO l0auth (rid, secret) VALUES (:rid, :secret)');
    $st->bindValue(':rid', $_REQUEST['rid'], SQLITE3_TEXT);
    $st->bindValue(':token', $request_token_info['oauth_token'], SQLITE3_TEXT);
    $st->bindValue(':secret', $request_token_info['oauth_token_secret'], SQLITE3_TEXT);
    $st->execute();
    header('Location: '.OSM_OAUTH_URL."authorize?oauth_token=".
      $request_token_info['oauth_token']);
  } catch(OAuthException $E) {
    echo('OAuth error '.$E->getCode().': '.$E->getMessage());
  }
  exit;

} elseif ($action == 'check' && isset($_REQUEST['rid'])) {
  $st = $db->prepare('SELECT * FROM l0auth WHERE rid = :rid AND when IS NOT NULL');
  $st->bindValue(':rid', $_REQUEST['rid'], SQLITE3_TEXT);
  $result = $st->execute();
  $row = $result ? $result->fetchArray(SQLITE3_ASSOC) : null;
  if ($row && isset($row['rid'])) {
    if ($row['when'] >= time() - 10) {
      echo $row['token'];
      echo $row['secret'];
    }
    $st = $db->prepare('DELETE FROM l0auth WHERE rid = :rid');
    $st->bindValue(':rid', $_REQUEST['rid']);
    $st->execute();
  }
  exit;

} elseif ($action == 'callback') {
  if(!isset($_GET['oauth_token'])) {
    echo "Error! There is no OAuth token!";
    exit;
  }
  $st = $db->prepare('SELECT * FROM l0auth WHERE token = :token');
  $st->bindValue(':token', $_REQUEST['oauth_token'], SQLITE3_TEXT);
  $result = $st->execute();
  $row = $result ? $result->fetchArray(SQLITE3_ASSOC) : null;
  if (!$row || !isset($row['secret'])) {
    echo "Error! There is no OAuth secret!";
    exit;
  }
  try {
    $oauth = new OAuth(CLIENT_ID, CLIENT_SECRET,
      OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
    $oauth->setToken($_GET['oauth_token'], $row['secret']);
    $access_token_info = $oauth->getAccessToken(OSM_OAUTH_URL.'access_token');

    $st = $db->prepare('UPDATE l0auth SET token = :token, secret = :secret, when = '.time().' WHERE rid = :rid');
    $st->bindValue(':rid', $row['rid'], SQLITE3_TEXT);
    $st->bindValue(':token', strval($access_token_info['oauth_token']), SQLITE3_TEXT);
    $st->bindValue(':secret', strval($access_token_info['oauth_token_secret']), SQLITE3_TEXT);
    $st->execute();

    // TODO: close the window
    echo('Please close this window.');
  } catch(OAuthException $E) {
    echo("<pre>Exception:\n");
    print_r($E);
    echo '</pre>';
  }
  exit;
}
