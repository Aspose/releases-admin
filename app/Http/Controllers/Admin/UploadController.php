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
use App\Classes\PrepareSheetData;
class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Upload New Release/File";
        $settings = "";
        $DropDownContent = $this->GetDropDownContent();
        return view('admin.upload.index', compact('DropDownContent', 'title'));
    }
    //new release unique title check ajax
    public function release_exists_check_by_title(Request $request){
        $title = trim($request["title"]);
        $return = "no";
        if (Release::where('filetitle', $title)->exists()) {
            $return = "yes";
        }
        echo $return;
    }

    //translate release ajax
    public function onlytranslate(Request $request){
        $sheetdatahelper = new PrepareSheetData(); 
        $SpreadsheetId_Manual = env('SPREADSHEETIDMANUAL', '');
        $is_multilingual = env('MULTILINGUAL', false);
        if( !empty($SpreadsheetId_Manual) && $is_multilingual ){
            $host = $request->getHttpHost();
            $filecontent = $request["filecontent"];
            $id = $request["id"];
            $message = "failed somthing went wrong....";
            $release_exists = Release::where('id', $id)->first();
            if($release_exists){
                $release = Release::find($id);
                $content_path = ( storage_path() . '/app/public/mdfiles/content/');
                $added_to_sheet = $this->PrepareSheetcontentAndAddToSheet_Manual($sheetdatahelper, $content_path, $host, $filecontent, $release, $SpreadsheetId_Manual);
                if($added_to_sheet > 0){
                    sleep(10); // wait 10 sec // translate using api
                    $md_content_arranged = $this->ReadSheetData_ManualTranslation($sheetdatahelper, $content_path, $release->folder_link, $SpreadsheetId_Manual);
                    if(!empty($md_content_arranged ) && count($md_content_arranged)){
                        $res = $this->AddTranslatedFilesToRepo($md_content_arranged, $host );
                        $this->addlogentry($release->id, $res);
                        $message = "Content Translated and Updating Repo Updated";
                    }
                }else{
                    $message = "Failed To Write Sheet $added_to_sheet rows added";
                }
                
            }else{
                $message = "release not found " . $id;
            }

        }else{
            $message = "Add SPREADSHEETID-MANUAL to env and enable MULTILINGUAL";
        }
        echo $message;
        exit;
    }

    public function PrepareSheetcontentAndAddToSheet_Manual($sheetdatahelper, $content_path, $host, $filecontent, $release, $spreadsheetId){
        
        $javahomepage = false;
        $filepath = $release->folder_link;

        $folder_link =  $release->folder_link;

        $segments = explode('/', trim(parse_url($folder_link, PHP_URL_PATH), '/'));
        $numSegments = count($segments);
        $filename = $segments[$numSegments - 1];

        $parent_folder_path = implode("/", $segments);
        $plorp = substr(strrchr($parent_folder_path,'/'), 1);
        $parent_folder_path = substr($parent_folder_path, 0, - strlen($plorp));

        $src_file_path = $content_path . "/en/". $parent_folder_path ;
        $src_file_path = str_replace("//", '/', $src_file_path);
        $new_file_to_trandslate = $src_file_path . ''.  $filename. ".md"; 
        $new_file_to_trandslate = str_replace("//", '/', $new_file_to_trandslate);

        if (!file_exists( $src_file_path )) { // folder not exists
            mkdir($src_file_path, 0755, true);
        }


        if (!file_exists( $new_file_to_trandslate )) {
            touch($new_file_to_trandslate);
        }

        //add new line after line before and after div
        $filecontent = preg_replace('/(<div(?: class="[^"]+")?>)/', "\n$1", $filecontent);
        $filecontent = preg_replace('/(<\/div>)/', "$1\n", $filecontent);
        file_put_contents($new_file_to_trandslate, $filecontent);

        $final_sheet_data = $sheetdatahelper->getfilecontent($javahomepage, $new_file_to_trandslate, false);
        $transalted_md_files = $this->AddArrangedDataToSheet($release->folder_link, $final_sheet_data, $host, $spreadsheetId);

        return $transalted_md_files;
    }

    public function AddArrangedDataToSheet($folder_link, $mdfile, $host, $spreadsheetId ){
        $rows_count = 0;
        $translated_file_path = array();
        // clear exsiing data in sheet
        $this->clearsheet_admin_manual_translate($spreadsheetId);
        
        /*
        * We need to get a Google_Client object first to handle auth and api calls, etc.
        */
        $configJson = base_path().'/service-account.json';
        $client = new \Google_Client();
        $client->setApplicationName('Releases Transaltion App');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($configJson);
        /*
        * With the Google_Client we can get a Google_Service_Sheets service object to interact with sheets
        */
        $sheets = new \Google_Service_Sheets($client);

        $range = 'A1:O';
        $rows = $sheets->spreadsheets_values->get($spreadsheetId, $range, ['majorDimension' => 'ROWS']);
        $transalted_data = $rows['values'];

        if(count($transalted_data)){
            $Count = count($transalted_data);
            $row_key = 2;
            $Final_Array_To_Csv = array();

            foreach($mdfile as $pkey=>$line){
    
                $Final_Array_To_Csv[] = array( 
                    $folder_link,
                    $line['place_holder'], 
                    $line['type'], 
                    $line['replacement_array'], 
                    $line['text_to_translate'], 
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","de")',
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","el")',
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","es")',
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","fr")',
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","id")',
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","ja")',
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","pt")',
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","ru")',
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","tr")',
                    '=GOOGLETRANSLATE(E'.$row_key.',"en","zh")',
                );
                $row_key++;
            }

            //$translated_file_path = $this->ReadSheetData($mdfile, $spreadsheetId);
            $valueRange = new \Google_Service_Sheets_ValueRange();
            $valueRange->setValues($Final_Array_To_Csv);
            $range = 'Sheet1!A1:A';
            $conf = ["valueInputOption" => "USER_ENTERED"];
            $sheets->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $conf);
            
            $rows_count = count($Final_Array_To_Csv);
           
        }
       
        return $rows_count;

    }

    public function ReadSheetData_ManualTranslation($sheetdatahelper, $content_path, $mdfile, $spreadsheetId){

        $response = array();
        $target_languages_array = array("de", "el", "en", "es", "fr", "id", "ja", "pt", "ru", "tr", "zh");
        $source_lanaguage = "en";
        /*
        * We need to get a Google_Client object first to handle auth and api calls, etc.
        */
        $configJson = base_path().'/service-account.json';
        $client = new \Google_Client();
        $client->setApplicationName('Releases Transaltion App');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($configJson);
        /*
        * With the Google_Client we can get a Google_Service_Sheets service object to interact with sheets
        */
        $sheets = new \Google_Service_Sheets($client);

        $release_common_pre_translated_array = $this->parse_csv_file(base_path().'/csv/common.csv');
        $final_release_common_pre_translated_array = array();
        foreach($release_common_pre_translated_array as $key=>$single){
            $final_release_common_pre_translated_array[$single['text']] = $single;
        }

        $range = 'A1:O';
        $rows = $sheets->spreadsheets_values->get($spreadsheetId, $range, ['majorDimension' => 'ROWS']);
        $transalted_data = $rows['values'];
        $transalted_data_arranged = $this->arrange_sheet_data_manual_tranlation($transalted_data);
        //echo "<pre>"; print_r($transalted_data_arranged);  echo "</pre>"; exit;

        if(!empty($transalted_data_arranged)){
            foreach($target_languages_array as $target_lanaguage ){
                $place_holder = "";
                foreach($transalted_data_arranged as $keyfilepath=>$translatedcontent){
                        $mdfile = $keyfilepath;
                        //echo $mdfile;
                        $md_filepath = $keyfilepath;
                        $actual_file_path = rtrim($md_filepath,"/");
                        $actual_file_path = ltrim($actual_file_path,"/");
                        $en_mdfile = $content_path . "/". $source_lanaguage ."/" . $actual_file_path. ".md";
                        $en_mdfile = str_replace("//", '/', $en_mdfile);
                        //echo $en_mdfile; exit;
                        $segments = explode('/', trim(parse_url($actual_file_path, PHP_URL_PATH), '/'));
                        $numSegments = count($segments);
                        $filename = $segments[$numSegments - 1];
                        $filepath = implode("/", $segments);
                        $plorp = substr(strrchr($filepath,'/'), 1);
                        $filepath = substr($filepath, 0, - strlen($plorp)); 

                        $target_file_src = $content_path . "/". $target_lanaguage . '/' . $filepath . ''.  $filename. ".md"; 
                        $target_file_src = str_replace("//", '/', $target_file_src);

                        if( $target_lanaguage != 'en'){ //no translation in case of english
                            $target_file_src = str_replace("/en/", '/'.$target_lanaguage.'/', $en_mdfile);
                            $translated_md_file = $sheetdatahelper->replace_translated_placeholders(false, $en_mdfile, $translatedcontent, $target_lanaguage);
                            if($target_lanaguage == "zh"){
                                $translated_md_file = $sheetdatahelper->fix_zh_specfic($translated_md_file);
                            }
                            if($target_lanaguage == "zh" || $target_lanaguage == "ja" ){
                                $translated_md_file = $sheetdatahelper->fix_zh_ja_specfic($translated_md_file);
                            }


                            foreach($final_release_common_pre_translated_array as $keypattern=>$common){
                                $translated_replacemnt = $common[$target_lanaguage];
                                 if( $keypattern == 'Description'){
                                     $translated_md_file = preg_replace("#<h4>Description</h4>#",  "<h4>$translated_replacemnt</h4>" , $translated_md_file);
                                 } else if( $keypattern == 'File Size'){
                                     $translated_md_file = preg_replace("#File Size: {{#",  "$translated_replacemnt: {{" , $translated_md_file);
                                 }else{
                                    $translated_md_file = preg_replace("/$keypattern/",  $translated_replacemnt, $translated_md_file);
                                 }
                            }
                            
                            if (!file_exists( $content_path . "/". $target_lanaguage . '/' . $filepath )) { // folder not exists
                                mkdir($content_path . "/". $target_lanaguage . '/' . $filepath, 0755, true);
                            }
                            if (!file_exists( $target_file_src )) {
                                touch($target_file_src);
                            }
                            file_put_contents($target_file_src, $translated_md_file);
                        }  
                        $response[] = $target_file_src;

                }
                 
            }
        }
        return $response; // return paths of translated file
    }

    function arrange_sheet_data_manual_tranlation($transalted_data){
        $cols = array_shift( $transalted_data );
        $new_offersvalues = array();
        $new_offersvalues_final = array();
        foreach( $transalted_data as $k=>$v )
        {
            $new_offersvalues[ $k ] = array();
    
            foreach( $v as $k2=>$v2 )
            {
                $new_offersvalues[ $k ][ $cols[ $k2 ] ] = $v2;
            }
    
         unset( $transalted_data[ $k ] );
        }
    
    
        if(!empty($new_offersvalues)){
          foreach($new_offersvalues as $target_lanaguage ){
            $new_offersvalues_final[$target_lanaguage['path']][$target_lanaguage['place_holder']] = $target_lanaguage;
          }
        }
        
    
        return $new_offersvalues_final;
    }

    public function adminreleasetranlsate(Request $request, $id)
    {
        $show_translate_button = false;
        $SpreadsheetId_Manual = env('SPREADSHEETIDMANUAL', '');
        $is_multilingual = env('MULTILINGUAL', false);
        if( !empty($SpreadsheetId_Manual) && $is_multilingual ){
            $show_translate_button = true;
        }
        $title = "Translate Release ";
        $amazon_s3_settings = AmazonS3Setting::where('id', 1)->first();
        $hugositeurl = $amazon_s3_settings->hugositeurl;
        $release = Release::where('id', $id)->first();
        if($release){
            $release = Release::find($id);
        }else{
            dd("Release Not Found");
        }
        //dd($release->folder);
        $DropDownContent = $this->GetDropDownContent();
        $family_url = $hugositeurl .''.$release->family.'/';
        $product_url = $hugositeurl .''.$release->product;
        
        //dd($family_url);
        $familySelected = $this->searchSingle($DropDownContent, 'url', $family_url);
        if(empty($familySelected)){
            $family_url = $hugositeurl .''.$release->family;
            $familySelected = $this->searchSingle($DropDownContent, 'url', $family_url);
        }
        $productSelected = $this->searchSingle($DropDownContent, 'url', $product_url);
        
        //$folders = array('Examples'=> 'examples', 'New Releases'=> 'new-releases', 'Resources'=> 'resources' );



        if(!empty($product_url)){
            $folders = $this->getchildnodeslist($product_url);
            if(!empty($folders)){
                if(count($folders) == 1){
                    $folders['New Releases'] = 'new-releases';
                }
            }
        }
       
        if(empty($folders)){
            $url = false;
            $folders = array('Examples'=> 'examples', 'New Releases'=> 'new-releases', 'Resources'=> 'resources' );
            $selected_folder = $release->folder;
        }else{
            $url = true;
            $selected_folder = $hugositeurl .''.$release->product.$release->folder.'/';
        }
        
        return view('admin.upload.translate', compact('familySelected',  'productSelected', 'folders', 'selected_folder', 'release', 'title', 'show_translate_button'));
        
        
    }

    public function edit(Request $request, $id)
    {
        $title = "Edit Release/File";
        $amazon_s3_settings = AmazonS3Setting::where('id', 1)->first();
        $hugositeurl = $amazon_s3_settings->hugositeurl;
        $release = Release::where('id', $id)->first();
        if($release){
            $release = Release::find($id);
        }else{
            dd("Release Not Found");
        }
        //dd($release->folder);
        $DropDownContent = $this->GetDropDownContent();
        $family_url = $hugositeurl .''.$release->family.'/';
        $product_url = $hugositeurl .''.$release->product;
        
        //dd($family_url);
        $familySelected = $this->searchSingle($DropDownContent, 'url', $family_url);
        if(empty($familySelected)){
            $family_url = $hugositeurl .''.$release->family;
            $familySelected = $this->searchSingle($DropDownContent, 'url', $family_url);
        }
        $productSelected = $this->searchSingle($DropDownContent, 'url', $product_url);
        
        //$folders = array('Examples'=> 'examples', 'New Releases'=> 'new-releases', 'Resources'=> 'resources' );



        if(!empty($product_url)){
            $folders = $this->getchildnodeslist($product_url);
            if(!empty($folders)){
                if(count($folders) == 1){
                    $folders['New Releases'] = 'new-releases';
                }
            }
        }
       
        if(empty($folders)){
            $url = false;
            $folders = array('Examples'=> 'examples', 'New Releases'=> 'new-releases', 'Resources'=> 'resources' );
            $selected_folder = $release->folder;
        }else{
            $url = true;
            $selected_folder = $hugositeurl .''.$release->product.$release->folder.'/';
        }
        
        if(isset($request->action) && $request->action == 'manual' ){
            return view('admin.upload.editmanual', compact('familySelected',  'productSelected', 'folders', 'selected_folder', 'release', 'title'));
        }else{
            return view('admin.upload.edit', compact('familySelected',  'productSelected', 'folders', 'selected_folder', 'release', 'title'));
        }
        
        
    }

    


    public function GetDropDownContent(){
        $amazon_s3_settings = AmazonS3Setting::where('id', 1)->first();
        $hugositeurl = $amazon_s3_settings->hugositeurl;
        $hugositeurl =  $hugositeurl. '/index.json?Return_content='.time();
        $data = json_decode(file_get_contents($hugositeurl), true);
        return $data;
    }


    function searchSingle($array, $key, $value)
    {
    $results = array();

    if (is_array($array)) {
        if (isset($array[$key]) && $array[$key] == $value) {
            $results[] = $array;
        }

        foreach ($array as $subarray) {
            $results = array_merge($results, $this->searchSingle($subarray, $key, $value));
        }
    }

    return $results;
    }

    function searcharray($array, $key, $value)
        {
            $results = array();

            if (is_array($array)) {
                if (isset($array[$key]) && $array[$key] == $value) {
                    $results[] = $array;
                }

                foreach ($array as $subarray) {
                    $results = array_merge($results, $this->searcharray($subarray, $key, $value));
                }
            }

            return $results;
        }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(UploadsRequest $request)
    {
        $host = $request->getHttpHost();
        if(!empty($request->all())){
            
            $filter_family = $request->productfamily;
            $filter_product = $request->product;
            $filter_folder = $request->folder;
            $reload_filter = "?filter_productfamily=".$filter_family."&filter_product=".$filter_product."&filter_folder=".$filter_folder;
            
            $upload_info = $this->UploadImageToS3($request->all(), 'new');
            
            if(!empty($upload_info)){
                //echo "<pre>"; print_r($upload_info); echo "</pre>"; 
                $mdfile =$this->generate_mdfile($request->all(), $upload_info);

                $spreadsheetId = "";
                $is_multilingual = env('MULTILINGUAL', false);
                $spreadsheetId = env('SPREADSHEETID', '');
                $admin_email = Auth::user()->email;
                if( !empty($spreadsheetId) && $is_multilingual ){
                    $transalted_md_files = $this->AddReleaseToSpreadsheetAndTranslate($mdfile, $host);
                    $res = $this->AddTranslatedFilesToRepo($transalted_md_files, $host );
                }else{
                    $res = $this->forceDownloadMdFile($mdfile['data'], $mdfile['file_name'], $mdfile['file_path'], $host );
                }

                $this->addlogentry($mdfile['last_insert_update_id'], $res);
                return redirect('/admin/ventures/file/manage-files/' . $reload_filter)->with('success','Published Successfully.');
            }else{
                return redirect('/admin/ventures/file/manage-files')->with('error','Published Failed.');
            }
        }
    }

    public function update(UpdateRequest $request)
    {
        $host = $request->getHttpHost();
        $edit_id = $request->edit_id;
        
        if(!empty($request->all())){
            
            if(!empty($request->file)){ // if file uploaded in edit page
                $upload_info = $this->UploadImageToS3($request->all(), 'update');
            }else{
                $upload_info = $this->DontUploadFileToS3($request->all());
            }
            
            if(!empty($upload_info)){
                $mdfile =$this->generate_mdfile($request->all(), $upload_info);
                $res= $this->forceDownloadMdFile($mdfile['data'], $mdfile['file_name'], $mdfile['file_path'], $host );
                $this->addlogentry($mdfile['last_insert_update_id'], $res);
                return redirect('/admin/ventures/file/edit/' . $mdfile['last_insert_update_id'])->with('success','Update Successfully.' .$res);
            }else{
                return redirect('/admin/ventures/file/edit/'. $edit_id)->with('success','Update Failed.');
            }
            
        }
    }

    public function addlogentry($id, $log){
        $log = CommitLog::create([
            'release_id' => $id,
            'log' => $log,
        ]);
    }

    public function viewlogs(Request $request, $id){
        $title = "Release Commit Logs";
        $logs = CommitLog::where('release_id', $id)->orderBy('created_at', 'desc')->get();
        //dd($logs);
        return view('admin.upload.commitlogs', compact('logs','title'));
    }


    public function updatemaual(Request $request)
    {
        $edit_id = $request->edit_id;
        $release = Release::find($edit_id);
           $release->filetitle = trim($request->filetitle);
           $release->description = trim($request->description);
           $release->release_notes_url = trim($request->release_notes_url);
           $release->date_added = trim($request->date_added);
           $release->s3_path = trim($request->s3_path);
           $release->filesize = trim($request->filesize);
           $release->filename = trim($request->filename);
           $release->folder_link = trim($request->folder_link);
           $release->is_new = trim($request->is_new);
           $release->product = trim($request->product);
        // $release->folder = trim($request->folder);
        $release->weight = trim($request->weight);
        $release->save();
        return redirect('/admin/ventures/file/edit/'. $edit_id.'?action=manual')->with('success','Update s3 link.');
    }

    public function getchildnodes(Request $request){
        if(!empty($request->id)){
            $childtype = $request->childtype;
            $DropDownContent = $this->GetDropDownContent(1);
            $child = $this->searcharray($DropDownContent, 'url', $request->id);
            $final_array = array();
            if($childtype == 'folder'){ // if folder
                if(str_contains($request->id, '/corporate/')){ //folder and corporate get child from json
                    if(!empty($child[0]['nodes'])){
                        $child =  $child[0]['nodes'];
                        foreach($child as $single){
                            $final_array[$single['text']] = $single['url'];
                        }
                        return $final_array;    
                    }else{
                        
                        return array('Examples'=> 'examples', 'New Releases'=> 'new-releases', 'Resources'=> 'resources' );
                    }
                }else{ // fix for ocr folders
                    return array('Examples'=> 'examples', 'New Releases'=> 'new-releases', 'Resources'=> 'resources' );
                }

            }else{
                if(!empty($child[0]['nodes'])){
                    $child =  $child[0]['nodes'];
                    foreach($child as $single){
                        $final_array[$single['text']] = $single['url'];
                    }
                    return $final_array;    
                }else{
                    return array();    
                }
            }
            
        }
    }

    public function getchildnodeslist($id){
        if(!empty($id)){
            $childtype = $id;
            $DropDownContent = $this->GetDropDownContent(1);
            $child = $this->searcharray($DropDownContent, 'url', $id);
            $final_array = array();
            if(!empty($child[0]['nodes'])){
                $child =  $child[0]['nodes'];
                foreach($child as $single){
                    $final_array[$single['text']] = $single['url'];
                }
                return $final_array;    
            }else{
                if($childtype == 'folder'){
                    return array('Examples'=> 'examples', 'New Releases'=> 'new-releases', 'Resources'=> 'resources' );
                }
            }
            
        }
    }

    public function generate_mdfile($data, $upload_info){
        
       $posted_by_name = Auth::user()->name ;
        $productfamily  = $data['productfamily'];
        $product  = $data['product'];
        $folder  = $data['folder'];
        $title = $data['title'];
        $tags = $data['tags'];
        $description = $data['description'];
        $releaseurl = $data['releaseurl'];
        $weight = 1;
        
        

        $productfamily_path = parse_url($productfamily, PHP_URL_PATH);
        $productfamily_path = rtrim($productfamily_path, '/');

        
       $title_slug = $upload_info['title_slug'];
       $section_parent_path = $upload_info['section_parent_path'];
       $parent_path = $upload_info['parent_path'];
       $s3_path =  $upload_info['s3_path'];
       $fileSize = $upload_info['fileSize'];
       
       $download_link = $upload_info['download_link'];
       $folder_link = $upload_info['folder_link'];
       $title_new_tag =  $upload_info['title_new_tag'];
       $etag_id =  $upload_info['etag_id'];
       $orignal_etag_id = $upload_info['etag_id'];
       $image_link =  $upload_info['image_link'];

       $f_family =  $upload_info['family'];
       $f_product =  $upload_info['product'];
       //$f_product = rtrim($f_product, '/'); 
       $f_folder =  $upload_info['folder'];

       $actual_file_name =  $upload_info['actual_file_name'];
       $title =  $upload_info['title'];

      // echo $f_family . " === " . $f_product . " ==== ". $f_folder; exit;
     $view_count_intial = 1;
      if(!empty($upload_info['view_count'])){
            $view_count_intial = $upload_info['view_count'];
      }

      $download_count_intial = 1;
      if(!empty($upload_info['download_count'])){
        $download_count_intial = $upload_info['download_count'];
      }

      
      
     
     
     $f_family = rtrim($f_family, '/');
     $last_insert_update_id = 0;
     
     if($upload_info['insert_release']){

        $MaxWeightCount = Release::where('product', $f_product)->where('folder',$f_folder )->max('weight');
        if($MaxWeightCount){
            $weight = $MaxWeightCount + 1;
        }

        if(!empty($upload_info['weight'])){ // IF SET MANULLY
            $weight = $upload_info['weight'];
        }

        $release = Release::create([
            'family'=> $f_family,
            'product'=> $f_product,
            'folder'=> $f_folder,
            'filename'=>$actual_file_name,
            'filetitle'=>$title,
            'folder_link' => $folder_link,
            'etag_id' => $etag_id,
            's3_path' => $s3_path,
            'posted_by' => $posted_by_name,
            'view_count' => $view_count_intial,
            'download_count' => $download_count_intial,
            'description' => $description,
            'release_notes_url' => $releaseurl,
            'filesize' => $fileSize,
            'date_added' => date('Y-m-d H:i:s'),
            'sha1' => '',
            'md5' => '',
            'is_new' => 1,
            'weight' => $weight,
            'tags' => $tags
            ]);
            $Downloads_count = $download_count_intial;
            $Views_count = $view_count_intial;
            $down_date = date('j/n/Y');
            $last_insert_update_id = $release->id;
            /* ---------- Append primary key to Etag id (Etag not unique in some cases) -------*/
            if($last_insert_update_id){
                $release_tag_update = Release::find($last_insert_update_id);
                $etag_id = $etag_id.'-'.$last_insert_update_id;
                $release_tag_update->etag_id = $etag_id;
                $release_tag_update->save();
            }
            /* ---------- Append primary key to Etag id (Etag not unique in some cases) -------*/
     }else{
        $last_insert_update_id = $data['edit_id'];
        $release = Release::find($data['edit_id']);
        $release->filetitle = $title;
        $release->description = $description;
        $release->release_notes_url = $releaseurl;
        $release->tags = $tags;
        
        if($upload_info['contains_file']){
            $release->filename = $actual_file_name;
            $release->filesize = $fileSize;
            $release->s3_path = $s3_path;
            $etag_id = $etag_id.'-'.$last_insert_update_id;
            $release->etag_id = $etag_id;
        }
        $release->save();

        $Downloads_count = $release->download_count;
        $Views_count = $release->view_count;
        $down_date = date('j/n/Y', strtotime($release->date_added));
        $weight = $release->weight;
     }
       
        
        
        $buttons = array(
            'Download' => $download_link,
            'Support Forum' => 'https://forum.aspose.com/c'.$productfamily_path.'',

        );

        // replace Etag with new Generated
        $download_link = str_replace($orignal_etag_id, $etag_id, $download_link);
        
        $download_count_text = " $down_date Downloads: $Downloads_count  Views: $Views_count ";

        $md_file_content = "";
        $md_file_content .= "---";
        $md_file_content .= "\n";
        $md_file_content .= "\n";
        $md_file_content .= "title: \"Downloads ---$title_new_tag-". $title_slug ."\"" ;
        $md_file_content .= "\n";
        $md_file_content .= "description: \" \"";
        $md_file_content .= "\n";
        $md_file_content .= "keywords: \"\"";
        $md_file_content .= "\n";
        $md_file_content .= "page_type: single_release_page";
        $md_file_content .= "\n";

        $md_file_content .= "folder_link: \"$folder_link\"";
        $md_file_content .= "\n";

        //$md_file_content .= "s3_path: \"$s3_path\"";
        //$md_file_content .= "\n";
        
        $md_file_content .= "folder_name: \"$title\"";
        $md_file_content .= "\n";

        $md_file_content .= "download_link: \"$download_link\"";
        $md_file_content .= "\n";

        $md_file_content .= "download_text: \"Download\"";
        $md_file_content .= "\n";

        $md_file_content .= "intro_text: \"$description\"";
        $md_file_content .= "\n";
        
    
        $md_file_content .= "image_link: \"$image_link\"";
        $md_file_content .= "\n";
        
        $md_file_content .= "download_count: \"$download_count_text\"";
        $md_file_content .= "\n";

        $md_file_content .= "file_size: \"File Size: $fileSize\"";
        $md_file_content .= "\n";
        
        $md_file_content .= "parent_path: \"$parent_path\"";
        $md_file_content .= "\n";
        
        if(!empty($section_parent_path)){
            $md_file_content .= "section_parent_path: \"$section_parent_path\"";
            $md_file_content .= "\n";
        }else{
            $md_file_content .= "section_parent_path: \"$parent_path\"";
            $md_file_content .= "\n";
        }
        $md_file_content .= "\n";
        $md_file_content .= "tags: \"$tags\"";
        $md_file_content .= "\n";
        $md_file_content .= "release_notes_url: \"$releaseurl\"";
        $md_file_content .= "\n";
        $md_file_content .= "weight: " ."$weight";
        $md_file_content .= "\n";
        $md_file_content .= "\n";

        $md_file_content .= "---";
        $md_file_content .= "\n";
        $md_file_content .= "\n";



      //$image_link_md = str_replace('https://downloads.aspose.com/resources/', '/resources/', $image_link);
      $md_file_content .= "{{< Releases/ReleasesWapper >}}";
      $md_file_content .= "\n";
      $md_file_content .= "  {{< Releases/ReleasesHeading H2txt=\"$title\" imagelink=\"$image_link\">}}";
      $md_file_content .= "\n";
      $md_file_content .= "  {{< Releases/ReleasesButtons >}}";
      $md_file_content .= "\n";
      foreach($buttons as $key=>$value){
          // replace Etag with new Generated
          $value = str_replace($orignal_etag_id, $etag_id, $value);
          $md_file_content .= "    {{< Releases/ReleasesSingleButtons text=\"$key\" link=\"$value\" >}}";
          $md_file_content .= "\n";
      }
      $md_file_content .= "  {{< Releases/ReleasesButtons >}}";

      $md_file_content .= "\n";
      $md_file_content .= "  {{< Releases/ReleasesFileArea >}}";
      $md_file_content .= "\n";
          $md_file_content .= "    {{< Releases/ReleasesHeading h4txt=\"File Details\">}}";
          $md_file_content .= "\n";
          $md_file_content .= "    {{< Releases/ReleasesDetailsUl >}}";
          $md_file_content .= "\n";
             // $md_file_content .= "      {{< RelaseDetailsli >}}";
             // $md_file_content .= "\n";
              //$md_file_content .= "      <li>Downloads:</li><li id='downloads_count'>8414</li><li>File Size:</li><li id='fileSize'>$fileSize</li><li>Posted By:</li><li id='postedby'>$posted_by_name </li><li>Views:</li><li id='view_count'>1387</li><li>Date Added:</li><li id='date_added'>9/22/2021</li><li>SHA1:</li><li>aca4ea3c7db8c1d6e8e46696c8d3178c4dd19569</li><li>MD5:</li><li>b5cb8c63591b61cba98f2729c6ad5b2e</li>";
             // $md_file_content .= "      {{< /RelaseDetailsli >}}";
             $md_file_content .= '      {{< Common/li >}} Downloads: {{< /Common/li >}}';
             $md_file_content .= "\n";
             $md_file_content .= '      {{< Common/li class="downloadcount" id="dwn-update-'.$etag_id.'" >}} '.$Downloads_count.' {{< /Common/li >}}';
             $md_file_content .= "\n";
             $md_file_content .= '      {{< Common/li >}} File Size: {{< /Common/li >}}';
             $md_file_content .= "\n";
             $md_file_content .= '      {{< Common/li id="size-update-'.$etag_id.'" >}} '.$fileSize.' {{< /Common/li >}}';
             $md_file_content .= "\n";
             //$md_file_content .= '      {{< Common/li >}} Posted By: {{< /Common/li >}}';
             //$md_file_content .= "\n";
             //$md_file_content .= '      {{< Common/li id="author-update-'.$etag_id.'" >}} '.$posted_by_name.' {{< /Common/li >}}';
             //$md_file_content .= "\n";
             //$md_file_content .= '      {{< Common/li >}} Views: {{< /Common/li >}}';
             //$md_file_content .= "\n";
             //$md_file_content .= '      {{< Common/li id="view-update-'.$etag_id.'" >}} 1 {{< /Common/li >}}';
             $md_file_content .= "\n";
             $md_file_content .= '      {{< Common/li >}} Date Added: {{< /Common/li >}}';
             $md_file_content .= "\n";
             $md_file_content .= '      {{< Common/li id="added-update-'.$etag_id.'" >}}'. $down_date.' {{< /Common/li >}}';
              $md_file_content .= "\n";
          $md_file_content .= "    {{< /Releases/ReleasesDetailsUl >}}";
          $md_file_content .= "\n";
      //$md_file_content .= "  {{< /Releases/ReleasesFileArea >}}";
      $md_file_content .= "\n";
      if(!empty($releaseurl)){
      $md_file_content .= "  {{< Releases/ReleasesFileFeatures >}}";
      $md_file_content .= "\n";
              $md_file_content .= "      <h4>Release Notes</h4><div><a href='$releaseurl'>$releaseurl</a></div>";
              $md_file_content .= "\n";
      $md_file_content .= "  {{< /Releases/ReleasesFileFeatures >}}";
      }
      $md_file_content .= "\n";
      $md_file_content .= "  {{< Releases/ReleasesFileFeatures >}}";
      $md_file_content .= "\n";
              $md_file_content .= "      <h4>Description</h4><div class=\"HTMLDescription\">$description</div>";
              $md_file_content .= "\n";
      $md_file_content .= "  {{< /Releases/ReleasesFileFeatures >}}";
      $md_file_content .= "\n";
      $md_file_content .= " {{< /Releases/ReleasesFileArea >}}";
      $md_file_content .= "\n";


    $md_file_content .= "{{< /Releases/ReleasesWapper >}}";
    $md_file_content .= "\n";
    $md_file_content .= "\n";
    $md_file_content .= "\n";

      //echo $md_file_content;


      return array(
            'last_insert_update_id' => $last_insert_update_id,
            'file_path'=> $f_product.$f_folder,
            'file_name' => $title_slug,
            'sheet_title' => "Downloads ---" .$title_new_tag."-". $title_slug ,
            'sheet_description' => $description,
            'sheet_intro_text' => $description,
            'sheet_folder_name' => $title,
            'sheet_folder_link' => $folder_link,
            'data'=> $md_file_content
      );
    }

    public function AddTranslatedFilesToRepo($transalted_md_files, $host){
        $last_key = count($transalted_md_files) -1;
        $commit_files = "no";
        $clear_folder = "no";
        $output = " l1 --- ";
        foreach($transalted_md_files as $key => $download_path){
            if (file_exists($download_path)) {
                if ($key == $last_key) { // last iternation
                    $commit_files = "yes";
                    $clear_folder = "no";
                } else if ($key == 0) { //first iteration
                    $commit_files = "no";
                    $clear_folder = "yes";
                }else{ // others
                    $commit_files = "no";
                    $clear_folder = "no";
                }
                $hugo_content_path = strstr($download_path, 'content/');
                $initial_file_name_array = explode("/", $download_path); 
                $initial_file_name_only = end($initial_file_name_array); 
                $file_to_commit = $initial_file_name_only;
                $release_parent_folder_path =  preg_replace('#\/[^/]*$#', '', $hugo_content_path);
                //echo $download_path . " >> " . $hugo_content_path . " >> " . $release_parent_folder_path .  " >> " . $file_to_commit  . "<br>";

                /* ===================== COMMIT FILE ============= */

                    $local_clone_repo_path = env('LOCAL_REPO_CLONE_PATH', '');
                    if(!empty($local_clone_repo_path)){

                        $GIT_USERNAME = env('GIT_USERNAME', '');
                        $GIT_TOKEN = env('GIT_TOKEN', '');
                        $GIT_REPO = "";
                        $GIT_REPO = env('GIT_REPO', '');
                        
                        if(!empty($GIT_USERNAME) && !empty($GIT_TOKEN) && !empty($GIT_REPO) ){
                            
                            $repo_url = "https://$GIT_USERNAME:$GIT_TOKEN@github.com/$GIT_REPO";
                            $posted_by_email = Auth::user()->email ;
                            $commit_msg = "'new Release added by $posted_by_email '";
                            $download_path = "'$download_path'";
                            $hugo_content_path = "'$hugo_content_path'";
                            $file_to_commit = "'$file_to_commit'";
                            $release_parent_folder_path = "'$release_parent_folder_path'";
                            $commit_files = "'$commit_files'";
                            $clear_folder = "'$clear_folder'";
                            if(in_array($host, array('admindemo.aspose', 'admindemo.groupdocs'))){  //local
                    
                                $public_path = getcwd();
                                $bash_script_path = str_replace('/public', '/.scripts/', $public_path );
                                chdir($bash_script_path);
                                //$output .= " ========= before file run ========= ";
                                $output .= "  ======== addmdfilemutilang.sh $download_path $hugo_content_path $file_to_commit $local_clone_repo_path $repo_url $commit_msg $release_parent_folder_path $commit_files $clear_folder =====/===== ";
                                $output .= shell_exec('./addmdfilemutilang.sh '.$download_path.' '.$hugo_content_path.' '.$file_to_commit.' '.$local_clone_repo_path.' '.$repo_url.' '.$commit_msg.'  '.$release_parent_folder_path.' '.$commit_files.' '.$clear_folder.' ');
                                //$output .= " ========= After file run ========== ";
                                chdir($public_path);
                        
                            }else{ //prod/stage
                                //$output .= " before file run";
                                $output .= " ========= /var/www/scripts/addmdfilemutilang.sh $download_path $hugo_content_path $file_to_commit $local_clone_repo_path $repo_url $commit_msg $release_parent_folder_path $commit_files $clear_folder =====/===== ";
                                $output .= shell_exec('/var/www/scripts/addmdfilemutilang.sh '.$download_path.' '.$hugo_content_path.' '.$file_to_commit.' '.$local_clone_repo_path.' '.$repo_url.' '.$commit_msg.'  '.$release_parent_folder_path.' '.$commit_files.' '.$clear_folder.' ');
                                //$output .= " After file run";
                            }    
                        }
                    }
                    /* ===================== /COMMIT FILE ============= */
            }
        }
        return $output;
    }

    public function forceDownloadMdFile( $content, $filename, $file_path, $host) {
        
        $file_to_commit = $filename.".md"; 
        $filename_info = $file_path.'/'.$file_to_commit;
        Storage::put('/public/mdfiles/content' . $filename_info, $content);
        $download_path = ( storage_path() . '/app/public/mdfiles/content'.$filename_info);

        $MULTILINGUAL = env('MULTILINGUAL', false);
        if($MULTILINGUAL){
            $hugo_content_path = "content/en" . $file_path .'/';
        }else{
            $hugo_content_path = "content" . $file_path .'/';
        }
        
        //echo $download_path; exit;
        if (file_exists($download_path)) {
            
            
            
           
            /* ===================== COMMIT FILE ============= */
            $local_clone_repo_path = env('LOCAL_REPO_CLONE_PATH', '');
            if(!empty($local_clone_repo_path)){

                $GIT_USERNAME = env('GIT_USERNAME', '');
                $GIT_TOKEN = env('GIT_TOKEN', '');
                $GIT_REPO = "";

                /*if(in_array($host, array('admindemo.aspose', 'releases.admin.aspose.com'))){ //aspose.com
                    $GIT_REPO = env('GIT_REPO', '');
                }else if(in_array($host, array('admindemo.groupdocs', 'releases.admin.groupdocs.com'))){ //groupdocs.com
                    $GIT_REPO = env('GIT_REPO', '');
                }*/
                
                $GIT_REPO = env('GIT_REPO', '');
                
                if(!empty($GIT_USERNAME) && !empty($GIT_TOKEN) && !empty($GIT_REPO) ){
                    
                    $repo_url = "https://$GIT_USERNAME:$GIT_TOKEN@github.com/$GIT_REPO";
                    //echo "<pre> local_clone_repo_path "; print_r($local_clone_repo_path);echo "</pre>"; 
                    //echo "<pre> download_path "; print_r($download_path);echo "</pre>"; 
                    //echo "<pre> hugo_content_path "; print_r($hugo_content_path);echo "</pre>"; 
                    //echo "<pre> file_to_commit "; print_r($file_to_commit);echo "</pre>"; 
                    //echo "<pre> REPO_URL "; print_r($repo_url);echo "</pre>"; 
                    $posted_by_email = Auth::user()->email ;
                    $commit_msg = "'new Release added by $posted_by_email '";
                    $download_path = "'$download_path'";
                    $hugo_content_path = "'$hugo_content_path'";
                    $file_to_commit = "'$file_to_commit'";
                    if(in_array($host, array('admindemo.aspose', 'admindemo.groupdocs'))){  //local
            
                        $public_path = getcwd();
                        $bash_script_path = str_replace('/public', '/.scripts/', $public_path );
                        chdir($bash_script_path);
                        //echo "<pre> public_path "; print_r($public_path);echo "</pre>"; 
                        //echo "<pre> bash script path "; print_r($bash_script_path);echo "</pre>"; 
                        //echo "<pre> shell script "; print_r('./addmdfile.sh '.$download_path.' '.$hugo_content_path.' '.$file_to_commit.' '.$local_clone_repo_path.' '.$repo_url.' ');echo "</pre>"; 
                        $output = shell_exec('./addmdfile.sh '.$download_path.' '.$hugo_content_path.' '.$file_to_commit.' '.$local_clone_repo_path.' '.$repo_url.' '.$commit_msg.' ');
                        chdir($public_path);
                   
                    }else{ //prod/stage
                        $output = shell_exec('/var/www/scripts/addmdfile.sh '.$download_path.' '.$hugo_content_path.' '.$file_to_commit.' '.$local_clone_repo_path.' '.$repo_url.' '.$commit_msg.' ');
                    }
                    
                    //echo "<pre> file_to_commit "; print_r($output);echo "</pre>"; exit;
                    
                }
            }
            /* ===================== /COMMIT FILE ============= */
            return $output;

            /* ===================== FORCE DOWNLOAD ============= */
            $maxRead = 1 * 1024 * 1024; // 1MB
            $fh = fopen($download_path, 'r');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename_info . '"');
            while (!feof($fh)) {
                echo fread($fh, $maxRead);
                ob_flush();
            }
            /* ===================== /FORCE DOWNLOAD ============= */

            /* ===================== DELETE FILE ============= */
            //unlink($download_path);
            /* ===================== DELETE FILE ============= */
            return $output;
            exit;
            
        } else {
            //echo('File not found.');
            return 'File not found.';
        }

    }

    public function UploadImageToS3($data, $action){
        
        $insert_release = 1;
        if($action == 'update'){
            $release = Release::where('id', $data['edit_id'])->first();
            $insert_release = 0;
        }
        //exit;
        # get file from request object
        # get s3 object make sure your key matches with
        # config/filesystem.php file configuration
        $filetoupload = $data['file'];
        $productfamily  = $data['productfamily'];
        $product  = $data['product'];
        $folder_ini  = $data['folder'];

        $section_parent_path = "";
        $parent_path = "";

        if(strstr($folder_ini, '/')){
            $folder = rtrim($folder_ini, '/'); 
            //echo " 11111111111 ".$folder . " 11111111111 <BR>";
            //echo " 222222222222 ".$folder . " 222222222222 <BR>";
            $folder = substr(strrchr($folder, '/'), 1);
            //echo " 33333333333 ". $pppppp . " 33333333333 <BR>";
            $parent_path = parse_url($folder_ini, PHP_URL_PATH);
            $parent_path = rtrim($parent_path, '/');

            $section_parent_path = parse_url($product, PHP_URL_PATH);
            $section_parent_path = rtrim($section_parent_path, '/');
            $section_parent_path = ltrim($section_parent_path, '/');
        }else{
           // echo " BBBBBBBBBBB ".$folder . " BBBBBBBBBBB <BR>";
            $folder = $folder_ini ;
            $parent_path = parse_url($product, PHP_URL_PATH);
            $parent_path = rtrim($parent_path, '/');
        }
        

        $title = $data['title'];
        $productfamily_path = parse_url($productfamily, PHP_URL_PATH);
        $productfamily_path = rtrim($productfamily_path, '/'); 
        if($action == 'update'){
            /* POSSIBLE FIX TO MAINTAIN INTIAL FAIL NAME (KEEP DOWNLOAD MD FILE NAME SAME) */
                $initial_file_name = rtrim($release->folder_link, '/');
                $initial_file_name_array = explode("/", $initial_file_name); 
                $initial_file_name_only = end($initial_file_name_array); 
                $title_slug = str_replace(' ', '-', strtolower($initial_file_name_only));
            /* /POSSIBLE FIX TO MAINTAIN INTIAL FAIL NAME  */
        }else{
            $title_slug = str_replace(' ', '-', strtolower($title));
        }
        
       
       
       $special_case = array('all','examples', 'new-releases',  'resources' );
       $title_new_ = array('all'=>'all', 'examples'=>'Examples', 'new-releases'=>'New Releases',  'resources'=>'Resources');
       //echo $folder . ' ---------- folder ---  ';
       if(in_array($folder, $special_case)){
            $title_new_text = $title_new_[$folder];
        }else{
            $folder = rtrim($folder, '/'); 
            $path = explode("/", $folder); // splitting the path
            $last = end($path); 
            $title_new_text = ucfirst($last);
        }
      
       
      
       

       //echo "<pre> section_parent_path: "; print_r($section_parent_path); echo "</pre>"; 
       //echo "<pre> parent_path: "; print_r($parent_path); echo "</pre>"; 
       $folder_full_path = parse_url($folder, PHP_URL_PATH);
       $productfamily_full_path  = parse_url($productfamily, PHP_URL_PATH);
       $product_full_path = parse_url($product, PHP_URL_PATH);

       $download_link = "";
       $folder_link = "";
       if(!empty($section_parent_path)){
         $folder_link = '/'.$section_parent_path. '/'.$title_slug.'/';
         $download_link = $folder_link;
       }else{
         $folder_link = $parent_path."/".$folder.'/'.$title_slug . '/';
         $download_link = $folder_link;
       }

       
       $ETag = Str::random(40);
       $fileSizeBytes = "99877450";
       
       //$result = get_remote_file_info($filetoupload);
       $filetoupload = urldecode($filetoupload);
       $s3_file_headers = get_headers($filetoupload,1);
       if(!empty($s3_file_headers)){
        $ETag = trim($s3_file_headers['ETag'], '"');
        $fileSizeBytes = $s3_file_headers['Content-Length'];
       }

        $s3_file_info = pathinfo($filetoupload);
        # rename file name to random name
        $file_name = $s3_file_info['basename'];
        # define s3 target directory to upload file to
        $fileSize =  $this->formatBytes($fileSizeBytes, 2);

        $image_link = "/resources/img/random-file-icon.png";
        if($s3_file_info['extension'] == 'zip'){
            $image_link = "/resources/img/zip-icon.png";
        } else if($s3_file_info['extension'] == 'msi'){
            $image_link = "/resources/img/msi-icon.png";
        } else if($s3_file_info['extension'] == 'pdf'){
            $image_link = "/resources/img/pdf-icon.png";
        } else if($s3_file_info['extension'] == 'doc'){
            $image_link = "/resources/img/doc-icon.png";
        }
        return array(
            's3_path' => $filetoupload,
            'folder_link' => $folder_link,
            'download_link' => $download_link . $ETag,
            'parent_path' => ltrim($parent_path, '/'),
            'section_parent_path' => $section_parent_path,
            'title' => $title,
            'actual_file_name' =>$file_name,
            'title_slug' => $title_slug,
            'title_new_tag' => $title_new_text,
            'fileSize' => $fileSize,
            'etag_id' => $ETag,
            'image_link' => $image_link,
            'family' => $productfamily_full_path,
            'product' => $product_full_path,
            'folder'=> $folder_full_path,
            'insert_release'=> $insert_release,
            'view_count'=>'',
            'download_count'=>'',
            'weight'=>'',
            'contains_file' => 1
            );
    }

    
    public function DontUploadFileToS3($data){
        $release = Release::where('id', $data['edit_id'])->first();

        $productfamily  = $data['productfamily'];
        $product  = $data['product'];
        $folder_ini  = $data['folder'];

        $section_parent_path = "";
        $parent_path = "";

        if(strstr($folder_ini, '/')){
            $folder = rtrim($folder_ini, '/'); 
            $folder = substr(strrchr($folder, '/'), 1);
            $parent_path = parse_url($folder_ini, PHP_URL_PATH);
            $parent_path = rtrim($parent_path, '/');
            $section_parent_path = parse_url($product, PHP_URL_PATH);
            $section_parent_path = rtrim($section_parent_path, '/');
            $section_parent_path = ltrim($section_parent_path, '/');
        }else{
            $folder = $folder_ini ;
            $parent_path = parse_url($product, PHP_URL_PATH);
            $parent_path = rtrim($parent_path, '/');
        }
        

        $title = $data['title'];
        $productfamily_path = parse_url($productfamily, PHP_URL_PATH);
        $productfamily_path = rtrim($productfamily_path, '/'); 
        
        /* POSSIBLE FIX TO MAINTAIN INTIAL FAIL NAME (KEEP DOWNLOAD MD FILE NAME SAME) */
        $initial_file_name = rtrim($release->folder_link, '/');
        $initial_file_name_array = explode("/", $initial_file_name); 
        $initial_file_name_only = end($initial_file_name_array); 
        $title_slug = str_replace(' ', '-', strtolower($initial_file_name_only));
        /* /POSSIBLE FIX TO MAINTAIN INTIAL FAIL NAME  */
       
       $special_case = array('all','examples', 'new-releases',  'resources' );
       $title_new_ = array('all'=>'all', 'examples'=>'Examples', 'new-releases'=>'New Releases',  'resources'=>'Resources');
       if(in_array($folder, $special_case)){
            $title_new_text = $title_new_[$folder];
        }else{
            $folder = rtrim($folder, '/'); 
            $path = explode("/", $folder); // splitting the path
            $last = end($path); 
            $title_new_text = ucfirst($last);
        }
      
       $folder_full_path = parse_url($folder, PHP_URL_PATH);
       $productfamily_full_path  = parse_url($productfamily, PHP_URL_PATH);
       $product_full_path = parse_url($product, PHP_URL_PATH);

       $download_link = "";
       $folder_link = "";
       if(!empty($section_parent_path)){
         $folder_link = '/'.$section_parent_path. '/'.$title_slug.'/';
         $download_link = $folder_link;
       }else{
         $folder_link = $parent_path."/".$folder.'/'.$title_slug . '/';
         $download_link = $folder_link;
       }

       


        # rename file name to random name
        //$file_name = uniqid() .'.'. $image->getClientOriginalExtension();
        $ext = "";
        $ext = pathinfo($release->filename, PATHINFO_EXTENSION);

        $image_link = "/resources/img/random-file-icon.png";
        if($ext == 'zip'){
            $image_link = "/resources/img/zip-icon.png";
        } else if($ext == 'msi'){
            $image_link = "/resources/img/msi-icon.png";
        } else if($ext == 'pdf'){
            $image_link = "/resources/img/pdf-icon.png";
        } else if($ext == 'doc'){
            $image_link = "/resources/img/doc-icon.png";
        }
        
        return array(
            's3_path' => $release->s3_path,
            'folder_link' => $release->folder_link,
            'download_link' => $release->folder_link . $release->etag_id,
            'parent_path' => ltrim($parent_path, '/'),
            'section_parent_path' => $section_parent_path,
            'title' => $title,
            'actual_file_name' =>$release->filename,
            'title_slug' => $title_slug,
            'title_new_tag' => $title_new_text,
            'fileSize' => $release->filesize,
            'etag_id' => $release->etag_id,
            'image_link' => $image_link,
            'family' => $productfamily_full_path,
            'product' => $product_full_path,
            'folder'=> $folder_full_path,
            'view_count'=>'',
            'download_count'=>'',
            'weight'=>'',
            'insert_release'=> 0,
            'contains_file' => 0
         );
        
        

    }

    

    public function formatBytes($bytes, $precision = 2) {
        if ($bytes > pow(1024,3)) return round($bytes / pow(1024,3), $precision)."GB";
        else if ($bytes > pow(1024,2)) return round($bytes / pow(1024,2), $precision)."MB";
        else if ($bytes > 1024) return round($bytes / 1024, $precision)."KB";
        else return ($bytes)." B";
    }

    public function DownloadS3File( Request $request, $family, $product, $folder, $file, $tagid){
        $ip_address = $request->ip();
        $referer = $request->headers->get('referer');
        if(!empty($tagid)){
            
            if ( Release::where('etag_id', $tagid)->exists()) {
                $Release = Release::where('etag_id', $tagid)->first();
                $info = pathinfo($Release->s3_path);
                $file_name = $info['basename'];
                if($Release->s3_path != 's3_path' ){
                    
                    $file_url = $Release->s3_path;

                   if($Release->is_new){
                        //$file_url_new = str_replace('https://s3.us-west-2.amazonaws.com/releases-qa.aspose.com/', '', $file_url);
                        //$file_url_new = str_replace('https://s3-us-west-2.amazonaws.com/aspose.files/', '', $file_url_new);
                        //$signedurl =  $this->getPreSignedUrl($file_url_new, $Release->is_new);
                        $signedurl = $file_url;
                   }else{
                        $AWS_BUCKET = env('AWS_BUCKET');
                        $file_url_new = str_replace('https://s3-us-west-2.amazonaws.com/'.$AWS_BUCKET.'/', '', $file_url);
                        $signedurl =  $this->getPreSignedUrl($file_url_new, $Release->is_new);
                   }
                   if($signedurl){
                        //gecho  $signedurl;  exit;
                        $this->UpdateDownloadCount($Release->id);
                        $this->DownloadHistoryEntry($Release, $ip_address, $referer); 
                        header('Content-Type: application/octet-stream');
                        header("Content-Transfer-Encoding: Binary"); 
                        header("Content-disposition: attachment; filename=\"".$file_name."\""); 
                        readfile($signedurl);
                         
                   }else{
                        $this->UpdateDownloadCount($Release->id);
                        $this->DownloadHistoryEntry($Release, $ip_address, $referer);  
                        header('Content-Type: application/octet-stream');
                        header("Content-Transfer-Encoding: Binary"); 
                        header("Content-disposition: attachment; filename=\"".$file_name."\""); 
                        readfile($file_url);
                        
                   }

                }else{ // 
                    $file_url = $Release->s3_path;
                    echo "Failed to Download Try Again....";
                }
                exit;
            }else{
                echo "file not exists";
            }
        }
    }


    public function getPreSignedUrl($key, $is_new){

        $expiryInMinutes = 60;
        if(!$is_new){
            $AWS_ACCESS_KEY_ID = env('AWS_ACCESS_KEY_ID');
            $AWS_SECRET_ACCESS_KEY = env('AWS_SECRET_ACCESS_KEY');
            $AWS_DEFAULT_REGION = env('AWS_DEFAULT_REGION');
           // $AWS_BUCKET = 'aspose.files';
            $AWS_BUCKET = env('AWS_BUCKET');
        }else{
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
                    'secret' => $AWS_SECRET_ACCESS_KEY ,
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
    
    public function UpdateDownloadCount($ID){
        $Release = Release::find($ID);
        $Release->download_count = $Release->download_count + 1;
        $Release->save();
    }
    public function DownloadHistoryEntry($Release, $ip_address, $referer){
           
        if (Auth::check()) {
            $posted_by_name = Auth::user()->name ;
            $Email =  Auth::user()->email ;
            $IsCustomer = 0;
        }else{
            $posted_by_name = $ip_address ;
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
            'IsCustomer' =>$IsCustomer,
            'IPAddress'=> $ip_address,
            'UrlReferrer'=> $referer,
            'UserName'=> $posted_by_name,
            'TimeStamp'=> date('Y-m-d H:i:s'),

        ]);
        
    }

    public function managefiles(Request $request){
        
        $filter_productfamily = $request->filter_productfamily;
        $filter_product = $request->filter_product;
        $filter_folder = $request->filter_folder;
        
        $filter_productfamily = parse_url($filter_productfamily, PHP_URL_PATH);
        $filter_productfamily = rtrim($filter_productfamily, '/');

        $filter_product = parse_url($filter_product, PHP_URL_PATH);
        $filter_product = rtrim($filter_product, '/');


         $filter_folder = parse_url($filter_folder, PHP_URL_PATH);
         $filter_folder = rtrim($filter_folder, '/');
         $filter_folder = explode('/', $filter_folder);
         $filter_folder = end($filter_folder); 
        
        //print_r($filter_productfamily . ' --- ' . $filter_product . ' ---- ' . $filter_folder);
       
        ;
        $DropDownContent = $this->GetDropDownContent();
        $familySelected = "";
        $current_child_products = array();
        
        
        if(isset($request->filter_productfamily)){
            $familySelected = $request->filter_productfamily;
            $current_child_products = $this->getchildnodeslist($request->filter_productfamily);
            $folders = $this->getchildnodeslist($request->filter_product);
            if(!empty($folders)){
                if(count($folders) == 1){
                    $folders['New Releases'] = 'new-releases';
                }
            }

        }
        //echo "<pre>"; print_r($request->filter_product); echo "</pre>";
        //echo "<pre>"; print_r($folders); echo "</pre>";
        if(empty($folders)){
            $folders = array('Examples'=> 'examples', 'New Releases'=> 'new-releases', 'Resources'=> 'resources' );
        }
        //dd($current_child_products);
        //$productSelected = $request->filter_product;
        //$familySelected = $this->searchSingle($DropDownContent, 'url', $request->filter_productfamily);
        //$productSelected = $this->searchSingle($DropDownContent, 'url', $request->filter_product);
        $productSelected = $request->filter_product;
        $folderSelected = $request->filter_folder;

        
        // echo "<pre>"; print_r($familySelected); echo "</pre>"; 
        // echo "<pre>"; print_r($productSelected); echo "</pre>"; 
        // echo "<pre>"; print_r($folderSelected); echo "</pre>"; 

        
        
        $title = "";
        //$releases = Release::get();
        $releases = NULL;
        //$releases = Release::where('family', 'like', $filter_productfamily)->where('product', 'like', $filter_product)->where('folder', 'like', $filter_folder)->orderBy('id', 'desc')->paginate(15);
        
        if(isset($request->filter_folder)){
            $releases = DB::table('releases')
            ->where('family', 'like', '%' . $filter_productfamily . '%')
            ->where('product', 'like', '%' . $filter_product . '%')
            ->where('folder', 'like', '%' . $filter_folder . '%')
            ->orderBy('date_added', 'desc')->paginate(15);
        }
        //$releases = DB::table('releases')->paginate(15);
     //dd($folders);
        $amazon_s3_settings = AmazonS3Setting::where('id', 1)->first();
        $hugositeurl = $amazon_s3_settings->hugositeurl;
       return view('admin.upload.list', compact('DropDownContent', 'familySelected', 'productSelected', 'folderSelected', 'current_child_products', 'folders', 'releases', 'title', 'hugositeurl'));
    }


    public function manualreleaseuploadform(){
        $title = "Upload New Release/File Maunully";
        $DropDownContent = $this->GetDropDownContent();
        return view('admin.upload.manualreleaseupload', compact('DropDownContent', 'title'));
    }
   
    public function manualreleaseupload(Request $request){
        $host = $request->getHttpHost();
        if(!empty($request->all())){
            $upload_info = $this->UploadImageToS3MissingReleases($request->all(), 'new');
            //SET MANUALLY

            $upload_info['view_count'] = $request['view_count'];
            $upload_info['weight'] = $request['weight'];
            $upload_info['download_count'] = $request['download_count'];

            if(!empty($upload_info)){
                $mdfile =$this->generate_mdfile($request->all(), $upload_info);
                $res = $this->forceDownloadMdFile($mdfile['data'], $mdfile['file_name'], $mdfile['file_path'], $host );
                $this->addlogentry($mdfile['last_insert_update_id'], $res);
                return redirect('/admin/ventures/file/edit/' . $mdfile['last_insert_update_id'])->with('success','Published Successfully.' .$res);
            }else{
                return redirect('/admin/ventures/file/manage-files')->with('error','Published Failed.');
            }
        }
    }

    public function UploadImageToS3MissingReleases($data, $action){
        
        $insert_release = 1;
        if($action == 'update'){
            $release = Release::where('id', $data['edit_id'])->first();
            $insert_release = 0;
        }
        //exit;
        # get file from request object
        # get s3 object make sure your key matches with
        # config/filesystem.php file configuration
        $filetoupload = $data['file'];
        $productfamily  = $data['productfamily'];
        $product  = $data['product'];
        $folder_ini  = $data['folder'];

        $section_parent_path = "";
        $parent_path = "";

        if(strstr($folder_ini, '/')){
            $folder = rtrim($folder_ini, '/'); 
            //echo " 11111111111 ".$folder . " 11111111111 <BR>";
            //echo " 222222222222 ".$folder . " 222222222222 <BR>";
            $folder = substr(strrchr($folder, '/'), 1);
            //echo " 33333333333 ". $pppppp . " 33333333333 <BR>";
            $parent_path = parse_url($folder_ini, PHP_URL_PATH);
            $parent_path = rtrim($parent_path, '/');

            $section_parent_path = parse_url($product, PHP_URL_PATH);
            $section_parent_path = rtrim($section_parent_path, '/');
            $section_parent_path = ltrim($section_parent_path, '/');
        }else{
           // echo " BBBBBBBBBBB ".$folder . " BBBBBBBBBBB <BR>";
            $folder = $folder_ini ;
            $parent_path = parse_url($product, PHP_URL_PATH);
            $parent_path = rtrim($parent_path, '/');
        }
        

        $title = $data['title'];
        $productfamily_path = parse_url($productfamily, PHP_URL_PATH);
        $productfamily_path = rtrim($productfamily_path, '/'); 
        if($action == 'update'){
            /* POSSIBLE FIX TO MAINTAIN INTIAL FAIL NAME (KEEP DOWNLOAD MD FILE NAME SAME) */
                $initial_file_name = rtrim($release->folder_link, '/');
                $initial_file_name_array = explode("/", $initial_file_name); 
                $initial_file_name_only = end($initial_file_name_array); 
                $title_slug = str_replace(' ', '-', strtolower($initial_file_name_only));
            /* /POSSIBLE FIX TO MAINTAIN INTIAL FAIL NAME  */
        }else{
            $title_slug = str_replace(' ', '-', strtolower($title));
        }
        
       
       
       $special_case = array('all','examples', 'new-releases',  'resources' );
       $title_new_ = array('all'=>'all', 'examples'=>'Examples', 'new-releases'=>'New Releases',  'resources'=>'Resources');
       //echo $folder . ' ---------- folder ---  ';
       if(in_array($folder, $special_case)){
            $title_new_text = $title_new_[$folder];
        }else{
            $folder = rtrim($folder, '/'); 
            $path = explode("/", $folder); // splitting the path
            $last = end($path); 
            $title_new_text = ucfirst($last);
        }
      
       
      
       

       //echo "<pre> section_parent_path: "; print_r($section_parent_path); echo "</pre>"; 
       //echo "<pre> parent_path: "; print_r($parent_path); echo "</pre>"; 
       $folder_full_path = parse_url($folder, PHP_URL_PATH);
       $productfamily_full_path  = parse_url($productfamily, PHP_URL_PATH);
       $product_full_path = parse_url($product, PHP_URL_PATH);

       $download_link = "";
       $folder_link = "";
       if(!empty($section_parent_path)){
         $folder_link = '/'.$section_parent_path. '/'.$title_slug.'/';
         $download_link = $folder_link;
       }else{
         $folder_link = $parent_path."/".$folder.'/'.$title_slug . '/';
         $download_link = $folder_link;
       }

       
       /*$ETag = Str::random(40);
       $fileSizeBytes = "99877450";
       
       //$result = get_remote_file_info($filetoupload);
       $filetoupload = urldecode($filetoupload);
       $s3_file_headers = get_headers($filetoupload,1);
       if(!empty($s3_file_headers)){
        $ETag = trim($s3_file_headers['ETag'], '"');
        $fileSizeBytes = $s3_file_headers['Content-Length'];
       }*/

       $ETag = $data['etag_id'];
       $fileSize =  $data['content_length'];

        $s3_file_info = pathinfo($filetoupload);
        # rename file name to random name
        $file_name = $s3_file_info['basename'];
        # define s3 target directory to upload file to
        //$fileSize =  $this->formatBytes($fileSizeBytes, 2);

        $image_link = "/resources/img/random-file-icon.png";
        if($s3_file_info['extension'] == 'zip'){
            $image_link = "/resources/img/zip-icon.png";
        } else if($s3_file_info['extension'] == 'msi'){
            $image_link = "/resources/img/msi-icon.png";
        } else if($s3_file_info['extension'] == 'pdf'){
            $image_link = "/resources/img/pdf-icon.png";
        } else if($s3_file_info['extension'] == 'doc'){
            $image_link = "/resources/img/doc-icon.png";
        }
        return array(
            's3_path' => $filetoupload,
            'folder_link' => $folder_link,
            'download_link' => $download_link . $ETag,
            'parent_path' => ltrim($parent_path, '/'),
            'section_parent_path' => $section_parent_path,
            'title' => $title,
            'actual_file_name' =>$file_name,
            'title_slug' => $title_slug,
            'title_new_tag' => $title_new_text,
            'fileSize' => $fileSize,
            'etag_id' => $ETag,
            'image_link' => $image_link,
            'family' => $productfamily_full_path,
            'product' => $product_full_path,
            'folder'=> $folder_full_path,
            'insert_release'=> $insert_release,
            'view_count'=>'',
            'download_count'=>'',
            'weight'=>'',
            'contains_file' => 1
            );
    }
    public function destroy($id){
       // print_r($id);
       $res = Release::find($id)->delete($id);
       if($res){

        $posted_by_email = Auth::user()->email ;
        $log_msg = "Delete by " . $posted_by_email;
        $this->addlogentry($id, $log_msg);

        return response()->json([
            'success' => 1,
            'msg' => 'Release deleted successfully!'
        ]);
       }else{
        return response()->json([
            'success' => 0,
            'msg' => 'Failed to deleted Release! Try again later'
        ]);
       }
    }



    public function AddReleaseToSpreadsheetAndTranslate($mdfile, $host ){
       
        $translated_file_path = array();
        $spreadsheetId = env('SPREADSHEETID', '');
        if(empty($spreadsheetId)){
            dd("SPREADSHEETID missing in env");
        }
        // clear exsiing data in sheet
        $this->clearsheet($spreadsheetId);

        /*
        * We need to get a Google_Client object first to handle auth and api calls, etc.
        */
        $configJson = base_path().'/service-account.json';
        $client = new \Google_Client();
        $client->setApplicationName('Releases Transaltion App');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($configJson);
        /*
        * With the Google_Client we can get a Google_Service_Sheets service object to interact with sheets
        */
        $sheets = new \Google_Service_Sheets($client);


        
        $range = 'A1:M';
        $rows = $sheets->spreadsheets_values->get($spreadsheetId, $range, ['majorDimension' => 'ROWS']);
        $transalted_data = $rows['values'];

        if(count($transalted_data)){
            $Count = count($transalted_data);
            $row = $Count + 1;
    
            $Final_Array_To_Csv = array();
            
            $intro_text = array( 
                $mdfile['sheet_folder_link'], 
                "intro_text", 
                $mdfile['sheet_intro_text'], 
                '=GOOGLETRANSLATE(C'.$row.',"en","de")',
                '=GOOGLETRANSLATE(C'.$row.',"en","el")',
                '=GOOGLETRANSLATE(C'.$row.',"en","es")',
                '=GOOGLETRANSLATE(C'.$row.',"en","fr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","id")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ja")',
                '=GOOGLETRANSLATE(C'.$row.',"en","pt")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ru")',
                '=GOOGLETRANSLATE(C'.$row.',"en","tr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","zh")',
            );
    
            array_push($Final_Array_To_Csv, $intro_text);
            $row = $row + 1; // next row
    
            $folder_name = array( 
                $mdfile['sheet_folder_link'], 
                "folder_name",  
                $mdfile['sheet_folder_name'], 
                '=GOOGLETRANSLATE(C'.$row.',"en","de")',
                '=GOOGLETRANSLATE(C'.$row.',"en","el")',
                '=GOOGLETRANSLATE(C'.$row.',"en","es")',
                '=GOOGLETRANSLATE(C'.$row.',"en","fr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","id")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ja")',
                '=GOOGLETRANSLATE(C'.$row.',"en","pt")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ru")',
                '=GOOGLETRANSLATE(C'.$row.',"en","tr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","zh")',
            );
    
            array_push($Final_Array_To_Csv, $folder_name);
            $row = $row + 1; // next row
    
            $title = array( 
                $mdfile['sheet_folder_link'], 
                "title",  
                $mdfile['sheet_title'],  
                '=GOOGLETRANSLATE(C'.$row.',"en","de")',
                '=GOOGLETRANSLATE(C'.$row.',"en","el")',
                '=GOOGLETRANSLATE(C'.$row.',"en","es")',
                '=GOOGLETRANSLATE(C'.$row.',"en","fr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","id")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ja")',
                '=GOOGLETRANSLATE(C'.$row.',"en","pt")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ru")',
                '=GOOGLETRANSLATE(C'.$row.',"en","tr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","zh")',
            );
    
            array_push($Final_Array_To_Csv, $title);
            $valueRange = new \Google_Service_Sheets_ValueRange();
            $valueRange->setValues($Final_Array_To_Csv);
            $range = 'Sheet1!A1:A';
            $conf = ["valueInputOption" => "USER_ENTERED"];
            $sheets->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $conf);
    
            $translated_file_path = $this->ReadSheetData($mdfile, $spreadsheetId);
        }
        return $translated_file_path;

    }

    public function debuggingsheet(){
        $host ="admindemo.groupdocs";
        $transalted_md_files = $this->testingcommit();
        echo "file content added to sheet";
        exit;
        //$res = $this->AddTranslatedFilesToRepo($transalted_md_files, $host );
        //echo $res;
    }


    public function testingcommit(){
        $translated_file_path = array();
        $spreadsheetId = env('SPREADSHEETID', '');
        $this->clearsheet($spreadsheetId);
        /*
        * We need to get a Google_Client object first to handle auth and api calls, etc.
        */
        $configJson = base_path().'/service-account.json';
        $client = new \Google_Client();
        $client->setApplicationName('Releases Transaltion App');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($configJson);
        /*
        * With the Google_Client we can get a Google_Service_Sheets service object to interact with sheets
        */
        $sheets = new \Google_Service_Sheets($client);


        
        $range = 'A1:M';
        $rows = $sheets->spreadsheets_values->get($spreadsheetId, $range, ['majorDimension' => 'ROWS']);
        $transalted_data = $rows['values'];

$mdfile =  array(
'sheet_title'=> 'Downloads ---New Releases-test-new-release',
'sheet_description' => 'test new release Short Description',
'sheet_intro_text' => 'test new release Short Description',
'sheet_folder_name' => 'test new release',
'sheet_folder_link' => '/classification/net/new-releases/test-new-release/',
'data' => '---

title: "Downloads ---New Releases-test-new-release-level1"
description: " "
keywords: ""
page_type: single_release_page
folder_link: "/total/net/new-releases/test-new-release-level1/"
folder_name: "test new release level1"
download_link: "/total/net/new-releases/test-new-release-level1/65e8c484ca1d819ba2e6888956bfd03c-9-1688"
download_text: "Download"
intro_text: "Short Description test new release level1"
image_link: "/resources/img/zip-icon.png"
download_count: " 15/11/2022 Downloads: 1  Views: 1 "
file_size: "File Size: 127.91MB"
parent_path: "total/net"
section_parent_path: "total/net"

tags: "test"
release_notes_url: "test"
weight: 14

---

{{<< Releases/ReleasesWapper >>}}
    {{<< Releases/ReleasesHeading H2txt="test new release level1" imagelink="/resources/img/zip-icon.png">>}}
    {{<< Releases/ReleasesButtons >>}}
    {{<< Releases/ReleasesSingleButtons text="Download" link="/total/net/new-releases/test-new-release-level1/65e8c484ca1d819ba2e6888956bfd03c-9-1688" >>}}
    {{<< Releases/ReleasesSingleButtons text="Support Forum" link="https://forum.aspose.com/c/total" >>}}
    {{<< Releases/ReleasesButtons >>}}
    {{<< Releases/ReleasesFileArea >>}}
    {{<< Releases/ReleasesHeading h4txt="File Details">>}}
    {{<< Releases/ReleasesDetailsUl >>}}
        {{<< Common/li >>}} Downloads: {{<< /Common/li >>}}
        {{<< Common/li class="downloadcount" id="dwn-update-65e8c484ca1d819ba2e6888956bfd03c-9-1688" >>}} 1 {{<< /Common/li >>}}
        {{<< Common/li >>}} File Size: {{<< /Common/li >>}}
        {{<< Common/li id="size-update-65e8c484ca1d819ba2e6888956bfd03c-9-1688" >>}} 127.91MB {{<< /Common/li >>}}

        {{<< Common/li >>}} Date Added: {{<< /Common/li >>}}
        {{<< Common/li id="added-update-65e8c484ca1d819ba2e6888956bfd03c-9-1688" >>}}15/11/2022 {{<< /Common/li >>}}
    {{<< /Releases/ReleasesDetailsUl >>}}

    {{<< Releases/ReleasesFileFeatures >>}}
        <h4>Release Notes</h4><div><a href="test">test</a></div>
    {{<< /Releases/ReleasesFileFeatures >>}}
    {{<< Releases/ReleasesFileFeatures >>}}
        <h4>Description</h4><div class="HTMLDescription">Short Description test new release level1</div>
    {{<< /Releases/ReleasesFileFeatures >>}}
    {{<< /Releases/ReleasesFileArea >>}}
{{<< /Releases/ReleasesWapper >>}}'
);
        
        if(count($transalted_data)){
            $Count = count($transalted_data);
            $row = $Count + 1;
    
            $Final_Array_To_Csv = array();
            
            $intro_text = array( 
                $mdfile['sheet_folder_link'], 
                "intro_text", 
                $mdfile['sheet_intro_text'], 
                '=GOOGLETRANSLATE(C'.$row.',"en","de")',
                '=GOOGLETRANSLATE(C'.$row.',"en","el")',
                '=GOOGLETRANSLATE(C'.$row.',"en","es")',
                '=GOOGLETRANSLATE(C'.$row.',"en","fr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","id")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ja")',
                '=GOOGLETRANSLATE(C'.$row.',"en","pt")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ru")',
                '=GOOGLETRANSLATE(C'.$row.',"en","tr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","zh")',
            );
    
            array_push($Final_Array_To_Csv, $intro_text);
            $row = $row + 1; // next row
    
            $folder_name = array( 
                $mdfile['sheet_folder_link'], 
                "folder_name",  
                $mdfile['sheet_folder_name'], 
                '=GOOGLETRANSLATE(C'.$row.',"en","de")',
                '=GOOGLETRANSLATE(C'.$row.',"en","el")',
                '=GOOGLETRANSLATE(C'.$row.',"en","es")',
                '=GOOGLETRANSLATE(C'.$row.',"en","fr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","id")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ja")',
                '=GOOGLETRANSLATE(C'.$row.',"en","pt")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ru")',
                '=GOOGLETRANSLATE(C'.$row.',"en","tr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","zh")',
            );
    
            array_push($Final_Array_To_Csv, $folder_name);
            $row = $row + 1; // next row
    
            $title = array( 
                $mdfile['sheet_folder_link'], 
                "title",  
                $mdfile['sheet_title'],  
                '=GOOGLETRANSLATE(C'.$row.',"en","de")',
                '=GOOGLETRANSLATE(C'.$row.',"en","el")',
                '=GOOGLETRANSLATE(C'.$row.',"en","es")',
                '=GOOGLETRANSLATE(C'.$row.',"en","fr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","id")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ja")',
                '=GOOGLETRANSLATE(C'.$row.',"en","pt")',
                '=GOOGLETRANSLATE(C'.$row.',"en","ru")',
                '=GOOGLETRANSLATE(C'.$row.',"en","tr")',
                '=GOOGLETRANSLATE(C'.$row.',"en","zh")',
            );
    
            array_push($Final_Array_To_Csv, $title);
            $valueRange = new \Google_Service_Sheets_ValueRange();
            $valueRange->setValues($Final_Array_To_Csv);
            $range = 'Sheet1!A1:A';
            $conf = ["valueInputOption" => "USER_ENTERED"];
            $sheets->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $conf);
    
            $translated_file_path = $this->ReadSheetData($mdfile, $spreadsheetId);
        }
        return $translated_file_path;
    }

    public function clearsheet_admin_manual_translate($spreadsheetId){

        /*
        * We need to get a Google_Client object first to handle auth and api calls, etc.
        */
        $configJson = base_path().'/service-account.json';
        $client = new \Google_Client();
        $client->setApplicationName('Releases Transaltion App');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($configJson);
        /*
        * With the Google_Client we can get a Google_Service_Sheets service object to interact with sheets
        */
        $sheets = new \Google_Service_Sheets($client);
        $range_delete = 'A2:O'; 
        $requestBody = new \Google_Service_Sheets_ClearValuesRequest();
        $response = $sheets->spreadsheets_values->clear($spreadsheetId, $range_delete, $requestBody);

    }

    public function clearsheet($spreadsheetId){

        /*
        * We need to get a Google_Client object first to handle auth and api calls, etc.
        */
        $configJson = base_path().'/service-account.json';
        $client = new \Google_Client();
        $client->setApplicationName('Releases Transaltion App');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($configJson);
        /*
        * With the Google_Client we can get a Google_Service_Sheets service object to interact with sheets
        */
        $sheets = new \Google_Service_Sheets($client);
        $range_delete = 'A2:M'; 
        $requestBody = new \Google_Service_Sheets_ClearValuesRequest();
        $response = $sheets->spreadsheets_values->clear($spreadsheetId, $range_delete, $requestBody);

    }

    public function ReadSheetData($mdfile, $spreadsheetId){

        $response = array();
        $content_path = ( storage_path() . '/app/public/mdfiles/content');

        $target_languages_array = array("de", "el", "en", "es", "fr", "id", "ja", "pt", "ru", "tr", "zh");
        $source_lanaguage = "en";
        /*
        * We need to get a Google_Client object first to handle auth and api calls, etc.
        */
        $configJson = base_path().'/service-account.json';
        $client = new \Google_Client();
        $client->setApplicationName('Releases Transaltion App');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($configJson);
        /*
        * With the Google_Client we can get a Google_Service_Sheets service object to interact with sheets
        */
        $sheets = new \Google_Service_Sheets($client);

        $release_common_pre_translated_array = $this->parse_csv_file(base_path().'/csv/common.csv');
        $final_release_common_pre_translated_array = array();
        foreach($release_common_pre_translated_array as $key=>$single){
            $final_release_common_pre_translated_array[$single['text']] = $single;
        }

        $range = 'A1:M';
        $rows = $sheets->spreadsheets_values->get($spreadsheetId, $range, ['majorDimension' => 'ROWS']);
        $transalted_data = $rows['values'];
        $transalted_data_arranged = $this->arrange_sheet_data($transalted_data);
   
        if(!empty($transalted_data_arranged)){
            foreach($target_languages_array as $target_lanaguage ){

                foreach($transalted_data_arranged as $keyfilepath=>$translatedcontent){
                
                    $md_filepath = $keyfilepath;
                    $actual_file_path = rtrim($md_filepath,"/");
                    $actual_file_path = ltrim($actual_file_path,"/");
                    $en_mdfile = $content_path . "/". $source_lanaguage ."/" . $actual_file_path. ".md";
                    $en_mdfile = str_replace("//", '/', $en_mdfile);
                    
                    $segments = explode('/', trim(parse_url($actual_file_path, PHP_URL_PATH), '/'));
                    $numSegments = count($segments);
                    $filename = $segments[$numSegments - 1];
                    $filepath = implode("/", $segments);
                    $plorp = substr(strrchr($filepath,'/'), 1);
                    $filepath = substr($filepath, 0, - strlen($plorp)); 

                    $target_file_src = $content_path . "/". $target_lanaguage . '/' . $filepath . ''.  $filename. ".md"; 
                    $target_file_src = str_replace("//", '/', $target_file_src);

                            
                    //if (!file_exists( $target_file_src )) {

                        $contents = $mdfile['data'];
                        if( $target_lanaguage != 'en'){ // transalation in case of english

                            if( isset($translatedcontent['title']) && !empty($translatedcontent['title']['text'])  && strlen(trim($translatedcontent['title']['text']))  >=4 ){
                                $title_text_translated = $translatedcontent['title'][$target_lanaguage];
                                $pattern = '#^title:.*"(.*)"#m';
                                $replacement = 'title: "'.$title_text_translated.'"';
                                $contents =  preg_replace($pattern, $replacement, $contents);
                            }

                            if( isset($translatedcontent['intro_text']) && !empty($translatedcontent['intro_text']['text']) && strlen(trim($translatedcontent['intro_text']['text']) ) >=4){
                                $intro_text_translated = $translatedcontent['intro_text'][$target_lanaguage];
                                $pattern = '#^intro_text: "(.*)"$#mi';
                                $replacement = 'intro_text: "'.$intro_text_translated.'"';
                                $contents =  preg_replace($pattern, $replacement, $contents);
                            }
                        
                            if( isset($translatedcontent['folder_name']) && !empty($translatedcontent['folder_name']['text']) && strlen(trim($translatedcontent['folder_name']['text']) ) >=4){
                                $folder_name_text_translated = $translatedcontent['folder_name'][$target_lanaguage];
                                $pattern = '#^folder_name: "(.*)"$#m';
                                $replacement = 'folder_name: "'.$folder_name_text_translated.'"';
                                $contents =  preg_replace($pattern, $replacement, $contents);

                                $folder_name_text_translated = $translatedcontent['folder_name'][$target_lanaguage];
                                $pattern = '#H2txt="(.*?)"#';
                                $replacement = 'H2txt="'.$folder_name_text_translated.'"';
                                $contents =  preg_replace($pattern, $replacement, $contents);
                            }

                            if( isset($translatedcontent['intro_text']) && !empty($translatedcontent['intro_text']['text']) && strlen(trim($translatedcontent['intro_text']['text']) ) >=4 ){
                                $description_text_translated = $translatedcontent['intro_text'][$target_lanaguage];
                                $pattern = '#<div class="HTMLDescription">(.*)</div>#';
                                $replacement = '<div class="HTMLDescription">'.$description_text_translated.'</div>';
                                $contents =  preg_replace($pattern, $replacement, $contents);
                            }

                            foreach($final_release_common_pre_translated_array as $keypattern=>$common){
                                $translated_replacemnt = $common[$target_lanaguage];
                                if( $keypattern == 'Description'){
                                    $contents = preg_replace("#<h4>Description</h4>#",  "<h4>$translated_replacemnt</h4>" , $contents);
                                } else if( $keypattern == 'File Size'){
                                    $contents = preg_replace("#File Size: {{#",  "$translated_replacemnt: {{" , $contents);
                                }else{
                                    $contents = preg_replace("/$keypattern/",  $translated_replacemnt, $contents);
                                }
                            }
                        }

                        if (!file_exists( $content_path . "/". $target_lanaguage . '/' . $filepath )) { // folderm not exists
                            mkdir($content_path . "/". $target_lanaguage . '/' . $filepath, 0755, true);
                        }
                        touch($target_file_src);
                        file_put_contents($target_file_src, $contents);
                        $response[] = $target_file_src;
                    //}
                }
            }
        }


        return $response; // return paths of translated file
    }

    public function arrange_sheet_data($transalted_data){
        $cols = array_shift( $transalted_data );
        $new_offersvalues = array();
        $new_offersvalues_final = array();
        foreach( $transalted_data as $k=>$v )
        {
            $new_offersvalues[ $k ] = array();
    
            foreach( $v as $k2=>$v2 )
            {
                $new_offersvalues[ $k ][ $cols[ $k2 ] ] = $v2;
            }
    
         unset( $transalted_data[ $k ] );
        }
    
    
        if(!empty($new_offersvalues)){
          foreach($new_offersvalues as $target_lanaguage ){
            $new_offersvalues_final[$target_lanaguage['path']][$target_lanaguage['lable']] = $target_lanaguage;
          }
        }
        
    
        return $new_offersvalues_final;
    }

    public function parse_csv_file($csvfile) {
        $csv = Array();
        $rowcount = 0;
        if (($handle = fopen($csvfile, "r")) !== FALSE) {
            $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
            $header = fgetcsv($handle, $max_line_length);
            $header_colcount = count($header);
            while (($row = fgetcsv($handle, $max_line_length)) !== FALSE) {
                $row_colcount = count($row);
                if ($row_colcount == $header_colcount) {
                    $entry = array_combine($header, $row);
                    $csv[] = $entry;
                }
                else {
                    //error_log("csvreader: Invalid number of columns at line " . ($rowcount + 2) . " (row " . ($rowcount + 1) . "). Expected=$header_colcount Got=$row_colcount");
                    return null;
                }
                $rowcount++;
            }
            //echo "Totally $rowcount rows found\n";
            fclose($handle);
        }
        else {
            //error_log("csvreader: Could not read CSV \"$csvfile\"");
            return null;
        }
        return $csv;
    }
    
}
//Examples
//New Releases
//Resources
