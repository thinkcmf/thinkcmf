<?php
use think\facade\Route;

Route::get('admin/menus', 'admin/Menu/menus');

Route::post('admin/setting/site', 'admin/Setting/sitePost');
Route::put('admin/mail/config', 'admin/Mail/configPut');
Route::put('admin/mail/template', 'admin/Mail/templatePut');

Route::resource('admin/slides', 'admin/Slide');
