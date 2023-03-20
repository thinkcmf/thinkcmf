<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace think\migration;

use InvalidArgumentException;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Db\Adapter\ProxyAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\MigrationInterface;
use Phinx\Util\Util;
use think\migration\Migrator;

class Migrate
{
    /**
     * @var array
     */
    protected $migrations;
    private $output;
    private $input;
    private $appName;
    private $pluginName;
    private $app;
    private $adapter;

    /**
     * @param string $appName
     * @param string $pluginName
     */
    public function __construct(string $appName = '', string $pluginName = '')
    {
        $this->appName    = $appName;
        $this->pluginName = $pluginName;
    }

    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function getAdapter()
    {
        if (isset($this->adapter)) {
            return $this->adapter;
        }

        $options = $this->getDbConfig();
        $adapter = AdapterFactory::instance()->getAdapter($options['adapter'], $options);

        if ($adapter->hasOption('table_prefix') || $adapter->hasOption('table_suffix')) {
            $adapter = AdapterFactory::instance()->getWrapper('prefix', $adapter);
        }

        $this->adapter = $adapter;

        return $adapter;
    }

    /**
     * 获取数据库配置
     * @return array
     */
    protected function getDbConfig(): array
    {
        $default = $this->app->config->get('database.default');
        $config  = $this->app->config->get("database.connections.{$default}");

        if (0 == $config['deploy']) {
            $dbConfig = [
                'adapter'       => $config['type'],
                'host'          => $config['hostname'],
                'name'          => $config['database'],
                'user'          => $config['username'],
                'pass'          => $config['password'],
                'port'          => $config['hostport'],
                'charset'       => $config['charset'],
                'table_prefix'  => $config['prefix'],
                'version_order' => $config['version_order'] ?? 'creation',
            ];
        } else {
            $dbConfig = [
                'adapter'       => explode(',', $config['type'])[0],
                'host'          => explode(',', $config['hostname'])[0],
                'name'          => explode(',', $config['database'])[0],
                'user'          => explode(',', $config['username'])[0],
                'pass'          => explode(',', $config['password'])[0],
                'port'          => explode(',', $config['hostport'])[0],
                'charset'       => explode(',', $config['charset'])[0],
                'table_prefix'  => explode(',', $config['prefix'])[0],
                'version_order' => explode(',', $config['version_order'])[0] ?? 'creation',
            ];
        }

        if ($this->appName) {
            $table = "{$this->appName}_migration";
        } elseif ($this->pluginName) {
            $table = 'plugin_' . cmf_parse_name($this->pluginName) . '_migration';
        } else {
            $table = 'migration';
        }

        $dbConfig['default_migration_table'] = $dbConfig['table_prefix'] . $table;

        return $dbConfig;
    }

