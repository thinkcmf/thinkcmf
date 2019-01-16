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
 * This class is responsible for storing and updating parsed annotation-data in PHP files.
 */
class AnnotationCache
{
    /**
     * @var string The PHP opening tag (used when writing cache files)
     */
    const PHP_TAG = "<?php\n\n";

    /**
     * @var int The file mode used when creating new cache files
     */
    private $_fileMode;

    /**
     * @var string Absolute path to a folder where cache files will be created
     */
    private $_root;

    /**
     * Initializes the file cache-provider
     *
     * @param string $root absolute path to the root-folder where cache-files will be stored
     * @param int $fileMode file creation mode; defaults to 0777
     */
    public function __construct($root, $fileMode = 0777)
    {
        $this->_root = $root;
        $this->_fileMode = $fileMode;
    }

    /**
     * Check if annotation-data for the key has been stored.
     *
     * @param string $key cache key
     *
     * @return bool true if data with the given key has been stored; otherwise false
     */
    public function exists($key)
    {
        return \file_exists($this->_getPath($key));
    }

    /**
     * Caches the given data with the given key.
     *
     * @param string $key cache key
     * @param array $code the source-code to be cached
     * @throws AnnotationException if file could not be written
     */
    public function store($key, $code)
    {
        $path = $this->_getPath($key);

        $content = self::PHP_TAG . $code . "\n";

        if (@\file_put_contents($path, $content, LOCK_EX) === false) {
            throw new AnnotationException("Unable to write cache file: {$path}");
        }

        if (@\chmod($path, $this->_fileMode) === false) {
            throw new AnnotationException("Unable to set permissions of cache file: {$path}");
        }
    }

    /**
     * Fetches data stored for the given key.
     *
     * @param string $key cache key
     * @return mixed the cached data
     */
    public function fetch($key)
    {
        return include($this->_getPath($key));
    }

    /**
     * Returns the timestamp of the last cache update for the given key.
     *
     * @param string $key cache key
     * @return int unix timestamp
     */
    public function getTimestamp($key)
    {
        return \filemtime($this->_getPath($key));
    }

    /**
     * Maps a cache-key to the absolute path of a PHP file
     *
     * @param string $key cache key
     * @return string absolute path of the PHP file
     */
    private function _getPath($key)
    {
        return $this->_root . DIRECTORY_SEPARATOR . $key . '.annotations.php';
    }

    /**
     * @return string absolute path of the folder where cache files are created
     */
    public function getRoot()
    {
        return $this->_root;
    }
}
