#!/usr/bin/env bash
rm -r vendor || build_fail_exception
rm composer.lock || build_fail_exception
