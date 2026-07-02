#!/bin/bash

cp ../router.php public
php -S 0.0.0.0:8080 -t public/ public/router.php & echo $! > var/php_pid
chromedriver --url-base=/wd/hub  --port=9515 & echo $! > var/chromedriver_pid