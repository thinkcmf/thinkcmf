<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\portal\service;


use app\portal\model\PortalCategoryModel;

class CategoryService
{
    /**
     *
     */
    public static function adminCategoryIds()
    {
        $categoryModel  = new PortalCategoryModel();
        $categoryIds    = $categoryModel->where('delete_time', 0)->column('id');
        $newCategoryIds = [];
        $adminId        = cmf_get_current_admin_id();
        foreach ($categoryIds as $categoryId) {
            if (cmf_auth_check($adminId, "portal/Category/index?id={$categoryId}")) {
                $newCategoryIds[] = $categoryId;
            }
        }

        return $newCategoryIds;
    }

}