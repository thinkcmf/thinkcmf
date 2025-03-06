<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use ArrayAccess;
use BackedEnum;
use Closure;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\db\BaseQuery as Query;
use think\db\exception\DbException as Exception;
use think\db\exception\ModelEventException;
use think\db\Express;
use think\db\Raw;
use think\exception\ValidateException;
use think\facade\Db;
use think\helper\Str;
use think\model\Collection;
use think\model\contract\EnumTransform;
use think\model\contract\FieldTypeTransform;
use think\model\contract\Modelable;
use think\model\contract\Typeable;
use think\model\Relation;
use think\model\type\Date;
use think\model\type\DateTime;
use think\model\type\Json;
use WeakMap;

/**
 * Class Entity.
 */
abstract class Entity implements JsonSerializable, ArrayAccess, Arrayable, Jsonable, Modelable
{
    private static ?WeakMap $weakMap = null;

    /**
     * 架构函数.
     *
     * @param array|object $data 实体模型数据
     * @param Model $model 模型连接对象
     */
    public function __construct(array | object $data = [], ?Model $model = null)
    {
        // 获取实体模型参数
        $options = $this->getOptions();

        if (!self::$weakMap) {
            self::$weakMap = new WeakMap;
        }

        self::$weakMap[$this] = [
            'get'           => [],
            'data'          => [],
            'origin'        => [],
            'relation'      => [],
            'together'      => [],
            'allow'         => [],
            'with_attr'     => [],
            'force'         => false,
            'schema'        => $options['schema'] ?? [],
            'update_time'   => $options['update_time'] ?? 'update_time',
            'create_time'   => $options['create_time'] ?? 'create_time',
            'connection'    => $options['connection'] ?? null,
            'name'          => $options['name'] ?? null,
            'table'         => $options['table'] ?? null,
            'suffix'        => $options['suffix'] ?? '',
            'pk'            => $options['pk'] ?? 'id',
            'validate'      => $options['validate'] ?? $this->parseValidate(),
            'type'          => $options['type'] ?? [],
            'readonly'      => $options['readonly'] ?? [],
            'disuse'        => $options['disuse'] ?? [],
            'hidden'        => $options['hidden'] ?? [],
            'visible'       => $options['visible'] ?? [],
            'append'        => $options['append'] ?? [],
            'mapping'       => $options['mapping'] ?? [],
            'strict'        => $options['strict'] ?? true,
            'bind_attr'     => $options['bind_attr'] ?? [],
            'auto_relation' => $options['auto_relation'] ?? [],
            'relation_keys' => $options['relation_keys'] ?? [],
        ];

        // 设置模型
        self::$weakMap[$this]['model'] = $this->initModel($model, $options['model_class'] ?? str_replace('\\entity\\', '\\model\\', static::class));

        // 初始化模型
        $this->init();

        // 初始化数据
        $this->initializeData($data);
    }

    /**
     *  初始化模型.
     *
     * @return void
     */
    protected function init(): void
    {
    }

    /**
     * 创建新的模型实例.
     *
     * @param array $data
     *
     * @return static
     */
    public function newInstance(array $data)
    {
        return new static($data);
    }

    /**
     * 在实体模型中定义 返回相关配置参数.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }

    /**
     * 批量设置模型参数
     * @param array  $options  值
     * @return void
     */
    protected function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * 设置模型参数
     *
     * @param string $name  参数名
     * @param mixed  $value  值
     *
     * @return void
     */
    protected function setOption(string $name, $value)
    {
        self::$weakMap[$this][$name] = $value;
    }

    /**
     * 获取模型参数
     *
     * @param string $name  参数名
     * @param mixed  $default  默认值
     *
     * @return mixed
     */
    protected function getOption(string $name, $default = null)
    {
        return self::$weakMap[$this][$name] ?? $default;
    }

    private function setWeakData($key, $name, $value)
    {
        self::$weakMap[$this][$key][$name] = $value;
    }

    private function getWeakData($key, $name, $default = null)
    {
        return self::$weakMap[$this][$key][$name] ?? $default;
    }

    /**
     * 获取主键名.
     *
     * @return string|array
     */
    public function getPk()
    {
        if ($this->model() instanceof Model) {
            return $this->model()->getPk();
        }
        return $this->getOption('pk');
    }

    /**
     * 获取表名（不含前后缀）.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getOption('name', Str::snake(class_basename(static::class)));
    }

    /**
     * 初始化模型.
     * @param Model  $model 模型对象
     * @param string $class 对象Model类名
     *
     * @return Model|Query
     */
    private function initModel(?Model $model, string $class)
    {
        // 获取对应模型对象
        if (is_null($model)) {
            if ($this->isView() || $this->isVirtual()) {
                // 虚拟或视图模型 无需对应模型
                $model = Db::newQuery();
            } elseif ($this->isSimple()) {
                // 简单模型
                $model = $this->getSimpleModel();
            } else {
                // 检测绑定模型 不存在则自动设置为简单模型
                $model = class_exists($class) ? new $class : $this->getSimpleModel();
            }
        }

        if ($model instanceof Model) {
            $model->setEntity($this);
        } else {
            $model->model($this);
        }

        return $model;
    }

