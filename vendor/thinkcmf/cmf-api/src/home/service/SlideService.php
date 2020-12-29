<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// | Date: 2019/01/11
// | Time:上午 11:31
// +----------------------------------------------------------------------
namespace api\home\service;

use api\home\model\SlideModel;

class SlideService
{
    /**
     * 幻灯片列表
     * @param $map
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function SlideList($map)
    {
        $slideModel = new SlideModel();
        $data       = $slideModel
//            ->relation(['items'])
            ->where('status', 1)
            ->where('delete_time', 0)
            ->where($map)
            ->find();
        return $data;
    }
}
