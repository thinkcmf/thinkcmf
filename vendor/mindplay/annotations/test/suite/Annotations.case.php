<?php

/**
 * TEST CASE: Sample Annotations
 */

use mindplay\annotations\Annotation;
use mindplay\annotations\IAnnotationParser;
use mindplay\annotations\AnnotationException;
use mindplay\annotations\standard\TypeAnnotation;

/**
 * @usage('class'=>true, 'property'=>true, 'method'=>true, 'inherited'=>true, 'multiple'=>true)
 */
class NoteAnnotation extends Annotation
{
    public $note;

    public function initAnnotation(array $params)
    {
        $this->map($params, array('note'));

        if (!isset($this->note)) {
            throw new AnnotationException("NoteAnnotation requires a note property");
        }
    }
}

/**
 * @usage
 */
class UselessAnnotation extends Annotation
{


}

/**
 * @usage('class'=>true)
 */
class DocAnnotation extends Annotation implements IAnnotationParser
{
    public $value;

    public static function parseAnnotation($value)
    {
        return array('value' => intval($value));
    }
}

/**
 * @usage('property'=>true, 'multiple'=>false)
 */
class SingleAnnotation extends Annotation
{
    public $test;
}

/**
 * @usage('property'=>true, 'multiple'=>false, 'inherited'=>true)
 */
class OverrideAnnotation extends Annotation
{
    public $test;
}

/**
 * @usage('method'=>true)
 */
class SampleAnnotation extends Annotation
{
    public $test;
}

/**
 * @usage('class'=>true, 'inherited'=>false)
 */
class UninheritableAnnotation extends Annotation
{
    public $test;
}

class InheritUsageAnnotation extends SampleAnnotation
{


}

/**
 * @Doc
 * @usage('class'=>true)
 */
class UsageAndNonUsageAnnotation extends Annotation
{

}

/**
 * @Doc
 */
class SingleNonUsageAnnotation extends Annotation
{


}

class WrongInterfaceAnnotation
{

}

class TypeAwareAnnotation extends TypeAnnotation
{


}

class NoUsageAnnotation
{

}

/**
 * TEST CASE: Sample Classes
 *
 * @doc 1234 (this is a sample PHP-DOC style annotation)
 */

/**
 * @note("Applied to the TestBase class")
 * @uninheritable('test'=>'Test cannot inherit this annotation')
 */
class TestBase
{
    /**
     * @note("Applied to a TestBase member")
     */
    protected $sample = 'test';

    /**
     * @single('test'=>'one is okay')
     * @single('test'=>'two is one too many')
     */
    protected $only_one;

    /**
     * @override('test'=>'This will be overridden')
     */
    private $override_me;

    /**
     * @note("First note annotation")
     * @override('test'=>'This annotation should get filtered')
     */
    private $mixed;

    /**
     * @note("Applied to a hidden TestBase method")
     * @sample('test'=>'This should get filtered')
     */
    public function run()
    {
    }
}

/**
 * A sample class with NoteAttributes applied to the source code:
 *
 * @Note(
 *   "Applied to the Test class (a)"
 * )
 *
 * @Note("And another one for good measure (b)")
 */
class Test extends TestBase
{
    /**
     * @Note("Applied to a property")
     */
    public $hello = 'World';
    /**
     * @Override('test'=>'This annotation overrides the one in TestBase')
     */
    private $override_me;
    /**
     * @Note("Second note annotation")
     */
    private $mixed;

    /**
     * @Note("First Note Applied to the run() method")
     * @Note("And a second Note")
     */
    public function run()
    {
    }
}

/**
 * @Note('class-first')
 */
class FirstClass
{
    /**
     * @var string
     * @Note('prop-first')
     */
    protected $prop;

    /**
     * @Note('method-first')
     */
    protected function someMethod()
    {

    }
}

/**
 * @Note('class-second')
 * @stop
 */
class SecondClass extends FirstClass
{
    /**
     * @var string
     * @Note('prop-second')
     */
    protected $prop;

    /**
     * @Note('method-second')
     */
    protected function someMethod()
    {

    }
}

/**
 * @Note('class-third')
 */
class ThirdClass extends SecondClass
{
    /**
     * @var string
     * @Note('prop-third')
     */
    protected $prop;

    /**
     * @Note('method-third')
     */
    protected function someMethod()
    {

    }
}


/**
 * Test that using an core class will not break parsing.
 * https://github.com/php-annotations/php-annotations/issues/59
 *
 * @Note("An example class annotation.")
 */
class TestClassExtendingCore extends ReflectionClass
{

}

/**
 * Test that using an extension class will not break parsing.
 * https://github.com/php-annotations/php-annotations/issues/59
 *
 * @Note("An example class annotation.")
 */
class TestClassExtendingExtension extends SplFileObject
{

}

/**
 * @Note("Base note.")
 */
class TestClassExtendingUserDefinedBase
{

}

/**
 * Test that using an user defined class will not break parsing.
 * https://github.com/php-annotations/php-annotations/issues/59
 *
 * @Note("Note of child.")
 */
class TestClassExtendingUserDefined extends TestClassExtendingUserDefinedBase
{

}
/**
 * @WrongInterface
 */
class TestClassWrongInterface
{

}

class TestClassFileAwareAnnotation
{

    /** @TypeAware IAnnotationParser */
    public $prop;

}

interface TestInterface
{

}

class BrokenParamAnnotationClass
{

    /**
     * @param $paramName
     */
    protected function brokenParamAnnotation($paramName)
    {

    }
}
