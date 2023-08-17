<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"full_url","url"}
 * )
 */
class AdminRouteSaveRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="demo/List/index",
     *     description="原始网址"
     * )
     */
    public $full_url;

    /**
     * @OA\Property(
     *     type="string",
     *     example="list/:id",
     *     description="显示网址"
     * )
     * @var string
     */
    public $url;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     enum={1,0},
     *     description="状态"
     * )
     * @var string
     */
    public $status;


}
