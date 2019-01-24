#!/usr/bin/env bash
/Applications/MAMP/bin/php/php5.6.32/bin/php /Users/aklump/bin/composer install --no-dev && composer dumpautoload --optimize && git add composer.lock && git add vendor || build_fail_exception
