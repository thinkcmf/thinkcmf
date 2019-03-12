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

if (!defined('T_TRAIT')) {
    define(__NAMESPACE__ . '\\T_TRAIT', -2);
}

/**
 * This class implements a parser for source code annotations
 */
class AnnotationParser
{
    const CHAR = -1;
    const SCAN = 1;
    const CLASS_NAME = 2;
    const SCAN_CLASS = 3;
    const MEMBER = 4;
    const METHOD_NAME = 5;
    const NAMESPACE_NAME = 6;
    const USE_CLAUSE = 11;
    const USE_CLAUSE_AS = 12;
    const TRAIT_USE_CLAUSE = 13;
    const TRAIT_USE_BLOCK = 14;
    const TRAIT_USE_AS = 15;
    const TRAIT_USE_INSTEADOF = 16;

    const SKIP = 7;
    const NAME = 8;
    const COPY_LINE = 9;
    const COPY_ARRAY = 10;

    /**
     * @var boolean $debug Set to TRUE to enable HTML output for debugging
     */
    public $debug = false;

    /**
     * @var boolean Enable PHP autoloader when searching for annotation classes (defaults to true)
     */
    public $autoload = true;

    /**
     * @var AnnotationManager Internal reference to the AnnotationManager associated with this parser.
     */
    protected $manager;

