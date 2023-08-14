<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"to", "subject", "content"},
 * )
 */
class AdminMailTestRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="xxxx@xxx.com",
     *     description="收件箱"
     * )
     */
    public $to;

    /**
     * @OA\Property(
     *     type="string",
     *     example="系统邮件测试",
     *     description="标题"
     * )
     * @var object
     */
    public $subject;

    /**
     * @OA\Property(
     *     type="string",
     *     example="这是一个测试邮件，请不要拦截",
     *     description="测试内容"
     * )
     * @var object
     */
    public $content;

}
