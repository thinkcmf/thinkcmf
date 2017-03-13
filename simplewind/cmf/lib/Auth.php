<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------

namespace cmf\lib;

use think\Db;
/**
 * ThinkCMF权限认证类
 */
class Auth
{

    //默认配置
    protected $_config = array();

    public function __construct()
    {
    }

    /**
     * 检查权限
     * @param $name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param $uid  int           认证用户的id
     * @param $relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @return boolean           通过验证返回true;失败返回false
     */
    public function check($uid, $name, $relation = 'or')
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
                $name = array($name);
            }
        }
        $list = array(); //保存验证通过的规则名
        $groups = Db::name('RoleUser')
            ->alias("a")
            ->join('__ROLE__ r','a.role_id = r.id')
            ->where(array("a.user_id" => $uid, "r.status" => 1))
            ->column("role_id");

        if (in_array(1, $groups)) {
            return true;
        }

        if (empty($groups)) {
            return false;
        }
        $rules = Db::name('AuthAccess')
            ->alias("a")
            ->join('__AUTH_RULE__ b ',' a.rule_name = b.name')
            ->where(array("a.role_id" => array("in", $groups), "b.name" => array("in", $name)))
            ->select();
        foreach ($rules as $rule) {
            if (!empty($rule['condition'])) { //根据condition进行验证
                $user = $this->getUserInfo($uid);//获取用户信息,一维数组

                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
                //dump($command);//debug
                @(eval('$condition=(' . $command . ');'));
                if ($condition) {
                    $list[] = strtolower($rule['name']);
                }
            } else {
                $list[] = strtolower($rule['name']);
            }
        }

        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
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
        static $userInfo = array();
        if (!isset($userInfo[$uid])) {
            $userInfo[$uid] = Db::name('user')->where(array('id' => $uid))->find();
        }
        return $userInfo[$uid];
    }

}
