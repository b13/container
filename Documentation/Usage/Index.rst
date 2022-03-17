.. include:: /Includes.rst.txt

=====
Usage
=====

.. contents:: Table of Contents:
   :backlinks: top
   :class: compact-list
   :depth: 2
   :local:

Adding your own container element
=================================

1. Register your custom container in your sitepackage or theme extension in
   `Configuration/TCA/Overrides/tt_content.php` as new Content Type.
2. Add TypoScript and your Fluid Template for frontend rendering.
3. Add an icon in Resources/Public/Icons/`<CType>`.svg.

.. seealso::
   `EXT:container_example <https://github.com/b13/container-example>`__
   provides a simple example of a custom container.

Registration of Container Elements
==================================

This is an example to create a 2 column container. The code snippet goes into
a file in your sitepackage or theme extension in the folder
`Configuration/TCA/Overrides/`. The file can have any name but it is good
practice to name it according to the database table it relates to. In this case
this would be `tt_content.php`.

.. code-block:: php

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

Methods of the ContainerConfiguration Object
--------------------------------------------

.. php:class:: ContainerConfiguration

   .. php:method:: setIcon($icon)

      Set icon file, or existing icon identifier.

      Default: `EXT:container/Resources/Public/Icons/Extension.svg`

      :param string $icon:

   .. php:method:: setBackendTemplate($backendTemplate)

      Set template for backend view.

      Default: `EXT:container/Resources/Private/Templates/Container.html`

      :param string $backendTemplate:

   .. php:method:: setGridTemplate($gridTemplate)

      Set template for grid.

      Default: `EXT:container/Resources/Private/Templates/Container.html`

      :param string $gridTemplate:

   .. php:method:: setGridPartialPaths($gridPartialPaths)

      Set partial root paths for grid. It only affects the
      :doc:`Fluid-based page module <ext_core:Changelog/10.3/Feature-90348-NewFluid-basedReplacementForPageLayoutView>`.

      Default: [`EXT:backend/Resources/Private/Partials/`, `EXT:container/Resources/Private/Partials/`]

      :param array $gridPartialPaths:

   .. php:method:: addGridPartialPath($gridPartialPath)

      Add partial root path for grid. It only affects the
      :doc:`Fluid-based page module <ext_core:Changelog/10.3/Feature-90348-NewFluid-basedReplacementForPageLayoutView>`.

      Default: [`EXT:backend/Resources/Private/Partials/`, `EXT:container/Resources/Private/Partials/`]

      :param string $gridPartialPath:

   .. php:method:: setSaveAndCloseInNewContentElementWizard($saveAndCloseInNewContentElementWizard)

      Enable "Save and close" in new content element wizard (v10 only).

      Default: `true`

      :param bool $saveAndCloseInNewContentElementWizard:

   .. php:method:: setRegisterInNewContentElementWizard($registerInNewContentElementWizard)

      Register in new content element wizard.

      Default: `true`

      :param bool $registerInNewContentElementWizard:

   .. php:method:: setGroup($group)

      Custom Group (used as optgroup for CType select (v10 only), and as tab in
      new Content Element Wizard). If empty "container" is used as tab and no
      optgroup in CType is used.

      Default: `container`

      :param string $group:

   .. php:method:: setDefaultValues($defaultValues)

      Default values for the newContentElement.wizardItems.

      Default: `[]`

      :param array $defaultValues:

**Notes:**

-  If `Content Defender <https://extensions.typo3.org/extension/content_defender/>`__
   >= 3.1.0 is installed you can use `allowed`, `disallowed` and `maxitems` in
   the column configuration
-  The container registry does multiple things:
   -  Adds CType to TCA select items
   -  Registers your icon
   -  Adds PageTSconfig for `newContentElement.wizardItems`
   -  Sets ``showitem`` for this CType (`sys_language_uid,CType,tx_container_parent,colPos,hidden`)
   -  Saves the configuration in TCA in ``$GLOBALS['TCA']['tt_content']['containerConfiguration'][<CType>]`` for further usage
-  We provide some default icons you can use, see `Resources/Public/Icons`
   -  container-1col
   -  container-2col
   -  container-2col-left
   -  container-2col-right
   -  container-3col
   -  container-4col

TypoScript
==========

The TypoScript is necessary to define the rendering of the container in the
frontend. Normally you will place it in your sitepackage or theme extension
near the place where you define other stuff regarding your content elements.
`templateRootPaths` must be adapted to reflect the path of the html files in
your sitepackage or theme extension.

.. code-block:: typoscript

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

Template
========

The html template file goes in the folder that you have defined in your
TypoScript above (see `templateRootPaths`). It's important to name it exactly
as defined in `templateName` in TypoScript, in this case `2ColsWithHeader.html`.
The file name is case-sensitive!

.. code-block:: html

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

With explicit colPos defined use `{children_200]201>}` as set in the example
above.
