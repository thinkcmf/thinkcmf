<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"name"}
 * )
 */
class AdminSlideSaveRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="这是第一张幻灯片",
     *     description="幻灯片名称"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     type="string",
     *     example="这是第一张幻灯片的描述",
     *     description="描述"
     * )
     * @var string
     */
    public $description;


}
