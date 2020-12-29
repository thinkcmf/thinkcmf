<?php

namespace api\swagger\reponse;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema()
 */
class ErrorResponse
{

    /**
     * @OA\Property(format="int64")
     * @var int
     */
    public $code;

    /**
     * @OA\Property()
     * @var string
     */
    public $msg;

    /**
     * @OA\Property()
     * @var object
     */
    public $data;

}
