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


done:
TCA namespace
Registry as singeleton ohne static
rm demo from EXT:container
rm ContainerRenderedChildsProcessor
mv Container to other page
unused Elements

todo
integrity

// todo more tests

* wieder weggeschmissen, s. u.: localization shows container colPos
* done new in edit element has default values
* done new childElement in translated Container in free has the translated Container uid as parent
* list module edit stuff
* done: change ColPos
* change CType


* copyToLanguage do not copy childs
* localize localize childs -> childs are not localizable during translation
* allow mixed mode
* fallback for FE

* move Element outside container reset parent field
* move child element changed localization colPos and parent

moveChildOutsideContainerResetParentField und moveChildElementMovesTranslations ist nicht das selbe, wie wenn ich das im BE verschiebe !!!



