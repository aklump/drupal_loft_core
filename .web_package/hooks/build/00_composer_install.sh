#!/usr/bin/env bash
/Applications/MAMP/bin/php/php5.6.40/bin/php /Users/aklump/bin/composer install --no-dev && composer dumpautoload --optimize && git add composer.lock || build_fail_exception