    /**
     * Creates a new instance of the annotation parser.
     *
     * @param AnnotationManager $manager The annotation manager associated with this parser.
     */
    public function __construct(AnnotationManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $source The PHP source code to be parsed
     * @param string $path The path of the source file being parsed (for error-reporting only)
     *
     * @return string PHP source code to construct the annotations of the given PHP source code
     * @throws AnnotationException if orphaned annotations are found at the end of the file
     */
    public function parse($source, $path)
    {
        $index = array();
        $traitMethodOverrides = array();

        $docblocks = array();
        $state = self::SCAN;
        $nesting = 0;
        $class = null;
        $namespace = '';
        $use = '';
        $use_as = '';
        $uses = array();

        $VISIBILITY = array(T_PUBLIC, T_PRIVATE, T_PROTECTED, T_VAR);

        $line = 0;

        if ($this->debug) {
            echo '<table><tr><th>Line</th><th>Type</th><th>String</th><th>State</th><th>Nesting</th></tr>';
        }

        foreach (token_get_all($source) as $token) {
            list($type, $str, $line) = is_array($token) ? $token : array(self::CHAR, $token, $line);

            switch ($state) {
                case self::SCAN:
                    if ($type == T_CLASS || $type == T_TRAIT) {
                        $state = self::CLASS_NAME;
                    }
                    if ($type == T_NAMESPACE) {
                        $state = self::NAMESPACE_NAME;
                        $namespace = '';
                    }
                    if ($type === T_USE && $nesting === 0) {
                        $state = self::USE_CLAUSE;
                        $use = '';
                    }
                    break;

                case self::NAMESPACE_NAME:
                    if ($type == T_STRING || $type == T_NS_SEPARATOR) {
                        $namespace .= $str;
                    } else {
                        if ($str == ';') {
                            $state = self::SCAN;
                        }
                    }
                    break;

                case self::USE_CLAUSE:
                    if ($type == T_AS) {
                        $use_as = '';
                        $state = self::USE_CLAUSE_AS;
                    } elseif ($type == T_STRING || $type == T_NS_SEPARATOR) {
                        $use .= $str;
                    } elseif ($type === self::CHAR) {
                        if ($str === ',' || $str === ';') {
                            if (strpos($use, '\\') !== false) {
                                $uses[substr($use, 1 + strrpos($use, '\\'))] = $use;
                            }
                            else {
                                $uses[$use] = $use;
                            }

                            if ($str === ',') {
                                $state = self::USE_CLAUSE;
                                $use = '';
                            } elseif ($str === ';') {
                                $state = self::SCAN;
                            }
                        }
                    }
                    break;

                case self::USE_CLAUSE_AS:
                    if ($type === T_STRING || $type === T_NS_SEPARATOR) {
                        $use_as .= $str;
                    } elseif ($type === self::CHAR) {
                        if ($str === ',' || $str === ';') {
                            $uses[$use_as] = $use;

                            if ($str === ',') {
                                $state = self::USE_CLAUSE;
                                $use = '';
                            } elseif ($str === ';') {
                                $state = self::SCAN;
                            }
                        }
                    }
                    break;

                case self::CLASS_NAME:
                    if ($type == T_STRING) {
                        $class = ($namespace ? $namespace . '\\' : '') . $str;
                        $traitMethodOverrides[$class] = array();
                        $index[$class] = $docblocks;
                        $docblocks = array();
                        $state = self::SCAN_CLASS;
                    }
                    break;

                case self::SCAN_CLASS:
                    if (in_array($type, $VISIBILITY)) {
                        $state = self::MEMBER;
                    }
                    if ($type == T_FUNCTION) {
                        $state = self::METHOD_NAME;
                    }
                    if ($type == T_USE) {
                        $state = self::TRAIT_USE_CLAUSE;
                        $use = '';
                    }
                    break;

                case self::TRAIT_USE_CLAUSE:
                    if ($type === self::CHAR) {
                        if ($str === '{') {
                            $state = self::TRAIT_USE_BLOCK;
                            $use = '';
                        } elseif ($str === ';') {
                            $state = self::SCAN_CLASS;
                        }
                    }
                    break;

                case self::TRAIT_USE_BLOCK:
                    if ($type == T_STRING || $type == T_NS_SEPARATOR || $type == T_DOUBLE_COLON) {
                        $use .= $str;
                    } elseif ($type === T_INSTEADOF) {
                        $state = self::TRAIT_USE_INSTEADOF;
                    } elseif ($type === T_AS) {
                        $state = self::TRAIT_USE_AS;
                        $use_as = '';
                    } elseif ($type === self::CHAR) {
                        if ($str === ';') {
                            $use = '';
                        } elseif ($str === '}') {
                            $state = self::SCAN_CLASS;
                        }
                    }
                    break;

                case self::TRAIT_USE_INSTEADOF:
                    if ($type === self::CHAR && $str === ';') {
                        $traitMethodOverrides[$class][substr($use, strrpos($use, '::') + 2)] = $use;
                        $state = self::TRAIT_USE_BLOCK;
                        $use = '';
                    }
                    break;

                case self::TRAIT_USE_AS:
                    if ($type === T_STRING) {
                        $use_as .= $str;
                    } elseif ($type === self::CHAR && $str === ';') {
                        // Ignore use... as statements that only change method visibility.
                        if ($use_as !== '') {
                            $traitMethodOverrides[$class][$use_as] = $use;
                        }
                        $state = self::TRAIT_USE_BLOCK;
                        $use = '';
                    }
                    break;

                case self::MEMBER:
                    if ($type == T_VARIABLE) {
                        $index[$class . '::' . $str] = $docblocks;
                        $docblocks = array();
                        $state = self::SCAN_CLASS;
                    }
                    if ($type == T_FUNCTION) {
                        $state = self::METHOD_NAME;
                    }
                    break;

                case self::METHOD_NAME:
                    if ($type == T_STRING) {
                        $index[$class . '::' . $str] = $docblocks;
                        $docblocks = array();
                        $state = self::SCAN_CLASS;
                    }
                    break;
            }

            if (($state >= self::SCAN_CLASS) && ($type == self::CHAR)) {
                switch ($str) {
                    case '{':
                        $nesting++;
                        break;

                    case '}':
                        $nesting--;
                        if ($nesting == 0) {
                            $class = null;
                            $state = self::SCAN;
                        }
                        break;
                }
            }

            if ($type == T_COMMENT || $type == T_DOC_COMMENT) {
                $docblocks[] = $str;
            }

            if ($type == T_CURLY_OPEN) {
                $nesting++;
            }

            if ($this->debug) {
                echo "<tr><td>{$line}</td><td>" . token_name($type) . "</td><td>"
                    . htmlspecialchars($str) . "</td><td>{$state}</td><td>{$nesting}</td></tr>\n";
            }
        }

        if ($this->debug) {
            echo '</table>';
        }

        unset($docblocks);

        $code = "return array(\n";
        $code .= "  '#namespace' => " . var_export($namespace, true) . ",\n";
        $code .= "  '#uses' => " . var_export($uses, true) . ",\n";
        $code .= "  '#traitMethodOverrides' => " . var_export($traitMethodOverrides, true) . ",\n";

        foreach ($index as $key => $docblocks) {
            $array = array();
            foreach ($docblocks as $str) {
                $array = array_merge($array, $this->findAnnotations($str));
            }
            if (count($array)) {
                $code .= "  " . trim(var_export($key, true)) . " => array(\n    " . implode(
                    ",\n    ",
                    $array
                ) . "\n  ),\n";
            }
        }
        $code .= ");\n";

        return $code;
    }

    /**
     * @param string $path The full path of a PHP source code file
     *
     * @return string PHP source code to construct the annotations of the given PHP source code
     * @see AttributeParser::parse()
     */
    public function parseFile($path)
    {
        return $this->parse(file_get_contents($path), $path);
    }

    /**
     * Scan a PHP source code comment for annotation data
     *
     * @param string $str PHP comment containing annotations
     * @return array PHP source code snippets with annotation initialization arrays
     *
     * @throws AnnotationException for various run-time errors
     */
    protected function findAnnotations($str)
    {
        $str = trim(preg_replace('/^[\/\*\# \t]+/m', '', $str)) . "\n";
        $str = str_replace("\r\n", "\n", $str);

        $state = self::SCAN;
        $nesting = 0;
        $name = '';
        $value = '';

        $matches = array();

        for ($i = 0; $i < strlen($str); $i++) {
            $char = substr($str, $i, 1);

            switch ($state) {
                case self::SCAN:
                    if ($char == '@') {
                        $name = '';
                        $value = '';
                        $state = self::NAME;
                    } elseif ($char != "\n" && $char != " " && $char != "\t") {
                        $state = self::SKIP;
                    }
                    break;

                case self::SKIP:
                    if ($char == "\n") {
                        $state = self::SCAN;
                    }
                    break;

                case self::NAME:
                    if (preg_match('/[a-zA-Z\-\\\\]/', $char)) {
                        $name .= $char;
                    } elseif ($char == ' ') {
                        $state = self::COPY_LINE;
                    } elseif ($char == '(') {
                        $nesting++;
                        $value = $char;
                        $state = self::COPY_ARRAY;
                    } elseif ($char == "\n") {
                        $matches[] = array($name, null);
                        $state = self::SCAN;
                    } else {
                        $state = self::SKIP;
                    }
                    break;

                case self::COPY_LINE:
                    if ($char == "\n") {
                        $matches[] = array($name, $value);
                        $state = self::SCAN;
                    } else {
                        $value .= $char;
                    }
                    break;

                case self::COPY_ARRAY:
                    if ($char == '(') {
                        $nesting++;
                    }
                    if ($char == ')') {
                        $nesting--;
                    }

                    $value .= $char;

                    if ($nesting == 0) {
                        $matches[] = array($name, $value);
                        $state = self::SCAN;
                    }
            }
        }

        $annotations = array();

        foreach ($matches as $match) {
            $name = $match[0];
            $type = $this->manager->resolveName($name);

            if ($type === false) {
                continue;
            }

            if (!class_exists($type, $this->autoload)) {
                continue; //ThinkCMF note ,ignore not support annotation
                //throw new AnnotationException("Annotation type '{$type}' does not exist");
            }

            $value = $match[1];

            $quoted_name = "'#name' => " . trim(var_export($name, true));
            $quoted_type = "'#type' => " . trim(var_export($type, true));

            if ($value === null) {
                # value-less annotation:
                $annotations[] = "array({$quoted_name}, {$quoted_type})";
            } elseif (substr($value, 0, 1) == '(') {
                # array-style annotation:
                $annotations[] = "array({$quoted_name}, {$quoted_type}, " . substr($value, 1);
            } else {
                # PHP-DOC-style annotation:
                if (!array_key_exists(__NAMESPACE__ . '\IAnnotationParser', class_implements($type, $this->autoload))) {
                    throw new AnnotationException("Annotation type '{$type}' does not support PHP-DOC style syntax (because it does not implement the " . __NAMESPACE__ . "\\IAnnotationParser interface)");
                }

                /** @var IAnnotationParser $type */
                $properties = $type::parseAnnotation($value);

                if (!is_array($properties)) {
                    throw new AnnotationException("Annotation type '{$type}' did not parse correctly");
                }

                $array = "array({$quoted_name}, {$quoted_type}";
                foreach ($properties as $name => $value) {
                    $array .= ", '{$name}' => " . trim(var_export($value, true));
                }
                $array .= ")";

                $annotations[] = $array;
            }
        }

        return $annotations;
    }
}
