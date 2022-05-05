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
 */
class ApiInfo
{

}
