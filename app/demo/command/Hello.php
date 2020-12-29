<?php

namespace app\demo\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Hello extends Command
{
    protected function configure()
    {
        $this->setName('demo:hello')
            ->addArgument('name', Argument::OPTIONAL, "your name")
            ->addOption('city', '-c', Option::VALUE_REQUIRED, 'city name')
            ->setDescription('Say App Hello');
    }

    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('name'));
        $city = $input->getOption('city');
        $city = $city ? $city : 'China';
        $name = $name ? $name : 'ThinkCMF';
        $output->writeln("Hello, My name is " . $name . '! I\'m from ' . $city);
    }


}
