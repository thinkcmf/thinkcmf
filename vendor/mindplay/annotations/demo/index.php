<?php
namespace mindplay\demo;

use Composer\Autoload\ClassLoader;
use mindplay\annotations\AnnotationCache;
use mindplay\annotations\Annotations;
use mindplay\demo\annotations\Package;

## Configure a simple auto-loader
$vendor_path = dirname(__DIR__) . '/vendor';

if (!is_dir($vendor_path)) {
    echo 'Install dependencies first' . PHP_EOL;
    exit(1);
}

require_once($vendor_path . '/autoload.php');

$auto_loader = new ClassLoader();
$auto_loader->addPsr4("mindplay\\demo\\", __DIR__);
$auto_loader->register();

## Configure the cache-path. The static `Annotations` class will configure any public
## properties of `AnnotationManager` when it creates it. The `AnnotationManager::$cachePath`
## property is a path to a writable folder, where the `AnnotationManager` caches parsed
## annotations from individual source code files.

Annotations::$config['cache'] = new AnnotationCache(__DIR__ . '/runtime');

## Register demo annotations.
Package::register(Annotations::getManager());

## For this example, we're going to generate a simple form that allows us to edit a `Person`
## object. We'll define a few public properties and annotate them with some useful metadata,
## which will enable us to make decisions (at run-time) about how to display each field,
## how to parse the values posted back from the form, and how to validate the input.
##
## Note the use of standard PHP-DOC annotations, such as `@var string` - this metadata is
## traditionally useful both as documentation to developers, and as hints for an IDE. In
## this example, we're going to use that same information as advice to our components, at
## run-time, to help them establish defaults and make sensible decisions about how to
## handle the value of each property.

class Person
{
    /**
     * @var string
     * @required
     * @length(50)
     * @text('label' => 'Full Name')
     */
    public $name;

    /**
     * @var string
     * @length(50)
     * @text('label' => 'Street Address')
     */
    public $address;

    /**
     * @var int
     * @range(0, 100)
     */
    public $age;
}

## To build a simple form abstraction that can manage the state of an object being edited,
## we start with a simple, abstract base class for input widgets.

abstract class Widget
{
    protected $object;
    protected $property;

    public $value;

    ## Each widget will maintain a list of error messages.

    public $errors = array();

    ## A widget needs to know which property of what object is being edited.

    public function __construct($object, $property)
    {
        $this->object = $object;
        $this->property = $property;
        $this->value = $object->$property;
    }

    ## Widget classes will use this method to add an error-message.

    public function addError($message)
    {
        $this->errors[] = $message;
    }

    ## This helper function provides a shortcut to get a named property from a
    ## particular type of annotation - if no annotation is found, the `$default`
    ## value is returned instead.

    protected function getMetadata($type, $name, $default = null)
    {
        $a = Annotations::ofProperty($this->object, $this->property, $type);

        if (!count($a)) {
            return $default;
        }

        return $a[0]->$name;
    }

    ## Each type of widget will need to implement this interface, which takes a raw
    ## POST value from the form, and attempts to bind it to the object's property.

    abstract public function update($input);

    ## After a widget successfully updates a property, we may need to perform additional
    ## validation - this method will perform some basic validations, and if errors are
    ## found, it will add them to the `$errors` collection.

    public function validate()
    {
        if (empty($this->value)) {
            if ($this->isRequired()) {
                $this->addError("Please complete this field");
            } else {
                return;
            }
        }

        if (is_string($this->value)) {
            $min = $this->getMetadata('@length', 'min');
            $max = $this->getMetadata('@length', 'max');

            if ($min !== null && strlen($this->value) < $min) {
                $this->addError("Minimum length is {$min} characters");
            } else {
                if ($max !== null && strlen($this->value) > $max) {
                    $this->addError("Maximum length is {$max} characters");
                }
            }
        }

        if (is_int($this->value)) {
            $min = $this->getMetadata('@range', 'min');
            $max = $this->getMetadata('@range', 'max');

            if (($min !== null && $this->value < $min) || ($max !== null && $this->value > $max)) {
                $this->addError("Please enter a value in the range {$min} to {$max}");
            }
        }
    }

    ## Each type of widget will need to implement this interface, which renders an
    ## HTML input representing the widget's current value.

    abstract public function display();

    ## This helper function returns a descriptive label for the input.

    public function getLabel()
    {
        return $this->getMetadata('@text', 'label', ucfirst($this->property));
    }

    ## Finally, this little helper function will tell us if the field is required -
    ## if a property is annotated with `@required`, the field must be filled in.

    public function isRequired()
    {
        return count(Annotations::ofProperty($this->object, $this->property, '@required')) > 0;
    }
}

