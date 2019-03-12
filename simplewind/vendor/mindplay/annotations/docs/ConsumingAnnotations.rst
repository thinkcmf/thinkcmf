Working with the Annotation Manager
===================================
.. note:: Some programmers learn best by seeing a practical example - if you belong to those who learn best by seeing
          things applied, you should start by taking a look at the :doc:`demo script <DemoScript>`, which provides
          a minimal, yet practical, real-world example of applying and consuming source code annotations.

The annotation framework lives in the ``mindplay\annotations`` namespace, and the library
of :doc:`standard annotations <AnnotationLibrary>` lives in the `mindplay\\annotations\\standard`_ namespace.

The heart of the annotation framework is the `AnnotationManager`_ class, which provides the following functionality:

* Inspecting (and filtering) annotations
* Annotation registry and name-resolution
* Caching annotations in the local filesystem (underneath the hood)

Behind the scenes, the ``AnnotationManager`` relies on the `AnnotationParser`_ to perform the parsing and compilation
of annotations into cacheable scripts.

For convenience, a static (singleton) wrapper-class for the annotation manager is also available.
This class is named `Annotations`_ - we will use it in the following examples.

Loading and Importing
^^^^^^^^^^^^^^^^^^^^^
Going into details about `autoloading`_ and `importing`_ the annotation classes is beyond the scope of this article.

I will assume you are familiar with these language features, and in the following examples, it is implied that
the static wrapper-class has been imported, e.g.:

.. code-block:: php

    use mindplay\annotations\Annotations;

Configuring the Annotation Manager
----------------------------------
For convenience, the static ``Annotations`` class provides a public ``$config`` array - the keys in this array are
applied the singleton ``AnnotationManager`` instance on first use, for example:

.. code-block:: php

    Annotations::$config = array(
        'cachePath' => sys_get_temp_dir()
    );

In this example, when the ``AnnotationManager`` is initialized, the public ``$cachePath`` property is set to point
to the local temp-dir on your system.

Other configurable properties include:

+------------------+----------+---------------+
| Property         | Type     | Description   |
+==================+==========+===============+
| ``$fileMode``    | int      | …             |
+------------------+----------+---------------+
| ``$autoload``    | bool     | …             |
+------------------+----------+---------------+
| ``$cachePath``   | string   | …             |
+------------------+----------+---------------+
| ``$cacheSeed``   | string   | …             |
+------------------+----------+---------------+
| ``$suffix``      | string   | …             |
+------------------+----------+---------------+
| ``$namespace``   | string   | …             |
+------------------+----------+---------------+
| ``$registry``    | array    | …             |
+------------------+----------+---------------+

The Annotation Registry
-----------------------
...

Inspecting Annotations
^^^^^^^^^^^^^^^^^^^^^^
...

Annotation Name Resolution
--------------------------
...

Filtering Annotations
---------------------
...

.. _mindplay\\annotations\\standard: https://github.com/php-annotations/php-annotations/tree/master/src/annotations/standard
.. _AnnotationManager: https://github.com/php-annotations/php-annotations/blob/master/src/annotations/AnnotationManager.php
.. _AnnotationParser: https://github.com/php-annotations/php-annotations/blob/master/src/annotations/AnnotationParser.php
.. _Annotations: https://github.com/php-annotations/php-annotations/blob/master/src/annotations/Annotations.php
.. _autoloading: http://php.net/manual/en/language.oop5.autoload.php
.. _importing: http://php.net/manual/en/language.namespaces.importing.php
