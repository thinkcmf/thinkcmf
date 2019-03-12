<?php

/**
 * This file is part of the php-annotation framework.
 *
 * (c) Rasmus Schultz <rasmus@mindplay.dk>
 *
 * This software is licensed under the GNU LGPL license
 * for more information, please see:
 *
 * <https://github.com/mindplay-dk/php-annotations>
 */

namespace mindplay\annotations;

/**
 * This Annotation is mandatory, and must be applied to all Annotations.
 */
class UsageAnnotation extends Annotation
{
    /**
     * @var boolean Set this to TRUE for Annotations that may be applied to classes.
     */
    public $class = false;

    /**
     * @var boolean Set this to TRUE for Annotations that may be applied to properties.
     */
    public $property = false;

    /**
     * @var boolean Set this to TRUE for Annotations that may be applied to methods.
     */
    public $method = false;

    /**
     * @var boolean $multiple Set this to TRUE for Annotations that allow multiple instances on the same member.
     */
    public $multiple = false;

    /**
     * @var boolean $inherited Set this to TRUE for Annotations that apply to members of child classes.
     */
    public $inherited = false;
}
