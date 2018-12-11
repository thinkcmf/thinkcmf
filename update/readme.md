* app/admin/model/RouteModel.php `exists`方法改为`existsRoute`

* 删除所有 exit或 die
* 获取请求对象:在控制器里用`$this->request`,控制器之外统一用`request()`助手函数获取
* 计划删除`phpoffice/phpexcel`扩展,使用`phpoffice/phpspreadsheet`
* `docker run -p 80:9501 -it  -v /Users/Dean/git/thinkcmf5:/data/www dnmpswoole_php  /bin/sh`
* hook,hook_one  去除`$extra`参数