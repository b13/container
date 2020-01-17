# EXT:container

## Features
- simple amazing containers (grids) as TYPO3 CE
- supports multilanguage (conntected or free mode (mixed mode not supported))
- supports workspaces
- supports the `àllowed CType` Feature like EXT:content_defender for container-columns (if EXT:content_defender is installed)
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
        'container_example', // extKey
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
- you should provide an Icon in `Resources/Public/Icons/<CType>.svg`
- this registry do
  - add CType to TCA Select Items
  - register your Icon (s. above)
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
                as = childsLeft
            }
            101 = B13\Container\DataProcessing\ContainerProcessor
            101 {
                colPos = 101
                as = childsRight
            }
        }
    }


### Template

    <f:for each="{childsLeft}" as="record">
        {record.header} <br />
        <f:format.raw>
            {record.renderedContent}
        </f:format.raw>
    </f:for>

    <f:for each="{childsRight}" as="record">
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
- copy or move childs of a container copies or moves translations also

## TODOs / Proofments
- integrity proofment
- list modlue actions

## Extension Tests
- run `composer install && composer require typo3/cms-workspaces:^10.0` (we do not want want EXT:workspace as Project Dependency (but needed for Tests))
- run `Build/Scripts/runTests.sh -s unit`
- run `Build/Scripts/runTests.sh -s functional`
- run `Build/Scripts/runTests.sh -s acceptance`

