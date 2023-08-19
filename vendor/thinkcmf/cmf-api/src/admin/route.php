<?php

use think\facade\Route;

Route::get('admin/apps', 'admin/App/index');
Route::post('admin/apps/:name', 'admin/App/install');
Route::put('admin/apps/:name', 'admin/App/update');
Route::delete('admin/apps/:name', 'admin/App/uninstall');

Route::get('admin/menus', 'admin/Menu/menus');

Route::delete('admin/setting/cache', 'admin/Setting/clearCache');
Route::put('admin/setting/site', 'admin/Setting/sitePut');
Route::put('admin/setting/upload', 'admin/Setting/uploadPut');
Route::put('admin/setting/storage', 'admin/Setting/storagePut');
Route::put('admin/setting/password', 'admin/Setting/passwordPut');

Route::put('admin/mail/config', 'admin/Mail/configPut');
Route::put('admin/mail/template', 'admin/Mail/templatePut');

Route::resource('admin/slides', 'admin/Slide');
Route::resource('admin/slide/items', 'admin/SlideItem');
Route::post('admin/slide/items/:id/toggle', 'admin/SlideItem/toggle')->pattern(['id' => '\d+',]);
Route::post('admin/slide/items/:id/status/:status', 'admin/SlideItem/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/slide/items/list/order', 'admin/SlideItem/listOrder');

Route::resource('admin/routes', 'admin/Route');
Route::post('admin/routes/:id/toggle', 'admin/Route/toggle')->pattern(['id' => '\d+',]);
Route::post('admin/routes/:id/status/:status', 'admin/Route/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/routes/list/order', 'admin/Route/listOrder');
Route::get('admin/routes/app/urls', 'admin/Route/appUrls');




