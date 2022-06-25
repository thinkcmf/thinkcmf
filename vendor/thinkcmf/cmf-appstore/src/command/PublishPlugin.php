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

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class PublishPlugin extends Command
{
    protected function configure()
    {
        $this->setName('publish:plugin')
            ->addArgument('name', Argument::REQUIRED, "The plugin name")
            ->setDescription('Publish a ThinkCMF plugin');
    }

    protected function execute(Input $input, Output $output)
    {
        $name = $input->getArgument('name');

        $name        = cmf_parse_name($name, 1);
        $pluginClass = cmf_get_plugin_class($name);

        if (!class_exists($pluginClass)) {
            $output->writeln("<error>plugin $name not exists!</error>");
            return;
        }

        $nameC = cmf_parse_name($name);

        $publishDir = CMF_DATA . "publish/";
        if (!file_exists($publishDir)) {
            mkdir($publishDir, '755');
        }

        $filename = $publishDir . "plugin_{$nameC}_" . date('Ymd_His') . '.zip';
        try {
            $zip = new \ZipArchive();

            if (file_exists($filename)) {
                $zip->open($filename, \ZipArchive::OVERWRITE);  //打开压缩包
            } else {
                $zip->open($filename, \ZipArchive::CREATE);  //打开压缩包
            }

            $pluginDir = WEB_ROOT . "plugins/$nameC/";
            $files     = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pluginDir, \RecursiveDirectoryIterator::UNIX_PATHS | \RecursiveDirectoryIterator::CURRENT_AS_SELF | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);

            foreach ($files as $file) {
                $subPath = $file->getSubPathname();
                if ($file->isDir()) {
                    $subPath = rtrim($subPath, '/') . '/';
                    $zip->addEmptyDir($nameC . '/' . $subPath);
                } else {
                    $zip->addFile($pluginDir . $subPath, $nameC . '/' . $subPath);
                }
            }
            $zip->close(); //关闭压缩包

            $output->writeln("<info>File generated</info>");
            $output->writeln("<info>File path:</info> $filename");
            $output->writeln("You can publish it to https://www.thinkcmf.com now!");

        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }


    }


}
