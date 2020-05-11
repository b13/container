# EXT:container

## Features
- simple amazing containers (grids) as TYPO3 CE
- supports multilanguage (conntected or free mode (mixed mode not supported))
- supports workspaces
- supports the `allowed CType` Feature like EXT:content_defender for container-columns (if EXT:content_defender is installed)
- Frontend Rendering via DataProcessor


## Configuration
- Register your Container in your Extension in Configuration/TCA/Overrides/tt_content.php as new CType
- add TypoScript and Template for Frontend-Rendering
- add an Icon in Resources/Public/Icons/<CType>.svg
- s. EXT:container_example for a simple usage

### Registration of Container Elements

    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\B13\Container\Tca\Registry::class)->registerContainer(
        'b13-2cols-with-header-container', // CType
        '2 Column Container With Header', // label
        'Some Description of the Container', // description
        'EXT:container/Resources/Public/Icons/Extension.svg', // icon file, or existing icon identifier
        [
            [
                ['name' => 'header', 'colPos' => 200, 'colspan' => 2, 'allowed' => ['CType' => 'header, textmedia']] // rowspan also supported
            ],
            [
                ['name' => 'left side', 'colPos' => 201],
                ['name' => 'right side', 'colPos' => 202]
            ]
        ] // grid
        'EXT:container/Resources/Private/Templates/Container.html' // Template for Backend View
        'EXT:container/Resources/Private/Templates/Grid.html' // Template for Grid
        true // register in new content element wizard
    );

__Notes__
- if EXT:content_defender ist installed allowed-CType Parameter in column Configuration cat be configured to restrict allowed CTypes in a container column
- this registry do
  - add CType to TCA Select Items
  - register your Icon
  - adds page TS for newContentElement.wizardItems
  - saves the Configuration in TCA in ``$GLOBALS['TCA']['tt_content']['containerConfiguration'][<CType>]`` for further usage

### TypoScript

    tt_content.2Cols < lib.contentElement
    tt_content.2Cols {
        templateName = 2Cols
        templateRootPaths {
            10 = EXT:container/Resources/Private/Contenttypes
        }
        dataProcessing {
            100 = B13\Container\DataProcessing\ContainerProcessor
            100 {
                colPos = 100
                as = childrenLeft
            }
            101 = B13\Container\DataProcessing\ContainerProcessor
            101 {
                colPos = 101
                as = childrenRight
            }
        }
    }


### Template

    <f:for each="{childrenLeft}" as="record">
        {record.header} <br />
        <f:format.raw>
            {record.renderedContent}
        </f:format.raw>
    </f:for>

    <f:for each="{childrenRight}" as="record">
        {record.header} <br />
        <f:format.raw>
            {record.renderedContent}
        </f:format.raw>
    </f:for>

## Concepts
- Complete Registration is done with one Call to TCA-Registry
- a container in the BE Page-Module is rendered like a page itselfs (s. View/ContainerLayoutView)
- for BE Clipboard and Drag & Drop <tx_container_parent>_<colPos> use used in the data-colpos Attribute in the wrapping CE-div Element (instead of just the colPos as in the PageLayoutView)
- the <tx_container_parent>_<colPos> Parameter ist resolved to tx_container_parent and colPos Value in Datahandler Hooks
- when translate a container all child Elements gets also translated (the child Elements are not explicit listed during the Translation-Dialog)
- copying or moving children of a container copies or moves translations as well

## TODOs / Proofments
- integrity proofment
- list modlue actions

## Extension Tests
- run `composer install`
- run `Build/Scripts/runTests.sh -s unit`
- run `Build/Scripts/runTests.sh -s functional`
- run `Build/Scripts/runTests.sh -s acceptance`

## Credits

This extension was created by Achim Fritz in 2020 for [b13 GmbH, Stuttgart](https://b13.com).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code..

