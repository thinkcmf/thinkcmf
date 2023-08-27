<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"user_login","user_email","role_ids"}
 * )
 */
class AdminUserSaveRequestForm
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
     *     property="role_ids[0]",
     *     type="integer",
     *     example=1,
     *     description="角色ID1"
     * )
     * @var string
     */
    public $role_ids1;

    /**
     * @OA\Property(
     *     property="role_ids[1]",
     *     type="integer",
     *     example=2,
     *     description="角色ID2"
     * )
     * @var string
     */
    public $role_ids2;



}
