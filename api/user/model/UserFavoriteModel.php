<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 <449134904@qq.com>
// +----------------------------------------------------------------------

namespace api\user\model;

use think\Model;

/**
 * @property mixed id
 */
class UserFavoriteModel extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * 关联表
     * @param  string $table_name [关联表名]
     * @return \think\model\relation\HasOne
     */
    protected function unionTable($table_name)
    {
        return $this->hasOne($table_name . 'Model', 'object_id');
    }

    /**
     * thumbnail 自动转化图片地址为绝对地址
     * @param $value
     * @return string
     */
    public function getThumbnailAttr($value)
    {
        if (!empty($value)) {
            $value = cmf_get_image_url($value);
        }

        return $value;
    }

    /**
     * url   自动转化
     * @param $value
     * @return string
     */
    public function getUrlAttr($value)
    {
        $url = json_decode($value, true);
        if (!empty($url)) {
            $url = url($url['action'], $url['param'], true, true);
        } else {
            $url = '';
        }
        return $url;
    }

    /**
     * 获取收藏内容
     * @param  array $data [select,find查询结果]
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getFavorite($data)
    {
        if (!is_string($data[0])) {
            foreach ($data as $key => $value) {
                $where[$value['table_name']][] = $value['object_id'];
            }
            foreach ($where as $key => $value) {
                $favoriteData[] = $this->unionTable($key)->select($value);
            }
        } else {
            $favoriteData = $this->unionTable($data['table_name'])->find($data['object_id']);
        }

        return $favoriteData;
    }

    /**
     * 添加收藏
     * @param $data
     * @return bool
     */
    public function addFavorite($data)
    {
        //获取收藏内容信息
        $Favorite =$this->allowField(true)->isUpdate(false)->data($data,true)->save();
        return $Favorite;
    }

    /**
     * [unsetFavorite 取消收藏]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-08-03T09:17:30+0800
     * @since:    1.0
     * @return    [type]                    [description]
     */
    public function unsetFavorite($id)
    {
        return self::destroy($id); //执行删除
    }
}
