<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"config"}
 * )
 */
class AdminPluginConfigPutRequest
{

    /**
     * @OA\Property(
     *     type="object",
     *     example={"config1":"config1_value","config2":"config2_value"},
     *     description=" 配置"
     * )
     */
    public $config;



}
