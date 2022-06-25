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

class PublishApp extends Command
{
    protected function configure()
    {
        $this->setName('publish:app')
            ->addArgument('name', Argument::REQUIRED, "The app name")
            ->addArgument('theme', Argument::OPTIONAL, "The app's theme name")
            ->setDescription('Publish a ThinkCMF app');
    }

    protected function execute(Input $input, Output $output)
    {
        $name   = $input->getArgument('name');
        $theme  = $input->getArgument('theme');
        $appDir = CMF_ROOT . "app/$name/";
        if (!file_exists($appDir)) {
            $output->writeln("<error>app $name not exists!</error>");
            return;
        }

        if (!empty($theme)) {
            $themeDir = WEB_ROOT . "themes/$theme/";

            if (!file_exists($themeDir)) {
                $output->writeln("<error>app's theme $name not exists!</error>");
                return;
            }
        }

        $publishDir = CMF_DATA . "publish/";
        if (!file_exists($publishDir)) {
            mkdir($publishDir, '755');
        }

        $filename = $publishDir . "app_{$name}_" . date('Ymd_His') . '.zip';
        try {
            $zip = new \ZipArchive();

            if (file_exists($filename)) {
                $zip->open($filename, \ZipArchive::OVERWRITE);  //打开压缩包
            } else {
                $zip->open($filename, \ZipArchive::CREATE);  //打开压缩包
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appDir, \RecursiveDirectoryIterator::UNIX_PATHS | \RecursiveDirectoryIterator::CURRENT_AS_SELF | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
            foreach ($files as $file) {
                $subPath = $file->getSubPathname();
                if ($file->isDir()) {
                    $subPath = rtrim($subPath, '/') . '/';
                    $zip->addEmptyDir("$name/app/$name/" . $subPath);
                } else {
                    $zip->addFile($appDir . $subPath, "$name/app/$name/" . $subPath);
                }
            }

            $apiDir = CMF_ROOT . "api/$name/";

            if (file_exists($apiDir)) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($apiDir, \RecursiveDirectoryIterator::UNIX_PATHS | \RecursiveDirectoryIterator::CURRENT_AS_SELF | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
                foreach ($files as $file) {
                    $subPath = $file->getSubPathname();
                    if ($file->isDir()) {
                        $subPath = rtrim($subPath, '/') . '/';
                        $zip->addEmptyDir("$name/api/$name/" . $subPath);
                    } else {
                        $zip->addFile($apiDir . $subPath, "$name/api/$name/" . $subPath);
                    }
                }
            }

            if (!empty($themeDir)) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($themeDir, \RecursiveDirectoryIterator::UNIX_PATHS | \RecursiveDirectoryIterator::CURRENT_AS_SELF | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
                foreach ($files as $file) {
                    $subPath = $file->getSubPathname();
                    if ($file->isDir()) {
                        $subPath = rtrim($subPath, '/') . '/';
                        $zip->addEmptyDir("$name/public/themes/$theme/" . $subPath);
                    } else {
                        $zip->addFile($themeDir . $subPath, "$name/public/themes/$theme/" . $subPath);
                    }
                }
            }

            $adminThemeDir = WEB_ROOT . "themes/admin_simpleboot3/$name/";
            if (file_exists($adminThemeDir)) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($adminThemeDir, \RecursiveDirectoryIterator::UNIX_PATHS | \RecursiveDirectoryIterator::CURRENT_AS_SELF | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
                foreach ($files as $file) {
                    $subPath = $file->getSubPathname();
                    if ($file->isDir()) {
                        $subPath = rtrim($subPath, '/') . '/';
                        $zip->addEmptyDir("$name/public/themes/admin_simpleboot3/$name/" . $subPath);
                    } else {
                        $zip->addFile($adminThemeDir . $subPath, "$name/public/themes/admin_simpleboot3/$name/" . $subPath);
                    }
                }
            }

            $zip->close(); //关闭压缩包

            $output->writeln("<info>File generated</info>");
            $output->writeln("<info>File path:</info> $filename");
            $output->writeln("You can publish it to https://www.thinkcmf.com now!");

        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getTraceAsString()}{$e->getMessage()}</error>");
        }


    }


}
