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
    Route::get('/ventures/file/debuggingsheet', 'UploadController@debuggingsheet');
    Route::get('/ventures/file/edit/{id}', 'UploadController@edit');
    Route::get('/ventures/file/translate/{id}', 'UploadController@adminreleasetranlsate');
    Route::post('/ventures/file/update', 'UploadController@update');
    Route::post('/ventures/file/updatemaual', 'UploadController@updatemaual');
    Route::get('/ventures/file/manage-files', 'UploadController@managefiles');
    Route::get('/ventures/file/viewlogs/{id}', 'UploadController@viewlogs');
    Route::any('/ventures/file/getchildnodes', ['as' => 'admin.getchildnodes', 'uses' => 'UploadController@getchildnodes']);
    Route::any('/ventures/file/upload/release_exists_check_by_title', ['as' => 'admin.releaseexists', 'uses' => 'UploadController@release_exists_check_by_title']);
    Route::any('/ventures/file/upload/onlytranslate', ['as' => 'admin.onlytranslate', 'uses' => 'UploadController@onlytranslate']);
    Route::delete('ventures/file/{id}', 'UploadController@destroy')->name('upload.destroy');

    //manual upload missing releases
    Route::post('/ventures/file/manualreleaseupload', 'UploadController@manualreleaseupload');
    Route::get('/ventures/file/manualreleaseupload', 'UploadController@manualreleaseuploadform');


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

    //Manage Total.Net Release
    Route::get('/manage-total-net-release', 'ManageTotalNetReleasesController@index')->middleware('canmanageusers');
    Route::post('/downloadandcompress', 'ManageTotalNetReleasesController@downloadandcompress');
    Route::post('/compressfiles', 'ManageTotalNetReleasesController@compressfiles');
    Route::post('/progressdownload', 'ManageTotalNetReleasesController@progressdownload');
    Route::post('/uploadziptos3', 'ManageTotalNetReleasesController@uploadziptos3');
    Route::get('/manage-total-net-release/uploadfilemanual', 'ManageTotalNetReleasesController@uploadfileform')->middleware('canmanageusers');
    Route::post('/manage-total-net-release/fileUploadPost', 'ManageTotalNetReleasesController@fileUploadPost')->name('file.upload.post');
    Route::post('/manage-total-net-release/removefilesinpath', 'ManageTotalNetReleasesController@removefilesinpath')->name('file.remove.post')->middleware('canmanageusers');
});



//Route::group(['middleware' => 'auth'], function () {
    Route::get('/{family}/{product}/{folder}/{file}/{tagid}', 'Admin\UploadController@DownloadS3File');
    Route::post('/download-release', 'Admin\DownloadFile@DownloadRelease');
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
   
   $res1 =  Mail::to('amjad.ali@goldevelopers.com')->send(new \App\Mail\MyTestMail($details));
   $res2 =  Mail::to('fahad.adeel@aspose.com')->send(new \App\Mail\MyTestMail($details));
    dd("Email is Sent res1 --- " . $res1 . " --- res2 " .$res2 . ' --- ');
});

