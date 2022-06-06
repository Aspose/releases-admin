<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddNewProductFamily;
use AWS\CRT\HTTP\Response;
use App\Models\AmazonS3Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManageProductFamilies extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Add New Product Family";
        $DropDownContent = $this->GetDropDownContent();
        return view('admin.productfamilies.index', compact('DropDownContent', 'title'));
    }

    public function addnew(AddNewProductFamily $request)
    {
        $host = $request->getHttpHost();
        if (!empty($request->all())) {
            $response = $this->generate_mdfile($request->all());
            $res= $this->AddFileToRepo($response['data'], $response['file_name'] , $response['file_path'], $host );
            return redirect('/admin/products/manage-families')->with('info', 'Product Family.' .$res);
        }
    }






    public function generate_mdfile($data)
    {

        $FamilyName = $data['FamilyName'];
        $metatitle  = $data['metatitle'];
        $metadescription  = $data['metadescription'];
        $Keywords  = $data['Keywords'];

        if(empty($metatitle)){
            $metatitle = $FamilyName;
        }
        if(empty($metadescription)){
            $metadescription = $FamilyName;
        }
        $FamilyName = $data['FamilyName'];
        $foldername = $data['foldername'];
        $forumLink = $data['txtForumLink'];
        $githubimage = $data['githubimage'];
        $weight = $data['SortOrder'];

        /*
            ---
            title: "3D Graphics APIs for Developers | Aspose.3D Product Family "
            description: "Download .NET & Java on-premise libraries to create, edit & convert 3D files. No 3D modeling software required. Work with geometry, scene hierarchy, share or split meshes, animate objects, add target camera and more. "
            keywords: "3D API "
            family_listing_page_title: "Aspose.3D Product Family"
            family_listing_page_description: ""
            family_listing_page_iconurl: ""
            family_listing_page_selfHosted: ""
            family_listing_page_type: "4"
            family_listing_page_venture: "4"
            family_listing_page_package: ""
            homepage_github_image: "https://aspose.github.io/img/aspose/aspose-3d.png"
            weight:  14
            ---

            {{< dbToolbar link="https://forum.aspose.com/c/3d" linktext=" Support Forum " >}}

            {{< ProductListingWrapper family="3d">}}
        */

        $md_file_content = "";
        $md_file_content .= "---";
        $md_file_content .= "\n";
        $md_file_content .= "\n";
        $md_file_content .= "title: \"$metatitle\"";
        $md_file_content .= "\n";
        $md_file_content .= "description:  \"$metadescription\"";
        $md_file_content .= "\n";
        $md_file_content .= "keywords:  \"$Keywords\"";
        $md_file_content .= "\n";
        $md_file_content .= "family_listing_page_title:  \"$FamilyName\"";
        $md_file_content .= "\n";
        $md_file_content .= "homepage_github_image:  \"$githubimage\"";
        $md_file_content .= "\n";
        $md_file_content .= "weight: " . "$weight";
        $md_file_content .= "\n";
        $md_file_content .= "\n";

        $md_file_content .= "---";
        $md_file_content .= "\n";
        $md_file_content .= "\n";

        $md_file_content .= '{{< dbToolbar link="' . $forumLink . '" linktext="Support Forum" >}}';
        $md_file_content .= "\n";
        $md_file_content .= "\n";
        $md_file_content .= '{{< ProductListingWrapper family="' . $foldername . '">}}';
        $md_file_content .= "\n";
        $md_file_content .= "\n";
        $md_file_content .= "\n";

        //echo $md_file_content;


        return array(
            'file_path' => "/" . $foldername,
            'file_name' => "_index.md",
            'data' => $md_file_content
        );
    }

    public function AddFileToRepo( $content, $filename, $file_path, $host) {
        
        $file_to_commit = $filename; 
        $filename_info = $file_path.'/'.$file_to_commit;
        Storage::put('/public/newproductfamily/content' . $filename_info, $content);
        $download_path = ( storage_path() . '/app/public/newproductfamily/content'.$filename_info);
        $hugo_content_path = "content" . $file_path;
        //echo $hugo_content_path; exit;
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
                    $commit_msg = "'new Product Family added'";
                    if(in_array($host, array('admindemo.aspose', 'admindemo.groupdocs'))){  //local
            
                        $public_path = getcwd();
                        $bash_script_path = str_replace('/public', '/.scripts/', $public_path );
                        chdir($bash_script_path);
                        // echo "<pre> public_path "; print_r($public_path);echo "</pre>"; 
                        // echo "<pre> download_path "; print_r($download_path);echo "</pre>"; 
                        // echo "<pre> hugo_content_path "; print_r($hugo_content_path);echo "</pre>"; 
                        // echo "<pre> file_to_commit "; print_r($file_to_commit);echo "</pre>"; 
                        // echo "<pre> bash script path "; print_r($bash_script_path);echo "</pre>"; 
                        // echo "<pre> shell script "; print_r('./addmdfile.sh '.$download_path.' '.$hugo_content_path.' '.$file_to_commit.' '.$local_clone_repo_path.' '.$repo_url.' ');echo "</pre>"; 
                        // //exit;
                        $output = shell_exec('./addmdfile.sh '.$download_path.' '.$hugo_content_path.' '.$file_to_commit.' '.$local_clone_repo_path.' '.$repo_url.' '.$commit_msg.' ');
                        chdir($public_path);
                   
                    }else{ //prod/stage
                        $output = shell_exec('/var/www/scripts/addmdfile.sh '.$download_path.' '.$hugo_content_path.' '.$file_to_commit.' '.$local_clone_repo_path.' '.$repo_url.' '.$commit_msg.' ');
                    }
                    return $output;
                    //echo "<pre> file_to_commit "; print_r($output);echo "</pre>"; exit;
                    
                }
            }else{
                return "empty in .env LOCAL_REPO_CLONE_PATH";
            }
            /* ===================== /COMMIT FILE ============= */
            

            

            /* ===================== DELETE FILE ============= */
            //unlink($download_path);
            /* ===================== DELETE FILE ============= */
           
           // exit;
            
        } else {
            //echo('File not found.');
            return 'Markdown File Not Generated > '. $download_path;
        }

    }

    public function GetDropDownContent()
    {
        $amazon_s3_settings = AmazonS3Setting::where('id', 1)->first();
        $hugositeurl = $amazon_s3_settings->hugositeurl;
        //$hugositeurl =  $hugositeurl . '/index.json';
        $hugositeurl =  $hugositeurl. '/index.json?Return_content='.time();
        //"https://releases-qa.aspose.com/index.json"
        $data = json_decode(file_get_contents($hugositeurl), true);
        return $data;
    }
}
