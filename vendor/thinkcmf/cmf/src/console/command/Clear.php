<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace cmf\console\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Clear extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('clear')
            ->addOption('path', 'd', Option::VALUE_OPTIONAL, 'path to clear', null)
            ->addOption('cache', 'c', Option::VALUE_NONE, 'clear cache file')
            ->addOption('log', 'l', Option::VALUE_NONE, 'clear log file')
            ->addOption('dir', 'r', Option::VALUE_NONE, 'clear empty dir')
            ->addOption('expire', 'e', Option::VALUE_NONE, 'clear cache file if cache has expired')
            ->setDescription('Clear runtime file');
    }

    protected function execute(Input $input, Output $output)
    {
        $runtimePath = CMF_DATA . 'runtime' . DIRECTORY_SEPARATOR;

        if ($input->getOption('cache')) {
            $path = $runtimePath . 'cache';
        } elseif ($input->getOption('log')) {
            $path = $runtimePath . 'log';
        } else {
            $path = $input->getOption('path') ?: $runtimePath;
        }

        $rmdir = $input->getOption('dir') ? true : false;
        // --expire 仅当 --cache 时生效
        $cacheExpire = $input->getOption('expire') && $input->getOption('cache') ? true : false;
        $this->clear(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $rmdir, $cacheExpire);

        $path= CMF_DATA . 'runtime_cli' . DIRECTORY_SEPARATOR;
        $this->clear(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, true, false);

        $output->writeln('<info>Clear Successed</info>');
    }

    protected function clear(string $path, bool $rmdir, bool $cacheExpire): void
    {
        $files = is_dir($path) ? scandir($path) : [];

        foreach ($files as $file) {
            if ($file == 'session') {
                continue;
            }
            if ('.' != $file && '..' != $file && is_dir($path . $file)) {
                $this->clear($path . $file . DIRECTORY_SEPARATOR, $rmdir, $cacheExpire);
                if ($rmdir) {
                    @rmdir($path . $file);
                }
            } elseif ('.gitignore' != $file && is_file($path . $file)) {
                if ($cacheExpire) {
                    if ($this->cacheHasExpired($path . $file)) {
                        unlink($path . $file);
                    }
                } else {
                    unlink($path . $file);
                }
            }
        }
    }

    /**
     * 缓存文件是否已过期
     * @param $filename string 文件路径
     * @return bool
     */
    protected function cacheHasExpired($filename)
    {
        $content = file_get_contents($filename);
        $expire  = (int)substr($content, 8, 12);
        return 0 != $expire && time() - $expire > filemtime($filename);
    }

}
