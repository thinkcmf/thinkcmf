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

namespace mindplay\annotations\standard;

use mindplay\annotations\AnnotationException;
use mindplay\annotations\AnnotationFile;
use mindplay\annotations\IAnnotationFileAware;
use mindplay\annotations\IAnnotationParser;
use mindplay\annotations\Annotation;

/**
 * Defines the return-type of a function or method
 *
 * @usage('method'=>true, 'inherited'=>true)
 */
class ReturnAnnotation extends Annotation implements IAnnotationParser, IAnnotationFileAware
{
    /**
     * @var string
     */
    public $type;

    /**
     * Annotation file.
     *
     * @var AnnotationFile
     */
    protected $file;

    /**
     * Parse the standard PHP-DOC annotation
     * @param string $value
     * @return array
     */
    public static function parseAnnotation($value)
    {
        $parts = explode(' ', trim($value), 2);

        return array('type' => array_shift($parts));
    }

    /**
     * Initialize the annotation.
     */
    public function initAnnotation(array $properties)
    {
        $this->map($properties, array('type'));

        parent::initAnnotation($properties);

        if (!isset($this->type)) {
            throw new AnnotationException('ReturnAnnotation requires a type property');
        }

        $this->type = $this->file->resolveType($this->type);
    }

    /**
     * Provides information about file, that contains this annotation.
     *
     * @param AnnotationFile $file Annotation file.
     *
     * @return void
     */
    public function setAnnotationFile(AnnotationFile $file)
    {
        $this->file = $file;
    }
}
