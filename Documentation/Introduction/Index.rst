.. include:: /Includes.rst.txt

============
Introduction
============

Why did we create another "Grid" extension?
===========================================

At b13 we've been long supporters and fans of
`Grid Elements <https://extensions.typo3.org/extension/gridelements>`__,
which we are thankful for and we used it in the past with great pleasure.

However, we had our pain points in the past with all solutions we've evaluated
and worked with. These are our reasons:

-  We wanted an extension that works with multiple versions of TYPO3 Core with
   the same extension, to support our company's
   `TYPO3 upgrade strategy <https://b13.com/solutions/typo3-upgrades>`__.
-  We wanted to overcome issues when dealing with `colPos` field and dislike any
   fixed value which isn't fully compatible with TYPO3 Core.
-  We wanted an extension that is fully tested with multilingual and workspaces
   functionality.
-  We wanted an extension that only does one thing: Adding tools to create and
   render container elements, and nothing else. No FlexForms, no permission
   handling or custom rendering.
-  We wanted an extension where every grid has its own Content Type (CType)
   making it as close to TYPO3 Core functionality as possible.
-  We wanted an extension where the configuration of a grid container element
   is located at one single place to make creation of custom containers easy.
-  We wanted an extension that has a progressive development workflow: We were
   working with new projects in TYPO3 v10 sprint releases and needed custom
   container elements and did not want to wait until TYPO3 v10 LTS.

Credits
=======

This extension was created by Achim Fritz in 2020 for
`b13 GmbH, Stuttgart <https://b13.com>`__.

Find examples, use cases and best practices for this extension in our
`container blog series on b13.com <https://b13.com/blog/flexible-containers-and-grids-for-typo3>`__.

Find `more TYPO3 extensions we have developed <https://b13.com/useful-typo3-extensions-from-b13-to-you>`__
that help us deliver value in client projects. As part of the way we work,
we focus on testing and best practices to ensure long-term performance,
reliability, and results in all our code.
