<?php
use App\Model\AdminSetting;
use App\Http\Services\Logger;
use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('test-log', function () {
    try {
        storeException('error', 'test Err');
        (new Logger())->logInfo('test Err');

        $logger = new Logger('test.log');
        $settings = AdminSetting::where('slug', 'exchange_url')->first();
        // dd(gettype($settings));

        $logger->log('unknown', $settings);
        $logger->logErr('custom test err log');

        throw new Exception('Test Exception');
    } catch (Exception $e) {
        storeException('ERROR', processExceptionMsg($e));
    }
    dd('log tested');
});


Route::group(['middleware' => 'installation'], function () {
    Route::group(['middleware' => 'default_lang'], function () {

        Route::get('testcase', 'AuthController@test')->name('testAuth');
        Route::get('/', 'AuthController@login')->name('login');
        Route::get('login', 'AuthController@login')->name('login');
        Route::post('login-process', 'AuthController@loginProcess')->name('loginProcess');
        Route::get('forgot-password', 'AuthController@forgotPassword')->name('forgotPassword');
        Route::get('verify-email', 'AuthController@verifyEmailPost')->name('verifyWeb');
        Route::get('reset-password', 'AuthController@resetPasswordPage')->name('resetPasswordPage');
        Route::post('send-forgot-mail', 'AuthController@sendForgotMail')->name('sendForgotMail');
        Route::post('reset-password-save-process', 'AuthController@resetPasswordSave')->name('resetPasswordSave');

    });

    require base_path('routes/link/admin.php');

    Route::group(['middleware' => ['auth']], function () {
        // Two Factor At Login
        Route::get('/two-factor-check', 'AuthController@g2fChecked')->name('twofactorCheck');
        Route::post('/two-factor-verify', 'AuthController@twoFactorVerify')->name('twoFactorVerify');
        Route::get('/verify-email', 'AuthController@verifyEmail')->name('verifyEmail');
        Route::get('/verify-phone', 'AuthController@verifyPhone')->name('verifyPhone');

        Route::get('logout', 'AuthController@logOut')->name('logOut');
    });
});

Route::get('dev-test', [TestController::class, 'index']);
