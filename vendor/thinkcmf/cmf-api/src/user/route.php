<?php
// +----------------------------------------------------------------------
// | 文件说明：路由
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.wuwuseo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: thinkcmf
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Date: 2017-8-11
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('admin/user/oauth/users$', 'user/AdminOauth/index');
Route::delete('admin/user/oauth/users/:id$', 'user/AdminOauth/delete');

Route::post('admin/user/actions/sync$', 'user/AdminUserAction/sync');
Route::get('admin/user/actions$', 'user/AdminUserAction/index');
Route::get('admin/user/actions/:id$', 'user/AdminUserAction/read')->pattern(['id' => '\d+']);
Route::put('admin/user/actions/:id$', 'user/AdminUserAction/update')->pattern(['id' => '\d+']);

Route::get('admin/user/users$', 'user/AdminUser/index');
Route::post('admin/user/users/:id/status/:status$', 'user/AdminUser/status')->pattern(['id' => '\d+', 'status' => '\d+',]);

Route::get('user/favorites/my$', 'user/Favorites/getFavorites'); //获取收藏列表
Route::get('user/comments/my$', 'user/Comments/getUserComments'); //获取我的评论列表
Route::get('user/comments$', 'user/Comments/getComments'); //获评论列表
Route::get('user/favorites/hasFavorite$', 'user/Favorites/hasFavorite'); // 判断是否已经收藏

Route::post('user/articles/deletes$', 'user/Articles/deletes');
Route::post('user/favorites$', 'user/Favorites/setFavorites'); //添加收藏
Route::post('user/comments$', 'user/Comments/setComments');//添加评论

Route::delete('user/favorites/:id$', 'user/Favorites/unsetFavorites');  //删除收藏
Route::delete('user/comments/:id$', 'user/Comments/delComments'); //删除评论

