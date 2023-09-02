<?php

namespace api\user\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={}
 * )
 */
class UserAdminUserActionSaveRequest
{

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     description="奖励积分"
     * )
     */
    public $score;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     description="奖励金币"
     * )
     */
    public $coin;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     description="奖励周期数量"
     * )
     */
    public $cycle_time;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     enum={0,1,2,3},
     *     description="奖励周期类型;0:不限;1:天;2:小时;3:永久"
     * )
     */
    public $cycle_type;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="1",
     *     description="奖励次数"
     * )
     */
    public $reward_number;

}
