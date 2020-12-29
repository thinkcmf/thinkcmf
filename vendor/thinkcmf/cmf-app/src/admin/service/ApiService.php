<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\service;

use app\admin\model\LinkModel;
use app\admin\model\SlideItemModel;
use app\admin\model\SlideModel;

class ApiService
{
    /**
     * 获取所有友情链接
     */
    public static function links()
    {
        return LinkModel::where('status', 1)->order('list_order ASC')->select();
    }

    /**
     * 获取所有幻灯片
     * @param $slideId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function slides($slideId)
    {
        $slideCount = SlideModel::where('id', $slideId)->where(['status' => 1, 'delete_time' => 0])->count();

        if ($slideCount == 0) {
            return [];
        }

        $slides = SlideItemModel::where('status', 1)->where('slide_id', $slideId)->order('list_order ASC')->select();

        return $slides;
    }
}
