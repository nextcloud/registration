#!/usr/bin/env bash

echo "travis_fold:start:app.check-code"

set -e

php -f ${BUILD_CORE_DIR}/occ app:check-code registration

echo "travis_fold:end:app.check-code"
