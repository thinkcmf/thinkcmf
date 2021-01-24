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

class PageApi
{
    /**
     * 页面列表 用于导航选择
     * @return array
     */
    public function nav()
    {
        $return = [
            'rule'  => [
                'action' => 'demo/Index/index',
                'param'  => [
                ]
            ],//url规则
            'items' => [
                ['id' => 1, 'name' => 'test']
            ] //每个子项item里必须包括id,name,如果想表示层级关系请加上 parent_id
        ];

        return $return;
    }

}
