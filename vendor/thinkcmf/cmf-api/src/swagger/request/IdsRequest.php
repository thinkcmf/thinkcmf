<?php

namespace api\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={}
 * )
 */
class IdsRequest
{

    /**
     * @OA\Property(
     *     type="array",
     *     description="排序数据",
     *     @OA\Items(type="integer")
     * )
     */
    public $ids;
}
