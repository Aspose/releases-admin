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
        dd($request->path);
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
                    'date_added' => date('d/m/Y', strtotime($singlerelase->date_added)),
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

    public function getcountbucket(){
        dd('21231');
        $AWS_DEFAULT_REGION = "us-west-2";
        $AWS_ACCESS_KEY_ID = "AKIAJ3IWQHR2VPPUU4AA";
        $AWS_SECRET_ACCESS_KEY ="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";
        $AWS_BUCKET = "aspose.files";


        /*$AWS_DEFAULT_REGION = "us-east-1";
        $AWS_ACCESS_KEY_ID = "AKIAQHFMOEYJSUVMED6D";
        $AWS_SECRET_ACCESS_KEY ="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";
        $AWS_BUCKET = "spacestation-invoices";*/

        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => $AWS_DEFAULT_REGION,
            'credentials' => [
                'key'    => $AWS_ACCESS_KEY_ID,
                'secret' => $AWS_SECRET_ACCESS_KEY ,
            ],
    
        ]);

        $iterator = $s3->getIterator('ListObjects', array(
            'Bucket' => $AWS_BUCKET
        ));
       $final_array = array();
        foreach ($iterator as $object) {
            //echo $object['Key'] . "<br>";
            
            $final_array[] = $object;
            
        }
       
        $json =  json_encode($final_array);
        //echo "<pre>"; print_r($json);  echo "</pre>"; //exit;
        Storage::put('/public/mdfiles/json/bucket.txt', $json);
        /*$response = $s3->putObject([
            'Bucket' => $AWS_BUCKET,
            'Key'    => $s3filePath, //'my-object',
            'SourceFile'   => $filetoupload,
            'ACL'    => 'public-read',
        ]);*/
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
