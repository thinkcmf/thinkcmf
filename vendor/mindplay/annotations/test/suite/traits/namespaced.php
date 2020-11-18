<?php

namespace mindplay\test\traits;

trait AnotherSimpleTrait
{
    /**
     * @note('another-simple-trait')
     */
    protected $sampleFromAnotherTrait = 'test';

    /**
     * @Note('another-simple-trait')
     */
    public function runFromAnotherTrait()
    {
    }
}

trait AliasBaseTrait
{
    /**
     * @Note('alias-base-trait')
     */
    public function run()
    {
    }
}

trait AliasTrait
{
    use \mindplay\test\traits\AliasBaseTrait {
        \mindplay\test\traits\AliasBaseTrait::run as baseTraitRun;
    }

    /**
     * @Note('alias-trait')
     */
    public function run()
    {
    }
}

trait InsteadofBaseTraitA
{
    /**
     * @Note('insteadof-base-trait-a')
     */
    public function baseTrait()
    {
    }
}

trait InsteadofBaseTraitB
{
    /**
     * @Note('insteadof-base-trait-b')
     */
    public function baseTrait()
    {
    }
}

trait InsteadofTraitA
{
    use InsteadofBaseTraitA, InsteadofBaseTraitB {
        InsteadofBaseTraitA::baseTrait insteadof InsteadofBaseTraitB;
    }

    /**
     * @Note('insteadof-trait-a')
     */
    public function trate()
    {
    }
}

trait InsteadofTraitB
{
    use InsteadofBaseTraitA, InsteadofBaseTraitB {
        InsteadofBaseTraitB::baseTrait insteadof InsteadofBaseTraitA;
    }

    /**
     * @Note('insteadof-trait-b')
     */
    public function trate()
    {
    }
}