## The first and most basic kind of widget, is this simple string widget.

class StringWidget extends Widget
{
    ## On update, take into account the min/max string length, and provide error
    ## messages if the constraints are violated.

    public function update($input)
    {
        $this->value = $input;

        $this->validate();
    }

    ## On display, render out a simple `<input type="text"/>` field, taking into account
    ## the maximum string-length.

    public function display()
    {
        $length = $this->getMetadata('@length', 'max', 255);

        echo '<input type="text" name="' . get_class($this->object) . '[' . $this->property . ']"'
            . ' maxlength="' . $length . '" value="' . htmlspecialchars($this->value) . '"/>';
    }
}

## For the age input, we'll need a specialized `StringWidget` that also checks the input type.

class IntWidget extends StringWidget
{
    ## On update, take into account the min/max numerical range, and provide error
    ## messages if the constraints are violated.

    public function update($input)
    {
        if (strval(intval($input)) === $input) {
            $this->value = intval($input);
            $this->validate();
        } else {
            $this->value = $input;

            if (!empty($input)) {
                $this->addError("Please enter a whole number value");
            }
        }
    }
}

## Next, we can build a simple form abstraction - this will hold and object and manage
## the widgets required to edit the object.

class Form
{
    private $object;

    /**
     * Widget list.
     *
     * @var Widget[]
     */
    private $widgets = array();

    ## The constructor just needs to know which object we're editing.
    ##
    ## Using reflection, we enumerate the properties of the object's type, and using the
    ## `@var` annotation, we decide which type of widget we're going to use.

    public function __construct($object)
    {
        $this->object = $object;

        $class = new \ReflectionClass($this->object);

        foreach ($class->getProperties() as $property) {
            $type = $this->getMetadata($property->name, '@var', 'type', 'string');

            $wtype = 'mindplay\\demo\\' . ucfirst($type) . 'Widget';

            $this->widgets[$property->name] = new $wtype($this->object, $property->name);
        }
    }

    ## This helper-method is similar to the one we defined for the widget base
    ## class, but fetches annotations for the specified property.

    private function getMetadata($property, $type, $name, $default = null)
    {
        $a = Annotations::ofProperty(get_class($this->object), $property, $type);

        if (!count($a)) {
            return $default;
        }

        return $a[0]->$name;
    }

    ## When you post information back to the form, we'll need to update it's state,
    ## validate each of the fields, and return a value indicating whether the form
    ## update was successful.

    public function update($post)
    {
        $data = $post[get_class($this->object)];

        foreach ($this->widgets as $property => $widget) {
            if (array_key_exists($property, $data)) {
                $this->widgets[$property]->update($data[$property]);
            }
        }

        $valid = true;

        foreach ($this->widgets as $widget) {
            $valid = $valid && (count($widget->errors) === 0);
        }

        if ($valid) {
            foreach ($this->widgets as $property => $widget) {
                $this->object->$property = $widget->value;
            }
        }

        return $valid;
    }

    ## Finally, this method renders out the form, and each of the widgets inside, with
    ## a `<label>` tag surrounding each input.

    public function display()
    {
        foreach ($this->widgets as $widget) {
            $star = $widget->isRequired() ? ' <span style="color:red">*</span>' : '';
            echo '<label>' . htmlspecialchars($widget->getLabel()) . $star . '<br/>';
            $widget->display();
            echo '</label><br/>';

            if (count($widget->errors)) {
                echo '<ul>';
                foreach ($widget->errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
            }
        }
    }
}

## Now let's put the whole thing to work...
##
## We'll create a `Person` object, create a `Form` for the object, and render it!
##
## Try leaving the name field empty, or try to tell the form you're 120 years old -
## it won't pass validation.
##
## You can see the state of the object being displayed below the form - as you can
## see, unless all updates and validations succeed, the state of your object is
## left untouched.

echo <<<HTML
<html>
  <head>
    <title>Metaprogramming With Annotations!</title>
  </head>
  <body>
    <h1>Edit a Person!</h1>
    <h4>Declarative Metaprogramming in action!</h4>
    <form method="post">
HTML;

$person = new Person;

$form = new Form($person);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($form->update($_POST)) {
        echo '<h2 style="color:green">Person Accepted!</h2>';
    } else {
        echo '<h2 style="color:red">Oops! Try again.</h2>';
    }
}

$form->display();

echo <<<HTML
    <br/>
    <input type="submit" value="Go!"/>
    </form>
HTML;

echo "<pre>\n\nHere's what your Person instance currently looks like:\n\n";
var_dump($person);
echo '</pre>';

echo <<<HTML
  </body>
</html>
HTML;
