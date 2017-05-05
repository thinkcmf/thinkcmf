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

namespace mindplay\demo\annotations;


use mindplay\annotations\AnnotationException;

/**
 * Specifies validation of a string, requiring a minimum and/or maximum length.
 *
 * @usage('property'=>true, 'inherited'=>true)
 */
class LengthAnnotation extends ValidationAnnotationBase
{
    /**
     * @var int|null Minimum string length (or null, if no minimum)
     */
    public $min = null;

    /**
     * @var int|null Maximum string length (or null, if no maximum)
     */
    public $max = null;

    /**
     * Initialize the annotation.
     */
    public function initAnnotation(array $properties)
    {
        if (isset($properties[0])) {
            if (isset($properties[1])) {
                $this->min = $properties[0];
                $this->max = $properties[1];
                unset($properties[1]);
            } else {
                $this->max = $properties[0];
            }

            unset($properties[0]);
        }

        parent::initAnnotation($properties);

        if ($this->min !== null && !is_int($this->min)) {
            throw new AnnotationException('LengthAnnotation requires an (integer) min property');
        }

        if ($this->max !== null && !is_int($this->max)) {
            throw new AnnotationException('LengthAnnotation requires an (integer) max property');
        }

        if ($this->min === null && $this->max === null) {
            throw new AnnotationException('LengthAnnotation requires a min and/or max property');
        }
    }
}
