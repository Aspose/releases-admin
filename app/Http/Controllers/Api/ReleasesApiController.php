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

        $json = '[
            {
               "Key":"2022\/06\/06\/asposewordssharepoint_219_047841300_1654509526.zip",
               "LastModified":"2022-06-06T09:58:47+00:00",
               "ETag":"\"ae0d2801ec2b89d8d91648aa4efed906-5\"",
               "Size":"24962881",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/06\/test_082552200_1654509727.zip",
               "LastModified":"2022-06-06T10:02:08+00:00",
               "ETag":"\"ad2084ba41ce5632353468b273970400-4\"",
               "Size":"18496973",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/06\/test_zip_013893400_1654511878.zip",
               "LastModified":"2022-06-06T10:37:59+00:00",
               "ETag":"\"3cc3f49572821e48e20c3efa6d7050af-1\"",
               "Size":"699",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/06\/test_zip_031240600_1654510990.zip",
               "LastModified":"2022-06-06T10:23:13+00:00",
               "ETag":"\"3cc3f49572821e48e20c3efa6d7050af-1\"",
               "Size":"699",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/06\/test_zip_copy_038889500_1654513572.zip",
               "LastModified":"2022-06-06T11:06:13+00:00",
               "ETag":"\"3cc3f49572821e48e20c3efa6d7050af-1\"",
               "Size":"699",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/07\/test_zip_copy_022838500_1654584926.zip",
               "LastModified":"2022-06-07T06:55:27+00:00",
               "ETag":"\"3cc3f49572821e48e20c3efa6d7050af-1\"",
               "Size":"699",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/07\/test_zip_copy_091961100_1654584990.zip",
               "LastModified":"2022-06-07T06:56:31+00:00",
               "ETag":"\"3cc3f49572821e48e20c3efa6d7050af-1\"",
               "Size":"699",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/08\/aspose_imaging_226_052504200_1654702332.zip",
               "LastModified":"2022-06-08T15:32:13+00:00",
               "ETag":"\"5535e339ef339b636523095664d91a3a-8\"",
               "Size":"36889329",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/08\/asposeimaging226_023137800_1654700278.msi",
               "LastModified":"2022-06-08T14:57:59+00:00",
               "ETag":"\"5793f8bfb098bd71709a1f6a15d01d3c-8\"",
               "Size":"40280064",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/08\/asposeimaging226_040593800_1654700412.msi",
               "LastModified":"2022-06-08T15:00:13+00:00",
               "ETag":"\"5793f8bfb098bd71709a1f6a15d01d3c-8\"",
               "Size":"40280064",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/08\/asposeimaging226_dlls_only_073897300_1654700537.zip",
               "LastModified":"2022-06-08T15:02:18+00:00",
               "ETag":"\"289969298d77c223d85946bbca7086bc-8\"",
               "Size":"37334171",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/09\/asposecells_226_030943000_1654772315.msi",
               "LastModified":"2022-06-09T10:58:36+00:00",
               "ETag":"\"3685ffe6906cc8ddd5bd42da9661531c-20\"",
               "Size":"103550976",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/09\/asposecells_226_054993300_1654772807.zip",
               "LastModified":"2022-06-09T11:06:48+00:00",
               "ETag":"\"2c930375f81e180caa87b886aafc5ca9-21\"",
               "Size":"105536174",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/09\/asposecells_226_083146000_1654772145.msi",
               "LastModified":"2022-06-09T10:55:46+00:00",
               "ETag":"\"3685ffe6906cc8ddd5bd42da9661531c-20\"",
               "Size":"103550976",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/09\/asposepub_22_6_037193700_1654777256.zip",
               "LastModified":"2022-06-09T12:20:57+00:00",
               "ETag":"\"8385c043922dbeeaad158a4b7582ab7f-21\"",
               "Size":"106826456",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/09\/asposepub_22_6_049695900_1654777679.msi",
               "LastModified":"2022-06-09T12:28:00+00:00",
               "ETag":"\"5b4d940adae757d4af2c8dcb4d5033c1-21\"",
               "Size":"107376640",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/09\/asposepub_22_6_069864600_1654777442.msi",
               "LastModified":"2022-06-09T12:24:03+00:00",
               "ETag":"\"5b4d940adae757d4af2c8dcb4d5033c1-21\"",
               "Size":"107376640",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/10\/aspose_cells_226_java_076030800_1654844396.zip",
               "LastModified":"2022-06-10T06:59:57+00:00",
               "ETag":"\"207e1541ea0e099e3554d276365ee77b-6\"",
               "Size":"26388620",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/10\/aspose_cells_226_java_087374000_1654844485.zip",
               "LastModified":"2022-06-10T07:01:26+00:00",
               "ETag":"\"207e1541ea0e099e3554d276365ee77b-6\"",
               "Size":"26388620",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/10\/aspose_email_225_java_079268800_1654864330.zip",
               "LastModified":"2022-06-10T12:32:11+00:00",
               "ETag":"\"7e3177d4ba2728107834a17fa552a914-4\"",
               "Size":"18144441",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/10\/asposeemail_225_000329000_1654863835.msi",
               "LastModified":"2022-06-10T12:23:56+00:00",
               "ETag":"\"9eeadaa26f84f45faca6d1287c33c42b-11\"",
               "Size":"54444032",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/10\/asposeemail_225_011108600_1654863543.msi",
               "LastModified":"2022-06-10T12:19:04+00:00",
               "ETag":"\"9eeadaa26f84f45faca6d1287c33c42b-11\"",
               "Size":"54444032",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/10\/asposeemail_225_057301200_1654864047.zip",
               "LastModified":"2022-06-10T12:27:28+00:00",
               "ETag":"\"9004be8f67522c1d38829f9790d1840f-10\"",
               "Size":"47796601",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/10\/asposeemail_for_c_225_070139300_1654864543.zip",
               "LastModified":"2022-06-10T12:35:44+00:00",
               "ETag":"\"9f766578b2171cdb78678aacf03ac4cd-64\"",
               "Size":"332204862",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/10\/asposeemail_for_python_via_net_224_py3_none_manylinux1_x86_64_068260900_1654865486.whl",
               "LastModified":"2022-06-10T12:51:27+00:00",
               "ETag":"\"71063943e395017441eb70036d4c8bad-11\"",
               "Size":"57088607",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/10\/asposeemail_for_python_via_net_224_py3_none_win_amd64_081815100_1654865658.whl",
               "LastModified":"2022-06-10T12:54:19+00:00",
               "ETag":"\"dd97cf4f62b34aea4ec50b4016317cee-9\"",
               "Size":"45753887",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/Aspose.CAD.22.6(dll_only).zip",
               "LastModified":"2022-06-13T13:11:55+00:00",
               "ETag":"\"b8d5f554d982f039da0b3025398821e3-20\"",
               "Size":"100340324",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/Aspose.CAD.22.6.msi",
               "LastModified":"2022-06-13T13:10:28+00:00",
               "ETag":"\"e5e8afe452d4b0c7fa03dc41788182e7-13\"",
               "Size":"64004096",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/Test-file-ver-1.1.0.zip",
               "LastModified":"2022-06-13T11:12:07+00:00",
               "ETag":"\"3cc3f49572821e48e20c3efa6d7050af-1\"",
               "Size":"699",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/aspose_cells_226_androidviajava_037085600_1655098907.zip",
               "LastModified":"2022-06-13T05:41:48+00:00",
               "ETag":"\"5acbe0cd34513866bb52f9b211d92504-3\"",
               "Size":"10943506",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/aspose_cells_226_androidviajava_090201900_1655100486.zip",
               "LastModified":"2022-06-13T06:08:07+00:00",
               "ETag":"\"5acbe0cd34513866bb52f9b211d92504-3\"",
               "Size":"10943506",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/aspose_words_2230_androidviajava_096329700_1655102426.zip",
               "LastModified":"2022-06-13T06:40:27+00:00",
               "ETag":"\"73825b75d3ed8a8c8682c4a26c6ebaa5-3\"",
               "Size":"14741464",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/asposecells_2261_017616200_1655109530.zip",
               "LastModified":"2022-06-13T08:38:51+00:00",
               "ETag":"\"795b8d0c2d62d229962bc73d4163484f-21\"",
               "Size":"105522729",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/asposecells_2261_055828200_1655109798.msi",
               "LastModified":"2022-06-13T08:43:19+00:00",
               "ETag":"\"fe8acf69b37f58acc40accb4672334a3-20\"",
               "Size":"103546880",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/asposecells_2261_069201900_1655109392.msi",
               "LastModified":"2022-06-13T08:36:33+00:00",
               "ETag":"\"fe8acf69b37f58acc40accb4672334a3-20\"",
               "Size":"103546880",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/asposecells_2261_091540000_1655109730.zip",
               "LastModified":"2022-06-13T08:42:11+00:00",
               "ETag":"\"795b8d0c2d62d229962bc73d4163484f-21\"",
               "Size":"105522729",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/asposecells_for_nodejs_via_java_226_032522800_1655106861.zip",
               "LastModified":"2022-06-13T07:54:22+00:00",
               "ETag":"\"dd1abc55e5b99d9175d5ab322adee840-3\"",
               "Size":"12186513",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            },
            {
               "Key":"2022\/06\/13\/asposecells_for_python_via_java_226_056720300_1655111756.zip",
               "LastModified":"2022-06-13T09:15:57+00:00",
               "ETag":"\"dbee37a76c70ba3ef5e06c380260546d-3\"",
               "Size":"12192933",
               "StorageClass":"STANDARD",
               "Owner":{
                  "DisplayName":"simonsupport",
                  "ID":"f4ac98dd72b659079d4fa01d6eaa2bcbf8b1c2bd5a6f32e97dd50b795ba7a310"
               }
            }
        ]';

        $re = '/_[0-9]{9}+_[0-9]{9}+./m';
        $decode = json_decode($json);
        foreach($decode as $s3object){
            //echo $s3object->Key. '<br>';
            //$s3_oldpath = "https://s3.us-west-2.amazonaws.com/aspose.files/".$s3object->Key;
            $s3_oldpath = $s3object->Key;
            preg_match_all($re, $s3_oldpath, $matches, PREG_SET_ORDER, 0);
            if(!empty($matches)){
                $str_to_replace = $matches[0][0];
                
                $old_path_full_url =  "https://s3.us-west-2.amazonaws.com/aspose.files/".$s3_oldpath;

                $imagefoundinlocaldb = DB::table('releases')->where('s3_path', '=', $old_path_full_url)->where('is_new', 1)->first();
                if($imagefoundinlocaldb){

                
                    $new_file_path = str_replace($str_to_replace, '', $s3_oldpath );
                    $new_file_path = str_replace('_', '-', $new_file_path );
                   
                    $new_path_full_url =  "https://s3.us-west-2.amazonaws.com/aspose.files/".$new_file_path;

                    $s3_file_info_old_image_name = pathinfo($s3_oldpath);
                    $file_name_old = $s3_file_info_old_image_name['basename'];

                    echo "<pre> old File name: "; print_r($file_name_old);  echo "</pre>"; 
                    echo "<pre> old File s3 url : "; print_r($old_path_full_url);  echo "</pre>"; 

                    $s3_file_info_newimage_name = pathinfo($new_file_path);
                    $file_name_new = $s3_file_info_newimage_name['basename'];

                    echo "<pre> new File name: "; print_r($file_name_new);  echo "</pre>"; 
                    echo "<pre> new s3 url : "; print_r($new_path_full_url);  echo "</pre>"; 

                    


                    $CopySource = "aspose.files/".$s3_oldpath;
                    $newfilekey = $new_file_path;

                    echo "<pre> CopySource: "; print_r($CopySource);  echo "</pre>"; 
                    echo "<pre> newfilekey: "; print_r($newfilekey);  echo "</pre>"; 

                    $clients = new S3Client([
                        'version' => 'latest',
                        'region'  => 'us-west-2',
                        'credentials' => [
                            'key'    => 'AKIAJ3IWQHR2VPPUU4AA',
                            'secret' => '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' ,
                        ],
                
                    ]);
                    try {
                        $result = $clients->copyObject(array(
                                'ACL' => 'public-read',
                                'Bucket' => 'aspose.files',
                                // CopySource is required
                                'CopySource' =>  $CopySource,
                                // Key is required
                                'Key' => $newfilekey,
                                'MetadataDirective' => 'REPLACE'
                        ));
                
                        $Release = Release::find($imagefoundinlocaldb->id);
                        
                        
                        echo "------------- BEFORE UPDATE --------------<BR>";
                        echo "<pre> UPDATE ID: "; print_r($Release->id);  echo "</pre>"; 
                        echo "<pre> FOLDER LINK: "; print_r($Release->folder_link);  echo "</pre>"; 
                        echo "<pre> S3LINK: "; print_r($Release->s3_path);  echo "</pre>"; 
                        echo "<pre> FILENAME: "; print_r($Release->filename);  echo "</pre>"; 
                        echo "------------- /BEFORE UPDATE --------------<BR>";

                        // UPDATE PATH
                        $Release->s3_path = $new_path_full_url;
                        $Release->filename = $file_name_new;
                        $Release->save();



                        $UPDATEDRelease = Release::find($imagefoundinlocaldb->id);

                        echo "------------- AFTER UPDATE --------------<BR>";
                        echo "<pre> UPDATE ID: "; print_r($UPDATEDRelease->id);  echo "</pre>"; 
                        echo "<pre> FOLDER LINK: "; print_r($UPDATEDRelease->folder_link);  echo "</pre>"; 
                        echo "<pre> S3LINK: "; print_r($UPDATEDRelease->s3_path);  echo "</pre>"; 
                        echo "<pre> FILENAME: "; print_r($UPDATEDRelease->filename);  echo "</pre>"; 
                        echo "------------- /AFTER UPDATE --------------<BR>";

                        echo json_encode($result);
                
                    } catch (Exception $e) {
                        echo json_encode($e->getMessage());
                    }

                    echo "<hr>";


                }else{
                    //echo "URL NOT FOUND IN DB" . $s3_oldpath .'<BR>';
                }
            }
            // Print the entire match result
           
        }
        
        exit;
        // $timestamp_in_images = DB::table('releases')->where('s3_path', 'REGEXP', '_[0-9]+_[0-9]+.')->where('is_new', 1)->get();
        // foreach($timestamp_in_images as $single){
        //     echo"<p style='font-size:12px;'><span>". $single->id ." </span>|  <span style='color:green'>". $single->folder_link ."/" . $single->etag_id ."</span> | <span style='color:orange'>". $single->filename . " </span>| <span style='color:red'>" . $single->s3_path .'</span></p><hr>';
        // }
        // exit;
        //dd($timestamp_in_images);
         die('weewwerwe');


        


        //https://s3.us-west-2.amazonaws.com/aspose.files/2022/06/06/test_zip_013893400_1654511878.zip
        //https://s3.us-west-2.amazonaws.com/aspose.files/2022/06/06/test-zip.zip

         dd('21231');
        $AWS_DEFAULT_REGION = "us-west-2";
        $AWS_ACCESS_KEY_ID = "AKIAJ3IWQHR2VPPUU4AA";
        $AWS_SECRET_ACCESS_KEY ="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";
        $AWS_BUCKET = "aspose.files";

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
        Storage::put('/public/mdfiles/json/bucket-14-june.txt', $json);
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
