<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"name","password"}
 * )
 */
class AdminAppUninstallDeleteRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="demo",
     *     description="应用名"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="网站创始人后台登录密码"
     * )
     */
    public $password;

}

