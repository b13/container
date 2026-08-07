# Run Tests local

## Requirements

* assure running mysql and having user with create database privileges
* chromedriver installed (or selenium grid)


## Setup

    composer install
    # prepare functional tests
    # prepare acceptance tests
    mkdir -p config/system
    cd config && ln -s ../Build/sites && cd -
    cp Build/settings.php config/system
    .Build/bin/typo3 extension:setup
    # run php webserver and chromedriver
    php -S 0.0.0.0:8080 -t .Build/Web/ &
    # for TYPO3 14
    cp Build/router.php .Build/Web
    php -S 0.0.0.0:8080 -t .Build/Web/ .Build/Web/router.php &
    chromedriver --url-base=/wd/hub  --port=9515 &
    # create database with "_at" postfix
    mysql -e 'CREATE DATABASE IF NOT EXISTS foox_at;'

 ## Run tests


    php -d memory_limit=2G .Build/bin/phpunit -c Build/phpunit/UnitTests.xml Tests/Unit/
    php -d memory_limit=2G .Build/bin/phpunit -c Build/phpunit/FunctionalTests.xml Tests/Functional
    php -d memory_limit=2G .Build/bin/codecept run Backend --env=local,classic -c Tests/codeception.yml
