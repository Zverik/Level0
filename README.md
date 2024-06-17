# Level0 OpenStreetMap Editor

This is a text-based in-browser editor for OSM data.
See [its wiki page](http://wiki.openstreetmap.org/wiki/Level0)
and [language guide](http://wiki.openstreetmap.org/wiki/Level0L).

## Installation

You will need PHP with `mod_gettext`.

* Point your `DocumentRoot` to the `www` directory.
* Run `composer install`.
* Open [this link](https://www.openstreetmap.org/oauth2/applications/new) and register
  your instance of Level0. It needs permissions for reading user details and modifying the map.
* Create `www/config.php` from `www/config.php.sample`, inserting both OAuth keys.
* Create `data` directory and give writing permissions for it to web server process.
  Check path in `config.php`. Maybe you'll also need `httpd_sys_rw_content` SELinux tag.

You're set: download some data in the editor and login to OSM.

Oh, one more thing: the database would accumulate a lot of obsolete base files.
To clean them, run `crontab -e` for a user that has write permissions to the database,
and add this line (change path accordingly):

    0 4 * * * sqlite3 /var/www/level0/data/level0.db "delete from base where created_at < datetime('now', '-1 day'); vacuum;"

## Translation

Localization strings are managed with [Transifex](https://www.transifex.com/projects/p/level0/).
There are not many of them, please add a translation for your language when you have time.

To update localizations in the code, configure [Transifex CLI](https://developers.transifex.com/docs/cli) and do `tx pull -a`

## Development

Run tests using `phpunit --display-warnings test/`

## License and authors

Level0 is written by Ilya Zverev and published under WTFPL license.

Thank you to [OpenCage](https://opencagedata.com/) for sponsoring work on Level0!
