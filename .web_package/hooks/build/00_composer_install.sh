#!/usr/bin/env bash
composer dumpautoload --optimize && git add composer.lock || build_fail_exception
