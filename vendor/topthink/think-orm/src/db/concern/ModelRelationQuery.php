<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\db\concern;

use Closure;
use think\helper\Str;
use think\Model;
use think\model\Collection as ModelCollection;

/**
 * 模型及关联查询
 */
trait ModelRelationQuery
{

    /**
     * 当前模型对象
     * @var Model
     */
    protected $model;

    /**
     * 指定模型
     * @access public
     * @param Model $model 模型对象实例
     * @return $this
     */
    public function model(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * 获取当前的模型对象
     * @access public
     * @return Model|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * 添加查询范围
     * @access public
     * @param array|string|Closure $scope 查询范围定义
     * @param array                $args  参数
     * @return $this
     */
    public function scope($scope, ...$args)
    {
        // 查询范围的第一个参数始终是当前查询对象
        array_unshift($args, $this);

        if ($scope instanceof Closure) {
            call_user_func_array($scope, $args);
            return $this;
        }

        if (is_string($scope)) {
            $scope = explode(',', $scope);
        }

        if ($this->model) {
            // 检查模型类的查询范围方法
            foreach ($scope as $name) {
                $method = 'scope' . trim($name);

                if (method_exists($this->model, $method)) {
                    call_user_func_array([$this->model, $method], $args);
                }
            }
        }

        return $this;
    }

    /**
     * 设置关联查询
     * @access public
     * @param array $relation 关联名称
     * @return $this
     */
    public function relation(array $relation)
    {
        if (empty($this->model) || empty($relation)) {
            return $this;
        }

        return $this->filter(function ($result, $options) use ($relation) {
            $result->relationQuery($relation, $this->options['with_relatioin_attr']);
        });
    }

    /**
     * 使用搜索器条件搜索字段
     * @access public
     * @param string|array  $fields 搜索字段
     * @param mixed         $data   搜索数据
     * @param string        $prefix 字段前缀标识
     * @return $this
     */
    public function withSearch($fields, $data = [], string $prefix = '')
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        $likeFields = $this->getConfig('match_like_fields') ?: [];

        foreach ($fields as $key => $field) {
            if ($field instanceof Closure) {
                $field($this, $data[$key] ?? null, $data, $prefix);
            } elseif ($this->model) {
                // 检测搜索器
                $fieldName = is_numeric($key) ? $field : $key;
                $method    = 'search' . Str::studly($fieldName) . 'Attr';

                if (method_exists($this->model, $method)) {
                    $this->model->$method($this, $data[$field] ?? null, $data, $prefix);
                } elseif (isset($data[$field])) {
                    $this->where($fieldName, in_array($fieldName, $likeFields) ? 'like' : '=', in_array($fieldName, $likeFields) ? '%' . $data[$field] . '%' : $data[$field]);
                }
            }
        }

        return $this;
    }

