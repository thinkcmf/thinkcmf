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
 * This class represents a php source-code file inspected for Annotations.
 */
class AnnotationFile
{
    /**
     * Data types considered to be simple.
     *
     * @var array
     */
    private static $simpleTypes = array(
        'array',
        'bool',
        'boolean',
        'callback',
        'double',
        'float',
        'int',
        'integer',
        'mixed',
        'number',
        'object',
        'string',
        'void',
    );

    /**
     * @var array hash where member name => annotation data
     */
    public $data;

    /**
     * @var string $path absolute path to php source-file
     */
    public $path;

    /**
     * @var string $namespace fully qualified namespace
     */
    public $namespace;

    /**
     * @var string[] $uses hash where local class-name => fully qualified class-name
     */
    public $uses;

    /**
     * @var string[] $traitMethodOverrides hash mapping FQCN to a hash mapping aliased method names to (trait, original method name)
     */
    public $traitMethodOverrides;

    /**
     * @param string $path absolute path to php source-file
     * @param array $data annotation data (as provided by AnnotationParser)
     */
    public function __construct($path, array $data)
    {
        $this->path = $path;
        $this->data = $data;
        $this->namespace = $data['#namespace'];
        $this->uses = $data['#uses'];

        if (isset($data['#traitMethodOverrides'])) {
            foreach ($data['#traitMethodOverrides'] as $class => $methods) {
                $this->traitMethodOverrides[$class] = \array_map(array($this, 'resolveMethod'), $methods);
            }
        }
    }

    /**
     * Qualify the class name in a method reference like 'Class::method'.
     *
     * @param string $raw_method Raw method string.
     *
     * @return string[] of fully-qualified class name, method name
     */
    public function resolveMethod($raw_method)
    {
        list($class, $method) = \explode('::', $raw_method, 2);
        return array(\ltrim($this->resolveType($class), '\\'), $method);
    }

    /**
     * Transforms not fully qualified class/interface name into fully qualified one.
     *
     * @param string $raw_type Raw type.
     *
     * @return string
     * @see http://www.phpdoc.org/docs/latest/for-users/phpdoc/types.html#abnf
     */
    public function resolveType($raw_type)
    {
        $type_parts = \explode('[]', $raw_type, 2);
        $type = $type_parts[0];

        if (!$this->isSimple($type)) {
            if (isset($this->uses[$type])) {
                $type_parts[0] = $this->uses[$type];
            } elseif ($this->namespace && \substr($type, 0, 1) != '\\') {
                $type_parts[0] = $this->namespace . '\\' . $type;
            }
        }

        return \implode('[]', $type_parts);
    }

    /**
     * Determines if given data type is scalar.
     *
     * @param string $type Type.
     *
     * @return boolean
     */
    protected function isSimple($type)
    {
        return \in_array(\strtolower($type), self::$simpleTypes);
    }
}
