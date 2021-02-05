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
 *     type="apiKey",
 *     in="header",
 *     securityScheme="api_key",
 *     name="Authorization"
 * )
 */
class ApiInfo
{

}
