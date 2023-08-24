<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"name"}
 * )
 */
class AdminRoleSaveRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="财务",
     *     description="角色名称"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     description="角色类型"
     * )
     */
    public $type;

    /**
     * @OA\Property(
     *     type="string",
     *     example="角色描述",
     *     description="角色描述"
     * )
     * @var string
     */
    public $remark;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     enum={0,1},
     *     description="状态;1:启用;0:禁用"
     * )
     */
    public $status;


}
