<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
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

//Route::get('/', 'BlogController@index');
Route::get('/', 'HomeController@index');



Auth::routes();
Route::get('/profile', 'Auth\\ProfileController@index')->middleware('auth');

Route::get('/home', 'HomeController@index');
Route::get('/admin', 'HomeController@index');




Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => 'auth'], function () {
    Route::resource('/ventures/amazon-s3-settings', 'AmazonS3SettingController');
    Route::post('/ventures/file/upload', 'UploadController@upload');
    Route::get('/ventures/file/upload', 'UploadController@index');
    Route::get('/ventures/file/edit/{id}', 'UploadController@edit');
    Route::post('/ventures/file/update', 'UploadController@update');
    Route::post('/ventures/file/updatemaual', 'UploadController@updatemaual');
    Route::get('/ventures/file/manage-files', 'UploadController@managefiles');
    Route::any('/ventures/file/getchildnodes', ['as' => 'admin.getchildnodes', 'uses' => 'UploadController@getchildnodes']);


    //Manage Product Families
    Route::post('/products/manage-families', 'ManageProductFamilies@addnew');
    Route::get('/products/manage-families', 'ManageProductFamilies@index');
    //Manage Product
    Route::post('/products/manage-allproducts', 'ManageProduct@addnew');
    Route::get('/products/manage-allproducts', 'ManageProduct@index');
  
    //reset pwd
    Route::post('/resetpassword', 'UserController@resetpassword');
    Route::get('/resetpwd', 'UserController@index');

    //Manage Users
    Route::post('/addnewuser', 'UserController@addnewuser')->middleware('canmanageusers');
    Route::get('/manage-users', 'UserController@createuser')->middleware('canmanageusers');

    Route::post('/updateuser', 'UserController@updateuser')->middleware('canmanageusers');
    Route::get('/edituser/{id}', 'UserController@edituser')->middleware('canmanageusers');
});



//Route::group(['middleware' => 'auth'], function () {
    Route::get('/{family}/{product}/{folder}/{file}/{tagid}', 'Admin\UploadController@DownloadS3File');
//});

//Route::get('login', 'Auth\LoginController@openidlogin');
//Route::post('login', [ 'as' => 'login', 'uses' => 'LoginController@openidlogin']);

Route::get('/register', 'HomeController@index');
Route::post('/register', 'HomeController@index');


Route::get('send-mail', function () {
   
    $details = [
        'title' => 'Mail from test page',
        'body' => 'This is for testing email using smtp'
    ];
   
   Mail::alwaysFrom('release@aspose.com');
   $res =  Mail::to('fahad.adeel@aspose.com')->send(new \App\Mail\MyTestMail($details));
    dd("Email is Sent." . $res . " --- ");
});
