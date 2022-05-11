<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Release;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

   
}
