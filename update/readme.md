## 数据库操作升级注意：
### 模型后缀
如果之前开启了类库后缀功能的话，你必须在模型类里面明确指定`name`属性。
所有模型文件加上`$name`属性用于指定表名
### 模型的`allowFiled(true)`用法取消了，统一传入实际字段名
### `Db::name($name)`用法不建议使用了，统一使用模型操作数据库
### `join('__TABLE_NAME t')`用法取消了

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


## 其它升级注意
* `url()`方法输出不再是字符串（待考虑是否优化）
