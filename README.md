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
        }
    } 
    

## Template



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



TCA namespace
Registry as singeleton ohne static
rm demo from EXT:container
rm ContainerRenderedChildsProcessor

