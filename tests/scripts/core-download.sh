#!/usr/bin/env bash

echo "travis_fold:start:core.download"

set -e

# export some generic paths
export BUILD_ROOT_DIR=$(dirname `pwd`)
export BUILD_CORE_DIR="${BUILD_ROOT_DIR}/core"
export BUILD_APPS_DIR="${BUILD_CORE_DIR}/apps"
export BUILD_APP_MODULE_DIR="${BUILD_APPS_DIR}/registration"

if [[ ${CORE_TYPE} == 'owncloud' ]]; then git clone https://github.com/owncloud/core.git --recursive --depth 1 -b ${CORE_BRANCH} ${BUILD_CORE_DIR}; fi
if [[ ${CORE_TYPE} == 'nextcloud' ]]; then git clone https://github.com/nextcloud/server.git --recursive --depth 1 -b ${CORE_BRANCH} ${BUILD_CORE_DIR}; fi

mv ${BUILD_ROOT_DIR}/registration ${BUILD_APPS_DIR}

echo "travis_fold:end:core.download"
