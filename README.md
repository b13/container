# EXT:container

## TypoScript

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
            200 = B13\Container\DataProcessing\ContainerRenderedChildsProcessor
            200 {
                colPos = 100
                as = contentLeft
            }
            201 = B13\Container\DataProcessing\ContainerRenderedChildsProcessor
            201 {
                colPos = 101
                as = contentRight
            }
        }
    } 
    

## Template


    <h1>plain child records</h1>
    
    <h2>left (100)</h2>
    <f:for each="{childsLeft}" as="record">
        {record.header} <br />
    </f:for>
    
    <h2>right (101)</h2>
    <f:for each="{childsRight}" as="record">
        {record.header} <br />
    </f:for>
    
    <h1>childs as rendered content</h1>
    
    <h2>left (100)</h2>
    <f:format.raw>
        {contentLeft}
    </f:format.raw>
    
    
    <h2>right (101)</h2>
    <f:format.raw>
        {contentRight}
    </f:format.raw>
