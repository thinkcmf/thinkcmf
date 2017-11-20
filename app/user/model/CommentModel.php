<?php

namespace app\user\model;

use think\Model;

class CommentModel extends Model
{
    /**
     * 关联 user表
     * @return $this
     */
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id')->setEagerlyType(1);
    }


    /**
     * content 自动转化
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    /**
     * content 自动转化
     * @param $value
     * @return string
     */
    public function setContentAttr($value)
    {

        $config = \HTMLPurifier_Config::createDefault();
        if (!file_exists(RUNTIME_PATH . 'HTMLPurifier_DefinitionCache_Serializer')) {
            mkdir(RUNTIME_PATH . 'HTMLPurifier_DefinitionCache_Serializer');
        }

        $config->set('Cache.SerializerPath', RUNTIME_PATH . 'HTMLPurifier_DefinitionCache_Serializer');
        $purifier  = new \HTMLPurifier($config);
        $cleanHtml = $purifier->purify(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
        return htmlspecialchars($cleanHtml);
    }

}

