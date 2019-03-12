Standard annotations library
============================
A library of standard annotations will eventually be (**but is not currently**) part of this package.

.. note::
   Experienced developers (primarily maintainers of libraries that rely on annotations), are encouraged to
   contribute or suggest changes or additions to the standard library.

The standard library of annotations generally belong to one (or more) of the following categories:

* **Reflective annotations** to describe aspects of code semantics not natively supported by the PHP language.
* **Data annotations** to describe storage/schema aspects of persistent types.
* **Display annotations** to specify input types, labels, display-formatting and other UI-related aspects of your
  domain- or view-models.
* **Validation annotations** to specify property/object validators for domain- or form-models, etc.
* **PHP-DOC annotations** - a subset of the standard PHP-DOC annotations.

Some of the annotations belong to more than one of these categories - the standard PHP-DOC annotations generally fall
into at least one of the other categories.

Most of the standard annotations were referenced from annotations that ship with other languages and frameworks that
support annotations natively, mainly .NET and Java. Due to the strict nature of these languages, as compared to the
loose nature of PHP, the standard  annotations were not merely ported from other languages, but adapted to better fit
with good, modern PHP code.

Available Annotations
^^^^^^^^^^^^^^^^^^^^^

.. note::
   The annotation library is not yet available, or still in development.

Category: Reflective, PHP-DOC
-----------------------------
+-------------------------+----------+------------------------------------------------------------+
| Annotation              | Scope    | Description                                                |
+=========================+==========+============================================================+
| MethodAnnotation        | Class    | Defines a magic/virtual method.                            |
+-------------------------+----------+------------------------------------------------------------+
| ParamAnnotation         | Method   | Defines a method-parameter’s type.                         |
+-------------------------+----------+------------------------------------------------------------+
| PropertyAnnotation      | Class    | Defines a magic/virtual property and it’s type.            |
+-------------------------+----------+------------------------------------------------------------+
| PropertyReadAnnotation  | Class    | Defines a magic/virtual read-only property and it’s type.  |
+-------------------------+----------+------------------------------------------------------------+
| PropertyWriteAnnotation | Class    | Defines a magic/virtual write-only property and it’s type. |
+-------------------------+----------+------------------------------------------------------------+
| ReturnAnnotation        | Method   | Defines the return-type of a function or method.           |
+-------------------------+----------+------------------------------------------------------------+
| VarAnnotation           | Property | Specifies validation of various common property types.     |
+-------------------------+----------+------------------------------------------------------------+
| TypeAnnotation          | Property | Specifies validation of various common property types.     |
+-------------------------+----------+------------------------------------------------------------+

Category: Display
-----------------
+----------------------+----------------+-------------------------------------------------------+
| Annotation           | Scope          | Description                                           |
+======================+================+=======================================================+
| DisplayAnnotation    | Property       | Defines various display-related metadata, such as     |
|                      |                | grouping and ordering.                                |
+----------------------+----------------+-------------------------------------------------------+
| EditableAnnotation   | Property       | Indicates whether a property should be user-editable  |
|                      |                | or not.                                               |
+----------------------+----------------+-------------------------------------------------------+
| EditorAnnotation     | Property       | Specifies a view-name (or path, or helper) to use for |
|                      |                | editing purposes - overrides ViewAnnotation when      |
|                      |                | rendering inputs.                                     |
+----------------------+----------------+-------------------------------------------------------+
| FormatAnnotation     | Property       | Specifies how to display or format a property value.  |
+----------------------+----------------+-------------------------------------------------------+
| TextAnnotation       | Property       | Defines various text (labels, hints, etc.) to be      |
|                      |                | displayed with the annotated property.                |
+----------------------+----------------+-------------------------------------------------------+
| ViewAnnotation       | Property/class | Specifies a view-name (or path) to use for            |
|                      |                | display/editing purposes.                             |
+----------------------+----------------+-------------------------------------------------------+

Category: Validation
--------------------
+----------------------+----------+--------------------------------------------------+
| Annotation           | Scope    | Description                                      |
+======================+==========+==================================================+
| EnumAnnotation       | Property | Specifies validation against a fixed enumeration |
|                      |          | of valid choices.                                |
+----------------------+----------+--------------------------------------------------+
| LengthAnnotation     | Property | Specifies validation of a string, requiring a    |
|                      |          | minimum and/or maximum length.                   |
+----------------------+----------+--------------------------------------------------+
| MatchAnnotation      | Property | Specifies validation of a string against a       |
|                      |          | regular expression pattern.                      |
+----------------------+----------+--------------------------------------------------+
| RangeAnnotation      | Property | Specifies validation against a minimum and/or    |
|                      |          | maximum numeric value.                           |
+----------------------+----------+--------------------------------------------------+
| RequiredAnnotation   | Property | Specifies validation requiring a non-empty       |
|                      |          | value.                                           |
+----------------------+----------+--------------------------------------------------+
| ValidateAnnotation   | Class    | Specifies a custom validation callback           |
|                      |          | method.                                          |
+----------------------+----------+--------------------------------------------------+
