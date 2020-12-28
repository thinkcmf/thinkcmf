<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\demo\controller;

use cmf\controller\RestBaseController;
use OpenApi\Annotations as OA;

/**
 * Class ArticlesController
 * @package api\demo\controller
 */
class ArticlesController extends RestBaseController
{
    /**
     * @OA\Get(
     *     tags={"demo"},
     *     path="/demo/articles",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="page param",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="success",
     *         @OA\JsonContent(
     *             example={
     *                 "id": "a3fb6",
     *                 "name": "sss"
     *             },
     *             ref="#/components/schemas/DemoArticlesIndexResponse"
     *         )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="error operation",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     * )
     */
    public function index()
    {
        $articles = [
            ['title' => 'article title1'],
            ['title' => 'article title2'],
        ];
        $this->success('请求成功!', ['articles' => $articles]);
    }

    /**
     * @OA\Post(
     *     tags={"demo"},
     *     path="/demo/articles",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Created user object",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/DemoArticlesSave")
     *         )
     *     ),
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     */
    public function save()
    {
    }

    /**
     * @OA\Get(
     *     tags={"demo"},
     *     path="/demo/articles/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="articles id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     */
    public function read($id)
    {
    }

    /**
     * @OA\Put(
     *     tags={"demo"},
     *     path="/demo/articles/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="articles id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="success",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="error operation",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     * )
     */
    public function update($id)
    {
    }

    /**
     * @OA\Delete(
     *     tags={"demo"},
     *     path="/demo/articles/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="articles id",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="success",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="error operation",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     * )
     */
    public function delete($id)
    {
    }
}
