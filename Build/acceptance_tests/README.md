### composer acceptance tests

    cd Build/acceptance_tests
    ./setup_local.sh
    ./start_env.sh
    php -d memory_limit=2G .Build/bin/codecept run Backend --env=local,localheadless,composer -c Tests/codeception.yml --fail-fast
    php -d memory_limit=2G .Build/bin/codecept run Backend --env=local,localheadless,composer -c Tests/codeception.yml Tests/Acceptance/Backend/PageTsConfigModuleCest.php
    ./stop_env.sh