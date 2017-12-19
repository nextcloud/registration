#!/usr/bin/env bash

echo "travis_fold:start:core.setup"

set -e

cd ${BUILD_CORE_DIR}

if [[ ${CORE_TYPE} == 'owncloud' ]]; then composer install -o --prefer-dist --no-suggest --no-interaction; fi

# Set up core
php -f occ maintenance:install --database-name oc_autotest --database-user oc_autotest --admin-user admin --admin-pass admin --database ${DB} --database-pass=''

# Set up app
php -f occ app:enable registration

# Enable app twice to check occ errors of registered commands
php -f occ app:enable registration

echo "travis_fold:end:core.setup"
