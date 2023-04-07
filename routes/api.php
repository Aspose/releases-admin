<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['api'], 'namespace' => 'Api'], function () {
    Route::get('/updatecount', 'ReleasesApiController@updatecount');
    Route::post('/addviewcount', 'ReleasesApiController@addviewcount');
    Route::get('/getcountbucket', 'ReleasesApiController@getcountbucket');

    //charts
    Route::post('/GetGeneralStatus', 'ReleasesApiController@GetGeneralStatus');
    Route::post('/GetDetailedReport', 'ReleasesApiController@GetDetailedReport');
    Route::get('/GetTotalDetailedReport', 'ReleasesApiController@GetTotalDetailedReport');
    Route::post('/GetTotalDetailedReportByDate', 'ReleasesApiController@GetTotalDetailedReportByDate');
    Route::post('/GetFamilyPIEChart', 'ReleasesApiController@GetFamilyPIEChart');
    Route::post('/GetPopularFiles', 'ReleasesApiController@GetPopularFiles');
    Route::post('/addJavavDownloadHistoryEntry', 'ReleasesApiController@addJavavDownloadHistoryEntry');

});