    /**
     * 设置数据字段获取器
     * @access public
     * @param string|array  $name     字段名
     * @param callable      $callback 闭包获取器
     * @return $this
     */
    public function withAttr($name, callable $callback = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->withAttr($key, $val);
            }
            return $this;
        }

        $this->options['with_attr'][$name] = $callback;

        if (strpos($name, '.')) {
            [$relation, $field] = explode('.', $name);

            if (!empty($this->options['json']) && in_array($relation, $this->options['json'])) {

            } else {
                $this->options['with_relatioin_attr'][$relation][$field] = $callback;
                unset($this->options['with_attr'][$name]);
            }
        }

        if (empty($this->model)) {
            return $this->filter(function ($result) {
                return $this->getResultAttr($result, $this->options['with_attr']);
            }, 'with_attr');
        }

        return $this->filter(function ($result) {
            $result->withAttr($this->options['with_attr']);
        }, 'with_attr');
    }

    /**
     * 关联预载入 In方式
     * @access public
     * @param array|string $with 关联方法名称
     * @return $this
     */
    public function with($with)
    {
        if (empty($this->model) || empty($with)) {
            return $this;
        }

        $with = (array) $with;

        $this->options['with'] = $with;
        return $this->filter(function ($result) use ($with) {
            if (empty($this->options['is_resultSet'])) {
                $result->eagerlyResult($with, $this->options['with_relatioin_attr'], false, $this->options['with_cache'] ?? false);
            }
        }, 'with');
    }

    /**
     * 关联预载入 JOIN方式
     * @access protected
     * @param array|string $with     关联方法名
     * @param string       $joinType JOIN方式
     * @return $this
     */
    public function withJoin($with, string $joinType = '')
    {
        if (empty($this->model) || empty($with)) {
            return $this;
        }

        $with  = (array) $with;
        $first = true;

        foreach ($with as $key => $relation) {
            $closure = null;
            $field   = true;

            if ($relation instanceof Closure) {
                // 支持闭包查询过滤关联条件
                $closure  = $relation;
                $relation = $key;
            } elseif (is_array($relation)) {
                $field    = $relation;
                $relation = $key;
            } elseif (is_string($relation) && strpos($relation, '.')) {
                $relation = strstr($relation, '.', true);
            }

            $result = $this->model->eagerly($this, $relation, $field, $joinType, $closure, $first);

            if (!$result) {
                unset($with[$key]);
            } else {
                $first = false;
            }
        }

        $this->via();
        $this->options['with_join'] = $with;

        return $this->filter(function ($result) use ($with) {
            // JOIN预载入查询
            if (empty($this->options['is_resultSet'])) {
                $result->eagerlyResult($with, $this->options['with_relatioin_attr'], true, $this->options['with_cache'] ?? false);
            }
        }, 'with_join');
    }

    /**
     * 关联统计
     * @access protected
     * @param array|string $relations 关联方法名
     * @param string       $aggregate 聚合查询方法
     * @param string       $field     字段
     * @param bool         $subQuery  是否使用子查询
     * @return $this
     */
    protected function withAggregate($relations, string $aggregate = 'count', $field = '*', bool $subQuery = true)
    {
        if (empty($this->model)) {
            return $this;
        }

        if (!$subQuery) {
            $this->options['with_aggregate'][] = [$relations, $aggregate, $field];
            return $this->filter(function ($result) use ($withAggregate) {
                foreach ($this->options['with_aggregate'] as $val) {
                    $result->relationCount($this, (array) $val[0], $val[1], $val[2], false);
                }
            });
        }

        if (!isset($this->options['field'])) {
            $this->field('*');
        }

        $this->model->relationCount($this, (array) $relations, $aggregate, $field, true);
        return $this;
    }

    /**
     * 关联缓存
     * @access public
     * @param string|array|bool $relation 关联方法名
     * @param mixed             $key    缓存key
     * @param integer|\DateTime $expire 缓存有效期
     * @param string            $tag    缓存标签
     * @return $this
     */
    public function withCache($relation = true, $key = true, $expire = null, string $tag = null)
    {
        if (empty($this->model)) {
            return $this;
        }

        if (false === $relation || false === $key || !$this->getConnection()->getCache()) {
            return $this;
        }

        if ($key instanceof \DateTimeInterface || $key instanceof \DateInterval || (is_int($key) && is_null($expire))) {
            $expire = $key;
            $key    = true;
        }

        if (true === $relation || is_numeric($relation)) {
            $this->options['with_cache'] = $relation;
            return $this;
        }

        $relations = (array) $relation;
        foreach ($relations as $name => $relation) {
            if (!is_numeric($name)) {
                $this->options['with_cache'][$name] = is_array($relation) ? $relation : [$key, $relation, $tag];
            } else {
                $this->options['with_cache'][$relation] = [$key, $expire, $tag];
            }
        }

        return $this;
    }

    /**
     * 关联统计
     * @access public
     * @param string|array $relation 关联方法名
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withCount($relation, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'count', '*', $subQuery);
    }

    /**
     * 关联统计Sum
     * @access public
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withSum($relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'sum', $field, $subQuery);
    }

    /**
     * 关联统计Max
     * @access public
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withMax($relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'max', $field, $subQuery);
    }

    /**
     * 关联统计Min
     * @access public
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withMin($relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'min', $field, $subQuery);
    }

    /**
     * 关联统计Avg
     * @access public
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withAvg($relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'avg', $field, $subQuery);
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param  string  $relation 关联方法名
     * @param  mixed   $operator 比较操作符
     * @param  integer $count    个数
     * @param  string  $id       关联表的统计字段
     * @param  string  $joinType JOIN类型
     * @return $this
     */
    public function has(string $relation, string $operator = '>=', int $count = 1, string $id = '*', string $joinType = '')
    {
        return $this->model->has($relation, $operator, $count, $id, $joinType, $this);
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param  string $relation 关联方法名
     * @param  mixed  $where    查询条件（数组或者闭包）
     * @param  mixed  $fields   字段
     * @param  string $joinType JOIN类型
     * @return $this
     */
    public function hasWhere(string $relation, $where = [], string $fields = '*', string $joinType = '')
    {
        return $this->model->hasWhere($relation, $where, $fields, $joinType, $this);
    }

    /**
     * JSON字段数据转换
     * @access protected
     * @param Model $result           查询数据
     * @param array $json             JSON字段
     * @param bool  $assoc            是否转换为数组
     * @return void
     */
    protected function jsonModelResult(Model $result, array $json = [], bool $assoc = false): void
    {
        $withRelationAttr = $this->options['with_attr'];
        foreach ($json as $name) {
            if (!isset($result->$name)) {
                continue;
            }

            $jsonData = json_decode($result->getData($name), true);

            if (isset($withRelationAttr[$name])) {
                foreach ($withRelationAttr[$name] as $key => $closure) {
                    $jsonData[$key] = $closure($jsonData[$key] ?? null, $jsonData);
                }
            }

            $result->set($name, !$assoc ? (object) $jsonData : $jsonData);
        }
    }

    /**
     * 查询数据转换为模型数据集对象
     * @access protected
     * @param array $resultSet 数据集
     * @return ModelCollection
     */
    protected function resultSetToModelCollection(array $resultSet): ModelCollection
    {
        if (empty($resultSet)) {
            return $this->model->toCollection();
        }

        $this->options['is_resultSet'] = true;
        foreach ($resultSet as $key => &$result) {
            // 数据转换为模型对象
            $this->resultToModel($result);
        }

        if (!empty($this->options['with'])) {
            // 预载入
            $result->eagerlyResultSet($resultSet, $this->options['with'], $this->options['with_relatioin_attr'], false, $this->options['with_cache'] ?? false);
        }

        if (!empty($this->options['with_join'])) {
            // 预载入
            $result->eagerlyResultSet($resultSet, $this->options['with_join'], $this->options['with_relatioin_attr'], true, $this->options['with_cache'] ?? false);
        }

        // 模型数据集转换
        return $this->model->toCollection($resultSet);
    }

    /**
     * 查询数据转换为模型对象
     * @access protected
     * @param array $result           查询数据
     * @return void
     */
    protected function resultToModel(array &$result): void
    {
        $options = $this->options;

        $result = $this->model->newInstance($result, !empty($options['is_resultSet']) ? null : $this->getModelUpdateCondition($options), $options);

        // 模型数据处理
        foreach ($this->options['filter'] as $filter) {
            call_user_func_array($filter, [$result, $options]);
        }

        // 刷新原始数据
        $result->refreshOrigin();
    }

    /**
     * 查询软删除数据
     * @access public
     * @return Query
     */
    public function withTrashed()
    {
        return $this->model ? $this->model->queryWithTrashed() : $this;
    }

    /**
     * 只查询软删除数据
     * @access public
     * @return Query
     */
    public function onlyTrashed()
    {
        return $this->model ? $this->model->queryOnlyTrashed() : $this;
    }
}
