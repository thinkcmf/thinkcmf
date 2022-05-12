<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\migration\command\migrate;

use InvalidArgumentException;
use RuntimeException;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument as InputArgument;
use think\console\input\Option;
use think\console\Output;
use think\migration\Creator;

class Create extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $help = sprintf('%sCreates a new database migration%s', PHP_EOL, PHP_EOL);
        $this->setName('migrate:create')
            ->setDescription('Create a new migration')
            ->addArgument('name', InputArgument::REQUIRED, 'The migration class name')
            ->addOption('app', 'a', Option::VALUE_OPTIONAL, 'this is app name', '')
            ->addOption('plugin', 'p', Option::VALUE_OPTIONAL, 'this is plugin name', '')
            ->setHelp(<<<EOT
$help

<info>php think migrate:create Test -a demo</info>
<info>php think migrate:create Test --app=demo</info>
<info>php think migrate:create Test -p Demo</info>
<info>php think migrate:create Test --plugin Demo</info>

EOT
            );
    }

    /**
     * Create the new migration.
     *
     * @param Input  $input
     * @param Output $output
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function execute(Input $input, Output $output)
    {
        /** @var Creator $creator */
        $creator = $this->app->get('migration.creator');

        $className  = $input->getArgument('name');
        $appName    = $input->getOption('app');
        $pluginName = $input->getOption('plugin');

        $path = $creator->create($className, $appName, $pluginName);

        $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', realpath($path)));
    }

}
