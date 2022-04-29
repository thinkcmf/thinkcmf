<?php

namespace plugins\demo\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Hello extends Command
{
    protected function configure()
    {
        $this->setName('plugin:hello')
            ->addArgument('name', Argument::OPTIONAL, "your name")
            ->addOption('city', '-c', Option::VALUE_REQUIRED, 'city name')
            ->setDescription('Say Plugin Hello');
    }

    protected function execute(Input $input, Output $output)
    {
        $name = $input->getArgument('name');
        $name = $name ? $name : 'ThinkCMF';
        $city = $input->getOption('city');
        $city = $city ? $city : 'China';
        $output->writeln("Hello, My name is " . $name . '! I\'m from ' . $city);
    }


}
