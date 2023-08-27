<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"from_name","from","host","port","username","password"}
 * )
 */
class AdminMyEmailSettingPutRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="ThinkCMF",
     *     description="发件人"
     * )
     */
    public $from_name;

    /**
     * @OA\Property(
     *     type="string",
     *     example="no-reply@thinkcmf.com",
     *     description="邮箱地址"
     * )
     * @var string
     */
    public $from;

    /**
     * @OA\Property(
     *     type="string",
     *     example="smtp.thinkcmf.com",
     *     description="SMTP服务器"
     * )
     */
    public $host;

    /**
     * @OA\Property(
     *     type="string",
     *     example="ssl",
     *     enum={"","ssl","tls"},
     *     description="连接方式,或选值:ssl,tls或留空"
     * )
     */
    public $smtp_secure;

    /**
     * @OA\Property(
     *     type="string",
     *     example="463",
     *     description="SMTP服务器端口"
     * )
     */
    public $port;

    /**
     * @OA\Property(
     *     type="string",
     *     example="463",
     *     description="发件箱帐号"
     * )
     */
    public $username;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="发件箱授权码"
     * )
     */
    public $password;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="邮箱个人签名"
     * )
     */
    public $signature;




}
