<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\user\controller;

use api\user\model\UserFavoriteModel;
use cmf\controller\RestBaseController;
use think\Validate;

class FavoritesController extends RestBaseController
{

    /**
     * 显示收藏列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFavorites()
    {
        $userId = $this->getUserId();

        $param             = $this->request->param();
        $param['where']    = [
            'user_id' => $userId
        ];
        $param['order']    = '-create_time';
        $userFavoriteModel = new UserFavoriteModel();
        $favoriteData      = $userFavoriteModel->getDatas($param);

        if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
            $response = $favoriteData;
        } else {
            $response = ['list' => $favoriteData,];
        }
        $this->success('请求成功', $response);
    }

    /**
     * 添加收藏
     */
    public function setFavorites()
    {
        $input = $this->request->param();

        //组装数据
        $data = $this->_FavoritesObject($input['title'], $input['url'], $input['description'], $input['table_name'], $input['object_id']);
        if (!$data) {
            $this->error('收藏失败');
        }
        $userFavoriteModel = new UserFavoriteModel();
        $count             = $userFavoriteModel
            ->where(['user_id' => $this->getUserId(), 'object_id' => $input['object_id']])
            ->where('table_name', $input['table_name'])
            ->count();
        if ($count > 0) {
            $this->error('已收藏', ['code' => 1]);
        }

        $favoriteId = $userFavoriteModel->setFavorite($data);
        if ($favoriteId) {
            $this->success('收藏成功', ['id' => $favoriteId]);
        } else {
            $this->error('收藏失败');
        }

    }

    /**
     * 收藏数据组装
     * @param $title
     * @param $url
     * @param $description
     * @param $table_name
     * @param $object_id
     * @return bool
     */
    protected function _FavoritesObject($title, $url, $description, $table_name, $object_id)
    {
        $data['user_id']     = $this->getUserId();
        $data['create_time'] = time();

        if (empty($title)) {
            return false;
        } else if (empty($url)) {
            return false;
        } elseif (empty($table_name)) {
            return false;
        } elseif (empty($object_id)) {
            return false;
        }
        $data['title']       = $title;
        $data['url']         = htmlspecialchars_decode($url);
        $data['description'] = $description;
        $data['table_name']  = $table_name;
        $data['object_id']   = $object_id;
        return $data;
    }

    /**
     * 取消收藏
     * @throws \Exception
     */
    public function unsetFavorites()
    {
        $id     = $this->request->param('id', 0, 'intval');
        $userId = $this->getUserId();

        $userFavoriteModel = new UserFavoriteModel();
        $count             = $userFavoriteModel->where(['id' => $id, 'user_id' => $userId])->count();

        if ($count == 0) {
            $this->error('收藏不存在,无法取消');
        }

        $userFavoriteModel->where(['id' => $id])->delete();

        $this->success('取消成功');

    }

    /**
     * 判断是否已经收藏
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function hasFavorite()
    {
        $input = $this->request->param();

        $validate = new Validate([
            'table_name' => 'require',
            'object_id'  => 'require',
        ]);

        if (!$validate->check($input)) {
            $this->error($validate->getError());
        }

        $userId = $this->userId;

        if (empty($this->userId)) {
            $this->error('用户登录');
        }

        $userFavoriteModel = new UserFavoriteModel();
        $findFavorite = $userFavoriteModel->where([
            'table_name' => $input['table_name'],
            'user_id'    => $userId,
            'object_id'  => intval($input['object_id'])
        ])->find();

        if ($findFavorite) {
            $this->success('success', $findFavorite);
        } else {
            $this->error('用户未收藏');
        }

    }
}
