<?php

namespace think\migration;

use InvalidArgumentException;
use Phinx\Util\Util;
use RuntimeException;
use think\App;

class Creator
{

    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function create(string $className, string $appName = '', string $pluginName = '')
    {
        $path = $this->ensureDirectory($appName, $pluginName);

        if (!Util::isValidPhinxClassName($className)) {
            throw new InvalidArgumentException(sprintf('The migration class name "%s" is invalid. Please use CamelCase format.', $className));
        }

        if ($appName) {
            $className = 'MigrationApp' . cmf_parse_name($appName, 1) . $className;
        } elseif ($pluginName) {
            $className = 'MigrationPlugin' . cmf_parse_name($pluginName, 1) . $className;
        } else {
            $className = 'MigrationCmf' . $className;
        }

        if (!Util::isUniqueMigrationClassName($className, $path)) {
            throw new InvalidArgumentException(sprintf('The migration class name "%s" already exists', $className));
        }

        // Compute the file path
        $fileName = Util::mapClassNameToFileName($className);
        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($filePath)) {
            throw new InvalidArgumentException(sprintf('The file "%s" already exists', $filePath));
        }

        // Verify that the template creation class (or the aliased class) exists and that it implements the required interface.
        $aliasedClassName = null;

        // Load the alternative template if it is defined.
        $contents = file_get_contents($this->getTemplate());


        // inject the class names appropriate to this migration
        $contents = strtr($contents, [
            '{%MigratorClass%}' => $className,
        ]);

        if (false === file_put_contents($filePath, $contents)) {
            throw new RuntimeException(sprintf('The file "%s" could not be written to', $path));
        }

        return $filePath;
    }

    protected function ensureDirectory(string $appName = '', string $pluginName = '')
    {
        if ($appName) {
            $path = $this->app->getAppPath() . $appName . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'migrations';
        } elseif ($pluginName) {
            $path = WEB_ROOT . 'plugins' . DIRECTORY_SEPARATOR . cmf_parse_name($pluginName) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'migrations';
        } else {
            $path = $this->app->getRootPath() . 'vendor/thinkcmf/cmf/src/data/migrations';
        }

        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            throw new InvalidArgumentException(sprintf('directory "%s" does not exist', $path));
        }

        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf('directory "%s" is not writable', $path));
        }

        return $path;
    }

    protected function getTemplate()
    {
        return __DIR__ . '/command/stubs/migrate.stub';
    }
}
