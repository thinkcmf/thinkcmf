Understanding annotations
=========================

What are annotations?
^^^^^^^^^^^^^^^^^^^^^
Annotations are meta-data that can be embedded in source code.

You may already be familiar with the `PHP-DOC`_ flavor of source code annotations, which are seen in most modern
PHP codebases - they look like this:

.. code-block:: php

    class Foo
    {
      /**
       * @var integer
       */
      public $bar;
    }

When you see ``@var integer`` in the source code, immediately preceding the ``public $bar`` declaration, this little
piece of meta-data tells us that the ``$bar`` property is expected to contain a value of type ``integer``.

This information is useful to programmers - an IDE can use this information to display popup hints when you're
writing code that works with an instance of ``Foo``, documentation generators can display this information in
reference material, etc.

Now imagine you had to build an HTML form that allows someone to edit a ``Foo`` object. When this information
comes back from a form-post, you will need to validate that the input is in fact an integer - using the information
from the ``@var`` annotation, you could abstract and automate this process, rather than having to code in by hand
every time.

The same information about the type of value required for this property could have many other uses besides
validation - for example, you could use it to decide what type of input to render on a form, or how to
persist the value to a database column.

Using other types of annotations besides ``@var``, you could provide more information about the ``$bar``
property - for example, you might use an annotation to specify the minimum and maximum allowed integer values of a
property for validation, or define a label to be displayed next to the input on forms or in the column-header of a
list of ``Foo`` objects:

.. code-block:: php

    class Foo
    {
      /**
       * @var integer
       * @range(0, 100)
       * @label('Number of Bars')
       */
      public $bar;
    }

We now have the information about the type of value, the allowed range, and the label, all associated with
the ``Foo::$bar`` property member. You may have noticed a subtle difference between the ``@var`` annotation
and the other two annotations in this example: the extra parentheses - we'll get to that below.

It's important to understand that this meta-data does not have a single predefined purpose - it is general information,
which when put to use in creative ways, can be used to simplify or eliminate repetitive work, and enables you to write
more elegant and reusable code.


What does this library do?
^^^^^^^^^^^^^^^^^^^^^^^^^^
This library allows you to implement annotation-types as classes, and apply them as objects.

Annotations are translated into objects using a simple rule: ``@name`` is essentially equivalent
to ``new NameAnnotation()`` - in other words, the annotation name is capitalized and an "Annotation" suffix is added
to the class-name; this prevents the class-names of annotation-types from colliding with the names of other classes.

Using this library, annotations applied to classes, methods and properties may be inspected at run-time. Combined with
the `Reflection`_ API, this enables you to apply `reflective meta-programming`_ in your PHP projects.


Annotation Syntax
^^^^^^^^^^^^^^^^^
This library provides support for two types of annotation syntax. While the difference between the two syntaxes is
subtle, the difference in terms of how they function is very different.

The first type of syntax is based on `PHP-DOC`_ syntax:

.. code-block:: php

    /**
     * @var integer
     */
    public $bar;

PHP-DOC annotations do not have a fixed syntax - that is, everything after ``@name`` is interpreted differently for
each type of annotation. For example, the syntax for ``@var`` is ``@var {type} {description}``, while the syntax for
``@param`` is ``@param {type} {$name} {description}``.

In other words, PHP-DOC style annotations have to go through an extra step at compile-time. (note that there is no
performance penalty for this extra step, since the compiled annotation properties are cached.)

For simple annotations (like those defined by the `PHP-DOC specification`_), this syntax is usually preferable.

For custom annotations (perhaps requiring more complex properties), a second syntax using parentheses is supported:

.. code-block:: php

    /**
     * @range(0, 100)
     */
    public $bar;

When this syntax is used, the run-time equivalent for this example is something along the lines of:

.. code-block:: php

    $annotation = new RangeAnnotation();
    $annotation->initAnnotation(array(0, 100));

In other words, everything between the parentheses is standard PHP `array`_ syntax - as you're probably already
comfortable with this syntax, there is no additional syntax to learn.

While an annotation-type can optionally implement a custom (PHP-DOC style) syntax annotation, the array-style syntax
is supported by every annotation. To achieve compatibility with IDEs and documentation generators, you should use the
PHP-DOC style syntax for annotations defined by the PHP-DOC standard.

Both syntaxes have unique advantages:

* PHP-DOC style offers shorter syntax for commonly used annotation-types, and compatibility with IDEs and documentation
  generators.
* Array-style syntax offers direct access to PHP language features, such as access to class-constants, static
  method-calls, nested arrays, etc.

Note that both syntaxes cause annotations to initialize in the same way, at run-time - via a call to
the ``IAnnotation::initAnnotation()`` interface, passing an array of property values. The PHP-DOC style syntax simply
adds an extra step where the annotation values are parsed, and the initialization code for those properties is
generated (and cached).

So what can I do with this?
^^^^^^^^^^^^^^^^^^^^^^^^^^^
See a real, working example of declarative meta-programming with this library in
:doc:`this commented, step-by-step example <DemoScript>` - the same script is available in the `demo folder`_ in the
project, and can be run from your local web-server.

.. _PHP-DOC: http://www.phpdoc.org/
.. _Reflection: http://php.net/manual/en/book.reflection.php
.. _reflective meta-programming: http://en.wikipedia.org/wiki/Reflection_(computer_programming)
.. _PHP-DOC specification: http://www.phpdoc.org/docs/latest/for-users/list-of-tags.html
.. _array: http://php.net/manual/en/language.types.array.php
.. _demo folder: https://github.com/php-annotations/php-annotations/tree/master/demo
