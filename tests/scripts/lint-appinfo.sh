#!/usr/bin/env bash

echo "travis_fold:start:lint.appinfo"

set -e

cd ${BUILD_APP_MODULE_DIR}

wget -nv https://apps.nextcloud.com/schema/apps/info.xsd
xmllint appinfo/info.xml --schema info.xsd --noout
rm info.xsd

echo "travis_fold:end:lint.appinfo"
