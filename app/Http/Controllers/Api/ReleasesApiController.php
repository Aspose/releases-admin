<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Download;
use App\Models\Release;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Exception;
use Illuminate\Support\Facades\Storage;

class ReleasesApiController extends Controller
{

    public function updatecount(Request $request)
    {
        // $tags = Tag::paginate(10);
        $path =
        // return TagResource::collection($tags);

        //print_r($family . ' --- '. $product);

        //print_r($family_product);
        $jsonresponse = array();
        if(isset($request->path)){
            if(isset($request->single) && $request->single == 1){
                $etag_id = $request->path;
                $Release = Release::where('etag_id', '=', $etag_id)->get();
            }else{
                $family_product = '/'. $request->path . '/';

                $regex = '|/corporate/success-stories/([a-z]+.[a-z]+)|';
                preg_match_all($regex,$family_product,$res_succ,PREG_SET_ORDER);

                $regex = '|/corporate/brochures/all|';
                preg_match_all($regex,$family_product,$res_bbb,PREG_SET_ORDER);

                //print_r($res);
                if(!empty($res_succ)){
                    $d_tag_id = $res_succ[0][1];
                    //print_r($d_tag_id);
                    $Release = Release::where('product', '=', '/corporate/success-stories/')->where('folder', '=', $d_tag_id)->get();

                }else if(!empty($res_bbb)){
                    $Release = Release::where('product', '=', '/corporate/brochures/')->get();
                }else{
                    $family_product = str_replace('resources/', '', $family_product);
                    $Release = Release::where('product', '=', $family_product)->get();
                }

            }
            foreach($Release as $singlerelase){
                $productfamily_path = ltrim($singlerelase->product, '/');
                $productfamily_path = rtrim($productfamily_path, '/');
                $productfamily_path = str_replace('/', '-', $productfamily_path);
                $jsonresponse[$singlerelase->etag_id] = array(
                    'download_count' => $singlerelase->download_count,
                    'view_count' => $singlerelase->view_count,
                    'date_added' => date('F d, Y', strtotime($singlerelase->date_added)),
                    //'posted_by' => $singlerelase->posted_by,
                    //'filesize' => $singlerelase->filesize,
                );
            }
        }
        //dd($jsonresponse);
        //exit;
        return $jsonresponse;
    }

    public function addviewcount(Request $request){
        $return = 0;

        if(!empty($request->etag_id)){
           $etag_id = trim($request->etag_id);
            if (Release::where('etag_id', $etag_id)->exists()) {
               $row = DB::table('releases')->where('etag_id', $etag_id)->first();
               if($row){
                $Release = Release::find($row->id);
                $Release->view_count = $Release->view_count + 1;
                $Release->save();
                $return = $Release->view_count  ;
               }
            }
        }
        return $return;
    }



    public function addJavavDownloadHistoryEntry(Request $request){

      if (!($request->hasHeader('x-api-key'))) {

        // Same as getallheaders(), just with lowercase keys
      //  print_r(array_map($get_first, $request->headers->all()));

        print_r($request->header());

         return response()->json(['error' => 'Not authorized. No Key found'],403);

      }

      if (strcmp(env('RELEASES_API_ACCESS_KEY'), $request->header('x-api-key')) !== 0) {

        // Same as getallheaders(), just with lowercase keys
      //  print_r(array_map($get_first, $request->headers->all()));

           return response()->json(['error' => 'Not authorized. Invalid Key.'],403);
      }

      $someObject  = json_decode($request->json);
      // print_r($request->isJson());

        $jsonRequestArray = json_decode($request->getContent(), true);
              // Dump all data of the Array
       //echo $someArray[0]["name"]; // Access Array data

       foreach($jsonRequestArray as $item) { //foreach element in $arr


         Download::create([
             'FileID' => trim($item['id']),
             'LOGID' => trim($item['id']),
             'family' => trim($item['family']),
             'product' => trim($item['product']),
             'folder' => trim($item['folder']),
             'etag_id' => trim($item['version']),
             'IsCustomer' => 1,
             'IPAddress' => trim($item['ip']),
             'UrlReferrer' => trim($item['referer']),
             'UserName' => trim($item['ip']),
             'TimeStamp' => date($item['createDate'])
         ]);

       }

/*
        Download::create([
            'FileID' => trim($request->id),
            'LOGID' => trim($request->id),
            'family' => trim($request->family),
            'product' => trim($request->product),
            'folder' => trim($request->folder),
            'etag_id' => trim($request->version),
            'IsCustomer' => 1,
            'IPAddress' => trim($request->ip),
            'UrlReferrer' => trim($request->referer),
            'UserName' => trim($request->ip),
            'TimeStamp' => date('Y-m-d H:i:s')
        ]);
*/

  //  $jason =  json_encode($someObject);
    return true;


    }



