<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"slide_id","title"}
 * )
 */
class AdminSlideItemSaveRequest
{

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     description="幻灯片ID"
     * )
     */
    public $slide_id;

    /**
     * @OA\Property(
     *     type="string",
     *     example="这里是标题",
     *     description="标题"
     * )
     */
    public $title;

    /**
     * @OA\Property(
     *     type="string",
     *     example="https://www.thinkcmf.com",
     *     description="链接地址"
     * )
     * @var string
     */
    public $url;

    /**
     * @OA\Property(
     *     type="string",
     *     example="_blank",
     *     enum={"_blank","_self","_top",""},
     *     description="打开方式"
     * )
     * @var string
     */
    public $target;

    /**
     * @OA\Property(
     *     type="string",
     *     example="default/xxxx.jpg",
     *     description="缩略图"
     * )
     * @var string
     */
    public $image;

    /**
     * @OA\Property(
     *     type="string",
     *     example="这里是描述",
     *     description="描述"
     * )
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *     type="string",
     *     example="这里是内容",
     *     description="内容"
     * )
     * @var string
     */
    public $content;


}
