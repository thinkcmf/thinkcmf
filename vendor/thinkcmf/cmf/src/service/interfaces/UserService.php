<?php


namespace service\interfaces;

/**
 * 用户服务类
 * Interface UserService
 * @package service\interfaces
 * @Service
 * @author YYW
 * @date 2020-11-17 19:21:39
 */
interface UserService
{
    /**
     * @param array $field
     * @param int $id
     * @return mixed
     * @author YYW
     * @date 2020-11-17 19:26:01
     */
    public function getId($field = ['id'], $id = 0);
}