<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"old_password","password","re_password"}
 * )
 */
class AdminSettingPasswordPutRequest
{
    /**
     * @OA\Property(
     *     property="old_password",
     *     type="string",
     *     example="",
     *     description="原始密码"
     * )
     */
    public $oldPassword;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="新密码"
     * )
     */
    public $password;


    /**
     * @OA\Property(
     *     property="re_password",
     *     type="string",
     *     example="",
     *     description="重复新密码"
     * )
     */
    public $rePassword;

}

