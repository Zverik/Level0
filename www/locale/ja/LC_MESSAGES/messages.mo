# SOME DESCRIPTIVE TITLE.
# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER
# This file is distributed under the same license as the PACKAGE package.
# 
# Translators:
# Satoshi IIDA <nyampire@gmail.com>, 2014
# Shu Higashi, 2016
msgid ""
msgstr ""
"Project-Id-Version: Level0\n"
"Report-Msgid-Bugs-To: \n"
"POT-Creation-Date: 2024-06-17 18:23+0300\n"
"PO-Revision-Date: 2014-04-03 17:22+0000\n"
"Last-Translator: Shu Higashi, 2016\n"
"Language-Team: Japanese (http://app.transifex.com/openstreetmap/level0/language/ja/)\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Language: ja\n"
"Plural-Forms: nplurals=1; plural=0;\n"

#: www/core.php:173 www/level0l.php:75
msgid "There can be only one changeset metadata"
msgstr "変更セットのメタデータは複数指定できません"

#: www/core.php:185
#, php-format
msgid "No version for object %s %s."
msgstr "オブジェクト %s %s のバージョンがありません"

#: www/core.php:220
#, php-format
msgid "Found older version of %s %s: %d instead of %d."
msgstr "%d ではなく、より古いバージョン %s %s: %d があります。"

#: www/core.php:263
#, php-format
msgid "Reading URL %s"
msgstr "URL %s を読み込み中"

#: www/core.php:265
#, php-format
msgid "Reading file %s"
msgstr "ファイル %s を読み込み中"

#: www/core.php:288 www/index.php:125 www/index.php:146 www/index.php:170
#, php-format
msgid "Error preparing data: %s."
msgstr "データ %s 処理中にエラー発生"

#: www/core.php:300
msgid "Nothing is modified, not going to update everything"
msgstr "変更点が無いため、更新は行いません"

#: www/core.php:303
#, php-format
msgid "%d object was modified, can update only %d of them (repeat for more)."
msgid_plural ""
"%d objects were modified, can update only %d of them (repeat for more)."
msgstr[0] "%d オブジェクトが変更されました。そのうち %d オブジェクトだけがアップロード可能です (何度か繰り返してください)"

#: www/core.php:389
#, php-format
msgid "Duplicate ID for %s %s"
msgstr "%s %s でID重複"

#: www/core.php:395 www/level0l.php:140
#, php-format
msgid "Way %d has less than two nodes"
msgstr "ウェイ %d の所属ノードが1つ以下"

#: www/core.php:397 www/level0l.php:142
#, php-format
msgid "Relation %d has no members"
msgstr "リレーション %d に所属メンバーなし"

#: www/core.php:416
#, php-format
msgid "No base data for %s %s"
msgstr "%s %s にベースデータ無し"

#: www/index.php:74
msgid "You are already logged in."
msgstr ""

#: www/index.php:90
msgid "too big"
msgstr "大きすぎます"

#: www/index.php:90
msgid "bigger than MAX_FILE_SIZE"
msgstr "MAX_FILE_SIZE より大きいファイルです"

#: www/index.php:90
msgid "partial upload"
msgstr "部分アップロード"

#: www/index.php:91
msgid "no file"
msgstr "ファイルがありません"

#: www/index.php:91
msgid "nowhere to store"
msgstr "格納場所がありません"

#: www/index.php:91
msgid "failed to write"
msgstr "書き込み失敗"

#: www/index.php:91
msgid "extension error"
msgstr "エクステンションエラー"

#: www/index.php:92
#, php-format
msgid "Error uploading file: %s."
msgstr "ファイル %s アップロード中にエラー発生"

#: www/index.php:99
msgid "Could not parse the URL."
msgstr "URLをパースできません"

#: www/index.php:101
msgid "Replace with what?"
msgstr "置き換え用データは？"

#: www/index.php:101
msgid "Add what?"
msgstr "追加データは？"

#: www/index.php:144
msgid "There are severe validation errors, please fix them."
msgstr "妥当性検証エラーあり。修正ください。"

#: www/index.php:148
msgid "Nothing to upload."
msgstr "アップロードするデータがありません。"

#: www/index.php:150
msgid "Please enter changeset comment."
msgstr "変更セットコメントを記載してください"

#: www/level0l.php:19
msgid ""
"Conflict! Your edits to the old version are saved in this comment.\\nPlease "
"make appropriate changes and remove '!' character from the entity header."
msgstr "競合が発生しました！ あなたの編集内容はコメント部分に格納されています。\\n内容を適切に編集し、エンティティ行の先頭部分にある \"!\" を除去してください。"

#: www/level0l.php:66
#, php-format
msgid "Please resolve conflict of %s %s"
msgstr "%s %sの競合を解決してください"

#: www/level0l.php:71
msgid "Deleting an unsaved object"
msgstr "保存していないオブジェクトを削除"

#: www/level0l.php:86
#, php-format
msgid "Coordinates specified for %s %d"
msgstr "%s %dの座標指定"

