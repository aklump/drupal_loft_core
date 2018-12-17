#!/usr/bin/env bash
#
# @file Do a composer update
#
# You should include composer1.sh as well so the note prints before the delay begins.
/Applications/MAMP/bin/php/php5.6.32/bin/php  /Users/aklump/bin/composer install
composer dumpautoload --optimize
