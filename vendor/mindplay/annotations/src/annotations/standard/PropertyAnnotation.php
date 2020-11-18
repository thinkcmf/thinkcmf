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

use mindplay\annotations\Annotation;
use mindplay\annotations\AnnotationException;
use mindplay\annotations\AnnotationFile;
use mindplay\annotations\IAnnotationFileAware;
use mindplay\annotations\IAnnotationParser;


/**
 * Defines a magic/virtual property and it's type.
 *
 * @usage('class'=>true, 'inherited'=>true)
 */
class PropertyAnnotation extends Annotation implements IAnnotationParser, IAnnotationFileAware
{
    /**
     * Specifies the property type.
     *
     * @var string
     */
    public $type;

    /**
     * Specifies the property name.
     *
     * @var string
     */
    public $name;

    /**
     * Specifies the property description.
     *
     * @var string
     */
    public $description;

    /**
     * Annotation file.
     *
     * @var AnnotationFile
     */
    protected $file;

    /**
     * Parse the standard PHP-DOC "property" annotation.
     *
     * @param string $value The raw string value of the Annotation.
     *
     * @return array ['type', 'name'] or ['type', 'name', 'description'] if description is set.
     */
    public static function parseAnnotation($value)
    {
        $parts = \explode(' ', \trim($value), 3);

        if (\count($parts) < 2) {
            // Malformed value, let "initAnnotation" report about it.
            return array();
        }

        $result = array('type' => $parts[0], 'name' => \substr($parts[1], 1));

        if (isset($parts[2])) {
            $result['description'] = $parts[2];
        }

        return $result;
    }

    /**
     * Initialize the annotation.
     */
    public function initAnnotation(array $properties)
    {
        $this->map($properties, array('type', 'name', 'description'));

        parent::initAnnotation($properties);

        if (!isset($this->type)) {
            throw new AnnotationException(basename(__CLASS__).' requires a type property');
        }

        if (!isset($this->name)) {
            throw new AnnotationException(basename(__CLASS__).' requires a name property');
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
