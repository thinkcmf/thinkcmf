<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin;

use app\admin\command\PublishApp;
use app\admin\command\PublishPlugin;
use app\admin\command\PublishTheme;
use think\Service;

class AppStoreService extends Service
{

    public function boot()
    {
        $this->commands([
            PublishApp::class,
            PublishPlugin::class,
            PublishTheme::class,
        ]);
    }

}
