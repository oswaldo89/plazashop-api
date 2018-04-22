<?php

use Illuminate\Http\Request;


Route::post('register', 'Api\AuthController@register');
Route::post('login', 'Api\AuthController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('profile', 'Api\AuthController@profile');
    Route::post('setFirebaseToken', 'Api\AuthController@saveFirebaseToken');
    Route::resource('product', 'Api\ProductController');
    Route::get('/product_listByUser/{total}/{user_id}', 'Api\ProductController@getListByUser');
    Route::post('/updatePet', 'Api\ProductController@updateProduct');
    Route::post('/deleteImage/{id}', 'Api\ProductController@deleteImage');
    Route::post('/sendMessage', 'Api\ProductController@sendMessage');
});
//url abierta al publico
Route::get('/product_list/{total}', 'Api\ProductController@getList');



