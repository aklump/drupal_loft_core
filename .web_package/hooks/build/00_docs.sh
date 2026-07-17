#!/usr/bin/env bash
# @file Compile documentation using Knowledge

know="/Applications/MAMP/bin/php/php7.4.33/bin/php /Users/aklump/Code/Packages/php/knowledge/app/bin/book.php"
if ! $know bind ./knowledge; then
  echo 'You must install https://github.com/aklump/knowledge to compile documentation' && exit 1
fi
