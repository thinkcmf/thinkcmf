<?php

/*
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
 * Specifies validation against a minimum and/or maximum numeric value.
 *
 * @usage('property'=>true, 'inherited'=>true)
 */
class RangeAnnotation extends ValidationAnnotationBase
{
    /**
     * @var mixed $min Minimum numeric value (integer or floating point)
     */
    public $min = null;

    /**
     * @var mixed $max Maximum numeric value (integer or floating point)
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

        if ($this->min !== null && !is_int($this->min) && !is_float($this->min)) {
            throw new AnnotationException('RangeAnnotation requires a numeric (float or int) min property');
        }

        if ($this->max !== null && !is_int($this->max) && !is_float($this->max)) {
            throw new AnnotationException('RangeAnnotation requires a numeric (float or int) max property');
        }

        if ($this->min === null && $this->max === null) {
            throw new AnnotationException('RangeAnnotation requires a min and/or max property');
        }
    }
}
