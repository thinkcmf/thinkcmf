<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\model;

use think\Db;
use think\Model;

class UserFavoriteModel extends Model
{
    public function favorites()
    {
        $userId        = cmf_get_current_user_id();
        $userQuery     = Db::name("UserFavorite");
        $favorites     = $userQuery->where('user_id', $userId)->order('id desc')->paginate(10);
        $data['page']  = $favorites->render();
        $data['lists'] = $favorites->items();
        return $data;
    }

    public function deleteFavorite($id)
    {
        $userId           = cmf_get_current_user_id();
        $userQuery        = Db::name("UserFavorite");
        $where['id']      = $id;
        $where['user_id'] = $userId;
        $data             = $userQuery->where($where)->delete();
        return $data;
    }

	/**
	 * 收藏分页
	 * @param $data
	 * @return \think\Paginator
	 * @throws \think\exception\DbException
	 */
	public function favoritePage($data)
	{
		$where = [];
		if (!empty($data['user_id'])) {
			$where['user_id'] = $data['user_id'];
		}
		$result = $this->where($where)->order('id desc')->paginate();
		return $result;
	}
}