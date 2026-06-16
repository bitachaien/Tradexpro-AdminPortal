<?php

use Illuminate\Support\Facades\Route;

Route::group(['group' => 'role'], function () {
    Route::get('admin-list', 'RoleManagmentController@adminList')->name('adminList');
    Route::post('admin', 'RoleManagmentController@addEditAdmin')->name('addEditAdmin')->middleware('check_demo');
    Route::get('admin-profile-{id}', 'RoleManagmentController@viewAdminProfile')->name('viewAdminProfile');
    Route::get('admin-edit-{id}', 'RoleManagmentController@editAdminProfile')->name('editAdminProfile');
    Route::get('admin-delete-{id}', 'RoleManagmentController@deleteAdminProfile')->name('deleteAdminProfile')->middleware('check_demo');

    Route::get('admin-role-list', 'RoleManagmentController@adminRoleList')->name('adminRoleList');
    Route::post('admin-role', 'RoleManagmentController@adminRoleSave')->name('adminRoleSave')->middleware('check_demo');
    Route::get('role-delete-{id}', 'RoleManagmentController@adminRoleDelete')->name('adminRoleDelete')->middleware('check_demo');

    Route::post('permission-route', 'RoleManagmentController@addPermissionRoute')->name('addPermissionRoute');
    Route::post('permission-route-delete-{id}', 'RoleManagmentController@addPermissionRouteDelete')->name('addPermissionRouteDelete')->middleware('check_demo');
    Route::get('permission-route-edit-{id}', 'RoleManagmentController@addPermissionRouteEdit')->name('addPermissionRouteEdit');
    Route::get('permission-route-reset', 'RoleManagmentController@addPermissionRouteReset')->name('addPermissionRouteReset');

    Route::get('admin-role-permission-group-list', 'RoleManagmentController@adminRolePermissionGroupList')->name('adminRolePermissionGroupList');
    Route::post('admin-role-permission-save', 'RoleManagmentController@adminRolePermissionSave')->name('adminRolePermissionSave')->middleware('check_demo');
});
