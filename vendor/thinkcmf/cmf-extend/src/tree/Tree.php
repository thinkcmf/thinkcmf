<?php

namespace tree;
/**
 * 通用的树型类，可以生成任何树型结构
 */
class Tree
{

    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public $arr = [];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    public  $icon = ['│', '├', '└'];
    public  $nbsp = "&nbsp;&nbsp;&nbsp;&nbsp;";
    private $str  = '';
    /**
     * @access private
     */
    public $ret = '';

    /**
     * 构造函数，初始化类
     * @param array 2维数组，例如：
     *      array(
     *      1 => array('id'=>'1','parent_id'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','parent_id'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','parent_id'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','parent_id'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','parent_id'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','parent_id'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','parent_id'=>3,'name'=>'三级栏目二')
     *      )
     * @return array
     */
    public function init($arr = [])
    {
        $this->arr = $arr;
        $this->ret = '';
        return is_array($arr);
    }

    /**
     * 得到父级数组
     * @param int
     * @return array
     */
    public function getParent($myId)
    {
        $newArr = [];
        if (!isset($this->arr[$myId]))
            return false;
        $pid = $this->arr[$myId]['parent_id'];
        $pid = $this->arr[$pid]['parent_id'];
        if (is_array($this->arr)) {
            foreach ($this->arr as $id => $a) {
                if ($a['parent_id'] == $pid)
                    $newArr[$id] = $a;
            }
        }
        return $newArr;
    }

    /**
     * 得到子级数组
     * @param int
     * @return array
     */
    public function getChild($myId)
    {
        $newArr = [];
        if (is_array($this->arr)) {
            foreach ($this->arr as $id => $a) {

                if ($a['parent_id'] == $myId) {
                    $newArr[$id] = $a;
                }
            }
        }

        return $newArr ? $newArr : false;
    }

    /**
     * 得到当前位置数组
     * @param int
     * @return array
     */
    public function getPosition($myId, &$newArr)
    {
        $a = [];
        if (!isset($this->arr[$myId]))
            return false;
        $newArr[] = $this->arr[$myId];
        $pid      = $this->arr[$myId]['parent_id'];
        if (isset($this->arr[$pid])) {
            $this->getPosition($pid, $newArr);
        }
        if (is_array($newArr)) {
            krsort($newArr);
            foreach ($newArr as $v) {
                $a[$v['id']] = $v;
            }
        }
        return $a;
    }

