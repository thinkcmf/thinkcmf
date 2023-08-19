<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"type"}
 * )
 */
class AdminSettingStoragePutRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="Local",
     *     description="存储类型"
     * )
     */
    public $type;

}

