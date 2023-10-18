#!/bin/bash

# Remove previous dist folder.
test -e "./dist" && rm -rf "./dist"

# Initiate webpack build process.
yarn && yarn build

# Verify the minified assets were built.
wp_wait_for_exists "./dist/clipboard.min.js"
