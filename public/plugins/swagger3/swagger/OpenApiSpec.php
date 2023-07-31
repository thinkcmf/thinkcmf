<?php

namespace plugins\swagger3\swagger;

use OpenApi\Annotations as OA;
/**
 * @OA\Info(
 *     title="ThinkCMF API",
 *     version="1.1.0",
 *     description="ThinkCMF API doc",
 *     @OA\Contact(
 *         email="catman@thinkcmf.com"
 *     ),
 * )
 * @OA\Server(
 *     url="/api"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="ApiToken-XX-Device-Type",
 *     type="apiKey",
 *     in="header",
 *     name="XX-Device-Type",
 *     description="设备类型：mobile,android,iphone,ipad,web,pc,mac,wxapp,ios",
 * )
 * @OA\SecurityScheme(
 *     securityScheme="ApiToken-XX-Api-Version",
 *     type="apiKey",
 *     in="header",
 *     name="XX-Api-Version",
 *     description="API版本号,默认1.1.0",
 * )
 * @OA\SecurityScheme(
 *     securityScheme="ApiToken-Authorization",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization"
 * )
 * @OA\OpenApi(
 *     security={
 *      {"ApiToken-Authorization":{}},
 *      {"ApiToken-XX-Device-Type":{}},
 *     }
 * )
 * @OA\Parameter(
 *     parameter="PageParameter",
 *     in="query",
 *     name="page",
 *     required=false,
 *     description="分页参数,格式如:1(第1页,每页条数用默认值);2,10(第2页,每页10条)",
 *     @OA\Schema(
 *         type="string",
 *         default="1"
 *     )
 * )
 * @OA\Response(
 *     response=200,
 *     description="success",
 *     @OA\JsonContent(
 *         ref="#/components/schemas/SuccessResponse"
 *     )
 * )
 * @OA\Response(
 *     response=0,
 *     description="error",
 *     @OA\JsonContent(
 *         ref="#/components/schemas/ErrorResponse"
 *     )
 * )
 */
class OpenApiSpec
{
    public static function test()
    {
        echo "test";
    }
}
