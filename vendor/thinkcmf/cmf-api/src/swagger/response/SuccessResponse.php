<?php

namespace api\swagger\response;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema()
 */
class SuccessResponse
{

    /**
     * @OA\Property(format="int64",example="1")
     * @var int
     */
    public $code;

    /**
     * @OA\Property(example="success")
     * @var string
     */
    public $msg;

    /**
     * @OA\Property()
     * @var object
     */
    public $data;

}
