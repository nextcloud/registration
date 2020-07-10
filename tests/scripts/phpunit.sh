#!/usr/bin/env bash

echo "travis_fold:start:phpunit"

set -e

cd ${BUILD_APP_MODULE_DIR}

composer install --prefer-dist --no-interaction -o
vendor/bin/phpunit -c tests/phpunit.xml

echo "travis_fold:end:phpunit"
