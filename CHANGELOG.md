# Level0 Change History

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
