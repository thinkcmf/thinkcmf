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

namespace think\migration\command\migrate;

use DateTime;
use Exception;
use Phinx\Migration\MigrationInterface;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\input\Option as InputOption;
use think\console\Output;
use think\migration\Migrate;

class Run extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:run')
            ->setDescription('Execute database migration')
            ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, '迁移到的版本号')
            ->addOption('--date', '-d', InputOption::VALUE_REQUIRED, '迁移日期')
            ->addOption('app', 'a', Option::VALUE_OPTIONAL, 'this is app name', '')
            ->addOption('plugin', 'p', Option::VALUE_OPTIONAL, 'this is plugin name', '')
            ->setHelp(<<<EOT
The <info>migrate:run</info> command runs all available migrations, optionally up to a specific version

<info>php think migrate:run</info>
<info>php think migrate:run -t 20110103081132</info>
<info>php think migrate:run -d 20110103</info>
<info>php think migrate:run -v</info>

EOT
            );
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return void
     * @throws Exception
     * @author : 小夏
     * @date   : 2021-05-14 10:33:16
     */
    protected function execute(Input $input, Output $output)
    {
        $version    = $input->getOption('target');
        $date       = $input->getOption('date');
        $appName    = $input->getOption('app');
        $pluginName = $input->getOption('plugin');


        // run the migrations
        $start = microtime(true);

        if (empty($appName) && empty($pluginName)) {
            $this->output->writeln("start cmf core migration:");
        }

        $migrate = new Migrate($appName, $pluginName);
        $migrate->setOutput($this->output);
        if (null !== $date) {
            $migrate->migrateToDateTime(new DateTime($date));
        } else {
            $migrate->migrate($version);
        }

        if (empty($appName) && empty($pluginName)) {
            $this->output->writeln("done");
        }

        if (empty($appName) && empty($pluginName)) {
            $apps = cmf_scan_dir($this->app->getAppPath() . '*', GLOB_ONLYDIR);
            foreach ($apps as $app) {
                $migrate = new Migrate($app);
                $path    = $migrate->getPath();
                if (!is_dir($path)) {
                    continue;
                }

                $this->output->writeln('');
                $this->output->writeln("start app $app migration:");
                $migrate->setOutput($this->output);
                if (null !== $date) {
                    $migrate->migrateToDateTime(new DateTime($date));
                } else {
                    $migrate->migrate($version);
                }

                $this->output->writeln("done");
            }

            $plugins = cmf_scan_dir(WEB_ROOT . 'plugins/*', GLOB_ONLYDIR);
            foreach ($plugins as $plugin) {
                $migrate = new Migrate('', $plugin);
                $path    = $migrate->getPath();
                if (!is_dir($path)) {
                    continue;
                }

                $this->output->writeln('');
                $this->output->writeln("start plugin $plugin migration:");
                $migrate->setOutput($this->output);
                if (null !== $date) {
                    $migrate->migrateToDateTime(new DateTime($date));
                } else {
                    $migrate->migrate($version);
                }

                $this->output->writeln("done");
            }
        }


        $end = microtime(true);

        $output->writeln('');
        $output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }


}
