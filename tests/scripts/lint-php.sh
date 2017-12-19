#!/usr/bin/env bash

echo "travis_fold:start:lint.php"

set -e

cd ${BUILD_APP_MODULE_DIR}

find . -name '*.php' -type f -not -path './vendor/*' -print0 | xargs --no-run-if-empty -0 -n1 -P8 php -l -d display_errors -d display_startup_errors 1>/dev/null

echo "travis_fold:end:lint.php"
