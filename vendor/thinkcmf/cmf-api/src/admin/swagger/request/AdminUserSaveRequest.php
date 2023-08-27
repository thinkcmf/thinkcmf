<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"user_login","user_email","role_ids"}
 * )
 */
class AdminUserSaveRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="用户名"
     * )
     */
    public $user_login;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="密码"
     * )
     * @var string
     */
    public $user_pass;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="邮箱"
     * )
     * @var string
     */
    public $user_email;

    /**
     * @OA\Property(
     *     type="array",
     *     example={1,2},
     *     description="角色ID列表",
     *     @OA\Items(type="integer")
     * )
     * @var string
     */
    public $role_ids;



}
