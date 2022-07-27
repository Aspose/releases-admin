<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadsRequest;
use App\Http\Requests\UpdateRequest;
use App\Models\AmazonS3Setting;
use App\Models\CommitLog;
use AWS\CRT\HTTP\Response;
// use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use App\Models\Release;
use App\Models\Download;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DownloadFile extends Controller
{

    public function DownloadRelease(Request $request)
    {
       
        if (!empty($request->all())) {
            $url = $request->tagid;
            $url = urldecode($url);
            $url = parse_url($url, PHP_URL_PATH);
            $tagid = basename($url);
        
            $ip_address = $request->ip();
            $referer = $request->headers->get('referer');
            if (!empty($tagid)) {

                if (Release::where('etag_id', $tagid)->exists()) {
                    $Release = Release::where('etag_id', $tagid)->first();
                    $info = pathinfo($Release->s3_path);
                    $file_name = $info['basename'];
                   
                    if ($Release->s3_path != 's3_path') {

                        $file_url = $Release->s3_path;

                        if ($Release->is_new) {
                            $signedurl = $file_url;
                        } else {
                            $file_url_new = str_replace('https://s3-us-west-2.amazonaws.com/aspose.files/', '', $file_url);
                            $signedurl =  $this->getPreSignedUrl($file_url_new, $Release->is_new);
                        }
                        
                        if ($signedurl) {
                            $this->UpdateDownloadCount($Release->id);
                            $this->DownloadHistoryEntry($Release, $ip_address, $referer);
                            // header('Content-Type: application/octet-stream');
                            // header("Content-Transfer-Encoding: Binary");
                            // header("Content-disposition: attachment; filename=\"" . $file_name . "\"");
                            // readfile($signedurl);
                            return  $signedurl;
                        } else {
                            $this->UpdateDownloadCount($Release->id);
                            $this->DownloadHistoryEntry($Release, $ip_address, $referer);
                            // header('Content-Type: application/octet-stream');
                            // header("Content-Transfer-Encoding: Binary");
                            // header("Content-disposition: attachment; filename=\"" . $file_name . "\"");
                            // readfile($file_url);
                            return  $signedurl;
                        }
                    } else { // 
                        $file_url = $Release->s3_path;
                        return "Failed to Download Try Again....";
                    }
                    exit;
                } else {
                    return "file not exists";
                }
            }
        }
    }


    public function getPreSignedUrl($key, $is_new)
    {

        $expiryInMinutes = 60;
        if (!$is_new) {
            $AWS_ACCESS_KEY_ID = 'AKIAJ3IWQHR2VPPUU4AA';
            $AWS_SECRET_ACCESS_KEY = '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';
            $AWS_DEFAULT_REGION = 'us-west-2';
            $AWS_BUCKET = 'aspose.files';
        } else {
            $AWS_ACCESS_KEY_ID = env('AWS_ACCESS_KEY_ID');
            $AWS_SECRET_ACCESS_KEY = env('AWS_SECRET_ACCESS_KEY');
            $AWS_DEFAULT_REGION = env('AWS_DEFAULT_REGION');
            $AWS_BUCKET = env('AWS_BUCKET');
        }


        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => $AWS_DEFAULT_REGION,
                'credentials' => [
                    'key'    => $AWS_ACCESS_KEY_ID,
                    'secret' => $AWS_SECRET_ACCESS_KEY,
                ],

            ]);
            $cmd = $s3Client->getCommand('GetObject', ['Bucket' => $AWS_BUCKET, 'Key' => $key]);

            $request = $s3Client->createPresignedRequest($cmd, '+' . $expiryInMinutes . ' minutes');
            return (string) $request->getUri();
        } catch (\Exception $e) {
            return false;
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function UpdateDownloadCount($ID)
    {
        $Release = Release::find($ID);
        $Release->download_count = $Release->download_count + 1;
        $Release->save();
    }
    public function DownloadHistoryEntry($Release, $ip_address, $referer)
    {

        if (Auth::check()) {
            $posted_by_name = Auth::user()->name;
            $Email =  Auth::user()->email;
            $IsCustomer = 0;
        } else {
            $posted_by_name = $ip_address;
            $Email = NULL;
            $IsCustomer = 1;
        }

        $Download = Download::create([
            'FileID' => $Release->id,
            'LOGID' => $Release->id,
            'Email' => $Email,
            'family' => $Release->family,
            'product' => $Release->product,
            'folder' => $Release->folder,
            'etag_id' => $Release->etag_id,
            'IsCustomer' => $IsCustomer,
            'IPAddress' => $ip_address,
            'UrlReferrer' => $referer,
            'UserName' => $posted_by_name,
            'TimeStamp' => date('Y-m-d H:i:s'),

        ]);
    }
}
