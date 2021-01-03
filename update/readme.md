## 数据库操作升级注意：
### 模型后缀
如果之前开启了类库后缀功能的话，你必须在模型类里面明确指定`name`属性。
所有模型文件加上`$name`属性用于指定表名
### 模型的`allowFiled(true)`用法取消了，统一传入实际字段名
### `Db::name($name)`用法不建议使用了，统一使用模型操作数据库
### `join('__TABLE_NAME t')`用法取消了
统一使用不带前缀的表名,如：`join(no_prefix_table_name t)`

### 取消`db`和`model`助手函数
这两个助手函数`5.1`版本已经不再建议使用了，`6.0`版本已经废弃掉这两个助手函数，请直接使用`\think\facade\Db`类静态方法和实际的模型类调用。
### 取消`setInc`/`setDec`方法
取消Query类的`setInc`/`setDec`方法，统一使用`inc`/`dec`方法替代。例如：
~~~
Db::name('user')->where('id', 1)
->inc('exp')
->dec('score')
->update();
~~~
### 取消`join`方法的批量操作
`join`方法不再支持批量操作多个表，如果你使用了`join`方法批量操作，需要改成每个表单独调用一次`join`方法。
### 取消`setField`方法

取消Query类的`setField`方法，请直接使用`data`方法或者`update`方法。
### 取消查询`eq/neq/gt/lt/egt/elt`表达式

由于存在两种用法，并且不够直观，全部统一为更直观的用法。

下面的用法不再支持

~~~
Db::name('user')->where('id', 'egt', 1)
->where('status', 'neq' ,1)
->select();
~~~

统一使用

~~~
Db::name('user')->where('id', '>=', 1)
->where('status', '<>' ,1)
->select();
~~~
### 取消`whereOr`等方法传入`Query`对象
因为`Query`对象查询只能使用一次，除了`where`方法本身可以传入`Query`对象外，其它的所有`where`查询方法（例如`whereOr`/`whereExp`等）都不再支持传入`Query`对象。

### 取消了模型的`get`/`all`方法

无论使用`Db`类还是模型类查询，全部统一使用`find`/`select`方法，取消了之前模型类额外提供的`get`/`all`方法。同时取消的方法还包括`getOrFail`/`allOrFail`。

### 取消全局查询范围`base`方法

取消模型类的全局查询范围`base`方法，改由使用`globalScope`属性定义（数组）需要全局查询的查询范围方法。

### 取消模型自动完成

模型的自动完成功能已经取消，请使用模型事件代替。

### 模型`save`方法调整

模型类的`save`方法不再支持`where`参数。

## 其它升级注意
* `url()`方法输出不再是字符串（待考虑是否优化）

## 删除关联类的setEagerlyType方法
一对一关联无需在定义关联的时候指定为JOIN查询，在查询的时候直接使用withJoin方法即可使用JOIN

## 更多注意问题
参见https://www.kancloud.cn/manual/thinkphp6_0/1037654
