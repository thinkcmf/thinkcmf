<?php

trait PropertyConflictTraitOne
{
    /**
     * @Note('property-conflict-trait-one')
     */
    protected $traitAndTraitAndParent = 1;

    /**
     * @Note('property-conflict-trait-one')
     */
    protected $unannotatedTraitAndAnnotatedTrait = 1;

    /**
     * @Note('property-conflict-trait-one')
     */
    protected $traitAndParentAndChild = 1;

    /**
     * @Note('property-conflict-trait-one')
     */
    protected $traitAndChild = 1;
}

trait PropertyConflictTraitTwo
{
    /**
     * @Note('property-conflict-trait-two')
     */
    protected $traitAndTraitAndParent = 1;

    protected $unannotatedTraitAndAnnotatedTrait = 1;
}

class PropertyConflictBaseTraitTester
{
    /**
     * @Note('property-conflict-base-trait-tester')
     */
    protected $traitAndTraitAndParent = 1;

    /**
     * @Note('property-conflict-base-trait-tester')
     */
    protected $traitAndParentAndChild = 1;
}


class PropertyConflictTraitTester extends PropertyConflictBaseTraitTester
{
    use PropertyConflictTraitTwo, PropertyConflictTraitOne;

    /**
     * @Note('property-conflict-trait-tester')
     */
    protected $traitAndChild = 1;

    /**
     * @Note('property-conflict-trait-tester')
     */
    protected $traitAndParentAndChild = 1;
}
