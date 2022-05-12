<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace cmf\console\command;

use think\console\Command;
use think\console\input\Option;

class VendorPublish extends Command
{
    public function configure()
    {
        $this->setName('vendor:publish')
            ->addOption('force', 'f', Option::VALUE_NONE, 'Overwrite any existing files')
            ->setDescription('Publish any publishable assets from vendor packages');
    }

    public function handle()
    {
        $force = $this->input->getOption('force');

        if (is_file($path = $this->app->getRootPath() . 'vendor/composer/installed.json')) {
            $packages = json_decode(@file_get_contents($path), true);
            // Compatibility with Composer 2.0
            if (isset($packages['packages'])) {
                $packages = $packages['packages'];
            }
            foreach ($packages as $package) {
                //配置
                $configDir = $this->app->getConfigPath();

                if (!empty($package['extra']['think']['config'])) {

                    $ignorePackages = ['topthink/think-captcha', 'topthink/think-trace'];
                    if (in_array($package['name'], $ignorePackages)) {
                        continue;
                    }
                    $installPath = $this->app->getRootPath() . 'vendor/' . $package['name'] . DIRECTORY_SEPARATOR;

                    foreach ((array)$package['extra']['think']['config'] as $name => $file) {

                        $target = $configDir . $name . '.php';
                        $source = $installPath . $file;

                        if (is_file($target) && !$force) {
                            $this->output->info("File {$target} exist!");
                            continue;
                        }

                        if (!is_file($source)) {
                            $this->output->info("File {$source} not exist!");
                            continue;
                        }

                        try {
                            $dir = dirname($target);
                            if (!file_exists($dir)) {
                                mkdir($dir, 0755, true);
                            }
                            copy($source, $target);
                        } catch (\Exception $e) {
                            $this->output->info($target);
                        }
                    }
                }
            }

            $this->output->writeln('<info>Succeed!</info>');
        }
    }
}
