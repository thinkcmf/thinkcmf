<?php

namespace api\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={}
 * )
 */
class ListOrdersRequestForm
{

    /**
     * @OA\Property(
     *     property="list_orders[1]",
     *     type="number",
     *     example="10000",
     *     description="ID为1的排序数据"
     * )
     *
     */
    public $listOrders1;

    /**
     * @OA\Property(
     *     property="list_orders[2]",
     *     type="number",
     *     example="10000",
     *     description="ID为2的排序数据"
     * )
     *
     */
    public $listOrders2;

    /**
     * @OA\Property(
     *     property="list_orders[3]",
     *     type="number",
     *     example="10000",
     *     description="ID为2的排序数据"
     * )
     *
     */
    public $listOrders3;


    /**
     * @OA\Property(
     *     property="list_orders[4]",
     *     type="number",
     *     example="10000",
     *     description="ID为2的排序数据"
     * )
     *
     */
    public $listOrders4;


    /**
     * @OA\Property(
     *     property="list_orders[5]",
     *     type="number",
     *     example="10000",
     *     description="ID为5的排序数据"
     * )
     *
     */
    public $listOrders5;

    /**
     * @OA\Property(
     *     property="list_orders[6]",
     *     type="number",
     *     example="10000",
     *     description="ID为5的排序数据"
     * )
     *
     */
    public $listOrders6;


    /**
     * @OA\Property(
     *     property="list_orders[7]",
     *     type="number",
     *     example="10000",
     *     description="ID为5的排序数据"
     * )
     *
     */
    public $listOrders7;

    /**
     * @OA\Property(
     *     property="list_orders[8]",
     *     type="number",
     *     example="10000",
     *     description="ID为5的排序数据"
     * )
     *
     */
    public $listOrders8;

    /**
     * @OA\Property(
     *     property="list_orders[9]",
     *     type="number",
     *     example="10000",
     *     description="ID为5的排序数据"
     * )
     *
     */
    public $listOrders9;

    /**
     * @OA\Property(
     *     property="list_orders[10]",
     *     type="number",
     *     example="10000",
     *     description="ID为10的排序数据"
     * )
     *
     */
    public $listOrders10;


}