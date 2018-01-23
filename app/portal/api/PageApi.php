<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\api;

use app\portal\model\PortalPostModel;

class PageApi
{
    /**
     * 页面列表 用于模板设计
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function index($param = [])
    {
        $portalPostModel = new PortalPostModel();

        $where = [
            'post_type'      => 2,
            'published_time' => [['< time', time()], ['> time', 0]],
            'post_status'    => 1,
            'delete_time'    => 0
        ];

        if (!empty($param['keyword'])) {
            $where['post_title'] = ['like', "%{$param['keyword']}%"];
        }

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        return $portalPostModel->field('id,post_title AS name')->where($where)->select();
    }

    /**
     * 页面列表 用于导航选择
     * @return array
     */
    public function nav()
    {
        $portalPostModel = new PortalPostModel();

        $where = [
            'post_type'      => 2,
            'published_time' => [['< time', time()], ['> time', 0]],
            'post_status'    => 1,
            'delete_time'    => 0
        ];


        $pages = $portalPostModel->field('id,post_title AS name')->where($where)->select();

        $return = [
            'rule'  => [
                'action' => 'portal/Page/index',
                'param'  => [
                    'id' => 'id'
                ]
            ],//url规则
            'items' => $pages //每个子项item里必须包括id,name,如果想表示层级关系请加上 parent_id
        ];

        return $return;
    }

}