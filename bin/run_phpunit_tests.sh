#!/usr/bin/env bash
s="${BASH_SOURCE[0]}";[[ "$s" ]] || s="${(%):-%N}";while [ -h "$s" ];do d="$(cd -P "$(dirname "$s")" && pwd)";s="$(readlink "$s")";[[ $s != /* ]] && s="$d/$s";done;__DIR__=$(cd -P "$(dirname "$s")" && pwd)

# ========= Begin Configutation =========
PHP="$(command -v php)"
#PHP=/opt/homebrew/opt/php@8.5/bin/php

# Paths should be relative to THIS file:
INSTALL_PATH="../tests_phpunit/"
CONFIG="../tests_phpunit/phpunit.xml"
VENDOR="../vendor/"
# ========= End Configuration =========

# ========= Validation =========
[[ -z "$INSTALL_PATH" ]] && echo "❌️ \$INSTALL_PATH cannot be empty" && exit 3
INSTALL_PATH="$(cd "$__DIR__/$INSTALL_PATH" && pwd)"
CONFIG="$__DIR__/$CONFIG"
VENDOR="$(cd "$__DIR__/$VENDOR" && pwd)"
[[ -z "$VENDOR" ]] && echo "❌️ \$VENDOR cannot be empty" && exit 4
[[ ! -d  "$VENDOR" ]] && echo "❌️ \"$VENDOR\" does not exist; check the \$VENDOR variable in $0" && exit 5
[[ ! -f $VENDOR/bin/phpunit ]] && echo "❌️ missing dependencies; try \`composer install\`" && echo && exit 6

# ========= Internal config =========
# shellcheck disable=SC2034
coverage_reports="$INSTALL_PATH/reports"

export INSTALL_PATH

# ========= Execute PHPUnit =========
"$PHP" "$VENDOR/bin/phpunit" -c "$CONFIG" "$@"
#"$PHP" "$VENDOR/bin/phpunit" -c "$CONFIG" --testdox "$@"
#export XDEBUG_MODE=$XDEBUG_MODE,coverage;"$PHP" "$VENDOR/bin/phpunit" -c "$CONFIG" --coverage-html="$coverage_reports" "$@"
#echo "$coverage_reports/index.html"
