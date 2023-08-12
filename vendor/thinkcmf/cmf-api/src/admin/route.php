<?php
use think\facade\Route;

Route::get('admin/menus', 'admin/Menu/menus');

Route::post('admin/setting/site', 'admin/Setting/sitePost');