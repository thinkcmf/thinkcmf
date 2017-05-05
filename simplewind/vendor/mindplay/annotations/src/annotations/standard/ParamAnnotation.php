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
 * Defines a method-parameter's type
 *
 * @usage('method'=>true, 'inherited'=>true, 'multiple'=>true)
 */
class ParamAnnotation extends Annotation implements IAnnotationParser, IAnnotationFileAware
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * Annotation file.
     *
     * @var AnnotationFile
     */
    protected $file;

    /**
     * Parse the standard PHP-DOC "param" annotation.
     *
     * @param string $value
     * @return array ['type', 'name']
     */
    public static function parseAnnotation($value)
    {
        $parts = explode(' ', trim($value), 3);

        if (count($parts) < 2) {
            // Malformed value, let "initAnnotation" report about it.
            return array();
        }

        return array('type' => $parts[0], 'name' => substr($parts[1], 1));
    }

    /**
     * Initialize the annotation.
     */
    public function initAnnotation(array $properties)
    {
        $this->map($properties, array('type', 'name'));

        parent::initAnnotation($properties);

        if (!isset($this->type)) {
            throw new AnnotationException('ParamAnnotation requires a type property');
        }

        if (!isset($this->name)) {
            throw new AnnotationException('ParamAnnotation requires a name property');
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
