# EXT:container - A TYPO3 Extension for creating nested content elements

## Features
- Simple amazing containers (grids) as custom TYPO3 Content Elements
- No default containers, everything will be built the way its needed for a project
- Supports multilanguage (connected or free mode (mixed mode not supported))
- Supports workspaces
- supports the `allowed CType` Feature like EXT:content_defender for container-columns (if EXT:content_defender is installed)
- Frontend Rendering via DataProcessor and Fluid Templates

## Why did we create another "Grid" extension?

At b13 we've been long supporters and fans of gridelements, which we are thankful for and we used it in the past with great pleasure.

However, we've had our pains in the past with any common solutions we've evaluted and worked with, which, and these are our reasons:

- We wanted an extension that works with multiple versions of TYPO3 Core with the same extension, to support our company's [TYPO3 upgrade strategy](https://b13.com/solutions/typo3-upgrades).
- We wanted to overcome issues when dealing with `colPos` field and dislike any fixed value which isn't fully compatible with TYPO3 Core.
- We wanted an extension that is fully tested with multilingual and workspaces functionality.
- We wanted an extension that only does one thing: EXT:container ONLY adds tools to create and render container elements, and nothing else. No FlexForms, no permission handling or custom rendering.
- We wanted an extension where every grid has its own Content Type (CType) making it as close to Core functionality as possible.
- We wanted an extension where the configuration of a grid container element is located at one single place to make creation of custom containers easy.
- We wanted an extension that has a progressive development workflow: We were working with new projects in TYPO3 v10 sprint releases, and needed custom container elements, and did not want to wait until TYPO3 v10 LTS.

## Installation

Install this extension via `composer req b13/container` or download it from the TYPO3 Extension Repository (extension name "container"), and activate
the extension in the Extension Manager of your TYPO3 installation.

Once installed, add a custom content element to your site extension (see "Adding your own container element").

## Adding your own container element

- Register your custom container in your Extension in Configuration/TCA/Overrides/tt_content.php as new Content Type
- Add TypoScript and your Fluid Template for Frontend-Rendering
- Add an Icon in Resources/Public/Icons/<CType>.svg

see `EXT:container_example` for a simple usage of a custom container.

### Registration of Container Elements

This is an example for create a 2 column container

```php
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\B13\Container\Tca\Registry::class)->addContainer(
    'b13-2cols-with-header-container', // CType
    '2 Column Container With Header', // label
    'Some Description of the Container', // description
    [
        [
            ['name' => 'header', 'colPos' => 200, 'colspan' => 2, 'allowed' => ['CType' => 'header, textmedia']] // rowspan also supported
        ],
        [
            ['name' => 'left side', 'colPos' => 201],
            ['name' => 'right side', 'colPos' => 202]
        ]
    ], // grid configuration
    'EXT:container/Resources/Public/Icons/Extension.svg', // icon file, or existing icon identifier
    'EXT:container/Resources/Private/Templates/Container.html', // Template for Backend View
    'EXT:container/Resources/Private/Templates/Grid.html', // Template for Grid
    true, // saveAndClose for new content element wizard (v10 only)
    true // register in new content element wizard
);
```

__Notes__
- if EXT:content_defender is installed allowed-CType parameter in column Configuration can be configured to restrict allowed CTypes in a container column
- The Container Registry does multiple things:
  - Adds CType to TCA Select Items
  - Registers your Icon
  - Adds PageTSconfig for newContentElement.wizardItems
  - Sets ``showitem`` for this CType (to: `sys_language_uid,CType,tx_container_parent,colPos,hidden`)
  - Saves the Configuration in TCA in ``$GLOBALS['TCA']['tt_content']['containerConfiguration'][<CType>]`` for further usage
- We provide some default icons you can use, see `Resources/Public/Icons`
  - container-1col
  - container-2col
  - container-2col-left
  - container-2col-right
  - container-3col
  - container-4col

### TypoScript

    // default/general configuration (will add 'children_<colPos>' variable to processedData for each colPos in container
    tt_content.b13-2cols-with-header-container < lib.contentElement
    tt_content.b13-2cols-with-header-container {
        templateName = 2ColsWithHeader
        templateRootPaths {
            10 = EXT:container/Resources/Private/Contenttypes
        }
        dataProcessing {
            100 = B13\Container\DataProcessing\ContainerProcessor
        }
    }

    // if need be you can use ContainerProcessor with explicitly set colPos/variable values
    tt_content.b13-2cols-with-header-container < lib.contentElement
    tt_content.b13-2cols-with-header-container {
        templateName = 2ColsWithHeader
        templateRootPaths {
            10 = EXT:container/Resources/Private/Contenttypes
        }
        dataProcessing {
            200 = B13\Container\DataProcessing\ContainerProcessor
            200 {
                colPos = 200
                as = childrenLeft
            }
            201 = B13\Container\DataProcessing\ContainerProcessor
            201 {
                colPos = 201
                as = childrenRight
            }
        }
    }


### Template

```html
<f:for each="{children_200}" as="record">
    {record.header} <br>
    <f:format.raw>
        {record.renderedContent}
    </f:format.raw>
</f:for>

<f:for each="{children_201}" as="record">
    {record.header} <br>
    <f:format.raw>
        {record.renderedContent}
    </f:format.raw>
</f:for>
```
with explicit colPos defined use `{children<Left|Right>}` as set in the example above

## Concepts
- Complete Registration is done with one PHP call to TCA Registry
- A container in the BE Page-Module is rendered like a page itself (s. View/ContainerLayoutView)
- for BE Clipboard and Drag & Drop <tx_container_parent>_<colPos> used in the data-colpos Attribute in the wrapping CE-div Element (instead of just the colPos as in the PageLayoutView)
- The <tx_container_parent>_<colPos> parameter is resolved to `tx_container_parent` and `colPos` value in DataHandler Hooks
- When translating a container all child elements gets also translated (the child elements are not explicit listed during the translation dialog)
- Copying or moving children of a container copies or moves translations as well
- Custom definitions make use of custom `colPos` values so Site Owners build their own elements, no fixed `colPos` given, so no interference with existing solutions
- Each container type is just a definition for its own `CType`

## TODOs / Proofments
- Integrity proofment
- List module actions

## Extension Tests and Coding Guidelines

You can run our test suite for this extension yourself:

- run `composer install`
- run `Build/Scripts/runTests.sh -s unit`
- run `Build/Scripts/runTests.sh -s functional`
- run `Build/Scripts/runTests.sh -s acceptance`

s. Tests/README.md for run the tests local (like github-actions runs the tests)

and assure Coding Guidelines are fullfilled:

- run ``.Build/bin/phpstan analyse -c Resources/Private/Configuration/phpstan.neon``
- run ``.Build/bin/php-cs-fixer fix --config=.Build/vendor/typo3/coding-standards/templates/extension_php_cs.dist --dry-run --stop-on-violation --using-cache=no .``

## Credits

This extension was created by Achim Fritz in 2020 for [b13 GmbH, Stuttgart](https://b13.com).

Find examples and use cases and best practices for this extension in our [Container blog series on b13.com](https://b13.com/blog/flexible-containers-and-grids-for-typo3).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code.
