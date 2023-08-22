<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"name","nav_id"}
 * )
 */
class AdminNavMenuSaveRequest
{
    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     description="导航Id"
     * )
     */
    public $nav_id;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     description="父级Id"
     * )
     */
    public $parent_id;


    /**
     * @OA\Property(
     *     type="string",
     *     example="http:/www.thinkcmf.com",
     *     description="外部链接"
     * )
     */
    public $external_href;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="链接数据base64，和external_href任选一个值"
     * )
     */
    public $href;

    /**
     * @OA\Property(
     *     type="string",
     *     example="首页",
     *     description="名称"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="打开方式"
     * )
     * @var string
     */
    public $target;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="图标"
     * )
     * @var string
     */
    public $icon;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     enum={0,1},
     *     description="显示状态;1:显示;0:隐藏"
     * )
     */
    public $status;



}
