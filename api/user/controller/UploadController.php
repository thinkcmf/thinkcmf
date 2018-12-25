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
use think\Db;
use think\facade\Env;

class UploadController extends RestUserBaseController
{
    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function one()
    {
        $file = $this->request->file('file');
//        print_r($file);exit;
        // 移动到框架应用根目录/public/upload/ 目录下
        $info     = $file->validate([
            /* 'size' => 15678,*/
            'ext' => 'jpg,png,gif'
        ]);

        $fileMd5  = $info->md5();
        $fileSha1 = $info->sha1();

        $findFile = Db::name("asset")->where('file_md5', $fileMd5)->where('file_sha1', $fileSha1)->find();

        if (!empty($findFile)) {
            $this->success("上传成功!", ['url' => $findFile['file_path'], 'filename' => $findFile['filename']]);
        }
        $info = $info->move(Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'upload');
        if ($info) {
            $saveName     = $info->getSaveName();
            $originalName = $info->getInfo('name');//name,type,size
            $fileSize     = $info->getInfo('size');
            $suffix       = $info->getExtension();

            $fileKey = $fileMd5 . md5($fileSha1);

            $userId = $this->getUserId();
            Db::name('asset')->insert([
                'user_id'     => $userId,
                'file_key'    => $fileKey,
                'filename'    => $originalName,
                'file_size'   => $fileSize,
                'file_path'   => $saveName,
                'file_md5'    => $fileMd5,
                'file_sha1'   => $fileSha1,
                'create_time' => time(),
                'suffix'      => $suffix
            ]);

            $this->success("上传成功!", ['url' => $saveName, 'filename' => $originalName]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

    }


}
