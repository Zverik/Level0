# SOME DESCRIPTIVE TITLE.
# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER
# This file is distributed under the same license as the PACKAGE package.
# 
# Translators:
# Supaplex <bejokeup@gmail.com>, 2014
msgid ""
msgstr ""
"Project-Id-Version: Level0\n"
"Report-Msgid-Bugs-To: \n"
"POT-Creation-Date: 2024-06-17 18:23+0300\n"
"PO-Revision-Date: 2014-04-03 17:22+0000\n"
"Last-Translator: Supaplex <bejokeup@gmail.com>, 2014\n"
"Language-Team: Chinese (Taiwan) (http://app.transifex.com/openstreetmap/level0/language/zh_TW/)\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Language: zh_TW\n"
"Plural-Forms: nplurals=1; plural=0;\n"

#: www/core.php:173 www/level0l.php:75
msgid "There can be only one changeset metadata"
msgstr "只可以有一個編輯變動 metadata"

#: www/core.php:185
#, php-format
msgid "No version for object %s %s."
msgstr "找不到物件 %s %s 的版本訊息。"

#: www/core.php:220
#, php-format
msgid "Found older version of %s %s: %d instead of %d."
msgstr "找到 %s 的舊版本，用 %s:%d 代替 %d。  "

#: www/core.php:263
#, php-format
msgid "Reading URL %s"
msgstr "載入網址 %s"

#: www/core.php:265
#, php-format
msgid "Reading file %s"
msgstr "載入檔案 %s"

#: www/core.php:288 www/index.php:125 www/index.php:146 www/index.php:170
#, php-format
msgid "Error preparing data: %s."
msgstr "載入資料發生錯誤： %s 。"

#: www/core.php:300
msgid "Nothing is modified, not going to update everything"
msgstr "沒有物件更動，將不會上傳任何東西。"

#: www/core.php:303
#, php-format
msgid "%d object was modified, can update only %d of them (repeat for more)."
msgid_plural ""
"%d objects were modified, can update only %d of them (repeat for more)."
msgstr[0] "%d 物件變動，只能上傳 %d 的部分 (請再重覆操作幾次)"

#: www/core.php:389
#, php-format
msgid "Duplicate ID for %s %s"
msgstr "%s %s  ID重覆"

#: www/core.php:395 www/level0l.php:140
#, php-format
msgid "Way %d has less than two nodes"
msgstr "路徑 %d 擁有節點少於兩個"

#: www/core.php:397 www/level0l.php:142
#, php-format
msgid "Relation %d has no members"
msgstr "關係 %d 並沒有成員"

#: www/core.php:416
#, php-format
msgid "No base data for %s %s"
msgstr "%s %s 並沒有資料"

#: www/index.php:74
msgid "You are already logged in."
msgstr ""

#: www/index.php:90
msgid "too big"
msgstr "太大了"

#: www/index.php:90
msgid "bigger than MAX_FILE_SIZE"
msgstr "大於 MAX_FILE_SIZE"

#: www/index.php:90
msgid "partial upload"
msgstr "部分上傳"

#: www/index.php:91
msgid "no file"
msgstr "並沒有檔案"

#: www/index.php:91
msgid "nowhere to store"
msgstr "沒有地方儲存"

#: www/index.php:91
msgid "failed to write"
msgstr "寫入失敗"

#: www/index.php:91
msgid "extension error"
msgstr "延伸錯誤"

#: www/index.php:92
#, php-format
msgid "Error uploading file: %s."
msgstr "上傳檔案： %s 發生錯誤"

#: www/index.php:99
msgid "Could not parse the URL."
msgstr "無法解析網址。"

#: www/index.php:101
msgid "Replace with what?"
msgstr "用什麼資料取代？"

#: www/index.php:101
msgid "Add what?"
msgstr "然後呢？"

#: www/index.php:144
msgid "There are severe validation errors, please fix them."
msgstr "請修正多個驗證錯誤"

#: www/index.php:148
msgid "Nothing to upload."
msgstr "沒有東西可上傳"

#: www/index.php:150
msgid "Please enter changeset comment."
msgstr "請輸入編輯變動註解"

#: www/level0l.php:19
msgid ""
"Conflict! Your edits to the old version are saved in this comment.\\nPlease "
"make appropriate changes and remove '!' character from the entity header."
msgstr "編輯衝突！你這次編輯舊的版本。\\n 請適當修改後把開頭的 \"!\" 驚嘆號移除。"

