<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace api\user\controller;

use cmf\controller\RestUserBaseController;
use api\user\traits\UploadTrait;
use OpenApi\Annotations as OA;

class UploadController extends RestUserBaseController
{
    /**
     * 用户上传
     * @OA\Post(
     *      tags={"user"},
     *      path="/user/upload/one",
     *      @OA\Parameter(
     *         name="filetype",
     *         description="文件类型（默认图片）",
     *         in="query",
     *         example="image"
     *     ),
     *     @OA\Parameter(
     *         name="app",
     *         description="应用code",
     *         example="default",
     *         in="query"
     *     ),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  @OA\Property(
     *                      property="file",
     *                      description="文件",
     *                      type="string",
     *                      format="binary"
     *                  )
     *             )
     *         ),
     *     ),
     *      @OA\Response(
     *           response="200",
     *           @OA\JsonContent(example={"code": 1,"msg": "","data": {"filepath": "default/20250227/83f785dee2f083f18ba329da41f0e62d.png","name": "微信截图_20240716192358.png","preview_url": "http://test2.to/upload/default/20250227/83f785dee2f083f18ba329da41f0e62d.png","url": "http://test2.to/upload/default/20250227/83f785dee2f083f18ba329da41f0e62d.png","filename": "微信截图_20240716192358.png"}})
     *      ),
     *  )
     *
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function one()
    {
        $this->uploadFile();
    }

    use UploadTrait;
}
