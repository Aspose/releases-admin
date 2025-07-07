<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Http\Controllers\Admin\UploadController;

Route::get('/', 'HomeController@index');

Auth::routes();

Route::get('/profile', 'Auth\\ProfileController@index')->middleware('auth');
Route::get('/home', 'HomeController@index');
Route::get('/admin', 'HomeController@index');

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => 'auth'], function () {
    // UploadController Routes
    Route::get('/ventures/file/upload', 'UploadController@index');
    Route::post('/ventures/file/upload', 'UploadController@upload');
    Route::get('/ventures/file/edit/{id}', 'UploadController@edit');
    Route::post('/ventures/file/update', 'UploadController@update');
    Route::post('/ventures/file/updatemaual', 'UploadController@updatemaual');
    Route::get('/ventures/file/manage-files', 'UploadController@managefiles');
    Route::get('/ventures/file/viewlogs/{id}', 'UploadController@viewlogs');
    Route::any('/ventures/file/getchildnodes', ['as' => 'admin.getchildnodes', 'uses' => 'UploadController@getchildnodes']);
    Route::any('/ventures/file/upload/release_exists_check_by_title', ['as' => 'admin.releaseexists', 'uses' => 'UploadController@release_exists_check_by_title']);
    Route::any('/ventures/file/upload/onlytranslate', ['as' => 'admin.onlytranslate', 'uses' => 'UploadController@onlytranslate']);
    Route::delete('ventures/file/{id}', 'UploadController@destroy')->name('upload.destroy');
    Route::get('/ventures/file/debuggingsheet', 'UploadController@debuggingsheet');

    // Compliance Upload Form + API
    Route::get('/ventures/file/compliance', 'ComplianceController@showForm');
    Route::post('/ventures/file/upload-compliance', 'ComplianceController@uploadComplianceAPI');
    Route::post('/ventures/file/generate-indexes', 'ComplianceController@ajaxGenerateIndexes');
    Route::post('/admin/ventures/file/getchildnodesforcompliance', 'ComplianceController@getchildnodesforcompliance')
        ->name('admin.getchildnodesforcompliance');
    Route::post('/ventures/file/ajax-check-compliance-requirements', 'ComplianceController@ajaxCheckComplianceRequirements')
        ->name('admin.compliance.ajax-check-requirements');


    // Manual Upload for Missing Releases
    Route::get('/ventures/file/manualreleaseupload', 'UploadController@manualreleaseuploadform');
    Route::post('/ventures/file/manualreleaseupload', 'UploadController@manualreleaseupload');

    // Translation
    Route::get('/ventures/file/translate/{id}', 'UploadController@adminreleasetranlsate');

    // Product Family Management
    Route::get('/products/manage-families', 'ManageProductFamilies@index');
    Route::post('/products/manage-families', 'ManageProductFamilies@addnew');

    // Product Management
    Route::get('/products/manage-allproducts', 'ManageProduct@index');
    Route::post('/products/manage-allproducts', 'ManageProduct@addnew');

    // User Management
    Route::middleware('canmanageusers')->group(function () {
        Route::get('/manage-users', 'UserController@createuser');
        Route::post('/addnewuser', 'UserController@addnewuser');
        Route::get('/edituser/{id}', 'UserController@edituser');
        Route::post('/updateuser', 'UserController@updateuser');
    });

    Route::get('/resetpwd', 'UserController@index');
    Route::post('/resetpassword', 'UserController@resetpassword');

    // Total .NET Release Management
    Route::middleware('canmanageusers')->group(function () {
        Route::get('/manage-total-net-release', 'ManageTotalNetReleasesController@index');
        Route::get('/manage-total-net-release/uploadfilemanual', 'ManageTotalNetReleasesController@uploadfileform');
        Route::post('/manage-total-net-release/fileUploadPost', 'ManageTotalNetReleasesController@fileUploadPost')->name('file.upload.post');
        Route::post('/manage-total-net-release/removefilesinpath', 'ManageTotalNetReleasesController@removefilesinpath')->name('file.remove.post');
    });

    Route::post('/downloadandcompress', 'ManageTotalNetReleasesController@downloadandcompress');
    Route::post('/compressfiles', 'ManageTotalNetReleasesController@compressfiles');
    Route::post('/progressdownload', 'ManageTotalNetReleasesController@progressdownload');
    Route::post('/uploadziptos3', 'ManageTotalNetReleasesController@uploadziptos3');
});

// Public Routes
Route::get('/{family}/{product}/{folder}/{file}/{tagid}', 'Admin\UploadController@DownloadS3File');
Route::post('/download-release', 'Admin\DownloadFile@DownloadRelease');

// Email Test
Route::get('send-mail', function () {
    $details = [
        'title' => 'Mail from test page',
        'body' => 'This is for testing email using smtp'
    ];
    Mail::to('amjad.ali@goldevelopers.com')->send(new \App\Mail\MyTestMail($details));
    Mail::to('fahad.adeel@aspose.com')->send(new \App\Mail\MyTestMail($details));
    dd("Emails sent.");
});

// Prevent public registration
Route::get('/register', 'HomeController@index');
Route::post('/register', 'HomeController@index');
