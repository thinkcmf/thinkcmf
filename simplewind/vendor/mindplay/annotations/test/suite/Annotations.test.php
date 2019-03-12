<?php
require_once __DIR__ . '/Annotations.case.php';
require_once __DIR__ . '/Annotations.Sample.case.php';

use mindplay\annotations\AnnotationFile;
use mindplay\annotations\AnnotationCache;
use mindplay\annotations\AnnotationManager;
use mindplay\annotations\Annotations;
use mindplay\annotations\Annotation;
use mindplay\annotations\standard\ReturnAnnotation;
use mindplay\test\annotations\Package;
use mindplay\test\lib\xTest;
use mindplay\test\lib\xTestRunner;

if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
    require_once __DIR__ . '/traits/namespaced.php';
    require_once __DIR__ . '/traits/toplevel.php';
}

/**
 * This class implements tests for core annotations
 */
class AnnotationsTest extends xTest
{
    const ANNOTATION_EXCEPTION = 'mindplay\annotations\AnnotationException';

    /**
     * Run this test.
     *
     * @param xTestRunner $testRunner Test runner.
     * @return boolean
     */
    public function run(xTestRunner $testRunner)
    {
        $testRunner->startCoverageCollector(__CLASS__);
        $cachePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'runtime';

        Annotations::$config = array(
            'cache' => new AnnotationCache($cachePath),
        );

        if (!is_writable($cachePath)) {
            die('cache path is not writable: ' . $cachePath);
        }

        // manually wipe out the cache:
        $pattern = Annotations::getManager()->cache->getRoot() . DIRECTORY_SEPARATOR . '*.annotations.php';

        foreach (glob($pattern) as $path) {
            unlink($path);
        }

        // disable some annotations not used during testing:
        Annotations::getManager()->registry['var'] = false;
        Annotations::getManager()->registry['undefined'] = 'UndefinedAnnotation';
        $testRunner->stopCoverageCollector();

        return parent::run($testRunner);
    }

    protected function testCanResolveAnnotationNames()
    {
        $manager = new AnnotationManager;
        $manager->namespace = ''; // look for annotations in the global namespace
        $manager->suffix = 'Annotation'; // use a suffix for annotation class-names

        $this->check(
            $manager->resolveName('test') === 'TestAnnotation',
            'should capitalize and suffix annotation names'
        );
        $this->check(
            $manager->resolveName('X\Y\Foo') === 'X\Y\FooAnnotation',
            'should suffix fully qualified annotation names'
        );

        $manager->registry['test'] = 'X\Y\Z\TestAnnotation';
        $this->check(
            $manager->resolveName('test') === 'X\Y\Z\TestAnnotation',
            'should respect registered annotation types'
        );
        $this->check(
            $manager->resolveName('Test') === 'X\Y\Z\TestAnnotation',
            'should ignore case of first letter in annotation names'
        );

        $manager->registry['test'] = false;
        $this->check($manager->resolveName('test') === false, 'should respect disabled annotation types');

        $manager->namespace = 'ABC';
        $this->check($manager->resolveName('hello') === 'ABC\HelloAnnotation', 'should default to standard namespace');
    }

    protected function testCanGetAnnotationFile()
    {
        // This test is for an internal API, so we need to perform some invasive maneuvers:

        $manager = Annotations::getManager();

        $manager_reflection = new ReflectionClass($manager);

        $method = $manager_reflection->getMethod('getAnnotationFile');
        $method->setAccessible(true);

        $class_reflection = new ReflectionClass('mindplay\test\Sample\SampleClass');

        // absolute path to the class-file used for testing
        $file_path = $class_reflection->getFileName();

        // Now get the AnnotationFile instance:

        /** @var AnnotationFile $file */
        $file = $method->invoke($manager, $file_path);

        $this->check($file instanceof AnnotationFile, 'should be an instance of AnnotationFile');
        $this->check(count($file->data) > 0, 'should contain Annotation data');
        $this->check($file->path === $file_path, 'should reflect path to class-file');
        $this->check($file->namespace === 'mindplay\test\Sample', 'should reflect namespace');
        $this->check(
            $file->uses === array('Test' => 'Test', 'SampleAlias' => 'mindplay\annotations\Annotation'),
            'should reflect use-clause'
        );
    }

