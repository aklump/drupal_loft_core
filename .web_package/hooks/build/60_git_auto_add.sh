#!/usr/bin/env bash

#
# @file
# Automatically add certain generated files to git during build
#
git=$(type git >/dev/null 2>&1 && which git)
if [ "$git" ]; then
    # Note to support symlinks, we should cd first (per git).
    (cd $7/docs && git add .)
    (cd $7/help && git add .)
fi
