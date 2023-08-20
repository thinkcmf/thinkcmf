<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"name","url"}
 * )
 */
class AdminLinkSaveRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="ThinkCMF",
     *     description="名称"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     type="string",
     *     example="http://www.thinkcmf.com",
     *     description="链接地址"
     * )
     * @var string
     */
    public $url;

    /**
     * @OA\Property(
     *     type="string",
     *     example="default/xxxx/xxx.png",
     *     description="图标，相对于upload"
     * )
     * @var string
     */
    public $image;

    /**
     * @OA\Property(
     *     type="string",
     *     example="_blank",
     *     description="打开方式"
     * )
     * @var string
     */
    public $target;

    /**
     * @OA\Property(
     *     type="string",
     *     example="thinkcmf 官网",
     *     description="描述"
     * )
     * @var string
     */
    public $description;






}
