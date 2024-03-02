![CI](https://github.com/b13/container/actions/workflows/ci.yml/badge.svg)

# EXT:container - A TYPO3 Extension for creating nested content elements

## Features
- Simple amazing containers (grids) as custom TYPO3 Content Elements
- No default containers, everything will be built the way its needed for a project
- Supports multilanguage (connected or free mode (mixed mode not supported))
- Supports workspaces
- Supports colPos-restrictions if EXT:content_defender >= 3.1.0 is installed
- Frontend Rendering via DataProcessor and Fluid templates

## Why did we create another "Grid" extension?

At b13 we've been long supporters and fans of gridelements, which we are thankful for and we used it in the past with great pleasure.

However, we had our pain points in the past with all solutions we've evaluted and worked with. These are our reasons:

- We wanted an extension that works with multiple versions of TYPO3 Core with the same extension, to support our company's [TYPO3 upgrade strategy](https://b13.com/solutions/typo3-upgrades).
- We wanted to overcome issues when dealing with `colPos` field and dislike any fixed value which isn't fully compatible with TYPO3 Core.
- We wanted an extension that is fully tested with multilingual and workspaces functionality.
- We wanted an extension that only does one thing: EXT:container ONLY adds tools to create and render container elements, and nothing else. No FlexForms, no permission handling or custom rendering.
- We wanted an extension where every grid has its own Content Type (CType) making it as close to TYPO3 Core functionality as possible.
- We wanted an extension where the configuration of a grid container element is located at one single place to make creation of custom containers easy.
- We wanted an extension that has a progressive development workflow: We were working with new projects in TYPO3 v10 sprint releases and needed custom container elements and did not want to wait until TYPO3 v10 LTS.

## Installation

Install this extension via `composer req b13/container` or download it from the [TYPO3 Extension Repository](https://extensions.typo3.org/extension/container/) and activate
the extension in the Extension Manager of your TYPO3 installation.

Once installed, add a custom content element to your sitepackage or theme extension (see "Adding your own container element").

## Adding your own container element

1. Register your custom container in your sitepackage or theme extension in `Configuration/TCA/Overrides/tt_content.php` as new Content Type
2. Add TypoScript and your Fluid Template for frontend rendering
3. Add an icon in Resources/Public/Icons/`<CType>`.svg

See [EXT:container_example](https://github.com/b13/container-example) for a simple example of a custom container.

### Registration of Container Elements

This is an example to create a 2 column container. The code snippet goes into a file in your sitepackage or theme extension in the folder `Configuration/TCA/Overrides/`. The file can have any name but it is good practice to name it according to the database table it relates to. In this case this would be `tt_content.php`.

```php
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\B13\Container\Tca\Registry::class)->configureContainer(
    (
        new \B13\Container\Tca\ContainerConfiguration(
            'b13-2cols-with-header-container', // CType
            '2 Column Container With Header', // label
            'Some Description of the Container', // description
            [
                [
                    ['name' => 'header', 'colPos' => 200, 'colspan' => 2, 'allowed' => ['CType' => 'header, textmedia']]
                ],
                [
                    ['name' => 'left side', 'colPos' => 201],
                    ['name' => 'right side', 'colPos' => 202]
                ]
            ] // grid configuration
        )
    )
    // set an optional icon configuration
    ->setIcon('EXT:container_example/Resources/Public/Icons/b13-2cols-with-header-container.svg')
);
```

#### Methods of the ContainerConfiguration Object

| Method name | Description | Parameters | Default |
| ----------- | ----------- | ---------- | ---------- |
| `setIcon` | icon file, or existing icon identifier | `string $icon` | `'EXT:container/Resources/Public/Icons/Extension.svg'` |
| `setBackendTemplate` | Template for backend view| `string $backendTemplate` | `'EXT:container/Resources/Private/Templates/Container.html'` |
| `setGridTemplate` | Template for grid | `string $gridTemplate` | `'EXT:container/Resources/Private/Templates/Container.html'` |
| `setGridPartialPaths` / `addGridPartialPath` | Partial root paths for grid, only affects the [Fluid-based page module](https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.3/Feature-90348-NewFluid-basedReplacementForPageLayoutView.html) | `array $gridPartialPaths` / `string $gridPartialPath` | `['EXT:backend/Resources/Private/Partials/', 'EXT:container/Resources/Private/Partials/']` |
| `setGridLayoutPaths` | Layout root paths for grid, only affects the [Fluid-based page module](https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.3/Feature-90348-NewFluid-basedReplacementForPageLayoutView.html) | `array $gridLayoutPaths` | `[]` |
| `setSaveAndCloseInNewContentElementWizard` | saveAndClose for new content element wizard (v10 only) | `bool $saveAndCloseInNewContentElementWizard` | `true` |
| `setRegisterInNewContentElementWizard` |register in new content element wizard | `bool $registerInNewContentElementWizard` | `true` |
| `setGroup` | Custom Group (used as optgroup for CType select (v10 only), and as tab in New Content Element Wizard). If empty "container" is used as tab and no optgroup in CType is used. | `string $group` | `'container'` |
| `setDefaultValues` | Default values for the newContentElement.wizardItems | `array $defaultValues` | `[]` |

__Notes:__
- If EXT:content_defender >= 3.1.0 is installed you can use `allowed`, `disallowed` and `maxitems` in the column configuration
- The container registry does multiple things:
  - Adds CType to TCA select items
  - Registers your icon
  - Adds PageTSconfig for `newContentElement.wizardItems`
  - Sets ``showitem`` for this CType (`sys_language_uid,CType,tx_container_parent,colPos,hidden`)
  - Saves the configuration in TCA in ``$GLOBALS['TCA']['tt_content']['containerConfiguration'][<CType>]`` for further usage
- We provide some default icons you can use, see `Resources/Public/Icons`
  - container-1col
  - container-2col
  - container-2col-left
  - container-2col-right
  - container-3col
  - container-4col

### TypoScript

The TypoScript is necessary to define the rendering of the container in the frontend. Normally you will place it in your sitepackage or theme extension near the place where you define other stuff regarding your content elements.
`templateRootPaths` must be adapted to reflect the path of the html files in your sitepackage or theme extension.

    // default/general configuration (will add 'children_<colPos>' variable to processedData for each colPos in container
    tt_content.b13-2cols-with-header-container < lib.contentElement
    tt_content.b13-2cols-with-header-container {
        templateName = 2ColsWithHeader
        templateRootPaths {
            10 = EXT:container_example/Resources/Private/Templates
        }
        dataProcessing {
            100 = B13\Container\DataProcessing\ContainerProcessor
        }
    }

    // if needed you can use ContainerProcessor with explicitly set colPos/variable values
    tt_content.b13-2cols-with-header-container < lib.contentElement
    tt_content.b13-2cols-with-header-container {
        templateName = 2ColsWithHeader
        templateRootPaths {
            10 = EXT:container_example/Resources/Private/Templates
        }
        dataProcessing {
            200 = B13\Container\DataProcessing\ContainerProcessor
            200 {
                colPos = 200
                as = children_200
            }
            201 = B13\Container\DataProcessing\ContainerProcessor
            201 {
                colPos = 201
                as = children_201
            }
        }
    }

#### Options for DataProcessing

| Option                      | Description                                                                                                | Default                                                      | Parameter   |
|-----------------------------|------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------|-------------|
| `contentId`                 | id of container to to process                                                                              | current uid of content element ``$cObj->data['uid']``        | ``?int``    |
| `colPos`                    | colPos of children to to process                                                                           | empty, all children are processed (as ``children_<colPos>``) | ``?int``    |
| `as`                        | variable to use for proceesedData (only if ``colPos`` is set)                                              | ``children``                                                 | ``?string`` |
| `skipRenderingChildContent` | do not call ``ContentObjectRenderer->render()`` for children, (``renderedContent`` in child will not exist) | empty                                                        | ``?int``    |

### Template

The html template file goes in the folder that you have defined in your TypoScript above (see `templateRootPaths`). It's important to name it exacly as defined in `templateName` in TypoScript, in this case `2ColsWithHeader.html`. The file name is case-sensitive!

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

With explicit colPos defined use `{children_200|201}` as set in the example above

## Concepts
- Complete registration is done with one PHP call to TCA Registry
- A container in the TYPO3 backend Page module is rendered like a page itself (see View/ContainerLayoutView)
- For backend clipboard and drag & drop `<tx_container_parent>_<colPos>` used in the data-colpos attribute in the wrapping CE-div Element (instead of just the colPos as in the PageLayoutView)
- The `<tx_container_parent>_<colPos>` parameter is resolved to `tx_container_parent` and `colPos` value in DataHandler hooks
- When translating a container, all child elements get also translated (the child elements are not explicit listed during the translation dialog)
- Copying or moving children of a container copies or moves translations as well
- Custom definitions make use of custom `colPos` values so site owners build their own elements, no fixed `colPos` given, so no interference with existing solutions
- Each container type is just a definition for its own `CType`

## CLI commands

There's several CLI commands to check/fix the integrity of the containers and their children.

```bash
# Check the sorting of container children
vendor/bin/typo3 container:sorting

# Fix the sorting of container children on page 123
vendor/bin/typo3 container:sorting --apply 123

# Check the sorting of records in page colPos
vendor/bin/typo3 container:sorting-in-page

# ??
bin/typo3 container:fixLanguageMode
bin/typo3 container:fixContainerParentForConnectedMode
bin/typo3 container:deleteChildrenWithWrongPid
bin/typo3 container:deleteChildrenWithNonExistingParent
```

## TODOs / Proofments
- Integrity proofment
- List module actions

## Extension Tests and Coding Guidelines

You can run our test suite for this extension yourself:

- run `composer install`
- run `Build/Scripts/runTests.sh -s unit`
- run `Build/Scripts/runTests.sh -s functional`
- run `Build/Scripts/runTests.sh -s acceptance`

See Tests/README.md how to run the tests local (like github-actions runs the tests).

To assure coding guidelines are fullfilled:

- run ``.Build/bin/phpstan analyse -c Build/phpstan10.neon``
- run ``.Build/bin/php-cs-fixer fix --config=Build/php-cs-fixer.php --dry-run --stop-on-violation --using-cache=no``

## Credits

This extension was created by Achim Fritz in 2020 for [b13 GmbH, Stuttgart](https://b13.com).

Find examples, use cases and best practices for this extension in our [container blog series on b13.com](https://b13.com/blog/flexible-containers-and-grids-for-typo3).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code.
