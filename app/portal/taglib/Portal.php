<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\taglib;

use think\template\TagLib;

class Portal extends TagLib
{
    /**
     * 定义标签列表
     */
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'articles'         => ['attr' => 'field,where,limit,order,page,relation,returnVarName,pageVarName,categoryIds', 'close' => 1],//非必须属性item
        'tagarticles'      => ['attr' => 'field,where,limit,order,page,relation,returnVarName,pageVarName,tagId', 'close' => 1],//非必须属性item
        'breadcrumb'       => ['attr' => 'cid', 'close' => 1],//非必须属性self
        'categories'       => ['attr' => 'where,order', 'close' => 1],//非必须属性item
        'subcategories'    => ['attr' => 'categoryId', 'close' => 1],//非必须属性item
        'allsubcategories' => ['attr' => 'categoryId', 'close' => 1],//非必须属性item
    ];

    /**
     * 文章列表标签
     */
    public function tagArticles($tag, $content)
    {
        $item          = empty($tag['item']) ? 'vo' : $tag['item'];//循环变量名
        $order         = empty($tag['order']) ? 'post.published_time DESC' : $tag['order'];
        $relation      = empty($tag['relation']) ? '' : $tag['relation'];
        $pageVarName   = empty($tag['pageVarName']) ? '__PAGE_VAR_NAME__' : $tag['pageVarName'];
        $returnVarName = empty($tag['returnVarName']) ? 'articles_data' : $tag['returnVarName'];

        $field = "''";
        if (!empty($tag['field'])) {
            if (strpos($tag['field'], '$') === 0) {
                $field = $tag['field'];
                $this->autoBuildVar($field);
            } else {
                $field = "'{$tag['field']}'";
            }
        }

        $where = '""';
        if (!empty($tag['where']) && strpos($tag['where'], '$') === 0) {
            $where = $tag['where'];
        }

        $limit = "''";
        if (!empty($tag['limit'])) {
            if (strpos($tag['limit'], '$') === 0) {
                $limit = $tag['limit'];
                $this->autoBuildVar($limit);
            } else {
                $limit = "'{$tag['limit']}'";
            }
        }

        $page = "''";
        if (!empty($tag['page'])) {
            if (strpos($tag['page'], '$') === 0) {
                $page = $tag['page'];
            } else {
                $page = intval($tag['page']);
                $page = "'{$page}'";
            }
        }

        $categoryIds = "''";
        if (!empty($tag['categoryIds'])) {
            if (strpos($tag['categoryIds'], '$') === 0) {
                $categoryIds = $tag['categoryIds'];
                $this->autoBuildVar($categoryIds);
            } else {
                $categoryIds = "'{$tag['categoryIds']}'";
            }
        }

        if (strpos($tag['order'], '$') === 0) {
            $order = $tag['order'];
            $this->autoBuildVar($order);
        } else {
            $order = "'{$order}'";
        }

        $parse = <<<parse
<?php
\${$returnVarName} = \app\portal\service\ApiService::articles([
    'field'   => {$field},
    'where'   => {$where},
    'limit'   => {$limit},
    'order'   => {$order},
    'page'    => {$page},
    'relation'=> '{$relation}',
    'category_ids'=>{$categoryIds}
]);

\${$pageVarName} = isset(\${$returnVarName}['page'])?\${$returnVarName}['page']:'';

 ?>
<volist name="{$returnVarName}.articles" id="{$item}">
{$content}
</volist>
parse;
        return $parse;
    }

    /**
     * 标签文章列表标签
     */
    public function tagTagArticles($tag, $content)
    {
        $item          = empty($tag['item']) ? 'vo' : $tag['item'];//循环变量名
        $order         = empty($tag['order']) ? 'post.published_time DESC' : $tag['order'];
        $relation      = empty($tag['relation']) ? '' : $tag['relation'];
        $pageVarName   = empty($tag['pageVarName']) ? '__PAGE_VAR_NAME__' : $tag['pageVarName'];
        $returnVarName = empty($tag['returnVarName']) ? 'tag_articles_data' : $tag['returnVarName'];

        $field = "''";
        if (!empty($tag['field'])) {
            if (strpos($tag['field'], '$') === 0) {
                $field = $tag['field'];
                $this->autoBuildVar($field);
            } else {
                $field = "'{$tag['field']}'";
            }
        }

        $where = '""';
        if (!empty($tag['where']) && strpos($tag['where'], '$') === 0) {
            $where = $tag['where'];
        }

        $limit = "''";
        if (!empty($tag['limit'])) {
            if (strpos($tag['limit'], '$') === 0) {
                $limit = $tag['limit'];
                $this->autoBuildVar($limit);
            } else {
                $limit = "'{$tag['limit']}'";
            }
        }

        $page = "''";
        if (!empty($tag['page'])) {
            if (strpos($tag['page'], '$') === 0) {
                $page = $tag['page'];
            } else {
                $page = intval($tag['page']);
                $page = "'{$page}'";
            }
        }

        $tagId = "''";
        if (!empty($tag['tagId'])) {
            if (strpos($tag['tagId'], '$') === 0) {
                $tagId = $tag['tagId'];
                $this->autoBuildVar($tagId);
            } else {
                $tagId = "'{$tag['tagId']}'";
            }
        }

        if (strpos($tag['order'], '$') === 0) {
            $order = $tag['order'];
            $this->autoBuildVar($order);
        } else {
            $order = "'{$order}'";
        }

        $parse = <<<parse
<?php
\${$returnVarName} = \app\portal\service\ApiService::tagArticles([
    'field'   => {$field},
    'where'   => {$where},
    'limit'   => {$limit},
    'order'   => {$order},
    'page'    => {$page},
    'relation'=> '{$relation}',
    'tag_id'=>{$tagId}
]);

\${$pageVarName} = isset(\${$returnVarName}['page'])?\${$returnVarName}['page']:'';

 ?>
<volist name="{$returnVarName}.articles" id="{$item}">
{$content}
</volist>
parse;
        return $parse;
    }

    /**
     * 面包屑标签
     */
    public function tagBreadcrumb($tag, $content)
    {
        $cid = empty($tag['cid']) ? '0' : $tag['cid'];

        if (!empty($cid)) {
            $this->autoBuildVar($cid);
        }

        $self = isset($tag['self']) ? $tag['self'] : 'false';

        $parse = <<<parse
<?php
if(!empty({$cid})){
    \$__BREADCRUMB_ITEMS__ = \app\portal\service\ApiService::breadcrumb({$cid},{$self});
?>

<volist name="__BREADCRUMB_ITEMS__" id="vo">
    {$content}
</volist>

<?php
}
?>
parse;

        return $parse;

    }

    /**
     * 文章分类标签
     */
    public function tagCategories($tag, $content)
    {
        $item          = empty($tag['item']) ? 'vo' : $tag['item'];//循环变量名
        $order         = empty($tag['order']) ? '' : $tag['order'];
        $returnVarName = 'portal_categories_data';
        $where         = '""';
        if (!empty($tag['where']) && strpos($tag['where'], '$') === 0) {
            $where = $tag['where'];
        }

        $parse = <<<parse
<?php
\${$returnVarName} = \app\portal\service\ApiService::categories([
    'where'   => {$where},
    'order'   => '{$order}',
]);

 ?>
<volist name="{$returnVarName}" id="{$item}">
{$content}
</volist>
parse;
        return $parse;
    }

    /**
     * 文章子分类标签
     */
    public function tagSubCategories($tag, $content)
    {
        $item          = empty($tag['item']) ? 'vo' : $tag['item'];//循环变量名
        $returnVarName = 'portal_sub_categories_data';

        $categoryId = "0";
        if (!empty($tag['categoryId'])) {
            if (strpos($tag['categoryId'], '$') === 0) {
                $categoryId = $tag['categoryId'];
                $this->autoBuildVar($categoryId);
            } else {
                $categoryId = intval($tag['categoryId']);
                $categoryId = "{$categoryId}";
            }
        }

        $parse = <<<parse
<?php
\${$returnVarName} = \app\portal\service\ApiService::subCategories({$categoryId});
 
 ?>
<volist name="{$returnVarName}" id="{$item}">
{$content}
</volist>
parse;
        return $parse;
    }

    /**
     * 文章分类所有子分类标签
     */
    public function tagAllSubCategories($tag, $content)
    {
        $item          = empty($tag['item']) ? 'vo' : $tag['item'];//循环变量名
        $returnVarName = 'portal_all_sub_categories_data';

        $categoryId = "0";
        if (!empty($tag['categoryId'])) {
            if (strpos($tag['categoryId'], '$') === 0) {
                $categoryId = $tag['categoryId'];
                $this->autoBuildVar($categoryId);
            } else {
                $categoryId = intval($tag['categoryId']);
                $categoryId = "{$categoryId}";
            }
        }

        $parse = <<<parse
<?php
\${$returnVarName} = \app\portal\service\ApiService::allSubCategories({$categoryId});
 ?>
<volist name="{$returnVarName}" id="{$item}">
{$content}
</volist>
parse;
        return $parse;
    }

}