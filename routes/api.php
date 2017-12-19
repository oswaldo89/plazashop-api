<?php

use Illuminate\Http\Request;


Route::post('register', 'Api\AuthController@register');
Route::post('login', 'Api\AuthController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('profile', 'Api\AuthController@profile');
    Route::resource('product', 'Api\ProductController');
    Route::get('/product_list/{total}', 'Api\ProductController@getList');
});
