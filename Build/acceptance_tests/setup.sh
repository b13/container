#!/bin/sh

set -e

export TYPO3_DB_DBNAME="bar"
export TYPO3_DB_USERNAME="dev"
export TYPO3_DB_PASSWORD="dev"
export TYPO3_DB_HOST="127.0.0.1"
export TYPO3_DB_DRIVER=mysqli
export TYPO3_DB_PORT=3306
export TYPO3_SERVER_TYPE=apache
export TYPO3_PROJECT_NAME="Container Test"
TYPO3_VERSION="${TYPO3:-14}"
RELATIVE_ROOT="../../"
PROJECT_PATH="."

echo "drop database if exists ${TYPO3_DB_DBNAME};" |mysql
echo "create database ${TYPO3_DB_DBNAME} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" |mysql


ln -snf "${RELATIVE_ROOT}" "${PROJECT_PATH}/b13-container"

rm -rf composer.lock config/ public/ var/ vendor/

mkdir -p "config/system/"
ln -snf "${RELATIVE_ROOT}sites" "${PROJECT_PATH}/config/sites"
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

cp "composer.${TYPO3_VERSION}.json" composer.json

# `composer require` implicitly performs an initial install since there is no composer.lock.
composer install --no-progress --no-interaction --dev
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