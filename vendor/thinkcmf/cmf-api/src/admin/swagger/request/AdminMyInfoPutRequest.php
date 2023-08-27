<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={}
 * )
 */
class AdminMyInfoPutRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="昵称"
     * )
     */
    public $user_nickname;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="",
     *     description="性别"
     * )
     * @var string
     */
    public $sex;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="生日，格式如:2013-01-04"
     * )
     * @var string
     */
    public $birthday;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="个人网址"
     * )
     * @var string
     */
    public $user_url;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="个性签名"
     * )
     * @var string
     */
    public $signature;



}
