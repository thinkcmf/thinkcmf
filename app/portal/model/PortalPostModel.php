<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\model;

use think\Model;
use think\Db;

class PortalPostModel extends Model
{

    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id')->setEagerlyType(0);
    }

    public function categories()
    {
        $prefix = $this->getConfig('prefix');
        return $this->belongsToMany('PortalCategoryModel', $prefix . 'portal_category_post', 'category_id', 'post_id');
    }

    public function getPostContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function setPublishedTimeAttr($value)
    {
        return strtotime($value);
    }

    public function adminAddArticle($data, $categories)
    {
        $data['user_id'] = cmf_get_current_admin_id();
        $this->allowField(true)->data($data, true)->save();

        if (is_string($categories)) {
            $categories = explode(',', $categories);
        }

        $this->categories()->save($categories);

        return $this;

    }

    public function adminEditArticle($data, $categories)
    {
        $data['user_id'] = cmf_get_current_admin_id();
        $this->allowField(true)->isUpdate(true)->data($data, true)->save();

        if (is_string($categories)) {
            $categories = explode(',', $categories);
        }

        $this->categories()->detach();

        $this->categories()->save($categories);

        return $this;

    }

    public function adminDeletePage($data)
    {

        if (isset($data['id'])) {
            $id = $data['id']; //获取删除id

            $res = $this->where(['id' => $id])->find();

            if ($res) {
                $res = json_decode(json_encode($res), true); //转换为数组

                $recycleData = [
                    'object_id'   => $res['id'],
                    'create_time' => time(),
                    'table_name'  => 'portal_post',
                    'name'        => $res['post_title'],

                ];

                Db::startTrans(); //开启事务
                $transStatus = false;
                try {
                    Db::name('portal_post')->where(['id' => $id])->update([
                        'post_status' => 3,
                        'delete_time' => time()
                    ]);
                    Db::name('recycle_bin')->insert($recycleData);

                    $transStatus = true;
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {

                    $transStatus = false;
                    // 回滚事务
                    Db::rollback();


                }
                return $transStatus;


            } else {
                return false;
            }
        } elseif (isset($data['ids'])) {
            $ids = $data['ids'];

            $res = $this->where(['id' => ['in', $ids]])
                ->select();

            if ($res) {
                $res = json_decode(json_encode($res), true);
                foreach ($res as $key => $value) {
                    $recycleData[$key]['object_id']   = $value['id'];
                    $recycleData[$key]['create_time'] = time();
                    $recycleData[$key]['table_name']  = 'portal_post';
                    $recycleData[$key]['name']        = $value['post_title'];

                }

                Db::startTrans(); //开启事务
                $transStatus = false;
                try {
                    Db::name('portal_post')->where(['id' => ['in', $ids]])
                        ->update([
                            'post_status' => 3,
                            'delete_time' => time()
                        ]);


                    Db::name('recycle_bin')->insertAll($recycleData);

                    $transStatus = true;
                    // 提交事务
                    Db::commit();

                } catch (\Exception $e) {

                    $transStatus = false;

                    // 回滚事务
                    Db::rollback();


                }
                return $transStatus;


            } else {
                return false;
                //  $this->error(lang('DELETE_FAILED'));
            }

        } else {
            return false;
            //$this->error(lang('DELETE_FAILED'));
        }
    }


    public function adminAddPage($data)
    {
        $data['user_id']   = cmf_get_current_admin_id();
        $data['post_type'] = 2;
        $this->allowField(true)->data($data, true)->save();

        return $this;

    }

    /**
     * @param $data
     * @return $this
     */
    public function adminEditPage($data)
    {
        $data['user_id']   = cmf_get_current_admin_id();
        $data['post_type'] = 2;
        $this->allowField(true)->isUpdate(true)->data($data, true)->save();

        return $this;
    }

}
