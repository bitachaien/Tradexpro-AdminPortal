<?php

use Illuminate\Support\Facades\Route;

// landing setting
Route::group(['group' => 'landing'], function () {
    Route::get('landing-page-setting', 'LandingController@adminLandingSetting')->name('adminLandingSetting');
    Route::get('landing-page-download-link-{type}', 'LandingController@adminLandingApiLinkUpdateView')->name('adminLandingApiLinkUpdateView');

    Route::group(['middleware' => 'check_demo', 'group' => 'landing'], function () {
        Route::post('landing-api-link-setting-save', 'LandingController@adminLandingApiLinkSave')->name('adminLandingApiLinkSave');
        Route::post('landing-section-setting-save', 'LandingController@adminLandingSectionSettingsSave')->name('adminLandingSectionSettingsSave');
        Route::post('landing-pair-asset-setting-save', 'LandingController@adminLandingPairAssetSave')->name('adminLandingPairAssetSave');
        Route::post('landing-page-setting-save', 'LandingController@adminLandingSettingSave')->name('adminLandingSettingSave');
    });
});

// landing banner
Route::group(['group' => 'banner'], function () {
    Route::get('landing-banner-list', 'BannerController@adminBannerList')->name('adminBannerList');
    Route::get('landing-banner-add', 'BannerController@adminBannerAdd')->name('adminBannerAdd');
    Route::get('landing-banner-edit-{id}', 'BannerController@adminBannerEdit')->name('adminBannerEdit');
    Route::post('landing-banner-save', 'BannerController@adminBannerSave')->name('adminBannerSave')->middleware('check_demo');
    Route::get('landing-banner-delete-{id}', 'BannerController@adminBannerDelete')->name('adminBannerDelete')->middleware('check_demo');
});

// landing announcement
Route::group(['group' => 'announcement'], function () {
    Route::get('landing-announcement-list', 'AnnouncementController@adminAnnouncementList')->name('adminAnnouncementList');
    Route::get('landing-announcement-add', 'AnnouncementController@adminAnnouncementAdd')->name('adminAnnouncementAdd');
    Route::get('landing-announcement-edit-{id}', 'AnnouncementController@adminAnnouncementEdit')->name('adminAnnouncementEdit');
    Route::post('landing-announcement-save', 'AnnouncementController@adminAnnouncementSave')->name('adminAnnouncementSave')->middleware('check_demo');
    Route::get('landing-announcement-delete-{id}', 'AnnouncementController@adminAnnouncementDelete')->name('adminAnnouncementDelete')->middleware('check_demo');
});

// landing feature
Route::group(['group' => 'feature'], function () {
    Route::get('landing-feature-list', 'LandingController@adminFeatureList')->name('adminFeatureList');
    Route::get('landing-feature-add', 'LandingController@adminFeatureAdd')->name('adminFeatureAdd');
    Route::get('landing-feature-edit-{id}', 'LandingController@adminFeatureEdit')->name('adminFeatureEdit');
    Route::post('landing-feature-save', 'LandingController@adminFeatureSave')->name('adminFeatureSave')->middleware('check_demo');
    Route::get('landing-feature-delete-{id}', 'LandingController@adminFeatureDelete')->name('adminFeatureDelete')->middleware('check_demo');
});

// landing social media
Route::group(['group' => 'media'], function () {
    Route::get('landing-social-media-list', 'LandingController@adminSocialMediaList')->name('adminSocialMediaList');
    Route::get('landing-social-media-add', 'LandingController@adminSocialMediaAdd')->name('adminSocialMediaAdd');
    Route::get('landing-social-media-edit-{id}', 'LandingController@adminSocialMediaEdit')->name('adminSocialMediaEdit');
    Route::post('landing-social-media-save', 'LandingController@adminSocialMediaSave')->name('adminSocialMediaSave')->middleware('check_demo');
    Route::get('landing-social-media-delete-{id}', 'LandingController@adminSocialMediaDelete')->name('adminSocialMediaDelete')->middleware('check_demo');
});

