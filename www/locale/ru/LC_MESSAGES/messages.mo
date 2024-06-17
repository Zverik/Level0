# SOME DESCRIPTIVE TITLE.
# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER
# This file is distributed under the same license as the PACKAGE package.
# 
# Translators:
# Ilya Zverev <zverik@textual.ru>, 2014,2024
msgid ""
msgstr ""
"Project-Id-Version: Level0\n"
"Report-Msgid-Bugs-To: \n"
"POT-Creation-Date: 2024-06-17 18:23+0300\n"
"PO-Revision-Date: 2014-04-03 17:22+0000\n"
"Last-Translator: Ilya Zverev <zverik@textual.ru>, 2014,2024\n"
"Language-Team: Russian (http://app.transifex.com/openstreetmap/level0/language/ru/)\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Language: ru\n"
"Plural-Forms: nplurals=4; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<12 || n%100>14) ? 1 : n%10==0 || (n%10>=5 && n%10<=9) || (n%100>=11 && n%100<=14)? 2 : 3);\n"

#: www/core.php:173 www/level0l.php:75
msgid "There can be only one changeset metadata"
msgstr "Метаданные пакета правок могут быть только в единственном экземпляре"

#: www/core.php:185
#, php-format
msgid "No version for object %s %s."
msgstr "Нет версии у объекта \"%s %s\"."

#: www/core.php:220
#, php-format
msgid "Found older version of %s %s: %d instead of %d."
msgstr "У объекта \"%s %s\" старая версия: %d вместо %d."

#: www/core.php:263
#, php-format
msgid "Reading URL %s"
msgstr "Загружаю адрес %s"

#: www/core.php:265
#, php-format
msgid "Reading file %s"
msgstr "Загружаю файл %s"

#: www/core.php:288 www/index.php:125 www/index.php:146 www/index.php:170
#, php-format
msgid "Error preparing data: %s."
msgstr "Ошибка при подготовке данных: %s."

#: www/core.php:300
msgid "Nothing is modified, not going to update everything"
msgstr "Изменений нет, всё подряд не обновляю."

#: www/core.php:303
#, php-format
msgid "%d object was modified, can update only %d of them (repeat for more)."
msgid_plural ""
"%d objects were modified, can update only %d of them (repeat for more)."
msgstr[0] "%d объект изменён, но обновить могу только %d (повторите после)."
msgstr[1] "%d объекта изменено, но обновить могу только %d из них (повторите после)."
msgstr[2] "%d объектов изменено, но обновить могу только %d из них (повторите после)."
msgstr[3] "%d объектов изменено, но обновить могу только %d из них (повторите после)."

#: www/core.php:389
#, php-format
msgid "Duplicate ID for %s %s"
msgstr "Повторный идентификатор у объекта \"%s %s\""

#: www/core.php:395 www/level0l.php:140
#, php-format
msgid "Way %d has less than two nodes"
msgstr "У линии %d меньше двух точек"

#: www/core.php:397 www/level0l.php:142
#, php-format
msgid "Relation %d has no members"
msgstr "У отношения %d нет членов"

#: www/core.php:416
#, php-format
msgid "No base data for %s %s"
msgstr "Нет базовых данных для \"%s %s\""

#: www/index.php:74
msgid "You are already logged in."
msgstr "Вы уже вошли."

#: www/index.php:90
msgid "too big"
msgstr "слишком большой"

#: www/index.php:90
msgid "bigger than MAX_FILE_SIZE"
msgstr "больше, чем MAX_FILE_SIZE"

#: www/index.php:90
msgid "partial upload"
msgstr "частично загружен"

#: www/index.php:91
msgid "no file"
msgstr "нет файла"

#: www/index.php:91
msgid "nowhere to store"
msgstr "негде хранить"

#: www/index.php:91
msgid "failed to write"
msgstr "не удалось сохранить"

#: www/index.php:91
msgid "extension error"
msgstr "ошибка расширения"

#: www/index.php:92
#, php-format
msgid "Error uploading file: %s."
msgstr "Проблема с загрузкой файла: %s."

#: www/index.php:99
msgid "Could not parse the URL."
msgstr "Не могу распознать URL-адрес."

#: www/index.php:101
msgid "Replace with what?"
msgstr "Заменить чем?"

#: www/index.php:101
msgid "Add what?"
msgstr "Добавить что?"

#: www/index.php:144
msgid "There are severe validation errors, please fix them."
msgstr "Обнаружены серьёзные ошибки валидации, поправьте их."

#: www/index.php:148
msgid "Nothing to upload."
msgstr "Загружать нечего."

#: www/index.php:150
msgid "Please enter changeset comment."
msgstr "Не забудьте ввести комментарий к пакету правок."

