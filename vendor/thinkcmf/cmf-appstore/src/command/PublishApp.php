<?php

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class PublishApp extends Command
{
    protected function configure()
    {
        $this->setName('publish:app')
            ->addArgument('name', Argument::REQUIRED, "The app name")
            ->setDescription('Publish a ThinkCMF app');
    }

    protected function execute(Input $input, Output $output)
    {
        $name = $input->getArgument('name');
        
        
    }


}