    /**
     * 简单模型自动获取对应的数据表和主键
     */
    private function getSimpleModel()
    {
        $db = Db::connect($this->getOption('connection'))
            ->newQuery()
            ->pk($this->getOption('pk'));

        return $this->getOption('table') ? $db->table($this->getOption('table'))
            : $db->name($this->getName())->suffix($this->getOption('suffix'));
    }

    /**
     * 获取数据表字段类型列表（或某个字段的类型）.
     *
     * @param string|null $field 字段名
     *
     * @return array|string
     */
    protected function getFields(?string $field = null)
    {
        $schema = $this->getOption('schema');
        if (empty($schema)) {
            if ($this->isView() || $this->isVirtual()) {
                $schema = $this->getOption('type', []);
            } else {
                // 获取数据表信息
                $model  = $this->getOption('model');
                $fields = $model->getFieldsType();
                $schema = array_merge($fields, $this->getOption('type', $model->getType()));
            }

            $this->setOption('schema', $schema);
        }

        if ($field) {
            return $schema[$field] ?? 'string';
        }

        return $schema;
    }

    /**
     * 解析模型数据.
     *
     * @param array|object $data 数据
     *
     * @return array
     */
    private function parseData(array | object $data): array
    {
        if ($data instanceof self) {
            $data = $data->getData();
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }

        return $data;
    }

    /**
     * 解析对应验证类.
     *
     * @return string
     */
    protected function parseValidate(): string
    {
        $validate = str_replace('\\entity\\', '\\validate\\', static::class);
        return class_exists($validate) ? $validate : '';
    }

    /**
     * 获取模型或数据对象实例.
     * @return Model|Query
     */
    public function model()
    {
        return $this->getOption('model')->schema($this->getOption('schema'));
    }

    /**
     * 初始化模型数据.
     *
     * @param array|object $data 实体模型数据
     * @param bool  $fromSave
     *
     * @return void
     */
    private function initializeData(array | object $data, bool $fromSave = false)
    {
        // 分析数据
        $data    = $this->parseData($data);
        $schema  = $this->getFields();
        $fields  = array_keys($schema);
        $mapping = $this->getOption('mapping');

        // 实体模型赋值
        foreach ($data as $name => $val) {
            if (in_array($name, $this->getOption('disuse'))) {
                // 废弃字段
                continue;
            }

            if (!empty($mapping)) {
                $name = array_search($name, $mapping) ?: $name;
            }

            if (str_contains($name, '__')) {
                // 组装关联JOIN查询数据
                [$relation, $attr] = explode('__', $name, 2);

                $relations[$relation][$attr] = $val;
                continue;
            }

            $trueName = $this->getRealFieldName($name);
            if ($this->isView() || $this->isVirtual() || in_array($trueName, $fields)) {
                // 读取数据后进行类型转换
                $value = $this->readTransform($val, $schema[$trueName] ?? 'string');
                // 数据赋值
                $this->setData($trueName, $value);
                // 记录原始数据
                $origin[$trueName] = $value;
            }
        }

        if (!empty($relations)) {
            // 设置关联数据
            $this->parseRelationData($relations);
        }

        if (!empty($origin) && !$fromSave) {
            $this->trigger('AfterRead');
            $this->setOption('origin', $origin);
            $this->setOption('get', []);
        }
    }

    /**
     * 设置关联JOIN数据.
     *
     * @param array $relations 关联数据
     *
     * @return void
     */
    private function parseRelationData(array $relations)
    {
        foreach ($relations as $relation => $val) {
            $relation = $this->getRealFieldName($relation);
            $type     = $this->getFields($relation);
            $bind     = $this->getBindAttr($this->getOption('bind_attr'), $relation);
            if (!empty($bind)) {
                // 绑定关联属性
                $this->bindRelationAttr($val, $bind);
            } elseif (is_subclass_of($type, Entity::class)) {
                // 明确类型直接设置关联属性
                $this->$relation = new $type($val);
            } else {
                // 寄存关联数据
                $this->setTempRelation($relation, $val);
            }
        }
    }

    /**
     * 寄存关联数据.
     *
     * @param string $relation 关联属性
     * @param array  $data  关联数据
     *
     * @return void
     */
    private function setTempRelation(string $relation, array $data)
    {
        $this->setWeakData('relation', $relation, $data);
    }

    /**
     * 获取寄存的关联数据.
     *
     * @param string $relation 关联属性
     *
     * @return array
     */
    public function getRelation(string $relation): array
    {
        return $this->getWeakData('relation', $relation, []);
    }