#: www/level0l.php:19
msgid ""
"Conflict! Your edits to the old version are saved in this comment.\\nPlease "
"make appropriate changes and remove '!' character from the entity header."
msgstr "Конфликт! Ваши изменения старой версии сохранены в этом комментарии.\\nПожалуйста, примените эти изменения к новой версии и удалите символ '!' из заголовка."

#: www/level0l.php:66
#, php-format
msgid "Please resolve conflict of %s %s"
msgstr "Не разрешён конфликт объекта \"%s %s\""

#: www/level0l.php:71
msgid "Deleting an unsaved object"
msgstr "Удаление несохранённого объекта"

#: www/level0l.php:86
#, php-format
msgid "Coordinates specified for %s %d"
msgstr "Указаны координаты для \"%s %d\""

#: www/level0l.php:88
msgid "Node without coordinates"
msgstr "У точки нет координат"

#: www/level0l.php:97
msgid "A node cannot have member objects"
msgstr "Точка не может иметь объектов-членов"

#: www/level0l.php:102
msgid "Role name specified for a way node"
msgstr "Указана роль для точки в линии"

#: www/level0l.php:104
msgid "Ways cannot have members besides nodes"
msgstr "У линий не может быть членов, кроме точек"

#: www/level0l.php:119
msgid "Duplicated tag"
msgstr "Повторный тег"

#: www/level0l.php:121 www/level0l.php:123
#, php-format
msgid "Unknown content while parsing %s %s"
msgstr "Непонятный текст внутри объекта \"%s %s\""

#: www/level0l.php:125
msgid "Unknown and unparsed content found"
msgstr "Обнаружен непонятный необработанный текст"

#: www/osmapi.php:65
msgid "OAuth token was lost, please log in again."
msgstr "Токен OAuth потерялся — попробуйте войти ещё раз."

#: www/osmapi.php:89
msgid "Could not aquire changeset id for a new changeset."
msgstr "Не удалось получить идентификатор нового пакета правок."

#: www/osmapi.php:102
#, php-format
msgid "Conflict while uploading changeset %d: %s."
msgstr "При отправке пакета правок %d случился конфликт: %s."

#: www/osmapi.php:119
#, php-format
msgid "Changeset %d was uploaded successfully."
msgstr "Пакет правок %d отправлен на сервер."

#: www/osmapi.php:123
#, php-format
msgid "OAuth error %d at stage \"%s\": %s."
msgstr "Ошибка OAuth %d на стадии \"%s\": %s."

#: www/osmapi.php:154
msgid "Failed to open XML stream"
msgstr "Не удалось открыть источник XML"

#: www/osmapi.php:252
#, php-format
msgid "Download is incomplete, maximum of %d objects has been reached"
msgstr "Загрузка прервана, скачан максимум в %d объектов"

#: www/page.php:4 www/page.php:12
msgid "Level0 OpenStreetMap Editor"
msgstr "Level0, редактор OpenStreetMap"

#: www/page.php:19
#, php-format
msgid "...(%d message skipped)..."
msgid_plural "...(%d messages skipped)..."
msgstr[0] "...(%d сообщение пропущено)..."
msgstr[1] "...(%d сообщения пропущено)..."
msgstr[2] "...(%d сообщений пропущено)..."
msgstr[3] "...(%d сообщений пропущено)..."

#: www/page.php:28
msgid "URL or group of IDs like \"n54,w33\""
msgstr "Адрес или набор объектов, например, \"n54,w33\""

#: www/page.php:29
msgid "OSM or OSC file"
msgstr "Файл OSM или OSC"

#: www/page.php:30
msgid "Add to editor"
msgstr "Добавить в редактор"

#: www/page.php:31
msgid "Replace data in editor"
msgstr "Заменить данные"

#: www/page.php:32
msgid "Revert changes"
msgstr "Отменить правки"

#: www/page.php:33
msgid "Clear data"
msgstr "Очистить"

#: www/page.php:37
#, php-format
msgid "You're %s."
msgstr "Вы %s."

#: www/page.php:37
msgid "Log out"
msgstr "Выйти"

#: www/page.php:39
msgid "Log in"
msgstr "Войти"

#: www/page.php:41
msgid "Download .osm"
msgstr "Скачать .osm"

#: www/page.php:42
msgid "Validate"
msgstr "Обновить"

#: www/page.php:43
msgid "Check for conflicts"
msgstr "Проверить на конфликты"

#: www/page.php:44
msgid "Show osmChange"
msgstr "Показать osmChange"

#: www/page.php:46
msgid "Changeset comment"
msgstr "Комментарий к пакету правок"

#: www/page.php:47
msgid "Upload to OSM"
msgstr "Отправить в OSM"

#: www/page.php:56
msgid "Edit this area"
msgstr "Загрузить эту область"

#: www/page.php:64
#, php-format
msgid "Line %d"
msgstr "Строка %d"

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
msgstr "Содержимое osmChange (это будет отправлено на сервер)"

#: www/page.php:80
msgid "Debug"
msgstr "Отладка"
