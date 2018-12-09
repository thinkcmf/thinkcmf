<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------
namespace api\common\model;

use think\Model;
use think\Loader;

class CommonModel extends Model
{
    //  关联模型过滤
    protected $relationFilter = [];

    /**
     * 内容查询
     * @access public
     * @param array $params 过滤参数
     * @return array|false|\PDOStatement|string|\think\Collection|Model  查询结果
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDatas($params = [])
    {
        if (empty($params)) {
            return $this->select();
        }

        $this->setCondition($params);
        if (!empty($params['id'])) {
            $datas = $this->find();
        } else {
            $datas = $this->select();
        }

        if (!empty($params['relation'])) {
            $allowedRelations = $this->allowedRelations($params['relation']);
            if (!empty($allowedRelations)) {
                if (!empty($params['id'])) {
                    if (!empty($datas)) {
                        $datas->append($allowedRelations);
                    }
                } else {
                    if (count($datas) > 0) {
                        $datas->load($allowedRelations);
                        $datas->append($allowedRelations);
                    }
                }
            }
        }

        return $datas;
    }

    /**
     * @access public
     * @param array $params 过滤参数
     * @return $this
     */
    public function setCondition($params)
    {
        if (empty($params)) {
            return $this;
        }
        if (!empty($params['relation'])) {
            $allowedRelations = $this->allowedRelations($params['relation']);
            if (!empty($allowedRelations)) {
                if (!empty($params['id']) && count($allowedRelations) == 1) {
                    $this->paramsFilter($params);
                } else {
                    $this->paramsFilter($params);//->with($allowedRelations);
                }
            }
        } else {
            $this->paramsFilter($params);
        }
        return $this;
    }

    /**
     * @access public
     * @param array $params 过滤参数
     * @param model $model 关联模型
     * @return model|array  $this|链式查询条件数组
     */
    public function paramsFilter($params, $model = null)
    {
        if (!empty($model)) {
            $_this = $model;
        } else {
            $_this = $this;
        }

        if (isset($_this->visible)) {
            $whiteParams = $_this->visible;
        }

        // 设置field字段过滤
        if (!empty($params['field'])) {
            $filterParams = $this->strToArr($params['field']);
            if (!empty($whiteParams)) {
                $mixedField = array_intersect($filterParams, $whiteParams);
            } else {
                $mixedField = $filterParams;
            }

            if (!empty($mixedField)) {
                $_this->field($mixedField);
            }
        }

        // 设置id，ids
        if (!empty($params['ids'])) {
            $ids = $this->strToArr($params['ids']);
            foreach ($ids as $key => $value) {
                $ids[$key] = intval($value);
            }
        }

        if (!empty($params['where']) && !is_string($params['where'])) {
            if (empty($model)) {
                $_this->where($params['where']);
            }
        }

        if (!empty($params['id'])) {
            $id = intval($params['id']);
            if (!empty($id)) {
                return $_this->where('id', $id);
            }
        } elseif (!empty($ids)) {
            $_this->where('id', 'in', $ids);
        }

        // 设置分页
        if (!empty($params['page'])) {
            $pageArr = $this->strToArr($params['page']);
            $page    = [];
            foreach ($pageArr as $value) {
                $page[] = intval($value);
            }
            if (count($page) == 1) {
                $_this->page($page[0]);
            } elseif (count($page) == 2) {
                $_this->page($page[0], $page[1]);
            }
        } elseif (!empty($params['limit'])) { // 设置limit查询
            $limitArr = $this->strToArr($params['limit']);
            $limit    = [];
            foreach ($limitArr as $value) {
                $limit[] = intval($value);
            }
            if (count($limit) == 1) {
                $_this->limit($limit[0]);
            } elseif (count($limit) == 2) {
                $_this->limit($limit[0], $limit[1]);
            }
        } else {
            $_this->limit(10);
        }

        //设置排序
        if (!empty($params['order'])) {
            $order = $this->strToArr($params['order']);
            foreach ($order as $key => $value) {
                $upDwn      = substr($value, 0, 1);
                $orderType  = $upDwn == '-' ? 'desc' : 'asc';
                $orderField = substr($value, 1);
                if (!empty($whiteParams)) {
                    if (in_array($orderField, $whiteParams)) {
                        $orderWhere[$orderField] = $orderType;
                    }
                } else {
                    $orderWhere[$orderField] = $orderType;
                }
            }

            if (!empty($orderWhere)) {
                $_this->order($orderWhere);
            }
        }

        return $_this;
    }

    /**
     * 设置链式查询
     * @access public
     * @param array $params 链式查询条件
     * @param model $model 模型
     * @return $this
     */
    public function setParamsQuery($params, $model = null)
    {
        if (!empty($model)) {
            $_this = $model;
        } else {
            $_this = $this;
        }
        $_this->alias('articles');
        if (!empty($params['field'])) {
            $_this->field($params['field']);
        }
        if (!empty($params['ids'])) {
            $_this->where('articles.id', $params['ids'][1], $params['ids'][2]);
        }
        if (!empty($params['limit'])) {
            $_this->limit($params['limit']);
        }
        if (!empty($params['page'])) {
            $_this->page($params['page']);
        }
        if (!empty($params['order'])) {
            $_this->order($params['order']);
        }
        return $_this;
    }

    public function allowedRelations($relations)
    {
        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        if (!is_array($relations)) {
            return false;
        }

        return array_intersect($this->relationFilter, $relations);
    }

    /**
     * 是否允许关联
     * @access public
     * @param string $relationName 模型关联方法名
     * @return boolean
     */
    public function isWhite($relationName)
    {
        if (!is_string($relationName)) {
            return false;
        }
        $name = Loader::parseName($relationName, 1, false);
        if (in_array($name, $this->relationFilter)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 懒人函数
     * @access public
     * @param string $value 字符串
     * @return array
     */
    public function strToArr($string)
    {
        return is_string($string) ? explode(',', $string) : $string;
    }
}