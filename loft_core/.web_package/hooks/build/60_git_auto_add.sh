#!/usr/bin/env bash

#
# @file
# Automatically add certain generated files to git during build
#
git=$(type git >/dev/null 2>&1 && which git)
if [ "$git" ]; then
    # Note to support symlinks, we should cd first (per git).
    (cd ./docs && git add .)
    (cd ./help && git add .)
fi
