#!/bin/bash

source "${11}/functions.sh"

# Remove previous dist folder.
test -e "$7/dist" && rm -rf "$7/dist"

# Initiate webpack build process.
cd "$7" && yarn && yarn build

# Verify the minified assets were built.
wp_wait_for_exists "$7/dist/clipboard.min.js"