    /**
     * 设置数据字段获取器.
     *
     * @param array $attr     字段获取器定义
     *
     * @return $this
     */
    public function withFieldAttr(array $attr)
    {
        foreach ($attr as $name => $closure) {
            $this->withAttr($name, $closure);
        }

        return $this;
    }

    /**
     * 动态设置字段获取器.
     *
     * @param string    $name     字段名
     * @param callable  $callback 闭包获取器
     *
     * @return $this
     */
    public function withAttr(string $name, callable $callback)
    {
        $name = $this->getRealFieldName($name);

        $this->setWeakData('with_attr', $name, $callback);
        // 自动追加输出
        self::$weakMap[$this]['append'][] = $name;
        return $this;
    }

    /**
     * 获取实际字段名.
     * 严格模式下 完全和数据表字段对应一致（默认）
     * 非严格模式 统一转换为snake规范（支持驼峰规范读取）
     *
     * @param string $name  字段名
     *
     * @return mixed
     */
    protected function getRealFieldName(string $name)
    {
        if (false === $this->getOption('strict')) {
            return Str::snake($name);
        }

        return $name;
    }

    /**
     * 数据读取 类型转换.
     *
     * @param mixed        $value 值
     * @param string|arrau $type  要转换的类型
     *
     * @return mixed
     */
    protected function readTransform($value, string | array $type)
    {
        if (is_null($value)) {
            return;
        }

        if ($value instanceof Raw || $value instanceof Express) {
            return $value;
        }

        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (str_contains($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        $typeTransform = static function (string $type, $value, $model) {
            if (class_exists($type) && !($value instanceof $type)) {
                if (is_subclass_of($type, Typeable::class)) {
                    $value = $type::from($value, $model);
                } elseif (is_subclass_of($type, FieldTypeTransform::class)) {
                    $value = $type::get($value, $model);
                } elseif (is_subclass_of($type, BackedEnum::class)) {
                    $value = $type::from($value);
                } else {
                    // 对象类型
                    $value = new $type($value);
                }
            }
            return $value;
        };

        return match ($type) {
            'string' => (string) $value,
            'int'       => (int) $value,
            'float'     => empty($param) ? (float) $value : (float) number_format($value, (int) $param, '.', ''),
            'bool'      => (bool) $value,
            'array'     => empty($value) ? [] : json_decode($value, true),
            'object'    => empty($value) ? new \stdClass() : json_decode($value),
            'json'      => $typeTransform(Json::class, $value, $this),
            'date'      => $typeTransform(Date::class, $value, $this),
            'datetime'  => $typeTransform(DateTime::class, $value, $this),
            'timestamp' => $typeTransform(DateTime::class, $value, $this),
            default     => $typeTransform($type, $value, $this),
        };
    }

    /**
     * 数据写入 类型转换.
     *
     * @param mixed        $value 值
     * @param string|array $type  要转换的类型
     *
     * @return mixed
     */
    protected function writeTransform($value, string | array $type)
    {
        if (null === $value) {
            return;
        }

        if ($value instanceof Raw || $value instanceof Express) {
            return $value;
        }

        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (str_contains($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        $typeTransform = static function (string $type, $value, $model) {
            if (class_exists($type)) {
                if (is_subclass_of($type, Typeable::class)) {
                    $value = $value->value();
                } elseif (is_subclass_of($type, FieldTypeTransform::class)) {
                    $value = $type::set($value, $model);
                } elseif ($value instanceof BackedEnum) {
                    $value = $value->value;
                } elseif ($value instanceof Stringable) {
                    $value = $value->__toString();
                }
            }
            return $value;
        };

        return match ($type) {
            'string'    => (string) $value,
            'int'       => (int) $value,
            'float'     => empty($param) ? (float) $value : (float) number_format($value, (int) $param, '.', ''),
            'bool'      => (bool) $value,
            'object'    => is_object($value) ? json_encode($value, JSON_FORCE_OBJECT) : $value,
            'array'     => json_encode((array) $value, JSON_UNESCAPED_UNICODE),
            'json'      => $typeTransform(Json::class, $value, $this),
            'date'      => $typeTransform(Date::class, $value, $this),
            'datetime'  => $typeTransform(DateTime::class, $value, $this),
            'timestamp' => $typeTransform(DateTime::class, $value, $this),
            default     => $typeTransform($type, $value, $this),
        };
    }

    /**
     * 关联数据写入或删除.
     *
     * @param array $relation 关联
     *
     * @return $this
     */
    public function together(array $relation)
    {
        $this->setOption('together', $relation);

        return $this;
    }

    /**
     * 允许写入字段.
     *
     * @param array $allow 允许字段
     *
     * @return $this
     */
    public function allowField(array $allow)
    {
        $this->setOption('allow', $allow);

        return $this;
    }

    /**
     * 强制写入或删除
     *
     * @param bool $force 强制更新
     *
     * @return $this
     */
    public function force(bool $force = true)
    {
        if ($this->model() instanceof Model) {
            $this->model()->force($force);
        } else {
            $this->setOption('force', $force);
        }

        return $this;
    }

    /**
     * 新增数据是否使用Replace.
     *
     * @param bool $replace
     *
     * @return $this
     */
    public function replace(bool $replace = true)
    {
        $this->model()->replace($replace);

        return $this;
    }

    /**
     * 设置需要附加的输出属性.
     *
     * @param array $append 属性列表
     * @param bool  $merge  是否合并
     *
     * @return $this
     */
    public function append(array $append, bool $merge = false)
    {
        $this->setOption('append', $merge ? array_merge($this->getOption('append'), $append) : $append);

        return $this;
    }

    /**
     * 设置需要隐藏的输出属性.
     *
     * @param array $hidden 属性列表
     * @param bool  $merge  是否合并
     *
     * @return $this
     */
    public function hidden(array $hidden, bool $merge = false)
    {
        $this->setOption('hidden', $merge ? array_merge($this->getOption('hidden'), $hidden) : $hidden);

        return $this;
    }

    /**
     * 设置需要输出的属性.
     *
     * @param array $visible
     * @param bool  $merge   是否合并
     *
     * @return $this
     */
    public function visible(array $visible, bool $merge = false)
    {
        $this->setOption('visible', $merge ? array_merge($this->getOption('visible'), $visible) : $visible);

        return $this;
    }

    /**
     * 设置属性的映射输出.
     *
     * @param array $map
     *
     * @return $this
     */
    public function mapping(array $map)
    {
        $this->setOption('mapping', $map);

        return $this;
    }

    /**
     * 字段值增长
     *
     * @param string $field 字段名
     * @param float  $step  增长值
     * @param int    $lazyTime 延迟时间（秒）
     *
     * @return $this
     */
    public function inc(string $field, float $step = 1, int $lazyTime = 0)
    {
        return $this->set($field, new Express('+', $step, $lazyTime));
    }

    /**
     * 字段值减少.
     *
     * @param string $field 字段名
     * @param float  $step  增长值
     * @param int    $lazyTime 延迟时间（秒）
     *
     * @return $this
     */
    public function dec(string $field, float $step = 1, int $lazyTime = 0)
    {
        return $this->set($field, new Express('-', $step, $lazyTime));
    }

    /**
     * 验证模型数据.
     *
     * @param array $data 数据
     * @param array $allow 需要验证的字段
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function validate(array $data, array $allow = [])
    {
        $validater = $this->getOption('validate');
        if (!empty($validater) && class_exists('think\validate')) {
            try {
                validate($validater)
                    ->only($allow ?: array_keys($data))
                    ->check($data);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                throw new InvalidArgumentException($e->getError());
            }
        }
    }

    /**
     * 保存模型实例数据.
     *
     * @param array|object $data 数据
     * @param mixed $where 更新条件
     * @return bool
     */
    public function save(array | object $data = [], $where = []): bool
    {
        if (!empty($data)) {
            // 初始化模型数据
            $this->initializeData($data, true);
        }

        if ($this->isVirtual() || $this->isView()) {
            return true;
        }

        if (true === $where) {
            $isUpdate = false;
        } elseif (!empty($where)) {
            $isUpdate = true;
        } else {
            $isUpdate = $this->getKey() ? true : false;
        }

        if (false === $this->trigger($isUpdate ? 'BeforeUpdate' : 'BeforeInsert')) {
            return false;
        }

        $data     = $this->getData();
        $origin   = $this->getOrigin();
        $allow    = $this->getOption('allow') ?: array_keys($this->getFields());
        $readonly = $this->getOption('readonly');
        $disuse   = $this->getOption('disuse');
        $allow    = array_diff($allow, $readonly, $disuse);

        // 验证数据
        $this->validate($data, $allow);


        foreach ($data as $name => &$val) {
            if ($val instanceof Entity || is_subclass_of($this->getFields($name), Entity::class)) {
                $relations[$name] = $val;
                unset($data[$name]);
            } elseif ($val instanceof Collection || !in_array($name, $allow)) {
                // 禁止更新字段（包括只读、废弃和数据集）
                unset($data[$name]);
            } elseif ($isUpdate && !$this->isForce() && $this->isNotRequireUpdate($name, $val, $origin)) {
                // 无需更新字段
                unset($data[$name]);
            } else {
                // 统一调用修改器或自动类型转换后写入
                $val = $this->setWithAttr($name, $val, $data);
            }
        }

        if (empty($data) || false === $this->trigger('BeforeWrite')) {
            return false;
        }

        // 自动时间戳处理
        $this->autoDateTime($data, $isUpdate);

        // 自动写入数据
        $this->autoWriteData($data, $isUpdate);

        $model = $this->model();
        if ($model instanceof Model) {
            if(!empty($where)) {
                $model->setUpdateWhere($where);
            } else {
                $model->setKey($this->getKey());
            }
            $result = $model->allowField($allow)->save($data);
            if (false === $result) {
                return false;
            }
        } else {
            if(!empty($where)) {
                $model->where($where);
            } else {
                $model->setKey($this->getKey());
            }            
            $result = $model->field($allow)->save($data, !$isUpdate);
            if (!$isUpdate) {
                $this->setKey($model->getLastInsID());
            }
            $this->trigger($isUpdate ? 'AfterUpdate' : 'AfterInsert');
        }

        $this->trigger('AfterWrite');

        // 保存关联数据
        if (!empty($relations)) {
            $this->relationSave($relations);
        }

        // 重置原始数据
        $this->refreshOrigin();
        return true;
    }

    /**
     * 刷新对象原始数据（为当前数据）.
     *
     * @return $this
     */
    public function refreshOrigin()
    {
        $this->setOption('origin', $this->getData());

        return $this;
    }

    /**
     * 时间字段自动写入.
     *
     * @param array $data 数据
     * @param bool $update 是否更新
     * @return void
     */
    protected function autoDateTime(array &$data, bool $update)
    {
        $dateTimeFields = [$this->getOption('update_time')];
        if (!$update) {
            array_unshift($dateTimeFields, $this->getOption('create_time'));
        }

        foreach ($dateTimeFields as $field) {
            if (is_string($field)) {
                $data[$field] = $this->getDateTime($field);
                $this->setData($field, $this->readTransform($data[$field], $this->getFields($field)));
            }
        }
    }

    /**
     * 字段自动写入.
     *
     * @param array $data 数据
     * @param bool  $isUpdate 是否更新
     * @return void
     */
    protected function autoWriteData(array &$data, bool $isUpdate)
    {
        $auto = $this->getOption($isUpdate ? 'auto_update' : 'auto_insert', []);
        foreach ($auto as $name => $val) {
            $field = is_string($name) ? $name : $val;
            if (!isset($data[$field])) {
                if ($val instanceof Closure) {
                    $value = $val($this);
                } else {
                    $value = is_string($name) ? $val : $this->setWithAttr($field, null, $data);
                }
                $data[$field] = $value;
                $this->setData($field, $value);
            }
        }
    }

    /**
     * 获取当前时间.
     *
     * @param string $field 字段名
     * @return void
     */
    protected function getDateTime(string $field)
    {
        $type = $this->getFields($field);
        if ('int' == $type) {
            $value = time();
        } elseif (is_subclass_of($type, Typeable::class)) {
            $value = $type::from('now', $this)->value();
        } elseif (str_contains($type, '\\')) {
            // 对象数据写入
            $obj = new $type();
            if ($obj instanceof Stringable) {
                // 对象数据写入
                $value = $obj->__toString();
            }
        } else {
            $value = DateTime::from('now', $this)->value();
        }
        return $value;
    }

    /**
     * 检查字段是否有更新（主键无需更新）.
     *
     * @param string $name 字段
     * @param mixed $val 值
     * @param array $origin 原始数据
     * @return bool
     */
    protected function isNotRequireUpdate(string $name, $val, array $origin): bool
    {
        return (array_key_exists($name, $origin) && $val === $origin[$name]) || $this->getPk() == $name;
    }

    /**
     * 写入模型关联数据（一对一）.
     *
     * @param array $relations 数据
     * @return void
     */
    private function relationSave(array $relations = [])
    {
        foreach ($relations as $name => $relation) {
            if ($relation && in_array($name, $this->getOption('together'))) {
                $relationKey = $this->getRelationKey($name);
                if ($relationKey) {
                    $relation->$relationKey = $this->getKey();
                }
                $relation->save();
            }
        }
    }

    /**
     * 删除模型关联数据（一对一）.
     *
     * @param array $relations 数据
     * @return void
     */
    private function relationDelete(array $relations = [])
    {
        foreach ($relations as $name => $relation) {
            if ($relation && in_array($name, $this->getOption('together'))) {
                $relation->delete();
            }
        }
    }

    /**
     * 获取关联的外键名.
     *
     * @param string $relation 关联名
     * @return string|null
     */
    protected function getRelationKey(string $relation)
    {
        $relationKey = $this->getOption('relation_keys', []);
        return $relationKey[$relation] ?? null;
    }

    /**
     * 是否为虚拟模型（不能查询）.
     *
     * @return bool
     */
    public function isVirtual(): bool
    {
        return false;
    }

    /**
     * 是否为视图模型（不能写入 也不会绑定模型）.
     *
     * @return bool
     */
    public function isView(): bool
    {
        return false;
    }

    /**
     * 是否为简单模型（单表操作 不支持关联 不绑定模型）.
     *
     * @return bool
     */
    public function isSimple(): bool
    {
        return false;
    }

    /**
     * 删除模型数据.
     *
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->isVirtual() || $this->isView()) {
            $this->clear();
            return true;
        }

        if ($this->isEmpty() || false === $this->trigger('BeforeDelete')) {
            return false;
        }

        foreach ($this->getData() as $name => $val) {
            if ($val instanceof Entity || $val instanceof Collection) {
                $relations[$name] = $val;
            }
        }

        $result = $this->model()->setKey($this->getKey())->delete();

        $this->trigger('AfterDelete');

        if ($result && !empty($relations)) {
            // 删除关联数据
            $this->relationDelete($relations);
        }

        $this->clear();
        return true;
    }

    /**
     * 写入数据.
     *
     * @param array|object  $data 数据
     * @param array  $allowField  允许字段
     * @param bool   $replace     使用Replace
     * @return static
     */
    public static function create(array | object $data, array $allowField = [], bool $replace = false): Entity
    {
        $model = new static();

        $model->allowField($allowField)->replace($replace)->save($data, true);

        return $model;
    }

    /**
     * 更新数据.
     *
     * @param array|object  $data 数据
     * @param mixed  $where       更新条件
     * @param array  $allowField  允许字段
     * @return static
     */
    public static function update(array | object $data, $where = [], array $allowField = []): Entity
    {
        $model = new static();

        $model->allowField($allowField)->save($data, $where);

        return $model;
    }

    /**
     * 删除记录.
     *
     * @param mixed $data  主键列表 支持闭包查询条件
     * @param bool  $force 是否强制删除
     *
     * @return bool
     */
    public static function destroy($data, bool $force = false): bool
    {
        $entity = new static();
        if ($entity->isVirtual() || $entity->isView()) {
            return true;
        }

        $model = $entity->model();
        if ($model instanceof Model) {
            return $model->destroy($data, $force);
        }

        if (is_array($data) && key($data) !== 0) {
            $model->where($data);
            $data = [];
        } elseif ($data instanceof Closure) {
            $data($model);
            $data = [];
        }

        $resultSet = $model->select((array) $data);

        foreach ($resultSet as $result) {
            $result->force($force)->delete();
        }
        return true;
    }

    /**
     * 设置主键值
     *
     * @param int|string $value 值
     * @return void
     */
    public function setKey($value)
    {
        $pk = $this->getPk();

        if (is_string($pk)) {
            $this->set($pk, $value);
        }
    }

    /**
     * 获取主键值
     *
     * @return mixed
     */
    public function getKey()
    {
        $pk = $this->getPk();
        if (is_string($pk)) {
            return $this->get($pk);
        }

        foreach ($pk as $name) {
            $data[$name] = $this->get($name);
        }
        return $data;
    }

    /**
     * 获取模型实际数据.
     *
     * @param string|null $name 字段名
     * @return mixed
     */
    public function getData(?string $name = null)
    {
        if ($name) {
            $name = $this->getRealFieldName($name);
            return $this->getWeakData('data', $name);
        }
        return $this->getOption('data');
    }

    /**
     * 设置数据对象的实际值
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return void
     */
    protected function setData(string $name, $value)
    {
        $this->setWeakData('data', $name, $value);
        if ($this->getWeakData('get', $name)) {
            $this->setWeakData('get', $name, null);
        }
    }

    /**
     * 重置模型数据.
     *
     * @param array $data
     *
     * @return void
     */
    public function data(array $data)
    {
        $this->initializeData($data);
    }

    /**
     * 清空模型数据.
     *
     * @return $this
     */
    public function clear()
    {
        $this->setOption('data', []);
        $this->setOption('origin', []);
        $this->setOption('get', []);
        $this->setOption('relation', []);
        return $this;
    }

    /**
     * 获取原始数据.
     *
     * @param string|null $name 字段名
     * @return mixed
     */
    public function getOrigin(?string $name = null)
    {
        if ($name) {
            $name = $this->getRealFieldName($name);
            return $this->getWeakData('origin', $name);
        }
        return $this->getOption('origin');
    }

    /**
     * 模型数据转数组.
     *
     * @param array $allow 允许输出字段
     * @return array
     */
    public function toArray(array $allow = []): array
    {
        $data = $this->getData();
        if (empty($allow)) {
            foreach (['visible', 'hidden', 'append'] as $convert) {
                ${$convert} = $this->getOption($convert);
                foreach (${$convert} as $key => $val) {
                    if (is_string($key)) {
                        $relation[$key][$convert] = $val;
                        unset(${$convert}[$key]);
                    } elseif (str_contains($val, '.')) {
                        [$relName, $name]               = explode('.', $val);
                        $relation[$relName][$convert][] = $name;
                        unset(${$convert}[$key]);
                    }
                }
            }
            $allow = array_diff($visible ?: array_keys($data), $hidden);
        }

        $item = [];
        foreach ($data as $name => $val) {
            if ($val instanceof self || $val instanceof Collection) {
                if (!empty($relation[$name])) {
                    // 处理关联数据输出
                    foreach ($relation[$name] as $key => $val) {
                        $val->$key($val);
                    }
                }
                $item[$name] = $val->toarray();
            } elseif (empty($allow) || in_array($name, $allow)) {
                // 通过获取器输出
                $item[$name] = $this->getWithAttr($name, $val, $data);
            }

            if (isset($item[$name]) && $key = $this->getWeakData('mapping', $name)) {
                // 检查字段映射
                $item[$key] = $item[$name];
                unset($item[$name]);
            }
        }

        // 输出额外属性 必须定义获取器
        foreach ($this->getOption('append') as $key) {
            $item[$key] = $this->get($key);
        }

        return $item;
    }

    /**
     * 判断数据是否为空.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->getData());
    }

    /**
     * 判断数据是否为空.
     *
     * @return bool
     */
    public function isForce(): bool
    {
        if ($this->model() instanceof Model) {
            return $this->model()->isForce();
        }
        return $this->getOption('force', false);
    }

    /**
     * 设置数据对象的值 并进行类型自动转换
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return $this
     */
    public function set(string $name, $value)
    {
        $name = $this->getMappingName($name);
        $type = $this->getFields($name);

        if (is_null($value) && is_subclass_of($type, Entity::class)) {
            // 关联数据为空 设置一个空模型
            $value = new $type();
        } elseif (!($value instanceof self || $value instanceof Collection)) {
            // 类型自动转换
            $value = $this->readTransform($value, $type);
        }

        $this->setData($name, $value);
        return $this;
    }

    /**
     * 使用修改器或类型自动转换处理数据（写入数据前自动调用）
     *
     * @param string $name  名称
     * @param mixed  $value 值
     * @param array  $data 所有数据
     *
     * @return mixed
     */
    private function setWithAttr(string $name, $value, array $data = [])
    {
        $attr   = Str::studly($name);
        $method = 'set' . $attr . 'Attr';
        if (method_exists($this, $attr) && $set = $this->$attr()['set'] ?? '') {
            $value = $set($value, $data);
        } elseif (method_exists($this, $method)) {
            $value = $this->$method($value, $data);
        } else {
            // 类型转换
            $value = $this->writeTransform($value, $this->getFields($name));
        }

        if ($value instanceof Express) {
            // 处理运算表达式
            $step   = $value->getStep();
            $origin = $this->getOrigin($name);
            $real   = match ($value->getType()) {
                '+'         => $origin + $step,
                '-'         => $origin - $step,
                '*'         => $origin * $step,
                '/'         => $origin / $step,
                default     => $origin,
            };
            $this->setData($name, $real);
        } elseif (is_scalar($value)) {
            // 同步写入修改器或类型自动转换结果
            $this->setData($name, $value);
        }

        return $value;
    }

    /**
     * 获取数据对象的值（支持使用获取器）
     *
     * @param string $name 名称
     * @param bool   $attr 是否使用获取器
     *
     * @return mixed
     */
    public function get(string $name, bool $attr = true)
    {
        $name = $this->getMappingName($name);
        if ($attr && $value = $this->getWeakData('get', $name)) {
            // 已经输出的数据直接返回
            return $value;
        }

        if (!array_key_exists($name, $this->getOption('data'))) {
            // 动态获取关联数据
            $value = $this->getRelationData($name) ?: null;
        } else {
            $value = $this->getData($name);
        }

        if ($attr) {
            // 通过获取器输出
            $value = $this->getWithAttr($name, $value, $this->getData());
            $this->setWeakData('get', $name, $value);
        }

        return $value;
    }

    /**
     * 获取映射字段
     *
     * @param string $name 名称
     *
     * @return string
     */
    protected function getMappingName(string $name): string
    {
        $mapping = $this->getOption('mapping');
        if (!empty($mapping)) {
            $name = array_search($name, $mapping) ?: $name;
        }
        return $this->getRealFieldName($name);
    }

    /**
     * 处理数据对象的值（经过获取器和类型转换）
     *
     * @param string $name 名称
     * @param mixed  $value 值
     * @param array  $data 所有数据
     *
     * @return mixed
     */
    private function getWithAttr(string $name, $value, array $data = [])
    {
        $attr     = Str::studly($name);
        $method   = 'get' . $attr . 'Attr';
        $withAttr = $this->getWeakData('with_attr', $name);
        if ($withAttr) {
            // 动态获取器
            $value = $withAttr($value, $data, $this);
        } elseif (method_exists($this, $attr) && $get = $this->$attr()['get'] ?? '') {
            // 属性器
            $value = $get($value, $data);
        } elseif (method_exists($this, $method)) {
            // 获取器
            $value = $this->$method($value, $data);
        } elseif ($value instanceof Typeable || is_subclass_of($value, EnumTransform::class)) {
            // 类型自动转换
            $value = $value->value();
        }
        return $value;
    }

    /**
     * 获取关联数据
     *
     * @param string $name 名称
     *
     * @return mixed
     */
    protected function getRelationData(string $name)
    {
        $method = Str::camel($name);
        if (method_exists($this->model(), $method)) {
            $modelRelation = $this->$method();
            if ($modelRelation instanceof Relation) {
                $value = $modelRelation->getRelation();
                $this->setData($name, $value);
                return $value;
            }
        }
    }

    /**
     * 触发事件.
     *
     * @param string $event 事件名
     *
     * @return bool
     */
    protected function trigger(string $event): bool
    {
        if ($this->model() instanceof Model) {
            return true;
        }

        $call = 'on' . Str::studly($event);

        try {
            $result = method_exists($this, $call) ? $this->$call($this) : true;

            return false !== $result;
        } catch (ModelEventException $e) {
            return false;
        }
    }

    /**
     * 构建实体模型查询.
     *
     * @param Query $query 查询对象
     * @return void
     */
    protected function query(Query $query)
    {
    }

    /**
     * 模型数据转Json.
     *
     * @param int $options json参数
     * @param array $allow 允许输出字段
     * @return string
     */
    public function tojson(int $options = JSON_UNESCAPED_UNICODE, array $allow = []): string
    {
        return json_encode($this->toarray($allow), $options);
    }

    /**
     * 转换数据集为数据集对象
     *
     * @param array|Collection $collection    数据集
     *
     * @return Collection
     */
    public function toCollection(iterable $collection = []): Collection
    {
        return new Collection($collection);
    }

    /**
     * 设置父模型对象
     *
     * @param self $model 模型对象
     *
     * @return $this
     */
    public function setParent($model)
    {
        $this->setOption('parent', $model);

        return $this;
    }

    /**
     * 获取父模型对象
     *
     * @return self
     */
    public function getParent()
    {
        return $this->getOption('parent');
    }

    /**
     * 获取属性 支持获取器
     *
     * @param string $name 名称
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * 设置数据 支持类型自动转换
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        if ($value instanceof Entity && $bind = $this->getBindAttr($this->getOption('bind_attr'), $name)) {
            // 关联属性绑定
            $this->bindRelationAttr($value, $bind);
        } else {
            $this->set($name, $value);
        }
    }

    /**
     * 设置关联数据.
     *
     * @param string $relation 关联属性
     * @param array  $data  关联数据
     *
     * @return void
     */
    public function setRelation(string $relation, $data)
    {
        $this->__set($relation, $data);
    }

    protected function getBindAttr($bind, $name)
    {
        if (true === $bind || (isset($bind[$name]) && true === $bind[$name])) {
            return true;
        }
        return $bind[$name] ?? [];
    }

    /**
     * 设置关联绑定数据
     *
     * @param Entity|array $entity 关联实体对象
     * @param array|bool  $bind  绑定属性
     * @return void
     */
    public function bindRelationAttr($entity, $bind = [])
    {
        $data = is_array($entity) ? $entity : $entity->getData();
        foreach ($data as $key => $val) {
            if (isset($bind[$key])) {
                $this->set($bind[$key], $val);
            } elseif ((true === $bind || in_array($key, $bind)) && !$this->__isset($key)) {
                $this->set($key, $val);
            }
        }
    }

    /**
     * 检测数据对象的值
     *
     * @param string $name 名称
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        $name = $this->getRealFieldName($name);
        return isset(self::$weakMap[$this]['data'][$name]);
    }

    /**
     * 销毁数据对象的值
     *
     * @param string $name 名称
     *
     * @return void
     */
    public function __unset(string $name): void
    {
        $name = $this->getRealFieldName($name);

        $this->setWeakData('data', $name, null);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function __debugInfo()
    {
        return [
            'data'   => $this->getOption('data'),
            'origin' => $this->getOption('origin'),
            'schema' => $this->getOption('schema'),
        ];
    }

    // JsonSerializable
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // ArrayAccess
    public function offsetSet(mixed $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function offsetGet(mixed $name): mixed
    {
        return $this->get($name);
    }

    public function offsetExists(mixed $name): bool
    {
        return $this->__isset($name);
    }

    public function offsetUnset(mixed $name): void
    {
        $this->__unset($name);
    }

    /**
     * 设置当前模型数据表的后缀
     *
     * @param string $suffix 数据表后缀
     *
     * @return $this
     */
    public function setSuffix(string $suffix)
    {
        $this->setOption('suffix', $suffix);

        return $this;
    }

    /**
     * 获取查询对象
     *
     * @return Query
     */
    public function db(): Query
    {
        $db = $this->model();
        $db = $db instanceof Query ? $db : $db->db();

        // 执行扩展查询
        $this->query($db->suffix($this->getOption('suffix')));
        return $db;    
    }

    public static function __callStatic($method, $args)
    {
        $model = new static();

        if ($model->isVirtual()) {
            throw new Exception('virtual model not support db query');
        }

        $db = $model->db();

        if (!empty(self::$weakMap[$model]['auto_relation'])) {
            // 自动获取关联数据
            $db->with(self::$weakMap[$model]['auto_relation']);
        }

        return call_user_func_array([$db, $method], $args);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->model(), $method], $args);
    }
}
