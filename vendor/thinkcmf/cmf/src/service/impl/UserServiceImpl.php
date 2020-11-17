<?php


namespace service\impl;


use cmf\model\UserModel;
use service\interfaces\UserService;

/**
 * 用户实现类
 * Class UserServiceImpl
 * @package service\impl
 *
 * @author YYW
 * @email:449134904@qq.com
 * @date 2020-11-17 19:21:02
 */
class UserServiceImpl implements UserService
{
    /**
     * @var UserModel
     */
    private $model;

    public function __construct(UserModel $model)
    {
        $this->model = $model;
    }

    public function getId($field = ['id'], $id = 0)
    {
        return $this->model->field($field)->where('id', $id)->find();
    }
}