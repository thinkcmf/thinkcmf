<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"app"}
 * )
 */
class AdminMenuImportRequest
{
    /**
     * @OA\Property(
     *     type="string",
     *     example="admin",
     *     description="应用名"
     * )
     * @var string
     */
    public $app;




}
