# Level0 Change History

## master

* Fixed user names with quotes.
* OAuth2 support.
* Data is now stored in a SQLite database instead of files.
* Unit tests (thanks @mtmail).

## 1.2, 9.02.2016

* Removed "follow cursor" and "text2coord" buttons.
* Added support for obsolete `/browse` osm.org links.
* Changeset number is now a link. [#14](https://github.com/Zverik/Level0/issues/14)
* Made messages a bit smaller.
* Changeset comment is preserved when using other buttons. [#18](https://github.com/Zverik/Level0/pull/18) (by @jgpacker)
* Pressing "Enter" in the changeset comment field now uploads changes. [#20](https://github.com/Zverik/Level0/issues/20)
* Deletion and addition of relation members did not register as changes. [#22](https://github.com/Zverik/Level0/issues/22)
* Fixed deletion order, so no conflicts occur. [#27](https://github.com/Zverik/Level0/issues/27)
* Relations are visualized, with referenced members highlighed. [#15](https://github.com/Zverik/Level0/issues/15)  (by @tyrasd)
* Map state is kept between sessions. [#16](https://github.com/Zverik/Level0/issues/16)

## 1.1, 20.05.2014

* Fixed parsing object links with hashes from osm.org. [#4](https://github.com/Zverik/Level0/issues/4)
* Enforced object limit for downloading.
* Overpass API links are now allowed. [#1](https://github.com/Zverik/Level0/issues/1)
* Tabs and newlines are now replaced with spaces on download. [#9](https://github.com/Zverik/Level0/issues/9)
* When displayed key / value differs from downloaded, the object is marked as modified. [#10](https://github.com/Zverik/Level0/issues/10)
* Now skipping some messages when there are more than 10.
* Fixed processing `\=` in Level0l. [#8](https://github.com/Zverik/Level0/issues/8)
* Ways are now drawn in the map view even when the cursor is not on a node reference. [#2](https://github.com/Zverik/Level0/issues/2)
* Language strings for nodes containing identifiers were rewritten to use `%s` instead of `%d`.
* "Show osmChange" button. [#11](https://github.com/Zverik/Level0/issues/11)
* Stream opening errors are now properly reported.
* Editing changeset tags is now possible. [#12](https://github.com/Zverik/Level0/issues/12), [#7](https://github.com/Zverik/Level0/issues/7)

## 1.0, 27.04.2014

Initial release
