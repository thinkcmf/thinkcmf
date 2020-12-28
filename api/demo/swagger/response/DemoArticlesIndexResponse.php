<?php

namespace api\demo\swagger\response;

use api\swagger\reponse\SuccessResponse;

use OpenApi\Annotations as OA;


/**
 * @OA\Schema()
 */
class DemoArticlesIndexResponse extends SuccessResponse
{

    /**
     * @OA\Property(
     *     type="object",
     *     ref="#/components/schemas/DemoArticlesIndexResponseData"
     * )
     * @var object
     */
    public $data;

}

/**
 * @OA\Schema()
 */
class DemoArticlesIndexResponseData
{

    /**
     * @OA\Property()
     * @var int
     */
    public $total;

    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/DemoArticlesIndexResponseDataListItem")
     * )
     * @var array
     */
    public $list;

}


/**
 * @OA\Schema()
 */
class DemoArticlesIndexResponseDataListItem
{
    /**
     * @OA\Property()
     * @var string
     */
    public $name;


}