    public function getcountbucket(){
        dd('nothing to do');
        exit;

    }

    public function GetGeneralStatus(Request $request){
        $days = $request->date;
        $date = \Carbon\Carbon::today()->subDays($days);
        //$users = Member::where('created_at', '>=', date($date))->get();
        $totalcount = Download::where('TimeStamp', '>=', date($date))->get();
        $allcount = $totalcount->count();

         $spec_counts = Download::where('TimeStamp', '>=', date($date))
         ->selectRaw('IsCustomer, count(*) as total')
        ->groupBy('IsCustomer')
         ->pluck('total','IsCustomer')->all();


        $final_array = array(
            'TotalDownloads'=>$allcount,
            'DownloadByCustomers'=>$spec_counts[1],
            'DownloadByAsposeStaffMember'=>$spec_counts[0]
        );
        $json =  json_encode($final_array);
        return $json;
    }


    public function GetTotalDetailedReportByDate(Request $request){
            $date = $request->date;
    //        $date = \Carbon\Carbon::today()->subDays($days);
            $startdate = $date." 00:00:00";
            $enddate = $date." 23:59:59";

        $spec_counts = Download::where('TimeStamp', '<=', date($enddate))
        ->orderBy('total', 'desc')
        ->selectRaw('product, count(*) as total')
       ->groupBy('product')
        ->pluck('total','product')->all();

        $final_array = array();
        foreach($spec_counts as $product=>$count){
           // echo "<pre>"; print_r($product . " === " . $count);echo "</pre>";
           //$product = rtrim($product, '/');
           //$product = ltrim($product, '/');
           //$product =  str_replace('corporate/', '', $product);
           //$product =  str_replace('/', '', $product);
            $final_array[] = array(
                'EntityName'=> $product,
                'EntityCount'=> $count,
                'EntityLastUpdate'=> date($date)
            );
        }
      //  $final_array = array_slice($final_array, 0, 10);
        $json =  json_encode($final_array);
        return $json;
    }

    // --- Daily downloads for one UTC day, for one product or for all of them ---
    // GET /api/GetProductDownloadsByDate?date=YYYY-MM-DD&product=/words/java/
    //
    // GetTotalDetailedReportByDate above returns RUNNING totals (every download up to
    // the end of the date), so a daily figure needs two of its calls, and each call
    // counts the whole downloads table. This method counts only the rows of one day.
    // It is fast because of the idx_downloads_timestamp_product index on
    // downloads (TimeStamp, product). Without that index it still answers correctly,
    // but it reads the whole table on every call.
    //
    // The entries keep the EntityName / EntityCount / EntityLastUpdate shape of the
    // older report endpoints, so existing report scripts can parse them the same way.
    public function GetProductDownloadsByDate(Request $request){

        // 1. date is required and must be a real calendar date written as YYYY-MM-DD.
        //    Validated by hand: $request->validate() answers a failure with a 302
        //    redirect unless the caller sends "Accept: application/json", which report
        //    scripts usually do not.
        $date = $request->query('date');
        if (!is_string($date)) {
            return response()->json(['error' => 'date is required, format YYYY-MM-DD'], 400);
        }
        $day = \DateTime::createFromFormat('!Y-m-d', $date, new \DateTimeZone('UTC'));
        // createFromFormat() quietly rolls impossible dates forward (2026-02-30 becomes
        // 2026-03-02), so the parsed date must print back exactly as it was sent.
        if ($day === false || $day->format('Y-m-d') !== $date) {
            return response()->json(['error' => 'date must be a real date, format YYYY-MM-DD'], 400);
        }

        // 2. product is optional. An empty product= arrives here as null (the global
        //    ConvertEmptyStringsToNull middleware) and means "all products". When given,
        //    it must match downloads.product exactly, which is the string the older
        //    endpoints return as EntityName, for example /words/java/.
        $product = $request->query('product');
        if ($product !== null && (!is_string($product) || mb_strlen($product) > 255)) {
            return response()->json(['error' => 'product must be a single value of at most 255 characters'], 400);
        }
        // A product that is not valid UTF-8 (for example %FF in the URL) cannot match
        // any stored name, and it would be echoed back as EntityName below, where
        // json_encode() fails: the caller would get a server error instead of a 400.
        if ($product !== null && !mb_check_encoding($product, 'UTF-8')) {
            return response()->json(['error' => 'product must be valid UTF-8'], 400);
        }

        // 3. One UTC day as a half-open range: from 00:00:00 up to, but not including,
        //    00:00:00 of the next day. TimeStamp is stored in UTC, the app timezone.
        //    A plain range on TimeStamp is what lets MySQL use the index.
        $start = $day->format('Y-m-d') . ' 00:00:00';
        $end = (clone $day)->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

        $query = Download::where('TimeStamp', '>=', $start)
            ->where('TimeStamp', '<', $end);
        if ($product !== null) {
            $query->where('product', '=', $product);
        }
        $spec_counts = $query->selectRaw('product, count(*) as total')
            ->groupBy('product')
            ->orderBy('total', 'desc')
            ->pluck('total', 'product')->all();

        // 4. A named product with no downloads that day still gets one entry with 0,
        //    so the caller can tell "no downloads" apart from "no answer".
        if ($product !== null && empty($spec_counts)) {
            $spec_counts = array($product => 0);
        }

        $final_array = array();
        foreach ($spec_counts as $name => $count) {
            $final_array[] = array(
                'EntityName' => $name,
                'EntityCount' => (int) $count,
                'EntityLastUpdate' => $date,
            );
        }
        return response()->json($final_array);
    }


