<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\command;

use app\admin\logic\PluginLogic;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class UninstallPlugin extends Command
{
    protected function configure()
    {
        $this->setName('uninstall:plugin')
            ->addArgument('name', Argument::REQUIRED, "The plugin name")
            ->setDescription('Uninstall a ThinkCMF plugin');
    }

    protected function execute(Input $input, Output $output)
    {
        $name = $input->getArgument('name');
        $output->writeln("<highlight>All data of the plugin <error>$name</error> will be removed!</highlight>");
        $answer = $output->confirm($input, "Are you sure to uninstall this plugin?", false);
        if (!$answer) {
            $output->writeln("User select NO!");
            return;
        }
        $answer = $output->confirm($input, 'Are you really sure to uninstall this plugin?', false);
        if (!$answer) {
            $output->writeln("User select NO!");
            return;
        }
        $answer = $output->ask($input, 'Please tell us the plugin name you want to uninstall?');

        if ($answer != $name) {
            $output->writeln("<error>error plugin name!</error>");
            return;
        }
        $result = PluginLogic::uninstall($name);

        if ($result === true) {
            $output->writeln("Uninstall successful!");
        } else if ($result === false) {
            $output->writeln("<error>Uninstall failed!</error>");
        } else {
            $output->writeln("<error>$result</error>");
        }

    }


}
