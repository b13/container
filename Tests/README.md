# Run Tests local

## Requirements

* assure running mysql and having user with create database privileges
* chromedriver installed (or selenium grid)


## Setup

    composer install
    # prepare functional tests
    cp Build/envs/.env.local .env
    # prepare acceptance tests
    mkdir config && cd config && ln -s ../Build/sites && cd -
    cp Build/LocalConfiguration.php .Build/Web/typo3conf/
    .Build/bin/typo3cms install:generatepackagestates
    .Build/bin/typo3cms database:update
    # run php webserver and chromedriver
    php -S 0.0.0.0:8888 -t .Build/Web/ &
    chromedriver --url-base=/wd/hub  &
    # create database with "_at" postfix
    mysql -e 'CREATE DATABASE IF NOT EXISTS foox_at;'

 adapt Tests/Acceptance/_envs/local.yml and/or .env if required

 ## Run tests

    .Build/bin/phpunit -c .Build/vendor/typo3/cms/typo3/sysext/core/Build/UnitTests.xml Tests/Unit/
    .Build/bin/phpunit -c .Build/vendor/typo3/cms/typo3/sysext/core/Build/FunctionalTests.xml Tests/Functional
