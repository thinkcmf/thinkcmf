<?php

use think\facade\Route;


Route::get('admin/apps$', 'admin/App/index');
Route::post('admin/apps/:name$', 'admin/App/install');
Route::put('admin/apps/:name$', 'admin/App/update');
Route::delete('admin/apps', 'admin/App/uninstall');

Route::get('admin/assets', 'admin/Asset/index');
Route::delete('admin/assets/:id', 'admin/Asset/delete');

Route::get('admin/hooks/:hook/plugins$', 'admin/Hook/plugins');
Route::get('admin/hooks$', 'admin/Hook/index');
Route::post('admin/hooks/plugins/list/order$', 'admin/Hook/pluginListOrder');
Route::post('admin/hooks/sync$', 'admin/Hook/sync');

Route::resource('admin/links', 'admin/Link');
Route::post('admin/links/list/order$', 'admin/Link/listOrder');
Route::post('admin/links/:id/status/:status$', 'admin/Link/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/links/status/:status$', 'admin/Link/status')->pattern(['status' => '\d+',]);

Route::post('admin/menus/lang/export$', 'admin/Menu/exportMenuLang');
Route::post('admin/menus/list/order$', 'admin/Menu/listOrder');
Route::get('admin/home/menus$', 'admin/Menu/menus');
Route::resource('admin/menus', 'admin/Menu');

Route::put('admin/my/info$', 'admin/My/infoPut');


Route::resource('admin/navs', 'admin/Nav');
Route::resource('admin/nav/menus', 'admin/NavMenu');
Route::post('admin/nav/menus/:id/toggle$', 'admin/NavMenu/toggle')->pattern(['id' => '\d+',]);
Route::post('admin/nav/menus/:id/status/:status$', 'admin/NavMenu/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/nav/menus/list/order$', 'admin/NavMenu/listOrder');

Route::get('admin/plugins$', 'admin/Plugin/index');
Route::post('admin/plugins/:id/status/:status$', 'admin/Plugin/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::get('admin/plugins/:id/config$', 'admin/Plugin/config')->pattern(['id' => '\d+',]);
Route::put('admin/plugins/:id/config$', 'admin/Plugin/configPut')->pattern(['id' => '\d+',]);
Route::post('admin/plugins/:name$', 'admin/Plugin/install');
Route::put('admin/plugins/:name$', 'admin/Plugin/update');
Route::delete('admin/plugins/:id$', 'admin/Plugin/uninstall');
Route::get('admin/plugins/hooks/:id$', 'admin/Plugin/hooks');

Route::get('admin/recycle/bin/items$', 'admin/RecycleBin/index');
Route::post('admin/recycle/bin/restore$', 'admin/RecycleBin/restore');
Route::delete('admin/recycle/bin/items$', 'admin/RecycleBin/delete');
Route::delete('admin/recycle/bin/clear$', 'admin/RecycleBin/clear');

Route::get('admin/roles/:id/api/authorize$', 'admin/Role/apiAuthorize');
Route::put('admin/roles/:id/api/authorize$', 'admin/Role/apiAuthorizePut');
Route::get('admin/roles/:id/authorize$', 'admin/Role/authorize');
Route::put('admin/roles/:id/authorize$', 'admin/Role/authorizePut');
Route::resource('admin/roles', 'admin/Role');

Route::delete('admin/setting/cache$', 'admin/Setting/clearCache');
Route::put('admin/setting/site$', 'admin/Setting/sitePut');
Route::put('admin/setting/upload$', 'admin/Setting/uploadPut');
Route::put('admin/setting/storage$', 'admin/Setting/storagePut');
Route::put('admin/setting/password$', 'admin/Setting/passwordPut');
Route::put('admin/setting/lang$', 'admin/Setting/langPut');

Route::post('admin/themes/:theme/active$', 'admin/Theme/active');
Route::get('admin/themes/not/installed$', 'admin/Theme/notInstalled');
Route::get('admin/themes$', 'admin/Theme/index');
Route::post('admin/themes/:theme$', 'admin/Theme/install');
Route::put('admin/themes/:theme$', 'admin/Theme/update');
Route::delete('admin/themes/:theme$', 'admin/Theme/uninstall');
Route::get('admin/theme/file/var/array$', 'admin/Theme/fileArrayData')->append(['tab'=>'var']);
Route::post('admin/theme/file/var/array$', 'admin/Theme/fileArrayDataEditPost')->append(['tab'=>'var']);
Route::delete('admin/theme/file/var/array$', 'admin/Theme/fileArrayDataDelete')->append(['tab'=>'var']);
Route::get('admin/theme/file/widget/array$', 'admin/Theme/fileArrayData')->append(['tab'=>'widget']);
Route::post('admin/theme/file/widget/array$', 'admin/Theme/fileArrayDataEditPost')->append(['tab'=>'widget']);
Route::delete('admin/theme/file/widget/array$', 'admin/Theme/fileArrayDataDelete')->append(['tab'=>'widget']);
Route::get('admin/theme/file/block/widget/array$', 'admin/Theme/fileArrayData')->append(['tab'=>'block_widget']);
Route::post('admin/theme/file/block/widget/array$', 'admin/Theme/fileArrayDataEditPost')->append(['tab'=>'block_widget']);
Route::delete('admin/theme/file/block/widget/array$', 'admin/Theme/fileArrayDataDelete')->append(['tab'=>'block_widget']);
Route::get('admin/theme/:theme/files$', 'admin/Theme/files');
Route::get('admin/theme/:theme/file/setting$', 'admin/Theme/fileSetting');
Route::post('admin/theme/:theme/file/setting$', 'admin/Theme/fileSettingPost');
Route::get('admin/theme/widget/setting$', 'admin/Theme/widgetSetting');
Route::post('admin/theme/widget/setting$', 'admin/Theme/widgetSettingPost');
Route::post('admin/theme/widgets/sort$', 'admin/Theme/widgetsSort');

Route::post('admin/my/email/setting/test$', 'admin/My/emailSettingTest');
Route::get('admin/my/email/setting$', 'admin/My/emailSetting');
Route::put('admin/my/email/setting$', 'admin/My/emailSettingPut');

Route::put('admin/mail/config$', 'admin/Mail/configPut');
Route::put('admin/mail/template$', 'admin/Mail/templatePut');

Route::resource('admin/slides', 'admin/Slide');
Route::resource('admin/slide/items', 'admin/SlideItem');
Route::post('admin/slide/items/:id/toggle$', 'admin/SlideItem/toggle')->pattern(['id' => '\d+',]);
Route::post('admin/slide/items/:id/status/:status$', 'admin/SlideItem/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/slide/items/list/order$', 'admin/SlideItem/listOrder');

Route::resource('admin/routes', 'admin/Route');
Route::post('admin/routes/:id/toggle$', 'admin/Route/toggle')->pattern(['id' => '\d+',]);
Route::post('admin/routes/:id/status/:status$', 'admin/Route/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/routes/list/order$', 'admin/Route/listOrder');
Route::get('admin/routes/app/urls$', 'admin/Route/appUrls');

Route::post('admin/users/:id/status/:status$', 'admin/User/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::resource('admin/users', 'admin/User');





