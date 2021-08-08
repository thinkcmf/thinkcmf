<?php

use app\admin\service\impl\AdminMenuServiceImpl;

// 容器Provider定义文件
return [
    'app\admin\service\AdminMenuService' => AdminMenuServiceImpl::class,
    'think\Paginator'                    => \cmf\paginator\Bootstrap::class
];