#: www/level0l.php:66
#, php-format
msgid "Please resolve conflict of %s %s"
msgstr "請解決編輯衝突 %s %s"

#: www/level0l.php:71
msgid "Deleting an unsaved object"
msgstr "刪除未儲存物件"

#: www/level0l.php:86
#, php-format
msgid "Coordinates specified for %s %d"
msgstr "%s %d 的座標"

#: www/level0l.php:88
msgid "Node without coordinates"
msgstr "沒有座標的節點"

#: www/level0l.php:97
msgid "A node cannot have member objects"
msgstr "節點無法擁有成員物件"

#: www/level0l.php:102
msgid "Role name specified for a way node"
msgstr "請指定路徑中節點的成員角色"

#: www/level0l.php:104
msgid "Ways cannot have members besides nodes"
msgstr "路徑無法擁有節點以外的成員"

#: www/level0l.php:119
msgid "Duplicated tag"
msgstr "重覆標籤"

#: www/level0l.php:121 www/level0l.php:123
#, php-format
msgid "Unknown content while parsing %s %s"
msgstr "解析 %s %s 時發現不知名內容"

#: www/level0l.php:125
msgid "Unknown and unparsed content found"
msgstr "找到不知名和未解析的內容"

#: www/osmapi.php:65
msgid "OAuth token was lost, please log in again."
msgstr ""

#: www/osmapi.php:89
msgid "Could not aquire changeset id for a new changeset."
msgstr "無法替編輯變動取得新的編輯變動編輯"

#: www/osmapi.php:102
#, php-format
msgid "Conflict while uploading changeset %d: %s."
msgstr "上傳編輯變動 %d: %s 時發生編輯衝突。"

#: www/osmapi.php:119
#, php-format
msgid "Changeset %d was uploaded successfully."
msgstr "編輯變動 %d 成功上傳"

#: www/osmapi.php:123
#, php-format
msgid "OAuth error %d at stage \"%s\": %s."
msgstr "階段 \"%s\": %s 時 OAuth 錯誤 %d。"

#: www/osmapi.php:154
msgid "Failed to open XML stream"
msgstr "無法開啟 XML stream"

#: www/osmapi.php:252
#, php-format
msgid "Download is incomplete, maximum of %d objects has been reached"
msgstr "下載不完全，目前最多下載 %d 的物件"

#: www/page.php:4 www/page.php:12
msgid "Level0 OpenStreetMap Editor"
msgstr "Level0 開放街圖編輯器"

#: www/page.php:19
#, php-format
msgid "...(%d message skipped)..."
msgid_plural "...(%d messages skipped)..."
msgstr[0] "...(%d 訊息跳過)..."

#: www/page.php:28
msgid "URL or group of IDs like \"n54,w33\""
msgstr "網址或是群組編號 \"n54,w33\""

#: www/page.php:29
msgid "OSM or OSC file"
msgstr "OSM 或是 OSC 檔案"

#: www/page.php:30
msgid "Add to editor"
msgstr "添加至編輯器"

#: www/page.php:31
msgid "Replace data in editor"
msgstr "取代編輯器中資料"

#: www/page.php:32
msgid "Revert changes"
msgstr "回復編輯變動"

#: www/page.php:33
msgid "Clear data"
msgstr "清除資料"

#: www/page.php:37
#, php-format
msgid "You're %s."
msgstr "你是 %s。"

#: www/page.php:37
msgid "Log out"
msgstr "登出"

#: www/page.php:39
msgid "Log in"
msgstr "登入"

#: www/page.php:41
msgid "Download .osm"
msgstr "下載 .osm 檔案"

#: www/page.php:42
msgid "Validate"
msgstr "驗證"

#: www/page.php:43
msgid "Check for conflicts"
msgstr "檢查是否有衝突"

#: www/page.php:44
msgid "Show osmChange"
msgstr "顯示 osmChange"

#: www/page.php:46
msgid "Changeset comment"
msgstr "編輯變動註解"

#: www/page.php:47
msgid "Upload to OSM"
msgstr "上傳至 OSM"

#: www/page.php:56
msgid "Edit this area"
msgstr "編輯這個區域"

#: www/page.php:64
#, php-format
msgid "Line %d"
msgstr "路徑 %d"

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
msgstr "OsmChange 內容 (這將會上傳到伺服器)"

#: www/page.php:80
msgid "Debug"
msgstr "除錯"
