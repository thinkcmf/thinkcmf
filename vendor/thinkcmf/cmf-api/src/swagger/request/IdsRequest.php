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
     *     description="IDs",
     *     @OA\Items(type="integer")
     * )
     */
    public $ids;
}
