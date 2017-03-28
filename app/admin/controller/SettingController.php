<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;

use think\Db;

/**
 * Class SettingController
 * @package app\admin\controller
 * @adminMenuRoot('name'=>'设置','icon'=>'cogs','action'=>'default','remark'=>'系统设置入口')
 */
class SettingController extends AdminBaseController
{

    // 网站信息
    public function site()
    {
        //$option=$this->options_model->where("option_name='site_info'")->find();
//		$cmfSettings=$this->options_model->where("option_name='cmf_settings'")->getField("option_value");
//		$tpls=sp_scan_dir(C("SP_TMPL_PATH")."*",GLOB_ONLYDIR);
//		$noneed=array(".","..",".svn");
//		$tpls=array_diff($tpls, $noneed);
//		$this->assign("templates",$tpls);
//
//		$adminstyles=sp_scan_dir("public/simpleboot/themes/*",GLOB_ONLYDIR);
//		$adminstyles=array_diff($adminstyles, $noneed);
//		$this->assign("adminstyles",$adminstyles);
//		if($option){
//			$this->assign(json_decode($option['option_value'],true));
//			$this->assign("option_id",$option['option_id']);
//		}
//
//		$cdnSettings=sp_get_option('cdn_settings');


        $this->assign(cmf_get_option('site_info'));

        $cdnSettings = [];
        $cmfSettings = "";

        $this->assign("templates", []);
        $this->assign("adminstyles", []);
        $this->assign("cdn_settings", $cdnSettings);

        $this->assign("cmf_settings", json_decode($cmfSettings, true));

        return $this->fetch();
    }

    // 网站信息设置提交
    public function sitePost()
    {
        if ($this->request->isPost()) {
            $options = $this->request->param('options/a');

            cmf_set_option('site_info', $options);

            $cmfSettings = $this->request->param('cmf_settings/a');

            $bannedUsernames                 = preg_replace("/[^0-9A-Za-z_\\x{4e00}-\\x{9fa5}-]/u", ",", $cmfSettings['banned_usernames']);
            $cmfSettings['banned_usernames'] = $bannedUsernames;

            cmf_set_option('cmf_settings', $cmfSettings);

            $cdnSettings = $this->request->param('cdn_settings/a');
            cmf_set_option('cdn_settings', $cdnSettings);

            $this->success("保存成功！");

        }
    }

    // 密码修改
    public function password()
    {
        return $this->fetch();
    }

    // 密码修改提交
    public function passwordPost()
    {
        if ($this->request->isPost()) {

            $data = $this->request->param();
            if (empty($data['oldPassword'])) {
                $this->error("原始密码不能为空！");
            }
            if (empty($data['password'])) {
                $this->error("新密码不能为空！");
            }
            $user_obj = Db::name('Users');
            $uid      = 1;//TODO

            $admin = $user_obj->where(["id" => $uid])->find();

            $oldPassword = input('post.oldPassword');
            $password    = input('post.password');

            if (cmf_compare_password($oldPassword, $admin['user_pass'])) {
                if ($password == input('post.rePassword')) {

                    if (cmf_compare_password($password, $admin['user_pass'])) {
                        $this->error("新密码不能和原始密码相同！");
                    } else {
                        $data['user_pass'] = cmf_password($password);
                        $data['id']        = $uid;
                        $r                 = $user_obj->update($data);
                        if ($r !== false) {
                            $this->success("修改成功！");
                        } else {
                            $this->error("修改失败！");
                        }
                    }
                } else {
                    $this->error("密码输入不一致！");
                }

            } else {
                $this->error("原始密码不正确！");
            }
        }
    }

    // 上传限制设置界面
    public function upload()
    {
        $uploadSetting = cmf_get_upload_setting();
        $this->assign($uploadSetting);
        return $this->fetch();
    }

    // 上传限制设置界面提交
    public function uploadPost()
    {
        if ($this->request->isPost()) {
            //TODO 非空验证
            $uploadSetting = $this->request->param();

            cmf_set_option('upload_setting', $uploadSetting);
            $this->success('保存成功！');
        }

    }

    // 清除缓存
    public function clearCache()
    {
        cmf_clear_cache();
        return $this->fetch();
    }


}