    public function verifyMigrationDirectory(string $path)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf('Migration directory "%s" does not exist', $path));
        }

        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf('Migration directory "%s" is not writable', $path));
        }
    }

    public function getPath()
    {
        $this->app = app();
        if ($this->appName) {
            $path = $this->app->getAppPath() . $this->appName . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'migrations';
        } elseif ($this->pluginName) {
            $path = WEB_ROOT . 'plugins' . DIRECTORY_SEPARATOR . cmf_parse_name($this->pluginName) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'migrations';
        } else {
            $path = $this->app->getRootPath() . 'vendor/thinkcmf/cmf/src/data/migrations';
        }

        return $path;
    }

    public function executeMigration(MigrationInterface $migration, $direction = MigrationInterface::UP)
    {
        $this->writeln(' ==' . ' <info>' . $migration->getVersion() . ' ' . $migration->getName() . ':</info>' . ' <comment>' . (MigrationInterface::UP === $direction ? 'migrating' : 'reverting') . '</comment>');

        // Execute the migration and log the time elapsed.
        $start = microtime(true);

        $startTime = time();
        $direction = (MigrationInterface::UP === $direction) ? MigrationInterface::UP : MigrationInterface::DOWN;
        $migration->setAdapter($this->getAdapter());

        // begin the transaction if the adapter supports it
        if ($this->getAdapter()->hasTransactions()) {
            $this->getAdapter()->beginTransaction();
        }

        // Run the migration
        if (method_exists($migration, MigrationInterface::CHANGE)) {
            if (MigrationInterface::DOWN === $direction) {
                // Create an instance of the ProxyAdapter so we can record all
                // of the migration commands for reverse playback
                /** @var ProxyAdapter $proxyAdapter */
                $proxyAdapter = AdapterFactory::instance()->getWrapper('proxy', $this->getAdapter());
                $migration->setAdapter($proxyAdapter);
                /** @noinspection PhpUndefinedMethodInspection */
                $migration->change();
                $proxyAdapter->executeInvertedCommands();
                $migration->setAdapter($this->getAdapter());
            } else {
                /** @noinspection PhpUndefinedMethodInspection */
                $migration->change();
            }
        } else {
            $migration->{$direction}();
        }

        // commit the transaction if the adapter supports it
        if ($this->getAdapter()->hasTransactions()) {
            $this->getAdapter()->commitTransaction();
        }

        // Record it in the database
        $this->getAdapter()
            ->migrated($migration, $direction, date('Y-m-d H:i:s', $startTime), date('Y-m-d H:i:s', time()));

        $end = microtime(true);

        $this->writeln(' ==' . ' <info>' . $migration->getVersion() . ' ' . $migration->getName() . ':</info>' . ' <comment>' . (MigrationInterface::UP === $direction ? 'migrated' : 'reverted') . ' ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }

    public function getVersionLog()
    {
        return $this->getAdapter()->getVersionLog();
    }

    public function getVersions()
    {
        return $this->getAdapter()->getVersions();
    }

    public function getMigrations()
    {
        if (null === $this->migrations) {
            $phpFiles = glob($this->getPath() . DIRECTORY_SEPARATOR . '*.php', defined('GLOB_BRACE') ? GLOB_BRACE : 0);

            // filter the files to only get the ones that match our naming scheme
            $fileNames = [];
            /** @var Migrator[] $versions */
            $versions = [];

            foreach ($phpFiles as $filePath) {
                if (Util::isValidMigrationFileName(basename($filePath))) {
                    $version = Util::getVersionFromFileName(basename($filePath));

                    if (isset($versions[$version])) {
                        throw new InvalidArgumentException(sprintf('Duplicate migration - "%s" has the same version as "%s"', $filePath, $versions[$version]->getVersion()));
                    }

                    // convert the filename to a class name
                    $class = Util::mapFileNameToClassName(basename($filePath));

                    if (isset($fileNames[$class])) {
                        throw new InvalidArgumentException(sprintf('Migration "%s" has the same name as "%s"', basename($filePath), $fileNames[$class]));
                    }

                    $fileNames[$class] = basename($filePath);

                    // load the migration file
                    /** @noinspection PhpIncludeInspection */
                    require_once $filePath;
                    if (!class_exists($class)) {
                        throw new InvalidArgumentException(sprintf('Could not find class "%s" in file "%s"', $class, $filePath));
                    }

                    // instantiate it
                    //$this->input  = new Input();
                    //$this->output = new Output();

                    $migration = new $class('production', $version, $this->input, $this->output);

                    if (!($migration instanceof AbstractMigration)) {
                        throw new InvalidArgumentException(sprintf('The class "%s" in file "%s" must extend \Phinx\Migration\AbstractMigration', $class, $filePath));
                    }

                    $versions[$version] = $migration;
                }
            }

            ksort($versions);
            $this->migrations = $versions;
        }

        return $this->migrations;
    }

    public function getCurrentVersion()
    {
        $versions = $this->getVersions();
        $version  = 0;

        if (!empty($versions)) {
            $version = end($versions);
        }

        return $version;
    }

    public function migrateToDateTime(DateTime $dateTime)
    {
        $versions   = array_keys($this->getMigrations());
        $dateString = $dateTime->format('YmdHis');

        $outstandingMigrations = array_filter($versions, function ($version) use ($dateString) {
            return $version <= $dateString;
        });

        if (count($outstandingMigrations) > 0) {
            $migration = max($outstandingMigrations);
            $this->writeln('Migrating to version ' . $migration);
            $this->migrate($migration);
        }
    }

    protected function writeln($content)
    {
        if ($this->output) {
            $this->output->writeln($content);
        }
    }

    public function migrate($version = null)
    {
        $path = $this->getPath();
        if (!is_dir($path)) {
            $this->writeln("$path not exists --> ignore");
            return false;
        }
        $migrations = $this->getMigrations();
        $versions   = $this->getVersions();
        $current    = $this->getCurrentVersion();

        if (empty($versions) && empty($migrations)) {
            return;
        }

        if (null === $version) {
            $version = max(array_merge($versions, array_keys($migrations)));
        } else if (0 != $version && !isset($migrations[$version])) {
            $this->writeln(sprintf('<comment>warning</comment> %s is not a valid version', $version));
            return;
        }

        // are we migrating up or down?
        $direction = $version > $current ? MigrationInterface::UP : MigrationInterface::DOWN;

        if ($direction === MigrationInterface::DOWN) {
            // run downs first
            krsort($migrations);
            foreach ($migrations as $migration) {
                if ($migration->getVersion() <= $version) {
                    break;
                }

                if (in_array($migration->getVersion(), $versions)) {
                    $this->executeMigration($migration, MigrationInterface::DOWN);
                }
            }
        }

        ksort($migrations);
        foreach ($migrations as $migration) {
            if ($migration->getVersion() > $version) {
                break;
            }

            if (!in_array($migration->getVersion(), $versions)) {
                $this->executeMigration($migration);
            }
        }
    }
}
