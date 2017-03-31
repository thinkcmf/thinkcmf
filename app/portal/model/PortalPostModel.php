<?php
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


    public function adminAddPage($data)
    {
        $data['user_id']   = cmf_get_current_admin_id();
        $data['post_type'] = 2;
        $this->allowField(true)->data($data, true)->save();

        return $this;

    }

    public function adminEditPage($categories, $data)
    {
        $data['user_id']   = cmf_get_current_admin_id();
        $data['post_type'] = 2;
        $this->allowField(true)->isUpdate(true)->data($data, true)->save();

        if (is_string($categories)) {
            $categories = explode(',', $categories);
        }

        $this->categories()->detach();

        $this->categories()->save($categories);

        return $this;
    }

}