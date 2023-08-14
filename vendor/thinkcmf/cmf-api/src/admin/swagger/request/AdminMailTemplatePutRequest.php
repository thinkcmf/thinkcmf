<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"template_key","subject","template"}
 * )
 */
class AdminMailTemplatePutRequest
{

    /**
     * @OA\Property(
     *     type="string",
     *     example="verification_code",
     *     description="模板类型键值"
     * )
     */
    public $template_key;

    /**
     * @OA\Property(
     *     type="string",
     *     example="ThinkCMF数字验证码",
     *     description="标题"
     * )
     * @var string
     */
    public $subject;

    /**
     * @OA\Property(
     *     type="string",
     *     example="",
     *     description="模板内容，请用{$code}代替数字验证码"
     * )
     * @var object
     */
    public $template;



}