    protected function testCanParseAnnotations()
    {
        $manager = new AnnotationManager;
        Package::register($manager);
        $manager->namespace = ''; // look for annotations in the global namespace
        $manager->suffix = 'Annotation'; // use a suffix for annotation class-names

        $parser = $manager->getParser();

        $source = "
            <?php

            namespace foo\\bar;

            use
                baz\\Hat as Zing,
                baz\\Zap;

            /**
             * @doc 123
             * @note('abc')
             * @required
             * @note('xyz');
             */
            class Sample {
                public function test()
                {
                    \$var = null;

                    \$test = function () use (\$var) {
                        // this inline function is here to assert that the parser
                        // won't pick up the use-clause of an inline function
                    };
                }
            }
        ";

        $code = $parser->parse($source, 'inline-test');
        $test = eval($code);

        $this->check($test['#namespace'] === 'foo\bar', 'file namespace should be parsed and cached');
        $this->check(
            $test['#uses'] === array('Zing' => 'baz\Hat', 'Zap' => 'baz\Zap'),
            'use-clauses should be parsed and cached: ' . var_export($test['#uses'], true)
        );

        $this->check($test['foo\bar\Sample'][0]['#name'] === 'doc', 'first annotation is an @doc annotation');
        $this->check($test['foo\bar\Sample'][0]['#type'] === 'DocAnnotation', 'first annotation is a DocAnnotation');
        $this->check($test['foo\bar\Sample'][0]['value'] === 123, 'first annotation has the value 123');

        $this->check($test['foo\bar\Sample'][1]['#name'] === 'note', 'second annotation is an @note annotation');
        $this->check($test['foo\bar\Sample'][1]['#type'] === 'NoteAnnotation', 'second annotation is a NoteAnnotation');
        $this->check($test['foo\bar\Sample'][1][0] === 'abc', 'value of second annotation is "abc"');

        $this->check(
            $test['foo\bar\Sample'][2]['#type'] === 'mindplay\test\annotations\RequiredAnnotation',
            'third annotation is a RequiredAnnotation'
        );

        $this->check($test['foo\bar\Sample'][3]['#type'] === 'NoteAnnotation', 'last annotation is a NoteAnnotation');
        $this->check($test['foo\bar\Sample'][3][0] === 'xyz', 'value of last annotation is "xyz"');
    }

    protected function testCanGetStaticAnnotationManager()
    {
        if (Annotations::getManager() instanceof AnnotationManager) {
            $this->pass();
        } else {
            $this->fail();
        }
    }

    protected function testCanGetAnnotationUsage()
    {
        $usage = Annotations::getUsage('NoteAnnotation');

        $this->check($usage->class === true);
        $this->check($usage->property === true);
        $this->check($usage->method === true);
        $this->check($usage->inherited === true);
        $this->check($usage->multiple === true);
    }

