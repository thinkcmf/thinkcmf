<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={}
 * )
 */
class AdminPluginConfigPutRequestForm
{

    /**
     * @OA\Property(
     *     property="config[config1]",
     *     example="config1_value",
     *     type="string",
     *     description="配置1"
     * )
     */
    public $config1;

    /**
     * @OA\Property(
     *     property="config[config2]",
     *     example="config2_value",
     *     type="string",
     *     description="配置2"
     * )
     */
    public $config2;



}
