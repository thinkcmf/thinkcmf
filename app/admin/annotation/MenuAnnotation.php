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

namespace app\admin\annotation;

use mindplay\annotations\AnnotationException;
use mindplay\annotations\Annotation;

/**
 * Specifies validation of a string, requiring a minimum and/or maximum length.
 *
 * @usage('method'=>true, 'inherited'=>true)
 */
class MenuAnnotation extends Annotation
{
    /**
     * @var int|null Minimum string length (or null, if no minimum)
     */
    public $remark = null;

    /**
     * @var int|null Maximum string length (or null, if no maximum)
     */
    public $icon = null;

    /**
     * @var int|null Minimum string length (or null, if no minimum)
     */
    public $name = null;

    /**
     * Initialize the annotation.
     */
    public function initAnnotation(array $properties)
    {
        parent::initAnnotation($properties);
    }
}
