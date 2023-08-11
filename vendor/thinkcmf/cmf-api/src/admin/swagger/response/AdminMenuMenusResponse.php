<?php

namespace api\admin\swagger\response;

use OpenApi\Annotations as OA;


/**
 * @OA\Schema()
 */
class AdminMenuMenusResponse
{
    /**
     * @OA\Property(format="int64",example="1")
     * @var int
     */
    public $code;

    /**
     * @OA\Property(example="请求成功!")
     * @var string
     */
    public $msg;

    /**
     * @OA\Property(
     *     type="object",
     *     ref="#/components/schemas/AdminMenuMenusResponseData"
     * )
     * @var object
     */
    public $data;

}

/**
 * @OA\Schema()
 */
class AdminMenuMenusResponseData
{

    /**
     * @OA\Property(example="10")
     * @var int
     */
    public $total;

    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/AdminMenuMenusResponseDataListItem")
     * )
     * @var array
     */
    public $list;

}


/**
 * @OA\Schema(example={
 *     "id": 113,
 *     "parent_id": 32,
 *     "type": 1,
 *     "status": 1,
 *     "list_order": 0,
 *     "app": "admin",
 *     "controller": "Setting",
 *     "action": "site",
 *     "param": "",
 *     "name": "网站信息",
 *     "icon": "",
 *     "remark": "网站信息"
 * })
 */
class AdminMenuMenusResponseDataListItem
{
    /**
     * @OA\Property()
     * @var string
     */
    public $title;


}
