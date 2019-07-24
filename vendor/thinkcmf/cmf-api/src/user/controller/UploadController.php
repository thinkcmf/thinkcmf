<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace api\user\controller;

use cmf\controller\RestUserBaseController;
use cmf\lib\Upload;

class UploadController extends RestUserBaseController
{
    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function one()
    {
        if ($this->request->isPost()) {
            $uploader = new Upload();

            $result = $uploader->upload();

            if ($result === false) {
                $this->error($uploader->getError());
            } else {
                $result['preview_url'] = cmf_get_image_preview_url($result["filepath"]);
                $result['url']         = cmf_get_image_url($result["filepath"]);
                $result['filename']    = $result["name"];
                $this->success('上传成功!', $result);
            }
        }
    }
}
