<?php

namespace api\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema()
 */
class LoginRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="user",
     *     description="手机号，邮箱，账户"
     * )
     */
    public $username;

    /**
     * @OA\Property(
     *     type="string",
     *     example="000000",
     *     description="密码"
     * )
     * @var string
     */
    public $password;

    /**
     * @OA\Property(
     *     type="string",
     *     example="web",
     *     description="设备类型：mobile,android,iphone,ipad,web,pc,mac,wxapp,ios"
     * )
     * @var object
     */
    public $device_type;

}
