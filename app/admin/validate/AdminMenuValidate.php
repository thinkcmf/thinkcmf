<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;
use think\Db;

class AdminMenuValidate extends Validate
{
    protected $rule = [
        'name'       => 'require',
        'app'        => 'require',
        'controller' => 'require',
        'parent_id'  => 'checkParentId',
        'action'     => 'require|unique:AdminMenu,app^controller^action',
    ];

    protected $message = [
        'name.require'       => '名称不能为空',
        'app.require'        => '应用不能为空',
        'parent_id'          => '超过了4级',
        'controller.require' => '名称不能为空',
        'action.require'     => '名称不能为空',
        'action.unique'      => '同样的记录已经存在!',
    ];

    protected $scene = [
        'add'  => ['name', 'app', 'controller', 'action', 'parent_id'],
        'edit' => ['name', 'app', 'controller', 'action', 'id', 'parent_id'],

    ];

    // 自定义验证规则
    protected function checkParentId($value)
    {
        $find = Db::name('AdminMenu')->where(["id" => $value])->value('parent_id');

        if ($find) {
            $find2 = Db::name('AdminMenu')->where(["id" => $find])->value('parent_id');
            if ($find2) {
                $find3 = Db::name('AdminMenu')->where(["id" => $find2])->value('parent_id');
                if ($find3) {
                    return false;
                }
            }
        }
        return true;
    }
}