    public function GetTotalDetailedReport(Request $request){
//        $days = $request->date;
//        $date = \Carbon\Carbon::today()->subDays($days);
        $spec_counts = Download::where('FileID', '>=', 1)
        ->orderBy('total', 'desc')
        ->selectRaw('product, count(*) as total')
       ->groupBy('product')
        ->pluck('total','product')->all();

        $final_array = array();
        foreach($spec_counts as $product=>$count){
           // echo "<pre>"; print_r($product . " === " . $count);echo "</pre>";
           //$product = rtrim($product, '/');
           //$product = ltrim($product, '/');
           //$product =  str_replace('corporate/', '', $product);
           //$product =  str_replace('/', '', $product);
            $final_array[] = array(
                'EntityName'=> $product,
                'EntityCount'=> $count,
                'EntityLastUpdate'=> date("Y-m-d",time())
            );
        }
      //  $final_array = array_slice($final_array, 0, 10);
        $json =  json_encode($final_array);
        return $json;
    }

    public function GetDetailedReport(Request $request){
        $days = $request->date;
        $date = \Carbon\Carbon::today()->subDays($days);
        $spec_counts = Download::where('TimeStamp', '>=', date($date))
        ->orderBy('total', 'desc')
        ->selectRaw('product, count(*) as total')
       ->groupBy('product')
        ->pluck('total','product')->all();

        $final_array = array();
        foreach($spec_counts as $product=>$count){
           // echo "<pre>"; print_r($product . " === " . $count);echo "</pre>";
           $product = rtrim($product, '/');
           $product = ltrim($product, '/');
           $product =  str_replace('corporate/', '', $product);
           $product =  str_replace('/', '', $product);
            $final_array[] = array(
                'EntityName'=> $product,
                'EntityCount'=> $count,
            );
        }
        $final_array = array_slice($final_array, 0, 10);
        $json =  json_encode($final_array);
        return $json;
    }
    public function GetFamilyPIEChart(Request $request){
        $days = $request->date;
        $date = \Carbon\Carbon::today()->subDays($days);
        $spec_counts = Download::where('TimeStamp', '>=', date($date))
        ->orderBy('total', 'desc')
        ->selectRaw('family, count(*) as total')
       ->groupBy('family')
        ->pluck('total','family')->all();

        $final_array = array();
        foreach($spec_counts as $product=>$count){
           $product = rtrim($product, '/');
           $product = ltrim($product, '/');
            $final_array[] = array(
                'EntityName'=> $product,
                'EntityCount'=> $count,
            );
        }
        $final_array = array_slice($final_array, 0, 10);
        $json =  json_encode($final_array);
        return $json;

    }

    public function GetPopularFiles(Request $request){
        $days = $request->date;
        $date = \Carbon\Carbon::today()->subDays($days);
        $spec_counts = Download::where('TimeStamp', '>=', date($date))
        ->orderBy('total', 'desc')
        ->selectRaw('etag_id, count(*) as total')
       ->groupBy('etag_id')
        ->pluck('total','etag_id')->all();

        $final_array = array();
     //print_r($spec_counts);
        $spec_counts = array_slice($spec_counts, 0, 10);
        $spec_counts = array_reverse($spec_counts);
        foreach($spec_counts as $product=>$count){
            $final_array[] = array(
                'EntityName'=> $this->getfilenamebyetag($product),
                'EntityCount'=> $count,
            );
        }

        $json =  json_encode($final_array);
        return $json;
    }

    public function getfilenamebyetag($tagid){
        if ( Release::where('etag_id', $tagid)->exists()) {
            $Release = Release::where('etag_id', $tagid)->first();
            return $Release->filename;
        }else{
            return $tagid;
        }
    }
}