#: www/level0l.php:88
msgid "Node without coordinates"
msgstr "ノードに座標情報がない"

#: www/level0l.php:97
msgid "A node cannot have member objects"
msgstr "ノードにはメンバーを含めることができません"

#: www/level0l.php:102
msgid "Role name specified for a way node"
msgstr "ウェイ内ノードへのロール名称指定"

#: www/level0l.php:104
msgid "Ways cannot have members besides nodes"
msgstr "ウェイにはノード以外のメンバーを含めることができません"

#: www/level0l.php:119
msgid "Duplicated tag"
msgstr "タグ重複"

#: www/level0l.php:121 www/level0l.php:123
#, php-format
msgid "Unknown content while parsing %s %s"
msgstr "%s %sのパース時に処理できないコンテンツあり"

#: www/level0l.php:125
msgid "Unknown and unparsed content found"
msgstr "パース不可のため、処理できなかったコンテンツあり"

#: www/osmapi.php:65
msgid "OAuth token was lost, please log in again."
msgstr ""

#: www/osmapi.php:89
msgid "Could not aquire changeset id for a new changeset."
msgstr "新しい変更セットに対して、変更セットIDを取得できませんでした"

#: www/osmapi.php:102
#, php-format
msgid "Conflict while uploading changeset %d: %s."
msgstr "変更セット %d: %sをアップロード中に競合発生"

#: www/osmapi.php:119
#, php-format
msgid "Changeset %d was uploaded successfully."
msgstr "変更セット %d のアップロード成功"

#: www/osmapi.php:123
#, php-format
msgid "OAuth error %d at stage \"%s\": %s."
msgstr "ステージ \"%s\": %s を処理中にOAuthエラー %d 発生"

#: www/osmapi.php:154
msgid "Failed to open XML stream"
msgstr "XMLストリームのオープンに失敗"

#: www/osmapi.php:252
#, php-format
msgid "Download is incomplete, maximum of %d objects has been reached"
msgstr "ダウンロードしていないオブジェクトがあり、最大 %d オブジェクトまで対応しています"

#: www/page.php:4 www/page.php:12
msgid "Level0 OpenStreetMap Editor"
msgstr "Level0 OpenStreetMap エディタ"

#: www/page.php:19
#, php-format
msgid "...(%d message skipped)..."
msgid_plural "...(%d messages skipped)..."
msgstr[0] "...(%d メッセージがスキップされました)..."

#: www/page.php:28
msgid "URL or group of IDs like \"n54,w33\""
msgstr "URL、あるいは \"n54,w33\" 形式のIDグループ"

#: www/page.php:29
msgid "OSM or OSC file"
msgstr "OSMあるいはOSCファイル"

#: www/page.php:30
msgid "Add to editor"
msgstr "エディタに追加"

#: www/page.php:31
msgid "Replace data in editor"
msgstr "エディタ内データを置換"

#: www/page.php:32
msgid "Revert changes"
msgstr "変更取り消し"

#: www/page.php:33
msgid "Clear data"
msgstr "データ消去"

#: www/page.php:37
#, php-format
msgid "You're %s."
msgstr "アカウント %s."

#: www/page.php:37
msgid "Log out"
msgstr "ログアウト"

#: www/page.php:39
msgid "Log in"
msgstr "ログイン"

#: www/page.php:41
msgid "Download .osm"
msgstr ".osmファイルをダウンロード"

#: www/page.php:42
msgid "Validate"
msgstr "妥当性検証"

#: www/page.php:43
msgid "Check for conflicts"
msgstr "競合チェック"

#: www/page.php:44
msgid "Show osmChange"
msgstr "osmChangeを表示"

#: www/page.php:46
msgid "Changeset comment"
msgstr "変更セットコメント"

#: www/page.php:47
msgid "Upload to OSM"
msgstr "OSMへアップロード"

#: www/page.php:56
msgid "Edit this area"
msgstr "この地域を編集"

#: www/page.php:64
#, php-format
msgid "Line %d"
msgstr "%d 行"

#: www/page.php:67
msgid ""
"Useful links: <a href=\"https://wiki.openstreetmap.org/wiki/Level0\">about "
"this editor</a>, <a "
"href=\"https://wiki.openstreetmap.org/wiki/Level0L\">language reference</a>,"
" <a href=\"https://wiki.openstreetmap.org/wiki/Map_Features\">tag "
"reference</a>, <a href=\"https://taginfo.openstreetmap.org/\">tag "
"statistics</a>."
msgstr ""

#: www/page.php:72
msgid ""
"Thank you to <a href=\"https://opencagedata.com/\">OpenCage</a> for "
"sponsoring work on Level0!"
msgstr ""

#: www/page.php:75
msgid "OsmChange contents (this will be uploaded to the server)"
msgstr "OsmChangeコンテンツ (サーバにアップロードされます)"

#: www/page.php:80
msgid "Debug"
msgstr "デバッグ"
