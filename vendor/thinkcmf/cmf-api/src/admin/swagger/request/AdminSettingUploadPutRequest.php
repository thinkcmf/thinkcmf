<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={}
 * )
 */
class AdminSettingUploadPutRequest
{

    /**
     * @OA\Property(
     *     type="integer",
     *     example="20",
     *     description="最大同时上传文件数"
     * )
     */
    public $max_files;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="512",
     *     description="文件分块上传分块大小,文件上传采用分块上传,文件分块大小默认512KB,可以根据服务器最大上传限制设置此数值"
     * )
     */
    public $chunk_size;


    /**
     * @OA\Property(
     *     type="object",
     *     description="最大同时上传文件数",
     *     ref="#/components/schemas/AdminSettingUploadPutRequestFileTypes"
     * )
     */
    public $file_types;

}


/**
 * @OA\Schema(
 *     required={}
 * )
 */
class AdminSettingUploadPutRequestFileTypes
{

    /**
     * @OA\Property(
     *     type="object",
     *     ref="#/components/schemas/AdminSettingUploadPutRequestFileType"
     * )
     */
    public $image;

    /**
     * @OA\Property(
     *     type="object",
     *     ref="#/components/schemas/AdminSettingUploadPutRequestFileType"
     * )
     */
    public $video;

    /**
     * @OA\Property(
     *     type="object",
     *     ref="#/components/schemas/AdminSettingUploadPutRequestFileType"
     * )
     */
    public $audio;

    /**
     * @OA\Property(
     *     type="object",
     *     ref="#/components/schemas/AdminSettingUploadPutRequestFileType"
     * )
     */
    public $file;

}

/**
 * @OA\Schema(
 *     required={}
 * )
 */
class AdminSettingUploadPutRequestFileType
{
    /**
     * @OA\Property(
     *     type="integer",
     *     example="10240",
     *     description="允许上传大小KB,1M=1024KB"
     * )
     */
    public $upload_max_filesize;

    /**
     * @OA\Property(
     *     type="integer",
     *     example="jpg,jpeg,png,gif,bmp4,mp4,avi,wmv,rm,rmvb,mkv,txt,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar",
     *     enum={"jpg,jpeg,png,gif,bmp4","mp4,avi,wmv,rm,rmvb,mkv","mp3,wma,wav","txt,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar"},
     *     description="允许上传扩展名列表,以英文逗号分隔"
     * )
     */
    public $extensions;


}

