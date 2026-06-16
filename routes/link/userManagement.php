<?php

use Illuminate\Support\Facades\Route;

Route::group(['group' => 'user'], function () {
    Route::get('users', 'UserController@adminUsers')->name('adminUsers');
    Route::get('user-profile', 'UserController@adminUserProfile')->name('adminUserProfile');
    Route::get('user-edit', 'UserController@UserEdit')->name('admin.UserEdit');
    Route::get('user-active-{id}', 'UserController@adminUserActive')->name('admin.user.active');
    Route::get('user-api-access-{id}', 'UserController@adminUserApiAccess')->name('admin.user.api.access');
    Route::get('user-remove-gauth-set-{id}', 'UserController@adminUserRemoveGauth')->name('admin.user.remove.gauth');
    Route::get('user-email-verify-{id}', 'UserController@adminUserEmailVerified')->name('admin.user.email.verify');
    Route::get('user-phone-verify-{id}', 'UserController@adminUserPhoneVerified')->name('admin.user.phone.verify');
    Route::get('deleted-users', 'UserController@adminDeletedUser')->name('adminDeletedUser');
    Route::get('user-export', 'UserController@userExport')->name('userExport');

    Route::group(['group' => 'user','middleware' => 'check_demo'], function () {
        Route::get('user-add', 'UserController@UserAddEdit')->name('admin.UserAddEdit');
        Route::get('user-delete-{id}', 'UserController@adminUserDelete')->name('admin.user.delete');
        Route::get('user-force-delete-{id}', 'UserController@adminUserForceDelete')->name('adminUserForceDelete');
        Route::get('user-suspend-{id}', 'UserController@adminUserSuspend')->name('admin.user.suspend');

        Route::get('profile-delete-request-deactive-{id}', 'UserController@adminUserDeleteRequestDeactive')->name('adminUserDeleteRequestDeactive');
        Route::get('profile-delete-request-sofdelete-{id}', 'UserController@adminUserDeleteRequestSoftDelete')->name('adminUserDeleteRequestSoftDelete');
        Route::get('profile-delete-request-force-delete-{id}', 'UserController@adminUserDeleteRequestForceDelete')->name('adminUserDeleteRequestForceDelete');
        Route::get('profile-delete-request-rejected-{id}', 'UserController@adminUserDeleteRequestRejected')->name('adminUserDeleteRequestRejected');

        Route::post('user-white-list-add', 'UserController@addUserWhiteList')->name('addUserWhiteList');
        Route::get('delete-user-white-list-{id?}', 'UserController@deleteUserWhiteList')->name('deleteUserWhiteList');
        Route::get('update-user-white-list-status', 'UserController@updateUserWhiteListStatus')->name('updateUserWhiteListStatus');
        Route::post('user-api-access-update', 'UserController@userApiAccessUpdate')->name('userApiAccessUpdate');
    });
});

Route::group(['group' => 'profile'], function () {
    Route::get('profile', 'DashboardController@adminProfile')->name('adminProfile');

    Route::group(['group' => 'profile','middleware' => 'check_demo'], function () {
        Route::post('user-profile-update', 'DashboardController@UserProfileUpdate')->name('UserProfileUpdate');
        Route::post('upload-profile-image', 'DashboardController@uploadProfileImage')->name('uploadProfileImage');
        Route::post("google-two-factor-enable", "DashboardController@g2fa_enable")->name("SaveTwoFactorAdmin");
        Route::post('update-two-factor', "DashboardController@updateTwoFactor")->name("UpdateTwoFactor");
    });
});


// ID Varification
Route::group(['group' => 'pending_id'], function () {
    Route::get('verification-details-{id}', 'UserController@VerificationDetails')->name('adminUserDetails');
    Route::get('pending-id-verified-user', 'UserController@adminUserIdVerificationPending')->name('adminUserIdVerificationPending');
    Route::get('verification-active-{id}-{type}', 'UserController@adminUserVerificationActive')->name('adminUserVerificationActive');
    Route::get('verification-reject', 'UserController@varificationReject')->name('varificationReject');
});
