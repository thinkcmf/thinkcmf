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
            ->addArgument('theme', Argument::REQUIRED, "The app's theme name")
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

        $themeDir = WEB_ROOT . "themes/$theme/";

        if (!file_exists($themeDir)) {
            $output->writeln("<error>app's theme $name not exists!</error>");
            return;
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
                    $zip->addEmptyDir("app/$name/" . $subPath);
                } else {
                    $zip->addFile($appDir . $subPath, "app/$name/" . $subPath);
                }
            }

            $apiDir = CMF_ROOT . "api/$name/";

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($apiDir, \RecursiveDirectoryIterator::UNIX_PATHS | \RecursiveDirectoryIterator::CURRENT_AS_SELF | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
            foreach ($files as $file) {
                $subPath = $file->getSubPathname();
                if ($file->isDir()) {
                    $subPath = rtrim($subPath, '/') . '/';
                    $zip->addEmptyDir("api/$name/" . $subPath);
                } else {
                    $zip->addFile($apiDir . $subPath, "api/$name/" . $subPath);
                }
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($themeDir, \RecursiveDirectoryIterator::UNIX_PATHS | \RecursiveDirectoryIterator::CURRENT_AS_SELF | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
            foreach ($files as $file) {
                $subPath = $file->getSubPathname();
                if ($file->isDir()) {
                    $subPath = rtrim($subPath, '/') . '/';
                    $zip->addEmptyDir("public/themes/$theme/" . $subPath);
                } else {
                    $zip->addFile($themeDir . $subPath, "public/themes/$theme/" . $subPath);
                }
            }

            $adminThemeDir = WEB_ROOT . "themes/admin_simpleboot3/$name/";

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($adminThemeDir, \RecursiveDirectoryIterator::UNIX_PATHS | \RecursiveDirectoryIterator::CURRENT_AS_SELF | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
            foreach ($files as $file) {
                $subPath = $file->getSubPathname();
                if ($file->isDir()) {
                    $subPath = rtrim($subPath, '/') . '/';
                    $zip->addEmptyDir("public/themes/admin_simpleboot3/$name/" . $subPath);
                } else {
                    $zip->addFile($adminThemeDir . $subPath, "public/themes/admin_simpleboot3/$name/" . $subPath);
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
