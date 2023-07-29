<?php

namespace api\admin\swagger\response;

use OpenApi\Annotations as OA;


/**
 * @OA\Schema()
 */
class AdminPublicLoginResponse
{
    /**
     * @OA\Property(format="int64",example="1")
     * @var int
     */
    public $code;

    /**
     * @OA\Property(example="登录成功!")
     * @var string
     */
    public $msg;

    /**
     * @OA\Property(
     *     type="object",
     *     ref="#/components/schemas/AdminPublicLoginResponseData"
     * )
     * @var object
     */
    public $data;

}

/**
 * @OA\Schema()
 */
class AdminPublicLoginResponseData
{
    /**
     * @OA\Property(
     *     type="string",
     *     example="01227ac27d40f95491c47847bf91b558d7640d81a1578bb72cb2ff8b14fd9cb4"
     * )
     * @var array
     */
    public $token;

}

