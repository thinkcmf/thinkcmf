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
 * This interface enables an Annotation to support PHP-DOC style Annotation
 * syntax - because this syntax is informal and varies between tags, such an
 * Annotation must be parsed by the individual Annotation class.
 */
interface IAnnotationParser
{
    /**
     * @param string $value The raw string value of the Annotation.
     *
     * @return array An array of Annotation properties.
     */
    public static function parseAnnotation($value);
}
