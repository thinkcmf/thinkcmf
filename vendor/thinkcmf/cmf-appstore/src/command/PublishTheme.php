<?php

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class PublishTheme extends Command
{
    protected function configure()
    {
        $this->setName('publish:theme')
            ->addArgument('name', Argument::REQUIRED, "The theme name")
            ->setDescription('Publish a ThinkCMF theme');
    }

    protected function execute(Input $input, Output $output)
    {
        $name = $input->getArgument('name');


    }


}