    /**
     * 得到树型结构
     * @param int ID，表示获得这个ID下的所有子级
     * @param string 生成树型结构的基本代码，例如："<option value=\$id \$selected>\$spacer\$name</option>"
     * @param int 被选中的ID，比如在做树型下拉框的时候需要用到
     * @return string
     */
    public function getTree($myId, $str, $sid = 0, $adds = '', $str_group = '')
    {
        $number = 1;
        //一级栏目
        $child = $this->getChild($myId);

        if (is_array($child)) {
            $total = count($child);

            foreach ($child as $key => $value) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer   = $adds ? $adds . $j : '';
                $selected = $value['id'] == $sid ? 'selected' : '';
                $id       = 0;
                $nstr     = '';
                $parentId = $value['parent_id'];

                $value['spacer']   = $spacer;
                $value['selected'] = $selected;
                $id                = $value['id'];

                if ($parentId == 0 && $str_group) {
                    $nstr = $this->parseTemplate($str_group, $value);
                } else {
                    $nstr = $this->parseTemplate($str, $value);
                }

                $this->ret .= $nstr;
                $nbsp      = $this->nbsp;
                $this->getTree($id, $str, $sid, $adds . $k . $nbsp, $str_group);
                $number++;
            }
        }
        return $this->ret;
    }

    private function parseTemplate($tmpl, $data)
    {
        $tmpl = preg_replace('/(\$[a-zA-Z_][a-zA-Z_0-9]{0,})/', '{${1}}', $tmpl);

        foreach ($data as $key => $value) {
            try {
                $tmpl = str_replace("{\$$key}", $value, $tmpl);
            } catch (\Exception $e) {

            }
        }

        return $tmpl;
    }

    /**
     * 生成树型结构数组
     * @param int myID，表示获得这个ID下的所有子级
     * @param int $maxLevel 最大获取层级,默认不限制
     * @param int $level    当前层级,只在递归调用时使用,真实使用时不传入此参数
     * @return array
     */
    public function getTreeArray($myId, $maxLevel = 0, $level = 1)
    {
        $returnArray = [];

        //一级数组
        $children = $this->getChild($myId);

        if (is_array($children)) {
            foreach ($children as $child) {
                $child['_level']           = $level;
                $returnArray[$child['id']] = $child;
                if ($maxLevel === 0 || ($maxLevel !== 0 && $maxLevel > $level)) {

                    $mLevel                                = $level + 1;
                    $returnArray[$child['id']]["children"] = $this->getTreeArray($child['id'], $maxLevel, $mLevel);
                }

            }
        }

        return $returnArray;
    }

    public function getTreeList($idField = 'id', $parentIdField = 'parent_id')
    {
        $childrenField = 'children';
        $tree          = [];
        $list          = array_column($this->arr, null, $idField);
        $oldList       = $list;
        foreach ($list as $v) {
            $parentId                   = $v[$parentIdField];
            $id                         = $v[$idField];
            $list[$v[$idField]]['path'] = "$parentId-$id";

            if (isset($list[$v[$parentIdField]])) {
                $list[$v[$parentIdField]][$childrenField][] = &$list[$v[$idField]];
                $list[$v[$idField]]['path']                 = $list[$v[$parentIdField]]['path'] . "-$id";
            } else {
                $tree[] =& $list[$v[$idField]];
            }

            $list[$v[$idField]]['_level']     = count(explode('-', $list[$v[$idField]]['path'])) - 1;
            $oldList[$v[$idField]]['path']    = $list[$v[$idField]]['path'];
            $oldList[$v[$idField]]['_level']  = $list[$v[$idField]]['_level'];
            $oldList[$v[$idField]]['_spacer'] = str_repeat($this->nbsp, $list[$v[$idField]]['_level'] - 1);
        }

        $array = [];
        foreach ($tree as $node) {
            $stack = [];
            array_push($stack, $node);
            $tmpNode = [];
            while (count($stack) > 0) {
                $tmpNode = array_pop($stack);
                if (!$tmpNode) return;
                $array[] = $oldList[$tmpNode[$idField]];
                if (!empty($tmpNode['children'])) {
                    $childrenCount = count($tmpNode['children']);
                    for ($i = $childrenCount - 1; $i >= 0; $i--) {
                        if ($i == $childrenCount - 1) {
                            $oldList[$tmpNode['children'][$i][$idField]]['_is_last'] = 1;
                            $oldList[$tmpNode['children'][$i][$idField]]['_spacer']  = $oldList[$tmpNode['children'][$i][$idField]]['_spacer'] . $this->icon[2] . ' ';
                        } else {
                            $oldList[$tmpNode['children'][$i][$idField]]['_is_last'] = 0;
                            $oldList[$tmpNode['children'][$i][$idField]]['_spacer']  = $oldList[$tmpNode['children'][$i][$idField]]['_spacer'] . $this->icon[1] . ' ';
                        }
                        array_push($stack, $tmpNode['children'][$i]);
                    }
                }
            }
        }

        return $array;

    }

    public function createTree($idField = 'id', $parentIdField = 'parent_id', $childrenField = "children")
    {
        $tree = [];
        $list = array_column($this->arr, null, $idField);
        foreach ($list as $v) {
            $parentId                    = $v[$parentIdField];
            $id                          = $v[$idField];
            $list[$v[$idField]]['_path'] = "$parentId-$id";

            if (isset($list[$v[$parentIdField]])) {
                $list[$v[$parentIdField]][$childrenField][] = &$list[$v[$idField]];
                $list[$v[$idField]]['_path']                = $list[$v[$parentIdField]]['_path'] . "-$id";
            } else {
                $tree[] =& $list[$v[$idField]];
            }

            $list[$v[$idField]]['_level']  = count(explode('-', $list[$v[$idField]]['_path'])) - 1;
            $list[$v[$idField]]['_spacer'] = str_repeat($this->nbsp, $list[$v[$idField]]['_level'] - 1);

        }

        return $tree;
    }

    //TODO 优化
    private function createTreeTest($list, $index = 'id', $pidField = 'parent_id', $childField = "children")
    {
        $tree = [];
        $list = array_column($list, null, $index);
        foreach ($list as $v) {
            if (isset($list[$v[$pidField]])) {
                $list[$v[$pidField]][$childField][] = &$list[$v[$index]];
            } else {
                $tree[] =& $list[$v[$index]];
            }
        }
        return $tree;
    }

    /**
     * 同上一方法类似,但允许多选
     */
    public function getTreeMulti($myId, $str, $sid = 0, $adds = '')
    {
        $number = 1;
        $child  = $this->getChild($myId);
        if (is_array($child)) {
            $total = count($child);
            foreach ($child as $id => $a) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds . $j : '';

                $selected = $this->have($sid, $id) ? 'selected' : '';

                /*
//                @extract($a);
//                eval("\$nstr = \"$str\";");
                //*/
                $id            = $a['id'];
                $a['selected'] = $selected;
                $a['spacer']   = $spacer;
                $nstr          = $this->parseTemplate($str, $a);
                //替换结束

                $this->ret .= $nstr;
                $this->getTreeMulti($id, $str, $sid, $adds . $k . '&nbsp;');
                $number++;
            }
        }
        return $this->ret;
    }

    private function have($list, $item)
    {
        return (strpos(',,' . $list . ',', ',' . $item . ','));
    }

}

