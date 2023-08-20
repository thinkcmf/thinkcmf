<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"name"}
 * )
 */
class AdminNavSaveRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="主导航",
     *     description="名称"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="备注"
     * )
     * @var string
     */
    public $remark;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     enum={0,1},
     *     description="是否为主导航"
     * )
     */
    public $is_main;


}
