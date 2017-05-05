Roadmap, upgrading and release notes
====================================
This project implements support for source-code annotations in PHP (5.3+).

Referencing established practices and proven features of annotation-support from other languages and platforms
with native support for annotations (mainly C#/.NET and Java), this library implements a complete,
":doc:`industrial strength <DesignConsiderations>`" annotation engine for PHP, drawing on the strengths (while
observing the limitations) of the language.

Status
^^^^^^
The current status of the individual components is as follows:

* The core annotation framework (and API) is stable and complete.
* Documentation is :doc:`being written <index>`.
* Some standard annotations are still stubs or only partially done.
* A fully documented :doc:`demonstration script <DemoScript>` is available.

Roadmap
^^^^^^^
The current release consists of the following components:

* Core annotation framework. (adds support for annotations)
* Self-contained unit test suite.
* Documentation.
* Example.

A library of useful standard (PHP-DOC and other) annotations has been started, but is incomplete.

Upgrading
^^^^^^^^^
**Version 1.1.x** introduces some incompatibilities with previous versions:

* The cache-abstraction has been removed - refer to `this note`_ explaining why. If you wrote your own
  cache-provider, you should remove it.
* If you derived custom annotation-types from the ``Annotation\Annotation`` base-class, you must rename
  the ``_map()`` method to ``map()`` - the underscore suggested a private method, but this method is actually
  protected.

.. _this note: https://github.com/php-annotations/php-annotations/pull/6#issuecomment-9279655
