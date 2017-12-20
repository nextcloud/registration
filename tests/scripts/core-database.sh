#!/usr/bin/env bash

echo "travis_fold:start:core.database.setup"

set -e

# MySQL
if [[ "${DB}" == 'mysql' ]]
then
    mysql -u root -e 'CREATE DATABASE oc_autotest;'
    mysql -u root -e "CREATE USER 'oc_autotest'@'localhost' IDENTIFIED BY '';"
    mysql -u root -e "GRANT ALL ON oc_autotest.* TO 'oc_autotest'@'localhost';"
fi

# Postgres
if [[ "${DB}" == 'pgsql' ]]
then
    psql -U postgres -c 'CREATE DATABASE oc_autotest;'
    psql -U postgres -c "CREATE USER oc_autotest WITH PASSWORD '';"
    psql -U postgres -c 'GRANT ALL PRIVILEGES ON DATABASE oc_autotest TO oc_autotest;'
fi

echo "travis_fold:end:core.database.setup"
