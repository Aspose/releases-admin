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
                //echo "<pre>"; print_r($mdfile); echo "</pre>"; exit;
                $res = $this->forceDownloadMdFile($mdfile['data'], $mdfile['file_name'], $mdfile['file_path'], $host );
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
        // $release->filetitle = trim($request->filetitle);
        // $release->description = trim($request->description);
        // $release->release_notes_url = trim($request->release_notes_url);
           $release->date_added = trim($request->date_added);
           $release->s3_path = trim($request->s3_path);
           $release->filesize = trim($request->filesize);
           $release->filename = trim($request->filename);
           $release->folder_link = trim($request->folder_link);
        // $release->family = trim($request->family);
        // $release->product = trim($request->product);
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
            'data'=> $md_file_content
      );
    }

    public function forceDownloadMdFile( $content, $filename, $file_path, $host) {
        
        $file_to_commit = $filename.".md"; 
        $filename_info = $file_path.'/'.$file_to_commit;
        Storage::put('/public/mdfiles/content' . $filename_info, $content);
        $download_path = ( storage_path() . '/app/public/mdfiles/content'.$filename_info);
        $hugo_content_path = "content" . $file_path .'/';
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
                        $file_url_new = str_replace('https://s3-us-west-2.amazonaws.com/aspose.files/', '', $file_url);
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
            $AWS_ACCESS_KEY_ID = 'AKIAJ3IWQHR2VPPUU4AA';
            $AWS_SECRET_ACCESS_KEY = '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';
            $AWS_DEFAULT_REGION = 'us-west-2';
            $AWS_BUCKET = 'aspose.files';
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
}
//Examples
//New Releases
//Resources
