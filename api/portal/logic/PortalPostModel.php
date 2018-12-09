<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\portal\logic;

use api\portal\model\PortalPostModel as PortalPost;
use think\Db;
class PortalPostModel extends PortalPost
{
    /**
     * 获取相关文章
     * @param int|string|array $postIds 文章id
     * @return array
     */
    public function getRelationPosts($postIds)
    {
        $posts = $this->with('articleUser')
            ->field('id,post_title,user_id,is_top,post_hits,post_like,comment_count,more')
            ->whereIn('id', $postIds)
            ->select();
        foreach ($posts as $post) {
            $post->appendRelationAttr('articleUser', 'user_nickname');
        }
        return $posts;
    }
    /**
     * 获取用户文章
     */
    public function getUserArticles($userId, $params)
    {
        $where = [
            'post_type' => 1,
            'user_id'   => $userId
        ];
        if (!empty($params)) {
            $this->paramsFilter($params);
        }
        return $this->where($where)->select();
    }

    /**
     * 会员添加文章
     * @param array $data 文章数据
     * @return $this
     */
    public function addArticle($data)
    {
    	//设置图片附件，写入字段过滤
    	$dataField  =   $this->setMoreField($data);
    	$data       =   $dataField[0];
	    array_push($dataField[1],'user_id');
	    $this->readonly = array_diff(['user_id'],$this->readonly);
        $this->allowField($dataField[1])->data($data, true)->isUpdate(false)->save();
        $categories = $this->strToArr($data['categories']);
        $this->categories()->attach($categories);
        if (!empty($data['post_keywords']) && is_string($data['post_keywords'])) {
            //加入标签
            $data['post_keywords'] = str_replace('，', ',', $data['post_keywords']);
            $keywords              = explode(',', $data['post_keywords']);
            $this->addTags($keywords, $this->id);
        }
        return $this;
    }

    /**
     * 会员文章编辑
     * @param array $data 文章数据
     * @param int $id 文章id
     * @param int $userId 文章所属用户id [可选]
     * @return boolean   成功 true 失败 false
     */
    public function editArticle($data, $id, $userId = '')
    {
        if (!empty($userId)) {
            $isBelong = $this->isuserPost($id, $userId);
            if ($isBelong === false) {
                return $isBelong;
            }
        }
	    //设置图片附件，写入字段过滤
	    $dataField             = $this->setMoreField($data);
        $data                  = $dataField[0];
        $data['id']            = $id;
        $this->allowField($dataField[1])->data($data, true)->isUpdate(true)->save();

        $categories            = $this->strToArr($data['categories']);
        $oldCategoryIds        = $this->categories()->column('category_id');
        $sameCategoryIds       = array_intersect($categories, $oldCategoryIds);
        $needDeleteCategoryIds = array_diff($oldCategoryIds, $sameCategoryIds);
        $newCategoryIds        = array_diff($categories, $sameCategoryIds);
        if (!empty($needDeleteCategoryIds)) {
            $this->categories()->detach($needDeleteCategoryIds);
        }
        if (!empty($newCategoryIds)) {
            $this->categories()->attach(array_values($newCategoryIds));
        }
        if (!isset($data['post_keywords'])) {
	        $keywords = [];
        } elseif (is_string($data['post_keywords'])) {
            //加入标签
            $data['post_keywords'] = str_replace('，', ',', $data['post_keywords']);
            $keywords              = explode(',', $data['post_keywords']);
        }
        $this->addTags($keywords, $data['id']);
        return $this;
    }

    /**
     * 根据文章关键字，增加标签
     * @param array $keywords 文章关键字数组
     * @param int $articleId 文章id
     * @return void
     */
    public function addTags($keywords, $articleId)
    {
        foreach ($keywords as $key => $value) {
            $keywords[$key] = trim($value);
        }
        $continue = true;
        $names    = $this->tags()->column('name');
        if (!empty($keywords) || !empty($names)) {
            if (!empty($names)) {
                $sameNames         = array_intersect($keywords, $names);
                $keywords          = array_diff($keywords, $sameNames);
                $shouldDeleteNames = array_diff($names, $sameNames);
                if (!empty($shouldDeleteNames)) {
                    $tagIdNames = $this->tags()
                        ->where('name', 'in', $shouldDeleteNames)
                        ->column('pivot.id', 'tag_id');
                    $tagIds     = array_keys($tagIdNames);
                    $tagPostIds = array_values($tagIdNames);
                    $tagPosts   = DB::name('portal_tag_post')->where('tag_id', 'in', $tagIds)
                        ->field('id,tag_id,post_id')
                        ->select();
                    $keepTagIds = [];
                    foreach ($tagPosts as $key => $tagPost) {
                        if ($articleId != $tagPost['post_id']) {
                            array_push($keepTagIds, $tagPost['tag_id']);
                        }
                    }
                    $keepTagIds         = array_unique($keepTagIds);
                    $shouldDeleteTagIds = array_diff($tagIds, $keepTagIds);
                    DB::name('PortalTag')->delete($shouldDeleteTagIds);
                    DB::name('PortalTagPost')->delete($tagPostIds);
                }
            } else {
                $tagIdNames = DB::name('portal_tag')->where('name', 'in', $keywords)->column('name', 'id');
                if (!empty($tagIdNames)) {
                    $tagIds = array_keys($tagIdNames);
                    $this->tags()->attach($tagIds);
                    $keywords = array_diff($keywords, array_values($tagIdNames));
                    if (empty($keywords)) {
                        $continue = false;
                    }
                }
            }
            if ($continue) {
                foreach ($keywords as $key => $value) {
                    if (!empty($value)) {
                        $this->tags()->attach(['name' => $value]);
                    }
                }
            }
        }
    }

