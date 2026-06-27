#!/bin/sh

#
# Set up a composer-installed TYPO3 test instance for acceptance testing.
# Called by runTests.sh -s acceptanceComposer.
#
# Creates symlinks so that Build/composer/composer.dist.json can reference
# local paths with short names instead of deeply-nested relative URLs.
# Mirrors the approach used in the TYPO3 core's setupAcceptanceComposer.sh.
#

set -e

cd "$(dirname $(realpath $0))/../../"

PROJECT_PATH=${1:-.Build/Web/typo3temp/var/tests/acceptance-composer}
export TYPO3_DB_DRIVER=${TYPO3_DB_DRIVER:-sqlite}
TYPO3_VERSION="${TYPO3:-14}"

# Compute the relative path from PROJECT_PATH back to the project root.
# e.g. ".Build/Web/typo3temp/var/tests/acceptance-composer" -> "../../../../../../"
RELATIVE_ROOT=$(echo "${PROJECT_PATH}" | sed -e 's/[^\/][^\/]*/../g' -e 's/\/$//')

mkdir -p "${PROJECT_PATH}"

# b13-container -> project root   (provides the b13/container path repository)
# packages      -> Build/tests/packages  (provides typo3tests/dataset-import)
ln -snf "${RELATIVE_ROOT}" "${PROJECT_PATH}/b13-container"
ln -snf "${RELATIVE_ROOT}/Build/acceptance_tests/packages" "${PROJECT_PATH}/packages"
rm -f "${PROJECT_PATH}/composer.json"
ln -snf "${RELATIVE_ROOT}/Build/acceptance_tests/composer.${TYPO3_VERSION}.json" "${PROJECT_PATH}/composer.json"
rm -rf "${PROJECT_PATH}/config"
mkdir -p "${PROJECT_PATH}/config/system/"
ln -snf "${RELATIVE_ROOT}/../Build/sites" "${PROJECT_PATH}/config/sites"

cd "${PROJECT_PATH}"

rm -rf composer.lock public/ var/ vendor/

cat > "config/system/additional.php" <<\EOF
<?php
$GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = true;
// "Temporary Password - 123"
$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword'] = '$argon2i$v=19$m=65536,t=16,p=1$c3hCMGVXOHhRd0M3MzhSVw$WPQHpElapKMxsxfSkkXw5YQxGKN+rGmjM8vQv3g79YY';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = true;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = '*';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['exceptionalErrors'] = E_ALL;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['errorHandlerErrors'] = E_ALL;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';
$GLOBALS['TYPO3_CONF_VARS']['GFX']['processor'] = 'GraphicsMagick';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'mbox';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_mbox_file'] = \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/mail.mbox';
EOF


composer install --no-progress --no-interaction --dev

TYPO3_SERVER_TYPE=apache \
TYPO3_PROJECT_NAME="Container Test" \
vendor/bin/typo3 setup --force --no-interaction

echo "empty db"
vendor/bin/typo3 dataset:empty-db

echo "import fixtures"
# Import acceptance test fixtures. vendor/b13/container symlinks back to the
# project root via the b13-container path repository.
FIXTURES="vendor/b13/container/Tests/Acceptance/Fixtures"
vendor/bin/typo3 dataset:import "${FIXTURES}/be_users.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/be_groups.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/sys_workspace.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pages.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/contentDefenderMaxitems.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/contentTCASelectCtype.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/emptyPage.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithContainer.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithContainer-2.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithContainer-3.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithContainer-4.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithContainer-5.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithContainer-6.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithDifferentContainers.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithLocalization.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithLocalizationFreeModeWithContainer.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithTranslatedContainer.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithTranslatedContainer-2.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithWorkspace.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithWorkspace-movedContainer.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithWorkspace-changedContainer.csv"
vendor/bin/typo3 dataset:import "${FIXTURES}/pageWithContainerAndContentElementOutside.csv"
echo "finished"