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
namespace app\user\model;

use think\Model;

class UserFavoriteModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_favorite';

    public function favorites()
    {
        $userId        = cmf_get_current_user_id();
        $favorites     = UserFavoriteModel::where('user_id', $userId)->order('id desc')->paginate(10);
        $data['page']  = $favorites->render();
        $data['lists'] = $favorites->items();
        return $data;
    }

    public function deleteFavorite($id)
    {
        $userId           = cmf_get_current_user_id();
        $where['id']      = $id;
        $where['user_id'] = $userId;
        $data             = UserFavoriteModel::where($where)->delete();
        return $data;
    }

}
