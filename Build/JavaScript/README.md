
build v13

    git clone https://github.com/typo3/typo3.git
    git checkout 13.1
    cp EXT:container/Build/JavaScript/js-13.patch .
    patch -p1 < js-13.patch
    cd Build
    nvm use v18.20.1 # v22.2.0 for main branch
    npm ci
    node_modules/grunt/bin/grunt scripts
    cp JavaScript/backend/layout-module/* EXT:container/Resources/Public/JavaScript/Overrides/


build patch file

    git format-patch  13.4 --stdout > js-13.patch

build with docker

    cd Build
    COMMAND="cd /Build; npm ci || exit 1; node_modules/grunt/bin/grunt scripts"
    docker run -v ${PWD}:/Build -it ghcr.io/typo3/core-testing-nodejs18:1.4 /bin/sh -c "$COMMAND"
