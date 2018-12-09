<?php
// +----------------------------------------------------------------------
// | 文件说明：评论
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: wuwu <15093565100@163.com>
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Date: 2017-7-29
// +----------------------------------------------------------------------

namespace api\user\model;

use api\common\model\CommonModel;
use think\Db;

class CommentModel extends CommonModel
{

    //模型关联方法
    protected $relationFilter = ['user', 'to_user'];

    /**
     * 基础查询
     */
    protected function base($query)
    {
        $query->where('delete_time', 0)
            ->where('status', 1);
    }

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    /**
     * more 自动转化
     * @param $value
     * @return array
     */
    public function getMoreAttr($value)
    {
        if (empty($value)) {
            return null;
        }

        $more = json_decode($value, true);
        if (!empty($more['thumbnail'])) {
            $more['thumbnail'] = cmf_get_image_url($more['thumbnail']);
        }

        if (!empty($more['photos'])) {
            foreach ($more['photos'] as $key => $value) {
                $more['photos'][$key]['url'] = cmf_get_image_url($value['url']);
            }
        }

        if (!empty($more['files'])) {
            foreach ($more['files'] as $key => $value) {
                $more['files'][$key]['url'] = cmf_get_image_url($value['url']);
            }
        }
        return $more;
    }

    /**
     * 关联 user表
     * @return $this
     */
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id');
    }

    public function toUser()
    {
        return $this->belongsTo('UserModel', 'to_user_id');
    }

    /**
     * [CommentList 评论列表获取]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-05-25T20:52:27+0800
     * @since:    1.0
     */
    public function CommentList($map, $limit, $order)
    {
        if (empty($map)) {
            return [];
        }
        $data = $this->with('to_user')->field(true)->where($map)->order($order)->limit($limit)->select();
        return $data;
    }

    /**
     * [setComment 添加评论]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-08-15T23:57:04+0800
     * @since:    1.0
     */
    public static function setComment($data)
    {
        if (!$data) {
            return false;
        }

        if ($obj = self::create($data)) {
            $objectId = intval($data['object_id']);
            try {
                $pk = Db::name($data['table_name'])->getPk();

                Db::name($data['table_name'])->where([$pk => $objectId])->setInc('comment_count');

                Db::name($data['table_name'])->where([$pk => $objectId])->update(['last_comment' => time()]);

            } catch (\Exception $e) {

            }
            return $obj->id;
        } else {
            return false;
        }
    }
}
