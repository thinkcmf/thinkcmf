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


use mindplay\annotations\AnnotationManager;

abstract class Package
{
    public static function register(AnnotationManager $annotationManager)
    {
        $annotationManager->registry['length'] = 'mindplay\demo\annotations\LengthAnnotation';
        $annotationManager->registry['required'] = 'mindplay\demo\annotations\RequiredAnnotation';
        $annotationManager->registry['text'] = 'mindplay\demo\annotations\TextAnnotation';
    }
}
