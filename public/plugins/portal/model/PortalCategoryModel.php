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
namespace plugins\portal\model;

use app\admin\model\RouteModel;
use think\Db;
use think\db\Query;
use think\Model;
use tree\Tree;

class PortalCategoryModel extends Model
{

    protected $type = [
        'more' => 'array',
    ];

    /**
     * 生成分类 select树形结构
     * @param int $selectId 需要选中的分类 id
     * @param int $currentCid 需要隐藏的分类 id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adminCategoryTree($selectId = 0, $currentCid = 0)
    {
        $categories = $this->order("list_order ASC")
            ->where('delete_time', 0)
            ->where(function (Query $query) use ($currentCid) {
                if (!empty($currentCid)) {
                    $query->where('id', 'neq', $currentCid);
                }
            })
            ->select()->toArray();

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        $newCategories = [];
        foreach ($categories as $item) {
            $item['selected'] = $selectId == $item['id'] ? "selected" : "";

            array_push($newCategories, $item);
        }

        $tree->init($newCategories);
        $str     = '<option value=\"{$id}\" {$selected}>{$spacer}{$name}</option>';
        $treeStr = $tree->getTree(0, $str);

        return $treeStr;
    }

    /**
     * 分类树形结构
     * @param int $currentIds
     * @param string $tpl
     * @param int $roleId
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adminAuthorizeCategoryTableTree($currentIds = 0, $tpl = '', $roleId = 0)
    {
//        if (!empty($currentCid)) {
//            $where['id'] = ['neq', $currentCid];
//        }
        $categories = $this->order("list_order ASC")->where('delete_time', 0)->select()->toArray();

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        $newCategories = [];

        foreach ($categories as $m) {
            $newCategories[$m['id']] = $m;
        }

        foreach ($categories as $item) {
            $ruleName                                 = "portal/Category/index?id={$item['id']}";
            $findRule                                 = Db::name("auth_access")->where(["role_id" => $roleId, "rule_name" => $ruleName, 'type' => 'portal_category'])->find();
            $itemId                                   = $item['id'];
            $newCategories[$itemId]['parent_id_node'] = ($item['parent_id']) ? ' class="child-of-node-' . $item['parent_id'] . '"' : '';
            $newCategories[$itemId]['style']          = empty($item['parent_id']) ? '' : 'display:none;';
            $newCategories[$itemId]['status_text']    = empty($item['status']) ? '<span class="label label-warning">隐藏</span>' : '<span class="label label-success">显示</span>';
            $newCategories[$itemId]['checked']        = empty($findRule) ? '' : 'checked';
            $newCategories[$itemId]['level']          = $this->_getLevel($item['id'], $newCategories);
            $newCategories[$itemId]['url']            = cmf_url('portal/List/index', ['id' => $item['id']]);
        }

        $tree->init($newCategories);

        if (empty($tpl)) {
            $tpl = " <tr id='node-\$id' \$parent_id_node style='\$style' data-parent_id='\$parent_id' data-id='\$id'>
                        <td style='padding-left:20px;'><input type='checkbox' class='js-check' data-yid='js-check-y' data-xid='js-check-x' name='ids[]' value='\$id' data-parent_id='\$parent_id' data-id='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'></td>
                        <td>\$id</td>
                        <td>\$spacer <a href='\$url' target='_blank'>\$name</a></td>
                        <td>\$description</td>
                        <td>\$status_text</td>
                    </tr>";
        }
        $treeStr = $tree->getTree(0, $tpl);

        return $treeStr;
    }

    /**
     * 添加文章分类
     * @param $data
     * @return bool
     */
    public function addCategory($data)
    {
        $result = true;
        self::startTrans();
        try {
            if (!empty($data['more']['thumbnail'])) {
                $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
            }
            $this->allowField(true)->save($data);
            $id = $this->id;
            if (empty($data['parent_id'])) {

                $this->where('id', $id)->update(['path' => '0-' . $id]);
            } else {
                $parentPath = $this->where('id', intval($data['parent_id']))->value('path');
                $this->where('id', $id)->update(['path' => "$parentPath-$id"]);

            }
            self::commit();
        } catch (\Exception $e) {
            self::rollback();
            $result = false;
        }

        if ($result != false) {
            //设置别名
            $routeModel = new RouteModel();
            if (!empty($data['alias']) && !empty($id)) {
                $routeModel->setRoute($data['alias'], 'portal/List/index', ['id' => $id], 2, 5000);
                $routeModel->setRoute($data['alias'] . '/:id', 'portal/Article/index', ['cid' => $id], 2, 4999);
            }
            $routeModel->getRoutes(true);
        }

        return $result;
    }

    public function editCategory($data)
    {
        $result = true;

        $id          = intval($data['id']);
        $parentId    = intval($data['parent_id']);
        $oldCategory = $this->where('id', $id)->find();

        if (empty($parentId)) {
            $newPath = '0-' . $id;
        } else {
            $parentPath = $this->where('id', intval($data['parent_id']))->value('path');
            if ($parentPath === false) {
                $newPath = false;
            } else {
                $newPath = "$parentPath-$id";
            }
        }

        if (empty($oldCategory) || empty($newPath)) {
            $result = false;
        } else {

            $data['path'] = $newPath;
            if (!empty($data['more']['thumbnail'])) {
                $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
            }
            $this->isUpdate(true)->allowField(true)->save($data, ['id' => $id]);

            $children = $this->field('id,path')->where('path', 'like', $oldCategory['path'] . "-%")->select();
            if (!$children->isEmpty()) {
                foreach ($children as $child) {
                    $childPath = str_replace($oldCategory['path'] . '-', $newPath . '-', $child['path']);
                    $this->where('id', $child['id'])->update(['path' => $childPath], ['id' => $child['id']]);
                }
            }

            $routeModel = new RouteModel();
            if (!empty($data['alias'])) {
                $routeModel->setRoute($data['alias'], 'portal/List/index', ['id' => $data['id']], 2, 5000);
                $routeModel->setRoute($data['alias'] . '/:id', 'portal/Article/index', ['cid' => $data['id']], 2, 4999);
            } else {
                $routeModel->deleteRoute('portal/List/index', ['id' => $data['id']]);
                $routeModel->deleteRoute('portal/Article/index', ['cid' => $data['id']]);
            }

            $routeModel->getRoutes(true);
        }


        return $result;
    }

    /**
     * 获取菜单深度
     * @param       $id
     * @param array $array
     * @param int $i
     * @return int
     */
    protected function _getLevel($id, $array = [], $i = 0)
    {
        if ($array[$id]['parent_id'] == 0 || empty($array[$array[$id]['parent_id']]) || $array[$id]['parent_id'] == $id) {
            return $i;
        } else {
            $i++;
            return $this->_getLevel($array[$id]['parent_id'], $array, $i);
        }
    }


}