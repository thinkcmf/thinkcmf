<?php
/**
 * Created by PhpStorm.
 * User: Powerless
 * Date: 2017-3-13
 * Time: 14:03
 */
namespace app\user\model;

use think\Db;
use think\Model;

class UserModel extends Model
{
    public function doMobile($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('mobile', $user['mobile'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function doName($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('user_login', $user['user_login'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function doEmail($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('user_email', $user['user_email'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function registerEmail($user)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('user_email', $user['user_email'])->find();
        if (empty($result)) {
            $data = [
                'user_login'      => '',
                'user_email'      => $user['user_email'],
                'mobile'          => '',
                'user_nickname'   => '',
                'user_pass'       => cmf_password($user['password']),
                'last_login_ip'   => get_client_ip(0, true),
                'create_time'     => time(),
                'last_login_time' => time(),
                'user_status'     => 1,
                "user_type"       => 2,
            ];
            $userQuery->insert($data);
            return 0;
        }
        return 1;
    }

    public function registerMobile($user)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('mobile', $user['mobile'])->find();
        if (empty($result)) {
            $data       = [
                'user_login'      => '',
                'user_email'      => '',
                'mobile'          => $user['mobile'],
                'user_nickname'   => '',
                'user_pass'       => cmf_password($user['password']),
                'last_login_ip'   => get_client_ip(0, true),
                'create_time'     => time(),
                'last_login_time' => time(),
                'user_status'     => 1,
                "user_type"       => 2,
            ];
            $uid        = $userQuery->insertGetId($data);
            $date = $userQuery->where('id', $uid)->find();
            cmf_update_current_user($date);
            return 0;
        }
        return 1;
    }


    public function resetEmail($user)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('user_email', $user['user_email'])->find();
        if (!empty($result)) {
            $data = [
                'user_pass'       => cmf_password($user['password']),
                'last_login_ip'   => get_client_ip(0, true),
                'last_login_time' => time(),
            ];
            $userQuery->where('user_email', $user['user_email'])->update($data);
            return 0;
        }
        return 1;
    }

    public function resetMobile($user)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('mobile', $user['mobile'])->find();
        if (!empty($result)) {
            $data = [
                'user_pass'       => cmf_password($user['password']),
                'last_login_ip'   => get_client_ip(0, true),
                'last_login_time' => time(),
            ];
            $userQuery->where('mobile', $user['mobile'])->update($data);
            return 0;
        }
        return 1;
    }

    public function editData($user)
    {
        $uid = cmf_get_current_user_id();
        $user['birthday'] = strtotime($user['birthday']);
        $userQuery = Db::name("user");
        if($userQuery->where('id',$uid)->update($user)){
            $data = $userQuery->where('id',$uid)->find();
            cmf_update_current_user($data);
            return 1;
        }
        return 0;
    }

    public function editPass($user)
    {
        $uid = cmf_get_current_user_id();
        $userQuery = Db::name("user");
        if($user['password1'] != $user['password2']){
            return 1;
        }
        $pass = $userQuery->where('id',$uid)->find();
        if(!cmf_compare_password($user['old_password'],$pass['user_pass'])){
            return 2;
        }
        $data['user_pass'] = cmf_password($user['password1']);
        $userQuery->where('id',$uid)->update($data);
        return 0;
    }

    public function favorites()
    {
        $uid = cmf_get_current_user_id();
        $userQuery = Db::name("UserFavorite");
        $favorites = $userQuery->where(array('user_id'=>$uid))->order('id desc')->paginate(10);
        $data['page'] = $favorites->render();
        $data['lists'] = $favorites->items();
        return $data;
    }

    public function addFavorite($id,$cid)
    {
        $portalQuery = Db::name("PortalPost");
        $portal = $portalQuery->where('id',$id)->find();
        $uid = cmf_get_current_user_id();
        $userQuery = Db::name("UserFavorite");
        $where['user_id'] = $uid;
        $where['object_id'] = $id;
        if($userQuery->where($where)->find()){
            return 2;
        }
        $where['title'] = $portal['post_title'];
        $url['action'] = 'portal/article/index';
        $url['param']['id'] = $id;
        $url['param']['cid'] = $cid;
        $where['url'] = json_encode($url);
        $where['description'] = $portal['post_excerpt'];
        $where['table_name'] = 'PortalPost';
        $where['create_time'] = time();
        if($userQuery->insert($where)){
            return 0;
        }
        return 1;
    }

    public function deleteFavorite($id)
    {
        $uid = cmf_get_current_user_id();
        $userQuery = Db::name("UserFavorite");
        $where['id'] = $id;
        $where['user_id'] = $uid;
        $data = $userQuery->where($where)->delete();
        return $data;
    }

    public function comments()
    {
        $uid = cmf_get_current_user_id();
        $userQuery = Db::name("Comment");
        $where['user_id'] = $uid;
        $where['delete_time'] = 0;
        $favorites = $userQuery->where($where)->order('id desc')->paginate(10);
        $data['page'] = $favorites->render();
        $data['lists'] = $favorites->items();
        return $data;
    }

    public function deleteComment($id)
    {
        $uid = cmf_get_current_user_id();
        $userQuery = Db::name("Comment");
        $where['id'] = $id;
        $where['user_id'] = $uid;
        $data['delete_time'] = time();
        $userQuery->where($where)->update($data);
        return $data;
    }

    public function bangMobile($user)
    {
        $userQuery = Db::name("user");
        $uid = cmf_get_current_user_id();
        $userQuery->where('id', $uid)->update($user);
        $data = $userQuery->where('id', $uid)->find();
        cmf_update_current_user($data);
        return 0;
    }

    public function bangEmail($user)
    {
        $userQuery = Db::name("user");
        $uid = cmf_get_current_user_id();
        $userQuery->where('id', $uid)->update($user);
        $data = $userQuery->where('id', $uid)->find();
        cmf_update_current_user($data);
        return 0;
    }
}
