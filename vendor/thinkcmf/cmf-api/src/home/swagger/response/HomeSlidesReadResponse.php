<?php

namespace api\home\swagger\response;

use OpenApi\Annotations as OA;


/**
 * @OA\Schema()
 */
class HomeSlidesReadResponse
{
    /**
     * @OA\Property(format="int64",example="1")
     * @var int
     */
    public $code;

    /**
     * @OA\Property(example="该组幻灯片获取成功!")
     * @var string
     */
    public $msg;

    /**
     * @OA\Property(
     *     type="object",
     *     ref="#/components/schemas/HomeSlidesReadResponseData"
     * )
     * @var object
     */
    public $data;

}

/**
 * @OA\Schema()
 */
class HomeSlidesReadResponseData
{
//    "id": 1,
//        "status": 1,
//        "delete_time": 0,
//        "name": "幻灯片一",
//        "remark": "苦",
    /**
     * @OA\Property(
     *     type="int64",
     *     example="1"
     * )
     */
    public $id;

    /**
     * @OA\Property(
     *     type="int64",
     *     example="1"
     * )
     */
    public $status;

    /**
     * @OA\Property(
     *     type="int64",
     *     example="0"
     * )
     */
    public $delete_time;

    /**
     * @OA\Property(
     *     type="string",
     *     example="幻灯片名称"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     type="string",
     *     example="备注"
     * )
     */
    public $remark;

    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/HomeSlidesReadResponseDataItem")
     * )
     */
    public $items;
}



/**
 * @OA\Schema()
 */
class HomeSlidesReadResponseDataItem
{
    /**
     * @OA\Property(
     *     type="int64",
     *     example="1"
     * )
     */
    public $id;

    /**
     * @OA\Property(
     *     type="int64",
     *     example="1"
     * )
     */
    public $slide_id;

    /**
     * @OA\Property(
     *     type="int64",
     *     example="1"
     * )
     */
    public $status;

    /**
     * @OA\Property(
     *     type="number",
     *     example="1.0"
     * )
     */
    public $list_order;

    /**
     * @OA\Property(
     *     type="string",
     *     example="页面一"
     * )
     */
    public $title;

    /**
     * @OA\Property(
     *     type="string",
     *     example="http://cmf6.im/upload/portal/20170810/a721883e7f17fb054be63ddb7331ddb5.jpg"
     * )
     */
    public $image;

    /**
     * @OA\Property(
     *     type="string",
     *     example="http://www.baidu.com"
     * )
     */
    public $url;

    /**
     * @OA\Property(
     *     type="string",
     *     example="_blank"
     * )
     */
    public $target;

    /**
     * @OA\Property(
     *     type="string",
     *     example="描述"
     * )
     */
    public $description;

    /**
     * @OA\Property(
     *     type="string",
     *     example="内容"
     * )
     */
    public $content;

    /**
     * @OA\Property(
     *     type="object",
     *     example={"xxxx":"xxxx"}
     * )
     */
    public $more;

}

