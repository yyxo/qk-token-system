<?php

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

Route::get('register', 'RegisterController@register');
Route::post('register-submit', 'RegisterController@registerSubmit');
Route::post('login-submit', 'RegisterController@loginSubmit');
Route::get('login', 'RegisterController@login');
Route::group(['middleware' => ['auth.user']], function () {
    Route::get('/', 'UsersController@index');

    Route::get('/user/logout', 'UsersController@logout');

    Route::get('/manage-assets',       'ManageAssetsController@balance');//我的资产页面
    Route::get('/manage-assets/addre', 'ManageAssetsController@address');//绑定地址页面
    Route::get('/manage-assets/charge-page', 'ManageAssetsController@chargePage');//充值页面
    Route::get('/manage-assets/withdraw-page', 'ManageAssetsController@withdrawPage');//提现页面
    Route::get('/manage-assets/assets-logs', 'ManageAssetsController@assetsLogs');//资产明细页面

    Route::post('/manage-assets/balance-get',    'ManageAssetsController@getUserBalance');//获取余额
    Route::get('/manage-assets/balance-log',    'ManageAssetsController@balanceLogs');//资产记录
    Route::post('/manage-assets/addre-bind',     'ManageAssetsController@bindAddress');//绑定地址提交
    Route::post('/manage-assets/addre-my',       'ManageAssetsController@getAddress');//我的地址
    Route::post('/manage-assets/addre-del',      'ManageAssetsController@delAddress');//地址解绑
    Route::post('/manage-assets/withdraw', 'ManageAssetsController@tokenWithdraw');//提现

    //开箱子
    Route::group(['prefix' => 'box'], function () {

        Route::get('/', 'BoxController@index');
        Route::get('userinfo', 'BoxController@userinfo');
        Route::post('open', 'BoxController@open');
        Route::get('boxes', 'BoxController@boxes');
    });
});
