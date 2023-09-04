<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"parent_id","name","app","controller","action"}
 * )
 */
class AdminMenuSaveRequest
{
    /**
     * @OA\Property(
     *     type="integer",
     *     example="0",
     *     description="父级菜单ID,0:表示一级菜单"
     * )
     */
    public $parent_id;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="名称"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     type="string",
     *     example="demo",
     *     description="应用名"
     * )
     * @var string
     */
    public $app;

    /**
     * @OA\Property(
     *     type="string",
     *     example="AdminIndex",
     *     description="控制名"
     * )
     * @var string
     */
    public $controller;

    /**
     * @OA\Property(
     *     type="string",
     *     example="index",
     *     description="方法名"
     * )
     * @var string
     */
    public $action;

    /**
     * @OA\Property(
     *     type="string",
     *     example="id=3&xxx=1",
     *     description="参数(暂无用)；例:id=3&p=3"
     * )
     * @var string
     */
    public $param;

    /**
     * @OA\Property(
     *     type="string",
     *     example="home",
     *     description="图标；不带前缀fa-，如fa-user => user"
     * )
     * @var string
     */
    public $icon;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="备注"
     * )
     * @var string
     */
    public $remark;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     enum={0,1},
     *     description="状态;1:显示;0:隐藏"
     * )
     * @var string
     */
    public $status;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     enum={0,1,2},
     *     description="类型;0:只作为菜单;1:有界面可访问菜单;2:无界面可访问菜单"
     * )
     * @var string
     */
    public $type;




}
