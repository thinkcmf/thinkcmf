<?php

use think\facade\Route;

Route::get('admin/menus', 'admin/Menu/menus');

Route::delete('admin/setting/cache', 'admin/Setting/clearCache');
Route::post('admin/setting/site', 'admin/Setting/sitePost');
Route::put('admin/mail/config', 'admin/Mail/configPut');
Route::put('admin/mail/template', 'admin/Mail/templatePut');

Route::resource('admin/slides', 'admin/Slide');
Route::resource('admin/slide/items', 'admin/SlideItem');
Route::post('admin/slide/items/:id/toggle', 'admin/SlideItem/toggle')->pattern(['id' => '\d+',]);
Route::post('admin/slide/items/:id/status/:status', 'admin/SlideItem/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/slide/items/list/order', 'admin/SlideItem/listOrder');

Route::resource('admin/routes', 'admin/Route');




