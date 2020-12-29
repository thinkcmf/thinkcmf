<?php
// +----------------------------------------------------------------------
// | 文件说明：评论
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: wuwu <15093565100@163.com>
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Date: 2017-7-29
// +----------------------------------------------------------------------

namespace api\user\model;

use think\facade\Db;
use think\Model;

/**
 * @property mixed id
 */
class CommentModel extends Model
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'comment';
    
    //模型关联方法
    protected $relationFilter = ['user', 'to_user'];


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
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id')->field('id,user_nickname');
    }

    /**
     *
     * @return \think\model\relation\BelongsTo
     */
    public function toUser()
    {
        return $this->belongsTo('UserModel', 'to_user_id')->field('id,user_nickname');
    }

    /**
     * 添加评论
     * @param $data
     * @return bool|mixed
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

                Db::name($data['table_name'])->where([$pk => $objectId])->inc('comment_count')->update();

                Db::name($data['table_name'])->where([$pk => $objectId])->update(['last_comment' => time()]);

            } catch (\Exception $e) {

            }
            return $obj->id;
        } else {
            return false;
        }
    }
}
