<?php
namespace app\admin\api;

use app\admin\model\SlideModel;

class SlideApi
{
    /**
     * 幻灯片列表 用于模板设计
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function index($param = [])
    {
        $slideModel = new SlideModel();

        $where = [];

        if (!empty($param['keyword'])) {
            $where['name'] = ['like', "%{$param['keyword']}%"];
        }

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        return $slideModel->where($where)->select();
    }

}