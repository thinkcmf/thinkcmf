Fully documented, step-by-step example of declarative meta-programming
======================================================================
File: **demo/index.php**

.. literalinclude:: ../demo/index.php
   :lines: 2-8

Configure a simple auto-loader

.. literalinclude:: ../demo/index.php
   :lines: 10-22

Configure the cache-path. The static ``Annotations`` class will configure any public
properties of ``AnnotationManager`` when it creates it. The ``AnnotationManager::$cachePath``
property is a path to a writable folder, where the ``AnnotationManager`` caches parsed
annotations from individual source code files.

.. literalinclude:: ../demo/index.php
   :lines: 27-29

Register demo annotations.

.. literalinclude:: ../demo/index.php
   :lines: 31-32

For this example, we're going to generate a simple form that allows us to edit a ``Person``
object. We'll define a few public properties and annotate them with some useful metadata,
which will enable us to make decisions (at run-time) about how to display each field,
how to parse the values posted back from the form, and how to validate the input.

Note the use of standard PHP-DOC annotations, such as ``@var string`` - this metadata is
traditionally useful both as documentation to developers, and as hints for an IDE. In
this example, we're going to use that same information as advice to our components, at
run-time, to help them establish defaults and make sensible decisions about how to
handle the value of each property.

.. literalinclude:: ../demo/index.php
   :lines: 43-67

To build a simple form abstraction that can manage the state of an object being edited,
we start with a simple, abstract base class for input widgets.

.. literalinclude:: ../demo/index.php
   :lines: 70-77

Each widget will maintain a list of error messages.

.. literalinclude:: ../demo/index.php
   :lines: 79-81

A widget needs to know which property of what object is being edited.

.. literalinclude:: ../demo/index.php
   :lines: 83-90

Widget classes will use this method to add an error-message.

.. literalinclude:: ../demo/index.php
   :lines: 92-97

This helper function provides a shortcut to get a named property from a
particular type of annotation - if no annotation is found, the ``$default``
value is returned instead.

.. literalinclude:: ../demo/index.php
   :lines: 101-112

Each type of widget will need to implement this interface, which takes a raw
POST value from the form, and attempts to bind it to the object's property.

.. literalinclude:: ../demo/index.php
   :lines: 115-117

After a widget successfully updates a property, we may need to perform additional
validation - this method will perform some basic validations, and if errors are
found, it will add them to the ``$errors`` collection.

.. literalinclude:: ../demo/index.php
   :lines: 121-154

Each type of widget will need to implement this interface, which renders an
HTML input representing the widget's current value.

.. literalinclude:: ../demo/index.php
   :lines: 157-159

This helper function returns a descriptive label for the input.

.. literalinclude:: ../demo/index.php
   :lines: 161-166

Finally, this little helper function will tell us if the field is required -
if a property is annotated with ``@required``, the field must be filled in.

.. literalinclude:: ../demo/index.php
   :lines: 169-175

The first and most basic kind of widget, is this simple string widget.

.. literalinclude:: ../demo/index.php
   :lines: 177-179

On update, take into account the min/max string length, and provide error
messages if the constraints are violated.

.. literalinclude:: ../demo/index.php
   :lines: 182-189

On display, render out a simple ``<input type="text"/>`` field, taking into account
the maximum string-length.

.. literalinclude:: ../demo/index.php
   :lines: 192-201

For the age input, we'll need a specialized ``StringWidget`` that also checks the input type.

.. literalinclude:: ../demo/index.php
   :lines: 203-205

On update, take into account the min/max numerical range, and provide error
messages if the constraints are violated.

.. literalinclude:: ../demo/index.php
   :lines: 208-223

Next, we can build a simple form abstraction - this will hold and object and manage
the widgets required to edit the object.

.. literalinclude:: ../demo/index.php
   :lines: 226-237

The constructor just needs to know which object we're editing.

Using reflection, we enumerate the properties of the object's type, and using the
``@var`` annotation, we decide which type of widget we're going to use.

.. literalinclude:: ../demo/index.php
   :lines: 242-257

This helper-method is similar to the one we defined for the widget base
class, but fetches annotations for the specified property.

.. literalinclude:: ../demo/index.php
   :lines: 260-271

When you post information back to the form, we'll need to update it's state,
validate each of the fields, and return a value indicating whether the form
update was successful.

.. literalinclude:: ../demo/index.php
   :lines: 275-300

Finally, this method renders out the form, and each of the widgets inside, with
a ``<label>`` tag surrounding each input.

.. literalinclude:: ../demo/index.php
   :lines: 303-322

Now let's put the whole thing to work...

We'll create a ``Person`` object, create a ``Form`` for the object, and render it!

Try leaving the name field empty, or try to tell the form you're 120 years old -
it won't pass validation.

You can see the state of the object being displayed below the form - as you can
see, unless all updates and validations succeed, the state of your object is
left untouched.

.. literalinclude:: ../demo/index.php
   :lines: 333-372
