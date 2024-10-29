# Run Tests local

## Requirements

* assure running mysql and having user with create database privileges
* chromedriver installed (or selenium grid)


## Setup

    composer install
    # prepare functional tests
    # prepare acceptance tests
    composer require helhum/dotenv-connector
    cp Build/envs/.env.local .env
    mkdir -p config/system
    cd config && ln -s ../Build/sites && cd -
    cp Build/settings.php config/system
    # 11
    cp Build/LocalConfiguration.php .Build/Web/typo3conf/
    .Build/bin/typo3 extension:setup
    # run php webserver and chromedriver
    php -S 0.0.0.0:8888 -t .Build/Web/ &
    chromedriver --url-base=/wd/hub  --port=9515 &
    # create database with "_at" postfix
    mysql -e 'CREATE DATABASE IF NOT EXISTS foox_at;'

 ## Run tests


    .Build/bin/phpunit -c Build/phpunit/UnitTests.xml Tests/Unit/
    .Build/bin/phpunit -c Build/phpunit/FunctionalTests.xml Tests/Functional
    .Build/bin/codecept run Backend --env=local,classic -c Tests/codeception.yml
