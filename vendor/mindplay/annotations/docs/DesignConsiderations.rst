Design considerations
=====================
This page will attempt to explain some of the design considerations behind this library.

Feature Set
^^^^^^^^^^^
The feature set was mainly referenced from languages with proven annotation support and established use of
annotations - the primary inspiration was the .NET platform and Java.

Other existing annotation-libraries for PHP were also referenced, for both good and evil:

* The popular `Addendum`_ library brought some good ideas to the table, but adds unnecessary custom syntax and parses
  annotations at run-time.
* Doctrine's `Annotations`_ library achieves a number of good things, but also adds unnecessary custom syntax.
* The `Recess`_ framework has good support for annotations and relies on them to solve a number of interesting
  challenges in highly original ways, making it a great inspiration.
* A proposed native `Class MetaData`_ extension to the PHP language: follows no existing standards, (incompatible with
  existing IDEs, documentation generators, existing practices and codebases); mixes `JSON`_, a data-serialization
  format, into PHP - I would welcome JSON support in PHP, but not solely for annotations, and it should not replace
  what can already be achieved with existing PHP language features.

When held against the annotation feature-set of the C#/.NET or Java platforms, these implementations have some
weak points:

* These libraries use various custom, data-formats to initialize annotations - neglecting support for common language
  features, such as class-constants, static method calls and closures.
* Support for inheritance is lacking, limited or incorrect in various ways - inheriting and overriding annotations is
  an absolute requirement.
* Common constraints are unsupported or too simple - applicable member types, `cardinality`_ and inheritance
  constraints should be easy to specify, and must be consistently enforced.

The absence of these features is what sparked the inception of this library.

Syntax
^^^^^^
The syntax is based on `PHP-DOC`_ annotations mixed with PHP standard `array`_ syntax.

The decision to use PHP-DOC syntax was made primarily because PHP-DOC source code annotations are already very
common in the PHP community, and well-established with good design-time support by many popular IDEs. Many types
of useful standard PHP-DOC annotations can be inspected at run-time.

Extending this syntax with standard PHP array syntax is practical for a number of reasons:

* PHP array-syntax is already familiar to PHP developers, and naturally allows you to initialize annotation properties
  using PHP language constructs, including constants, anonymous functions (closures), static method-calls, etc.
* It reduces the complexity of parsing, since PHP already knows how to parse arrays.
* There is no compelling reason to introduce new syntax (and more complexity) to achieve something that is already
  supported by the language.

Rather than attempting to reinvent (or having to forego) important aspects of existing language features, this library
builds on existing PHP syntax, and existing establish conventions, as much as possible, introducing a minimal amount of
new syntax. This makes it feel like a more natural extension to the language itself.

API
^^^
The API has two levels of public interfaces - an annotation-manager, which can be extended, if needed, and a simple
static wrapper-class with static methods, mostly for convenience.

Extending the `Reflection`_ API with annotation features might seem like a natural approach, since this is where you
would find it on other platforms such as .NET. There are a couple of reasons why this is not necessary or practical:

* PHP might very well add native support for annotations to the reflection classes someday - if (or when) that happens,
  we don't want our API to conflict with (or hide portions of) any eventual extensions to the native reflection API.
* This library minimally relies on reflection itself.
* There is nothing in particular to gain by mixing the annotation APIs with reflection in the first place.

Freedom
-------
This library has no external dependencies on any non-standard PHP modules or third-party libraries.

Annotation-types implement an interface - they do not need to extend a base-class, which enables it to fit into your
existing class-hierarchies without requiring you to refactor your existing codebase.

Performance
^^^^^^^^^^^
From a performance-oriented perspective, a scripting language may not be a good choice for writing any kind of parser.
Since some form of parsing is inevitable, the following design choices were made early on to minimize the overhead
of using annotations:

* The annotation-parser is only loaded as needed, e.g. after a change (invalidating the cache-file) is made to an
  inspected script-file.
* The annotation-manager compiles (`JIT`_) and caches annotation data - the annotations from one PHP script file are
  written to one cache-file. This simple strategy results in one additional script being loaded, when a script is
  inspected for annotations.
* Since the cache-file itself is a PHP script, the annotation library can take advantage of a `bytecode cache`_ for
  additional performance gains.

In general, as much work as possible (or practical) is done at compile-time, minimizing the run-time overhead -
no `tokenization`_ or parsing or is performed at run-time, except the first time a script is inspected.

.. _Addendum: http://code.google.com/p/addendum/
.. _Annotations: http://www.doctrine-project.org/projects/common/2.0/docs/reference/annotations/en
.. _Recess: http://www.recessframework.org
.. _Class MetaData: http://wiki.php.net/rfc/annotations
.. _JSON: http://json.org/
.. _cardinality: http://en.wiktionary.org/wiki/cardinality
.. _PHP-DOC: http://www.phpdoc.org/
.. _array: http://php.net/manual/en/language.types.array.php
.. _Reflection: http://php.net/manual/en/book.reflection.php
.. _JIT: http://en.wikipedia.org/wiki/Just-in-time_compilation
.. _bytecode cache: http://en.wikipedia.org/wiki/PHP_accelerator
.. _tokenization: http://php.net/manual/en/function.token-get-all.php
