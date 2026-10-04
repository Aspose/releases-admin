<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ComplianceController;


Route::group(['middleware' => ['api'], 'namespace' => 'Api'], function () {

    Route::post('/oauth/token', 'AuthController@getAccessToken');

    Route::get('/updatecount', 'ReleasesApiController@updatecount');
    Route::post('/addviewcount', 'ReleasesApiController@addviewcount');
    Route::get('/getcountbucket', 'ReleasesApiController@getcountbucket');

    //charts
    Route::post('/GetGeneralStatus', 'ReleasesApiController@GetGeneralStatus');
    Route::post('/GetDetailedReport', 'ReleasesApiController@GetDetailedReport');
    Route::get('/GetTotalDetailedReport', 'ReleasesApiController@GetTotalDetailedReport');
    Route::post('/GetTotalDetailedReportByDate', 'ReleasesApiController@GetTotalDetailedReportByDate');
    // Downloads on one UTC day, for one product or for all of them (see
    // ReleasesApiController::GetProductDownloadsByDate). Fast only because of the
    // idx_downloads_timestamp_product index on the downloads table.
    Route::get('/GetProductDownloadsByDate', 'ReleasesApiController@GetProductDownloadsByDate');
    Route::post('/GetFamilyPIEChart', 'ReleasesApiController@GetFamilyPIEChart');
    Route::post('/GetPopularFiles', 'ReleasesApiController@GetPopularFiles');
    Route::post('/addJavavDownloadHistoryEntry', 'ReleasesApiController@addJavavDownloadHistoryEntry');

});

Route::middleware('auth:api')->get('/user', function(Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->post('/product/release', 'Admin\\UploadController@uploadAPI');

Route::post('/upload-compliance', [ComplianceController::class, 'uploadComplianceAPI']);

Route::post('/api/compliance/upload', [ComplianceController::class, 'uploadComplianceRestAPI']);
