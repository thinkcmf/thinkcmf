<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\demo\api;

use think\db\Query;

class PageApi
{
    /**
     * 页面列表 用于模板设计
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function index($param = [])
    {
//        $param['keyword'];

        return [
            [
                'id'   => 1,
                'name' => '测试页面'
            ]
        ];
    }

    /**
     * 页面列表 用于导航选择
     * @return array
     */
    public function nav()
    {
        $return = [
            'rule'  => [
                'action' => 'portal/Page/index',
                'param'  => [
                    'id' => 'id'
                ]
            ],//url规则
            'items' => [
                [
                    'id'   => 1,
                    'name' => '测试页面'
                ]
            ] //每个子项item里必须包括id,name,如果想表示层级关系请加上 parent_id
        ];

        return $return;
    }

}
