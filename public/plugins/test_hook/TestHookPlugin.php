<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\test_hook;//Demo插件英文名，改成你的插件英文就行了
use cmf\lib\Plugin;

//Demo插件英文名，改成你的插件英文就行了
class TestHookPlugin extends Plugin
{

    public $info = [
        'name'        => 'TestHook',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '测试钩子插件',
        'description' => '测试钩子插件',
        'status'      => 1,
        'author'      => 'ThinkCMF',
        'version'     => '1.0',
        'demo_url'    => 'http://demo.thinkcmf.com',
        'author_url'  => 'http://www.thinkcmf.com'
    ];

    public $hasAdmin = 1;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    //admin_setting_site_view 钩子方法
    public function adminSettingSiteView($param)
    {
        return "test";
    }

    //admin_setting_clear_cache_view 钩子方法
    public function adminSettingClearCacheView($param)
    {
        return "test";
    }

    //admin_nav_index_view 钩子方法
    public function adminNavIndexView($param)
    {
        return "test";
    }

    //admin_link_index_view 钩子方法
    public function adminLinkIndexView($param)
    {
        return "test";
    }

    //admin_slide_index_view 钩子方法
    public function adminSlideIndexView($param)
    {
        return "test";
    }

    //admin_user_index_view 钩子方法
    public function adminUserIndexView($param)
    {
        return "test";
    }

    //admin_rbac_index_view 钩子方法
    public function adminRbacIndexView($param)
    {
        return "test";
    }

    //portal_admin_article_index_view 钩子方法
    public function portalAdminArticleIndexView($param)
    {
        return "test";
    }

    //portal_admin_category_index_view 钩子方法
    public function portalAdminCategoryIndexView($param)
    {
        return "test";
    }

    //portal_admin_page_index_view 钩子方法
    public function portalAdminPageIndexView($param)
    {
        return "test";
    }

    //portal_admin_tag_index_view 钩子方法
    public function portalAdminTagIndexView($param)
    {
        return "test";
    }

    //user_admin_index_view 钩子方法
    public function userAdminIndexView($param)
    {
        return "test";
    }

    //user_admin_asset_index_view 钩子方法
    public function userAdminAssetIndexView($param)
    {
        return "test";
    }

    //user_admin_oauth_index_view 钩子方法
    public function userAdminOauthIndexView($param)
    {
        return "test";
    }

    //admin_recycle_bin_index_view 钩子方法
    public function adminRecycleBinIndexView($param)
    {
        return "test";
    }

    //admin_menu_index_view 钩子方法
    public function adminMenuIndexView($param)
    {
        return "test";
    }

    //admin_custom_login_open 钩子方法
//    public function admin_custom_login_open($param)
//    {
//        return "test";
//    }

}