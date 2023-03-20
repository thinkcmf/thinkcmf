<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------
namespace cmf\lib;

use app\admin\model\RoleModel;
use cmf\model\AuthAccessModel;
use cmf\model\AuthRuleModel;
use cmf\model\RoleUserModel;
use cmf\model\UserModel;

/**
 * ThinkCMF权限认证类
 */
class Auth
{

    //默认配置
    protected $_config = [];

    public function __construct()
    {
    }

    /**
     * 检查权限
     * @param $name     string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param $uid      int           认证用户的id
     * @param $relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @param $roleType string    角色类型
     * @return boolean           通过验证返回true;失败返回false
     */
    public function check($uid, $name, $relation = 'or', $roleType = 'admin')
    {
        if (empty($uid)) {
            return false;
        }

        if ($uid == 1) {
            return true;
        }

        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $findAuthRule = AuthRuleModel::where('name', $name)->field('id')->find();
                if (empty($findAuthRule)) {//没有规则时,不验证!
                    return true;
                }
                $name = [$name];
            }
        }

        $list    = []; //保存验证通过的规则名
        $roleIds = RoleUserModel::where('user_id', $uid)->column('role_id');
        $groups  = RoleModel::where(['type' => $roleType, 'status' => 1])
            ->where('id', 'in', $roleIds)
            ->column('id');

        if (in_array(1, $groups)) {
            return true;
        }

        if (empty($groups)) {
            return false;
        }

        $ruleAccesses = AuthAccessModel::where(function ($query) use ($groups) {
            if (count($groups) == 1) {
                $query->where('role_id', $groups[0]);
            } else {
                $query->where('role_id', 'in', $groups);
            }
        })
            ->where(function ($query) use ($name) {
                if (count($name) == 1) {
                    $query->where('rule_name', $name[0]);
                } else {
                    $query->where('rule_name', 'in', $name);
                }
            })
            ->field('rule_name')
            ->select();

        if ($relation == 'or' && count($ruleAccesses) > 0) {
            return true;
        }

        foreach ($ruleAccesses as $ruleAccess) {
            $list[] = strtolower($ruleAccess['rule_name']);
        }

        $diff = array_diff($name, $list);
        if ($relation == 'and' && empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * 获得用户资料
     * @param $uid
     * @return mixed
     */
    private function getUserInfo($uid)
    {
        return UserModel::where('id', $uid)->find();
    }

}
