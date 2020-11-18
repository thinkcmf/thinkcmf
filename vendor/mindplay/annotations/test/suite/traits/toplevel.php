<?php

use mindplay\test\traits\AliasTrait;
use mindplay\test\traits\InsteadofTraitA;

/**
 * @note('simple-trait')
 */
trait SimpleTrait
{
    /**
     * @note('simple-trait')
     */
    protected $sampleFromTrait = 'test';

    /**
     * @Note('simple-trait')
     */
    public function runFromTrait()
    {
    }
}

class SimpleTraitTester
{
    use SimpleTrait, mindplay\test\traits\AnotherSimpleTrait;
}

trait InheritanceBaseTrait
{
    /**
     * @Note('inheritance-base-trait')
     */
    public function traitAndParent()
    {
    }

    /**
     * @Note('inheritance-base-trait')
     */
    public function baseTraitAndParent()
    {
    }
}

trait InheritanceTrait
{
    use InheritanceBaseTrait;

    /**
     * @Note('inheritance-trait')
     */
    public function traitAndParent()
    {
    }

    /**
     * @Note('inheritance-trait')
     */
    public function traitAndChild()
    {
    }

    /**
     * @Note('inheritance-trait')
     */
    public function traitAndParentAndChild()
    {
    }
}

class InheritanceBaseTraitTester
{
    /**
     * @Note('inheritance-base-trait-tester')
     */
    public function baseTraitAndParent()
    {
    }

    /**
     * @Note('inheritance-base-trait-tester')
     */
    public function traitAndParent()
    {
    }

    /**
     * @Note('inheritance-base-trait-tester')
     */
    public function traitAndParentAndChild()
    {
    }
}

class InheritanceTraitTester extends InheritanceBaseTraitTester
{
    use InheritanceTrait;

    /**
     * @Note('inheritance-trait-tester')
     */
    public function traitAndChild()
    {
    }

    /**
     * @Note('inheritance-trait-tester')
     */
    public function traitAndParentAndChild()
    {
    }
}

class AliasBaseTraitTester
{
    /**
     * @Note('alias-base-trait-tester')
     */
    public function baseTraitRun()
    {
    }

    /**
     * @Note('alias-base-trait-tester')
     */
    public function traitRun()
    {
    }

    /**
     * @Note('alias-base-trait-tester')
     */
    public function run()
    {
    }
}

class AliasTraitTester extends AliasBaseTraitTester
{
    use AliasTrait {
        AliasTrait::run as traitRun;
    }

    /**
     * @Note('alias-trait-tester')
     */
    public function run()
    {
    }
}

class InsteadofBaseTraitTester
{
    /**
     * @Note('insteadof-base-trait-tester')
     */
    public function trate()
    {
    }

    /**
     * @Note('insteadof-base-trait-tester')
     */
    public function baseTrait()
    {
    }
}

class InsteadofTraitTester extends InsteadofBaseTraitTester
{
    use InsteadofTraitA, mindplay\test\traits\InsteadofTraitB {
        InsteadofTraitA::trate insteadof mindplay\test\traits\InsteadofTraitB;
        mindplay\test\traits\InsteadofTraitB::baseTrait insteadof InsteadofTraitA;
    }
}