	/**
	 * 设置缩略图，图片，附件
	 * 懒人方法
	 * @param $data 表单数据
	 */
	public function setMoreField($data)
	{
		$allowField = [
			'post_title','post_keywords','post_source',
			'post_excerpt','post_content','more',
			'published_time'
		];
		if (!empty($data['more'])) {
			$data['more'] = $this->setMoreUrl($data['more']);
		}
		if (!empty($data['thumbnail'])) {
			$data['more']['thumbnail'] = cmf_asset_relative_url($data['thumbnail']);
		}
		return [$data,$allowField];
	}

    /**
     * 获取图片附件url相对地址
     * 默认上传名字 *_names  地址 *_urls
     * @param $annex 上传附件
     * @return array
     */
    public function setMoreUrl($annex)
    {
        $more = [];
        if (!empty($annex)) {
            foreach ($annex as $key => $value) {
                $nameArr = $key . '_names';
                $urlArr  = $key . '_urls';
                if (is_string($value[$nameArr]) && is_string($value[$urlArr])) {
                    $more[$key] = [$value[$nameArr], $value[$urlArr]];
                } elseif (!empty($value[$nameArr]) && !empty($value[$urlArr])) {
                    $more[$key] = [];
                    foreach ($value[$urlArr] as $k => $url) {
                        $url = cmf_asset_relative_url($url);
                        array_push($more[$key], ['url' => $url, 'name' => $value[$nameArr][$k]]);
                    }
                }
            }
        }
        return $more;
    }

    /**
     * 删除文章
     * @param $ids  int|array   文章id
     * @param int $userId 文章所属用户id  [可选]
     * @return bool|int 删除结果  true 成功 false 失败  -1 文章不存在
     */
    public function deleteArticle($ids, $userId)
    {
        $time   = time();
        $result = false;
        $where  = [];

        if (!empty($userId)) {
            if (is_numeric($ids)) {
                $article = $this->find($ids);
                if (!empty($article)) {
                    if ($this->isUserPost($ids, $userId) || $userId == 1) {
                        $where['id'] = $ids;
                    }
                }
            } else {
                $ids      = $this->strToArr($ids);
                $articles = $this->where('id', 'in', $ids)->select();
                if (!empty($articles)) {
                    $deleteIds = $this->isUserPosts($ids, $userId);
                    if (!empty($deleteIds)) {
                        $where['id'] = ['in', $deleteIds];
                    }
                }
            }
        } else {
            if (is_numeric($ids)) {
                $article = $this->find($ids);
                if (!empty($article)) {
                    $where['id'] = $ids;
                }
            } else {
                $ids      = $this->strToArr($ids);
                $articles = $this->where('id', 'in', $ids)->select();
                if (!empty($articles)) {
                    $where['id'] = ['in', $ids];
                }
            }
        }
        if (empty($article) && empty($articles)) {
            return -1;
        }
        if (!empty($where)) {
            $result = $this->useGlobalScope(false)
                ->where($where)
                ->setField('delete_time', $time);
        }
        if ($result) {
            $data = [
                'create_time' => $time,
                'table_name'  => 'portal_post'
            ];
            if (!empty($article)) {
                $data['name'] = $article['post_title'];
                $article->recycleBin()->save($data);
            }

            if (!empty($articles)) {
                foreach ($articles as $article) {
                    $data['name'] = $article['post_title'];
                    $article->recycleBin()->save($data);
                }
            }
        }
        return $result;
    }

    /**
     * 判断文章所属用户是否为当前用户，超级管理员除外
     * @params  int $id     文章id
     * @param   int $userId 当前用户id
     * @return  boolean     是 true , 否 false
     */
    public function isUserPost($id, $userId)
    {
        $postUserId = $this->useGlobalScope(false)
            ->getFieldById($id, 'user_id');
        if ($postUserId != $userId || $userId != 1) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 过滤属于当前用户的文章，超级管理员除外
     * @params  array $ids     文章id的数组
     * @param   int $userId 当前用户id
     * @return  array     属于当前用户的文章id
     */
    public function isUserPosts($ids, $userId)
    {
        $postIds = $this->useGlobalScope(false)
            ->where('user_id', $userId)
            ->where('id', 'in', $ids)
            ->column('id');
        return array_intersect($ids, $postIds);
    }
}
