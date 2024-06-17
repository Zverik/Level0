#!/bin/sh
xgettext --from-code=UTF-8 -o messages.pot -L php www/*.php
tx push
