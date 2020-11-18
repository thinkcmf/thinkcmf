Writing your own types of annotations
=====================================

What is an Annotation?
^^^^^^^^^^^^^^^^^^^^^^
An annotation is just a class - it is merely the way it gets initialized (and instantiated) that makes it an annotation.

In order for a class to work as an annotation, it must:

* have a constructor with no arguments - e.g.: ``function __construct()`` (or no constructor)
* implement the ``IAnnotation`` interface - e.g.: ``function initAnnotation($properties)``
* be annotated with an ``@usage`` annotation - see below for details.

Beyond the quantitative requirements, you should make some qualitative considerations. Here are some things to consider:

* Annotations are specifications - they can provide default values for various components, or define additional
  behaviors or metadata. But your components should not *depend* on a specific annotation - if you find you're trying
  to define an annotation that is *required* for your components to operate, there's a good chance you'd be better off
  defining that behavior as an interface.
* Try to design your annotation types for general purposes, rather than for a specific purpose - there is a good chance
  you may be able to use the same metadata in new ways at a later time. Choose broad terms for class-names (and
  property-names) so as not to imply any specific meaning - just describe the information, not it's purpose.
* Do you need a new annotation type, or can one of the existing types be used to define what you're trying to specify?
  Be careful not to duplicate your specifications, as this leads to situations where you'll be forced to write the same
  metadata in two different formats - the point of annotations is to help eliminate this kind of redundancy and overhead.

UsageAnnotation
^^^^^^^^^^^^^^^
The `UsageAnnotation`_ class defines the constraints and behavior of an annotation.

An instance of the built-in ``@usage`` annotation must be applied to every annotation class, or to it's ancestor -
the ``@usage`` annotation itself is inheritable, and can be overridden by a child class.

The standard ``@length`` annotation, for example, defines it's use as follows:

.. code-block:: php

    /**
     * Specifies validation of a string, requiring a minimum and/or maximum length.
     *
     * @usage('property'=>true, 'inherited'=>true)
     */
    class LengthAnnotation extends ValidationAnnotationBase
    {
      ...
    }

This specification indicates that the annotation may be applied to properties, and that the annotation can be
inherited by classes which extend a class to which the annotation was applied.

The ``@usage`` annotation is permissive; that is, all of it's properties are ``false`` by default - you have to turn
on any of the permissions/features that apply to your annotation class, by setting each property to ``true``.

Let's review the available properties.

* The ``$class``, ``$property`` and ``$method`` flags simply specify to which type(s) of source-code elements an
  annotation is applicable.
* The ``$multiple`` flag specifies whether more than one annotation of this type may be applied to the same source-code
  element
* The ``$inherited`` flag specifies whether the annotation(s) will be inherited by a class extending the class to which
  the annotations were applied.

Different combinations of the ``$multiple`` and ``$inherited`` flags result in the following behavior:

+--------------------------+--------------------------+--------------------------+
|                          | ``$multiple=true``       | ``$multiple=false``      |
+--------------------------+--------------------------+--------------------------+
| ``$inherited=true``      | Multiples allowed and    | Only one allowed,        |
|                          | inherited                | inherited with override  |
+--------------------------+--------------------------+--------------------------+
| ``$inherited=false``     | Multiples allowed, not   | Only one allowed, not    |
|                          | inherited                | inherited                |
+--------------------------+--------------------------+--------------------------+

Note that annotations with ``$multiple=false`` and ``$inherited=true`` are a special case, in which only one
annotation is allowed on the same code-element, and is inherited - but can be overridden by a child-class
which would otherwise inherit the annotation.

When overriding an inherited annotation, it's important to understand that the individual properties of an annotation
are *not* inherited - the *entire* annotation is replaced by the overriding annotation.

.. _UsageAnnotation: https://github.com/php-annotations/php-annotations/blob/master/src/annotations/UsageAnnotation.php
