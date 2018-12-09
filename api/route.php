<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
$apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

foreach ($apps as $app) {
    $routeFile = APP_PATH . $app . '/route.php';

    if (file_exists($routeFile)) {
        include_once $routeFile;
    }

}


if (file_exists(CMF_ROOT . "data/conf/route.php")) {
    $runtimeRoutes = include CMF_ROOT . "data/conf/route.php";
} else {
    $runtimeRoutes = [];
}

return $runtimeRoutes;