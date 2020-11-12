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

Route::get('user/favorites/my', 'user/favorites/getFavorites'); //获取收藏列表
Route::get('user/comments/my', 'user/comments/getUserComments'); //获取我的评论列表
Route::get('user/comments', 'user/comments/getComments'); //获评论列表
Route::get('user/favorites/hasFavorite', 'user/favorites/hasFavorite'); // 判断是否已经收藏

Route::post('user/articles/deletes', 'user/Articles/deletes');
Route::post('user/favorites', 'user/favorites/setFavorites'); //添加收藏
Route::post('user/comments', 'user/comments/setComments');//添加评论

Route::delete('user/favorites/:id', 'user/favorites/unsetFavorites');  //删除收藏
Route::delete('user/comments/:id', 'user/comments/delComments'); //删除评论
