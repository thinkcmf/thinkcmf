<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
$apps = cmf_scan_dir(CMF_ROOT . 'api/*', GLOB_ONLYDIR);

foreach ($apps as $app) {
    $routeFile = CMF_ROOT . 'api/' . $app . '/route.php';

    if (file_exists($routeFile)) {
        include_once $routeFile;
    }
}

$coreApps = cmf_scan_dir(CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/*', GLOB_ONLYDIR);

foreach ($coreApps as $app) {
    $routeFile = CMF_ROOT . 'vendor/thinkcmf/cmf-api/src/' . $app . '/route.php';

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