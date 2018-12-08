* app/admin/model/RouteModel.php `exists`方法改为`existsRoute`

* 删除所有 exit或 die
* 获取请求对象:在控制器里用`$this->request`,控制器之外统一用`request()`助手函数获取