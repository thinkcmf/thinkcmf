<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"slide_id","title"}
 * )
 */
class AdminSlideItemListOrderRequest
{

    /**
     * @OA\Property(
     *     type="object",
     *     example={"1":"10000","2":"1"},
     *     description="排序数据"
     * )
     */
    public $list_orders;
}
