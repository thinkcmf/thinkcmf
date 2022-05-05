<?php

namespace api;

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
 *
 * @OA\Server(
 *     url="/api"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="ApiToken-XX-Token",
 *     type="apiKey",
 *     in="header",
 *     name="XX-Token"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="ApiToken-Authorization",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="ApiToken-AuthorizationBearer",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\Parameter(
 *     parameter="DeviceTypeParameter",
 *     in="header",
 *     name="XX-Device-Type",
 *     required=true,
 *     description="设备类型：mobile,android,iphone,ipad,web,pc,mac,wxapp,ios",
 *     @OA\Schema(
 *         type="string",
 *         default="web"
 *     )
 * )
 * @OA\Response(
 *     response=200,
 *     description="HTTP 200 响应",
 *     @OA\JsonContent(
 *         ref="#/components/schemas/SuccessResponse"
 *     )
 * ),
 */
class ApiInfo
{

}
