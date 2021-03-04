<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use cmf\controller\HomeBaseController;
use app\user\model\UserModel;
use think\Validate;

class PublicController extends HomeBaseController
{

    // 用户头像api
    public function avatar()
    {
        $id   = $this->request->param("id", 0, "intval");
        $user = UserModel::find($id);

        $avatar = '';
        if (!empty($user)) {
            $avatar = cmf_get_user_avatar_url($user['avatar']);
            if (strpos($avatar, "/") === 0) {
                $avatar = $this->request->domain() . $avatar;
            }
        }

        if (empty($avatar)) {
            $cdnSettings = cmf_get_option('cdn_settings');
            if (empty($cdnSettings['cdn_static_root'])) {
                $avatar = $this->request->domain() . "/static/images/headicon.png";
            } else {
                $cdnStaticRoot = rtrim($cdnSettings['cdn_static_root'], '/');
                $avatar        = $cdnStaticRoot . "/static/images/headicon.png";
            }

        }

        return redirect($avatar);
    }

}
