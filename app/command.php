<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

$apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

$returnCommands = [];

foreach ($apps as $app) {
    $commandFile = APP_PATH . $app . '/command.php';

    if (file_exists($commandFile)) {
        $commands       = include $commandFile;

        $returnCommands = array_merge($returnCommands, $commands);
    }

}

return $returnCommands;