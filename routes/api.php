<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['api'], 'namespace' => 'Api'], function () {
    Route::get('/updatecount', 'ReleasesApiController@updatecount');
    Route::post('/addviewcount', 'ReleasesApiController@addviewcount');
});