    protected function testAnnotationWithNonUsageAndUsageAnnotations()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "The class 'UsageAndNonUsageAnnotation' must have exactly one UsageAnnotation (no other Annotations are allowed)"
        );

        Annotations::getUsage('UsageAndNonUsageAnnotation');
    }

    protected function testAnnotationWithSingleNonUsageAnnotation()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "The class 'SingleNonUsageAnnotation' must have exactly one UsageAnnotation (no other Annotations are allowed)"
        );

        Annotations::getUsage('SingleNonUsageAnnotation');
    }

    protected function testUsageAnnotationIsInherited()
    {
        $usage = Annotations::getUsage('InheritUsageAnnotation');
        $this->check($usage->method === true);
    }

    protected function testGetUsageOfUndefinedAnnotationClass()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Annotation type 'NoSuchAnnotation' does not exist"
        );

        Annotations::getUsage('NoSuchAnnotation');
    }

    protected function testAnnotationWithoutUsageAnnotation()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "The class 'NoUsageAnnotation' must have exactly one UsageAnnotation"
        );

        Annotations::getUsage('NoUsageAnnotation');
    }

    protected function testCanGetClassAnnotations()
    {
        $annotations = Annotations::ofClass(new \ReflectionClass('Test'));
        $this->check(count($annotations) > 0, 'from class reflection');

        $annotations = Annotations::ofClass(new Test());
        $this->check(count($annotations) > 0, 'from class object');

        $annotations = Annotations::ofClass('Test');
        $this->check(count($annotations) > 0, 'from class name');
    }

    protected function testCanGetMethodAnnotations()
    {
        $annotations = Annotations::ofMethod(new \ReflectionClass('Test'), 'run');
        $this->check(count($annotations) > 0, 'from class reflection and method name');

        $annotations = Annotations::ofMethod(new \ReflectionMethod('Test', 'run'));
        $this->check(count($annotations) > 0, 'from method reflection');

        $annotations = Annotations::ofMethod(new Test(), 'run');
        $this->check(count($annotations) > 0, 'from class object and method name');

        $annotations = Annotations::ofMethod('Test', 'run');
        $this->check(count($annotations) > 0, 'from class name and method name');
    }

    protected function testGetAnnotationsFromMethodOfNonExistingClass()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Unable to read annotations from an undefined class 'NonExistingClass'"
        );
        Annotations::ofMethod('NonExistingClass');
    }

    protected function testGetAnnotationsFromNonExistingMethodOfAClass()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            'Unable to read annotations from an undefined method Test::nonExistingMethod()'
        );
        Annotations::ofMethod('Test', 'nonExistingMethod');
    }

    protected function testCanGetPropertyAnnotations()
    {
        $annotations = Annotations::ofProperty(new \ReflectionClass('Test'), 'sample');
        $this->check(count($annotations) > 0, 'from class reflection and property name');

        $annotations = Annotations::ofProperty(new \ReflectionProperty('TestBase', 'sample'));
        $this->check(count($annotations) > 0, 'from property reflection');

        $annotations = Annotations::ofProperty(new Test(), 'sample');
        $this->check(count($annotations) > 0, 'from class object and property name');

        $annotations = Annotations::ofProperty('Test', 'sample');
        $this->check(count($annotations) > 0, 'from class name and property name');
    }

    protected function testGetAnnotationsFromPropertyOfNonExistingClass()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Unable to read annotations from an undefined class 'NonExistingClass'"
        );
        Annotations::ofProperty('NonExistingClass', 'sample');
    }

    public function testGetAnnotationsFromNonExistingPropertyOfExistingClass()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            'Unable to read annotations from an undefined property Test::$nonExisting'
        );
        Annotations::ofProperty('Test', 'nonExisting');
    }

    protected function testCanGetFilteredClassAnnotations()
    {
        $anns = Annotations::ofClass('TestBase', 'NoteAnnotation');

        if (!count($anns)) {
            $this->fail('No annotations found');
            return;
        }

        foreach ($anns as $ann) {
            if (!$ann instanceof NoteAnnotation) {
                $this->fail();
            }
        }

        $this->pass();
    }

    protected function testCanGetFilteredMethodAnnotations()
    {
        $anns = Annotations::ofMethod('TestBase', 'run', 'NoteAnnotation');

        if (!count($anns)) {
            $this->fail('No annotations found');
            return;
        }

        foreach ($anns as $ann) {
            if (!$ann instanceof NoteAnnotation) {
                $this->fail();
            }
        }

        $this->pass();
    }

    protected function testCanGetFilteredPropertyAnnotations()
    {
        $anns = Annotations::ofProperty('Test', 'mixed', 'NoteAnnotation');

        if (!count($anns)) {
            $this->fail('No annotations found');
            return;
        }

        foreach ($anns as $ann) {
            if (!$ann instanceof NoteAnnotation) {
                $this->fail();
            }
        }

        $this->pass();
    }

    protected function testCanGetInheritedClassAnnotations()
    {
        $anns = Annotations::ofClass('Test');

        foreach ($anns as $ann) {
            if ($ann->note == 'Applied to the TestBase class') {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    protected function testCanGetInheritedMethodAnnotations()
    {
        $anns = Annotations::ofMethod('Test', 'run');

        foreach ($anns as $ann) {
            if ($ann->note == 'Applied to a hidden TestBase method') {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    protected function testCanGetInheritedPropertyAnnotations()
    {
        $anns = Annotations::ofProperty('Test', 'sample');

        foreach ($anns as $ann) {
            if ($ann->note == 'Applied to a TestBase member') {
                $this->pass();
                return;
            }
        }

        $this->fail();
    }

    protected function testDoesNotInheritUninheritableAnnotations()
    {
        $anns = Annotations::ofClass('Test');

        if (count($anns) == 0) {
            $this->fail();
            return;
        }

        foreach ($anns as $ann) {
            if ($ann instanceof UninheritableAnnotation) {
                $this->fail();
                return;
            }
        }

        $this->pass();
    }

    protected function testThrowsExceptionIfSingleAnnotationAppliedTwice()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Only one annotation of 'SingleAnnotation' type may be applied to the same property"
        );

        Annotations::ofProperty('Test', 'only_one');
    }

    protected function testCanOverrideSingleAnnotation()
    {
        $anns = Annotations::ofProperty('Test', 'override_me');

        if (count($anns) != 1) {
            $this->fail(count($anns) . ' annotations found - expected 1');
            return;
        }

        $ann = reset($anns);

        if ($ann->test != 'This annotation overrides the one in TestBase') {
            $this->fail();
        } else {
            $this->pass();
        }
    }

    protected function testCanHandleEdgeCaseInParser()
    {
        // an edge-case was found in the parser - this test asserts that a php-doc style
        // annotation with no trailing characters after it will be parsed correctly.

        $anns = Annotations::ofClass('TestBase', 'DocAnnotation');

        $this->check(count($anns) == 1, 'one DocAnnotation was expected - found ' . count($anns));
    }

    protected function testCanHandleNamespaces()
    {
        // This test asserts that a namespaced class can be annotated, that annotations can
        // be namespaced, and that asking for annotations of a namespaced annotation-type
        // yields the expected result.

        $anns = Annotations::ofClass('mindplay\test\Sample\SampleClass', 'mindplay\test\Sample\SampleAnnotation');

        $this->check(count($anns) == 1, 'one SampleAnnotation was expected - found ' . count($anns));
    }

    protected function testCanUseAnnotationsInDefaultNamespace()
    {
        $manager = new AnnotationManager();
        $manager->namespace = 'mindplay\test\Sample';
        $manager->cache = false;

        $anns = $manager->getClassAnnotations(
            'mindplay\test\Sample\AnnotationInDefaultNamespace',
            'mindplay\test\Sample\SampleAnnotation'
        );

        $this->check(count($anns) == 1, 'one SampleAnnotation was expected - found ' . count($anns));
    }

    protected function testCanIgnoreAnnotations()
    {
        $manager = new AnnotationManager();
        $manager->namespace = 'mindplay\test\Sample';
        $manager->cache = false;

        $manager->registry['ignored'] = false;

        $anns = $manager->getClassAnnotations('mindplay\test\Sample\IgnoreMe');

        $this->check(count($anns) == 0, 'the @ignored annotation should be ignored');
    }

    protected function testCanUseAnnotationAlias()
    {
        $manager = new AnnotationManager();
        $manager->namespace = 'mindplay\test\Sample';
        $manager->cache = false;

        $manager->registry['aliased'] = 'mindplay\test\Sample\SampleAnnotation';

        /** @var Annotation[] $anns */
        $anns = $manager->getClassAnnotations('mindplay\test\Sample\AliasMe');

        $this->check(count($anns) == 1, 'the @aliased annotation should be aliased');
        $this->check(
            get_class($anns[0]) == 'mindplay\test\Sample\SampleAnnotation',
            'returned @aliased annotation should map to mindplay\test\Sample\SampleAnnotation'
        );
    }

    protected function testCanFindAnnotationsByAlias()
    {
        $ann = Annotations::ofProperty('TestBase', 'sample', '@note');

        $this->check(count($ann) === 1, 'TestBase::$sample has one @note annotation');
    }

    protected function testParseUserDefinedClasses()
    {
        $annotations = Annotations::ofClass('TestClassExtendingUserDefined', '@note');

        $this->check(count($annotations) == 2, 'TestClassExtendingUserDefined has two note annotations.');
    }

    protected function testDoNotParseCoreClasses()
    {
        $annotations = Annotations::ofClass('TestClassExtendingCore', '@note');

        $this->check(count($annotations) == 1, 'TestClassExtendingCore has one note annotations.');
    }

    protected function testDoNotParseExtensionClasses()
    {
        $annotations = Annotations::ofClass('TestClassExtendingExtension', '@note');

        $this->check(count($annotations) == 1, 'TestClassExtendingExtension has one note annotations.');
    }

    protected function testGetAnnotationsFromNonExistingClass()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Unable to read annotations from an undefined class/trait 'NonExistingClass'"
        );
        Annotations::ofClass('NonExistingClass', '@note');
    }

    protected function testGetAnnotationsFromAnInterface()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Reading annotations from interface 'TestInterface' is not supported"
        );
        Annotations::ofClass('TestInterface', '@note');
    }

    protected function testGetAnnotationsFromTrait()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->pass();
            return;
        }

        $annotations = Annotations::ofClass('SimpleTrait', '@note');

        $this->check(count($annotations) === 1, 'SimpleTrait has one note annotation.');
    }

    protected function testCanGetMethodAnnotationsIncludedFromTrait()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->pass();
            return;
        }

        $annotations = Annotations::ofMethod('SimpleTraitTester', 'runFromTrait');
        $this->check(count($annotations) > 0, 'for unnamespaced trait');

        $annotations = Annotations::ofMethod('SimpleTraitTester', 'runFromAnotherTrait');
        $this->check(count($annotations) > 0, 'for namespaced trait');
    }

    protected function testHandlesMethodInheritanceWithTraits()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->pass();
            return;
        }

        $annotations = Annotations::ofMethod('InheritanceTraitTester', 'baseTraitAndParent');
        $this->check(count($annotations) === 2, 'baseTraitAndParent inherits parent annotations');
        $this->check($annotations[0]->note === 'inheritance-base-trait-tester', 'parent annotation first');
        $this->check($annotations[1]->note === 'inheritance-base-trait', 'trait annotation second');

        $annotations = Annotations::ofMethod('InheritanceTraitTester', 'traitAndParent');
        $this->check(count($annotations) === 2, 'traitAndParent inherits parent annotations');
        $this->check($annotations[0]->note === 'inheritance-base-trait-tester', 'parent annotation first');
        $this->check($annotations[1]->note === 'inheritance-trait', 'trait annotation second');

        $annotations = Annotations::ofMethod('InheritanceTraitTester', 'traitAndChild');
        $this->check(count($annotations) === 1, 'traitAndChild does not inherit trait');
        $this->check($annotations[0]->note === 'inheritance-trait-tester', 'child annotation first');

        $annotations = Annotations::ofMethod('InheritanceTraitTester', 'traitAndParentAndChild');
        $this->check(count($annotations) === 2, 'traitAndParentAndChild does not inherit trait annotation');
        $this->check($annotations[0]->note === 'inheritance-base-trait-tester', 'parent annotation first');
        $this->check($annotations[1]->note === 'inheritance-trait-tester', 'child annotation second');
    }

    protected function testHandlesMethodAliasingWithTraits()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->pass();
            return;
        }

        $annotations = Annotations::ofMethod('AliasTraitTester', 'baseTraitRun');
        $this->check(count($annotations) === 2, 'baseTraitRun inherits annotation');
        $this->check($annotations[0]->note === 'alias-base-trait-tester', 'inherited annotation goes first');
        $this->check($annotations[1]->note === 'alias-base-trait', 'non-inherited annotation goes second');

        $annotations = Annotations::ofMethod('AliasTraitTester', 'traitRun');
        $this->check(count($annotations) === 2, 'traitRun inherits annotation');
        $this->check($annotations[0]->note === 'alias-base-trait-tester', 'inherited annotation goes first');
        $this->check($annotations[1]->note === 'alias-trait', 'non-inherited annotation goes second');

        $annotations = Annotations::ofMethod('AliasTraitTester', 'run');
        $this->check(count($annotations) === 2, 'run inherits annotation');
        $this->check($annotations[0]->note === 'alias-base-trait-tester', 'inherited annotation goes first');
        $this->check($annotations[1]->note === 'alias-trait-tester', 'non-inherited annotation goes second');
    }

    protected function testHandlesConflictedMethodSelectionWithTraits()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->pass();
            return;
        }

        $annotations = Annotations::ofMethod('InsteadofTraitTester', 'baseTrait');
        $this->check(count($annotations) === 2, 'baseTrait inherits annotation');
        $this->check($annotations[0]->note === 'insteadof-base-trait-tester', 'inherited annotation goes first');
        $this->check($annotations[1]->note === 'insteadof-base-trait-b', 'non-inherited annotation goes second');

        $annotations = Annotations::ofMethod('InsteadofTraitTester', 'trate');
        $this->check(count($annotations) === 2, 'trate inherits annotation');
        $this->check($annotations[0]->note === 'insteadof-base-trait-tester', 'inherited annotation goes first');
        $this->check($annotations[1]->note === 'insteadof-trait-a', 'non-inherited annotation goes second');
    }

    protected function testCanGetPropertyAnnotationsIncludedFromTrait()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->pass();
            return;
        }

        $annotations = Annotations::ofProperty('SimpleTraitTester', 'sampleFromTrait');
        $this->check(count($annotations) > 0, 'for unnamespaced trait');

        $annotations = Annotations::ofProperty('SimpleTraitTester', 'sampleFromAnotherTrait');
        $this->check(count($annotations) > 0, 'for namespaced trait');
    }

    protected function testHandlesPropertyConflictWithTraits()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->pass();
            return;
        }

        set_error_handler(function ($errno, $errstring) { });
        require_once __DIR__ . '/traits/property_conflict.php';
        restore_error_handler();

        $annotations = Annotations::ofProperty('PropertyConflictTraitTester', 'traitAndChild');
        $this->check(count($annotations) === 1, 'traitAndChild does not inherit trait');
        $this->check($annotations[0]->note === 'property-conflict-trait-tester', 'child annotation first');

        $annotations = Annotations::ofProperty('PropertyConflictTraitTester', 'traitAndTraitAndParent');
        $this->check(count($annotations) === 2, 'traitAndTraitAndParent inherits parent annotations');
        $this->check($annotations[0]->note === 'property-conflict-base-trait-tester', 'parent annotation first');
        $this->check($annotations[1]->note === 'property-conflict-trait-two', 'first listed trait annotation second');

        $annotations = Annotations::ofProperty('PropertyConflictTraitTester', 'unannotatedTraitAndAnnotatedTrait');
        $this->check(count($annotations) === 0, 'unannotatedTraitAndAnnotatedTrait has no annotations');

        $annotations = Annotations::ofProperty('PropertyConflictTraitTester', 'traitAndParentAndChild');
        $this->check(count($annotations) === 2, 'traitAndParentAndChild does not inherit trait annotation');
        $this->check($annotations[0]->note === 'property-conflict-base-trait-tester', 'parent annotation first');
        $this->check($annotations[1]->note === 'property-conflict-trait-tester', 'child annotation second');
    }

    protected function testDisallowReadingUndefinedAnnotationProperties()
    {
        $nodeAnnotation = new NoteAnnotation();
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            'NoteAnnotation::$nonExisting is not a valid property name'
        );
        $result = $nodeAnnotation->nonExisting;
    }

    protected function testDisallowWritingUndefinedAnnotationProperties()
    {
        $nodeAnnotation = new NoteAnnotation();
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            'NoteAnnotation::$nonExisting is not a valid property name'
        );
        $nodeAnnotation->nonExisting = 'new value';
    }

    protected function testAnnotationCacheGetTimestamp()
    {
        $annotationCache = new AnnotationCache(sys_get_temp_dir());
        $annotationCache->store('sample', '');

        $this->check(
            $annotationCache->getTimestamp('sample') > strtotime('midnight'),
            'Annotation cache last update timestamp is not stale'
        );
    }

    protected function testAnnotationFileTypeResolution()
    {
        $annotationFile = new AnnotationFile('', array(
            '#namespace' => 'LevelA\NS',
            '#uses' => array(
                'LevelBClass' => 'LevelB\Class',
            ),
        ));

        $this->check(
            $annotationFile->resolveType('SubNS1\SubClass') == 'LevelA\NS\SubNS1\SubClass',
            'Class in sub-namespace is resolved correctly'
        );

        $this->check(
            $annotationFile->resolveType('\SubNS1\SubClass') == '\SubNS1\SubClass',
            'Fully qualified class name is not changed during resolution'
        );

        $this->check(
            $annotationFile->resolveType('LevelBClass') == 'LevelB\Class',
            'The "uses ..." clause (exact match) is being used during resolution'
        );

        $this->check(
            $annotationFile->resolveType('SomeClass[]') == 'LevelA\NS\SomeClass[]',
            'The [] at then end of data type are preserved during resolution'
        );

        $this->check(
            $annotationFile->resolveType('integer') == 'integer',
            'Simple data type is kept as-is during resolution'
        );
    }

    protected function testAnnotationManagerWithoutCache()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            'AnnotationManager::$cache is not configured'
        );

        $annotationManager = new AnnotationManager();
        $annotationManager->getClassAnnotations('NoteAnnotation');
    }

    protected function testUsingNonExistentAnnotation()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Annotation type 'WrongInterfaceAnnotation' does not implement the mandatory IAnnotation interface"
        );

        Annotations::ofClass('TestClassWrongInterface');
    }

    protected function testReadingFileAwareAnnotation()
    {
        $annotations = Annotations::ofProperty('TestClassFileAwareAnnotation', 'prop', '@TypeAware');

        $this->check(count($annotations) == 1, 'the @TypeAware annotation was found');
        $this->check(
            $annotations[0]->type == 'mindplay\annotations\IAnnotationParser',
            'data type of type-aware annotation was resolved'
        );
    }

    protected function testAnnotationsConstrainedByCorrectUsageAnnotation()
    {
        $annotations = array(new NoteAnnotation());

        $this->assertApplyConstrains($annotations, 'class');
    }

    protected function testAnnotationsConstrainedByClass()
    {
        $annotations = array(new UselessAnnotation());

        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Annotation type 'UselessAnnotation' cannot be applied to a class"
        );

        $this->assertApplyConstrains($annotations, AnnotationManager::MEMBER_CLASS);
    }

    protected function testAnnotationsConstrainedByMethod()
    {
        $annotations = array(new UselessAnnotation());

        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Annotation type 'UselessAnnotation' cannot be applied to a method"
        );

        $this->assertApplyConstrains($annotations, AnnotationManager::MEMBER_METHOD);
    }

    protected function testAnnotationsConstrainedByProperty()
    {
        $annotations = array(new UselessAnnotation());

        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            "Annotation type 'UselessAnnotation' cannot be applied to a property"
        );

        $this->assertApplyConstrains($annotations, AnnotationManager::MEMBER_PROPERTY);
    }

    protected function assertApplyConstrains(array &$annotations, $memberType)
    {
        $manager = Annotations::getManager();
        $methodReflection = new ReflectionMethod(get_class($manager), 'applyConstraints');
        $methodReflection->setAccessible(true);
        $methodReflection->invokeArgs($manager, array(&$annotations, $memberType));

        $this->check(count($annotations) > 0);
    }

    public function testStopAnnotationPreventsClassLevelAnnotationInheritance()
    {
        $annotations = Annotations::ofClass('SecondClass', '@note');
        $this->check(count($annotations) === 1, 'class level annotation after own "@stop" not present');
        $this->check($annotations[0]->note === 'class-second', 'non-inherited annotation goes first');

        $annotations = Annotations::ofClass('ThirdClass', '@note');
        $this->check(count($annotations) === 2, 'class level annotation after parent "@stop" not present');
        $this->check($annotations[0]->note === 'class-second', 'inherited annotation goes first');
        $this->check($annotations[1]->note === 'class-third', 'non-inherited annotation goes second');
    }

    public function testStopAnnotationPreventsPropertyLevelAnnotationInheritance()
    {
        $annotations = Annotations::ofProperty('SecondClass', 'prop', '@note');
        $this->check(count($annotations) === 1, 'property level annotation after own "@stop" not present');
        $this->check($annotations[0]->note === 'prop-second', 'non-inherited annotation goes first');

        $annotations = Annotations::ofProperty('ThirdClass', 'prop', '@note');
        $this->check(count($annotations) === 2, 'property level annotation after parent "@stop" not present');
        $this->check($annotations[0]->note === 'prop-second', 'inherited annotation goes first');
        $this->check($annotations[1]->note === 'prop-third', 'non-inherited annotation goes second');
    }

    public function testStopAnnotationPreventsMethodLevelAnnotationInheritance()
    {
        $annotations = Annotations::ofMethod('SecondClass', 'someMethod', '@note');
        $this->check(count($annotations) === 1, 'method level annotation after own "@stop" not present');
        $this->check($annotations[0]->note === 'method-second', 'non-inherited annotation goes first');

        $annotations = Annotations::ofMethod('ThirdClass', 'someMethod', '@note');
        $this->check(count($annotations) === 2, 'method level annotation after parent "@stop" not present');
        $this->check($annotations[0]->note === 'method-second', 'inherited annotation goes first');
        $this->check($annotations[1]->note === 'method-third', 'non-inherited annotation goes second');
    }

    public function testDirectAccessToClassIgnoresStopAnnotation()
    {
        $annotations = Annotations::ofClass('FirstClass', '@note');
        $this->check(count($annotations) === 1);
        $this->check($annotations[0]->note === 'class-first');

        $annotations = Annotations::ofProperty('FirstClass', 'prop', '@note');
        $this->check(count($annotations) === 1);
        $this->check($annotations[0]->note === 'prop-first');

        $annotations = Annotations::ofMethod('FirstClass', 'someMethod', '@note');
        $this->check(count($annotations) === 1);
        $this->check($annotations[0]->note === 'method-first');
    }

    protected function testFilterUnresolvedAnnotationClass()
    {
        $annotations = Annotations::ofClass('TestBase', false);

        $this->check($annotations === array(), 'empty annotation list when filtering failed');
    }

    public function testMalformedParamAnnotationThrowsException()
    {
        $this->setExpectedException(
            self::ANNOTATION_EXCEPTION,
            'ParamAnnotation requires a type property'
        );

        Annotations::ofMethod('BrokenParamAnnotationClass', 'brokenParamAnnotation');
    }

    protected function testOrphanedAnnotationsAreIgnored()
    {
        $manager = new AnnotationManager();
        $manager->namespace = 'mindplay\test\Sample';
        $manager->cache = false;

        /** @var Annotation[] $annotations */
        $annotations = $manager->getMethodAnnotations('mindplay\test\Sample\OrphanedAnnotations', 'someMethod');

        $this->check(count($annotations) == 1, 'the @return annotation was found');
        $this->check(
            $annotations[0] instanceof ReturnAnnotation,
            'the @return annotation has correct type'
        );
    }

}

return new AnnotationsTest;
