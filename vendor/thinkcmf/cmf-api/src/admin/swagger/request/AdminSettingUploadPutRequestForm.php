<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={}
 * )
 */
class AdminSettingUploadPutRequestForm
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
     *     property="file_types[image][upload_max_filesize]",
     *     type="integer",
     *     example="10240",
     *     description="图片文件允许上传大小",
     * )
     */
    public $file_types_imamge_upload_max_filesize;

    /**
     * @OA\Property(
     *     property="file_types[image][extensions]",
     *     type="string",
     *     example="jpg,jpeg,png,gif,bmp4",
     *     description="图片文件允许上传格式扩展名列表,以英文逗号分隔",
     * )
     */
    public $file_types_imamge_extensions;


    /**
     * @OA\Property(
     *     property="file_types[video][upload_max_filesize]",
     *     type="integer",
     *     example="10240",
     *     description="视频文件允许上传大小",
     * )
     */
    public $file_types_video_upload_max_filesize;

    /**
     * @OA\Property(
     *     property="file_types[video][extensions]",
     *     type="string",
     *     example="mp4,avi,wmv,rm,rmvb,mkv",
     *     description="视频文件允许上传格式扩展名列表,以英文逗号分隔",
     * )
     */
    public $file_types_video_extensions;

    /**
     * @OA\Property(
     *     property="file_types[audio][upload_max_filesize]",
     *     type="integer",
     *     example="10240",
     *     description="音频文件允许上传大小",
     * )
     */
    public $file_types_audio_upload_max_filesize;

    /**
     * @OA\Property(
     *     property="file_types[audio][extensions]",
     *     type="string",
     *     example="mp3,wma,wav",
     *     description="音频文件允许上传格式扩展名列表,以英文逗号分隔",
     * )
     */
    public $file_types_audio_extensions;

    /**
     * @OA\Property(
     *     property="file_types[file][upload_max_filesize]",
     *     type="integer",
     *     example="10240",
     *     description="附件允许上传大小",
     * )
     */
    public $file_types_file_upload_max_filesize;

    /**
     * @OA\Property(
     *     property="file_types[file][extensions]",
     *     type="string",
     *     example="txt,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar",
     *     description="附件允许上传格式扩展名列表,以英文逗号分隔",
     * )
     */
    public $file_types_file_extensions;





}
