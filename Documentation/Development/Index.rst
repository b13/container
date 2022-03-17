.. include:: /Includes.rst.txt

===========
Development
===========

.. contents:: Table of Contents:
   :backlinks: top
   :class: compact-list
   :depth: 1
   :local:

Coding Guidelines
=================

To assure coding guidelines are fullfilled:

- run :bash:`.Build/bin/phpstan analyse -c Build/phpstan10.neon`
- run :bash:`.Build/bin/php-cs-fixer fix --config=Build/php_cs.php --dry-run --stop-on-violation --using-cache=no`

Tests
=====

You can run our test suite for this extension yourself:

- run :bash:`composer install`
- run :bash:`Build/Scripts/runTests.sh -s unit`
- run :bash:`Build/Scripts/runTests.sh -s functional`
- run :bash:`Build/Scripts/runTests.sh -s acceptance`

See Tests/README.md how to run the tests local (like github-actions runs the tests).

Concepts
========

-  Complete registration is done with one PHP call to TCA Registry
-  A container in the TYPO3 backend Page module is rendered like a page itself
   (see View/ContainerLayoutView)
-  For backend clipboard and drag & drop `<tx_container_parent>_<colPos>` used
   in the data-colpos attribute in the wrapping CE-div Element (instead of just
   the colPos as in the PageLayoutView)
-  The `<tx_container_parent>_<colPos>` parameter is resolved to
   `tx_container_parent` and `colPos` value in DataHandler hooks
-  When translating a container, all child elements get also translated
   (the child elements are not explicit listed during the translation dialog)
-  Copying or moving children of a container copies or moves translations as
   well
-  Custom definitions make use of custom `colPos` values so site owners build
   their own elements, no fixed `colPos` given, so no interference with existing
   solutions
-  Each container type is just a definition for its own `CType`

Todos
=====

-  Proof of integrity
-  List module actions
