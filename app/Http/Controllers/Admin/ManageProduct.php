<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddNewProduct;
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

class ManageProduct extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Add New Product";
        $DropDownContent = $this->GetDropDownContent();
        return view('admin.products.index', compact('DropDownContent', 'title'));
    }

    public function addnew(AddNewProduct $request)
    {
        $host = $request->getHttpHost();
        if (!empty($request->all())) {
            //dd($request->all());
            $response = $this->generate_mdfile($request->all());
            //dd($response);
            $res= $this->AddFileToRepo($response['data'], $response['file_name'] , $response['file_path'], $host );
            return redirect('/admin/products/manage-allproducts')->with('info', 'Product.' .$res);
        }
    }






    public function generate_mdfile($data)
    {

        $productfamily = $data['productfamily'];
        $productfamily_path = parse_url($productfamily, PHP_URL_PATH);
        $productfamily_path = rtrim($productfamily_path, '/'); 
        $productfamily_path = ltrim($productfamily_path, '/'); 
       // echo $productfamily_path; 
       
        $productfoldername = $data['productfoldername']; // folder name
        $productname = $data['productname']; // 

        $metatitle  = $data['metatitle'];
        $metadescription  = $data['metadescription'];
        $Keywords  = $data['Keywords'];

        if(empty($metatitle)){
            $metatitle = $productname;
        }
        if(empty($metadescription)){
            $metadescription = $productname;
        }

        
        $listingpagedesc = $data['listingpagedesc'];
        $forumlink = $data['forumlink'];
        $listingpageimagelink = $data['listingpageimagelink'];
        $pagecontent = $data['pagecontent'];

        $pagecontent  = str_replace('###PRODUCTNAME_PLACEHOLDER####', $productname, $pagecontent);
        $weight = $data['SortOrder'];

        
/*
            ---
title: "Java Gameware & CAD Library | Aspose.3D for Java"
description: "Download standalone Gameware and CAD API to manipulate 3D files. API supports most of the popular 3D file formats and applications can create, read, convert & modify files easily. "
keywords: "3D Java Library "
family_listing_page_title: "Aspose.3D for Java"
family_listing_page_description: "Aspose.3D for Java API is built to create, edit, manipulate and save 3D formats. It empowers Java applications to connect with 3D documents without installing any software package on the computer. Aspose.3D for Java API assist developers to model and create massive worlds in games, superb scenes for design visualization, and engage virtual reality experiences.

The API is user friendly and saves time and money than creating a similar solution from scratch."
family_listing_page_iconurl: "https://www.aspose.cloud/templates/aspose/App_Themes/V3/images/3d/272x272/aspose_3d-for-java.png"
family_listing_page_selfHosted: "1"
family_listing_page_type: "1"
family_listing_page_venture: "4"
family_listing_page_package: "62"
weight:  2
---

{{< dbToolbar link="https://forum.aspose.com/c/3d" linktext=" Support Forum " >}}


{{< ProductPageWrapper >}}

<!-- ProductPageContent-->
{{< Common/wrapper class="col-md-12" >}}
{{< Common/wrapper class="panel-body downloadfilebody" >}}
{{< Common/h1 text="Aspose.3D for Java" >}}
{{< Common/paragraph>}}
3D API enables Java applications to connect with 3D document formats to convert, build, alter and control the substances.
{{< Common/h2 text="Gameware &amp; CAD Library"  >}} {{< Common/ul>}}
    {{< Common/li >}} {{< Common/link href="https://docs.aspose.com/3d/java/export-scene-to-compressed-amf-format/" text="Export scene to compressed AMF"  >}}. {{< /Common/li >}}

   {{< Common/li >}} Import, create, customize, &amp; save 3D scenes. {{< /Common/li >}}

   {{< Common/li >}} {{< Common/link href="https://docs.aspose.com/3d/java/mesh/" text="Split &amp; triangulate Mesh"  >}}. {{< /Common/li >}}

   {{< Common/li >}} Add animation while setting the camera. {{< /Common/li >}}

   {{< Common/li >}} Perform element formatting using 3D transformations. {{< /Common/li >}}

   {{< Common/li >}} {{< Common/link href="https://docs.aspose.com/3d/java/working-with-linear-extrusion/" text="Perform Linear Extrusion"  >}}. {{< /Common/li >}}

   {{< Common/li >}} Manipulate 3D objects &amp; 3D models. {{< /Common/li >}}
 {{< /Common/ul>}}

{{< /Common/paragraph>}}
{{< Common/hr >}}
{{< Common/h4 text="Download Aspose.3D from Maven"  >}}
{{< Common/paragraph class="package-instructions">}}
You can easily use Aspose.3D for Java directly from a {{< Common/link href="https://repository.aspose.com/repo/com/aspose/aspose-3d/" text="Maven"  >}} based project by adding following configurations to the pom.xml.
 {{< /Common/paragraph>}}
{{< consolebox/consoleboxwrapper id="repository" >}}
       {{< consolebox/textarea id="repository" >}} <repository>
    <id>AsposeJavaAPI</id>
    <name>Aspose Java API</name>
    <url>http://repository.aspose.com/repo/</url>
</repository> {{< /consolebox/textarea >}}
{{< /consolebox/consoleboxwrapper >}}
{{< consolebox/consoleboxwrapper id="dependency" >}}
       {{< consolebox/textarea id="dependency" >}} <dependency>
     <groupId>com.aspose</groupId>
     <artifactId>aspose-3d</artifactId>
     <version>22.3</version>
</dependency> {{< /consolebox/textarea >}}
{{< /consolebox/consoleboxwrapper >}}
{{< Common/h4 text="Release Notes"  >}}
{{< Common/link href="https://docs.aspose.com/3d/java/aspose-3d-for-java-22-3-release-notes/" text="https://docs.aspose.com/3d/java/aspose-3d-for-java-22-3-release-notes/"  >}}
{{< /Common/wrapper >}}
{{< /Common/wrapper >}}

<!-- /ProductPageContent-->



<!-- ReleasesListProductPage-->
   {{< Releases/ReleasesListProductPage shownested="false"  directdownload="true" family="3d" product="java" >}}
<!-- /ReleasesListProductPage-->

{{< /ProductPageWrapper >}}


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
        $md_file_content .= "family_listing_page_title:  \"$productname\"";
        $md_file_content .= "\n";
        $md_file_content .= "family_listing_page_description:  \"$listingpagedesc\"";
        $md_file_content .= "\n";
        $md_file_content .= "family_listing_page_iconurl:  \"$listingpageimagelink\"";
        $md_file_content .= "\n";
        $md_file_content .= "weight: " . "$weight";
        $md_file_content .= "\n";
        //$md_file_content .= "draft: true";
        $md_file_content .= "\n";

        $md_file_content .= "---";
        $md_file_content .= "\n";
        $md_file_content .= "\n";

        $md_file_content .= '{{< dbToolbar link="' . $forumlink . '" linktext="Support Forum" >}}';
        $md_file_content .= "\n";
        $md_file_content .= "\n";
        $md_file_content .= '{{< ProductPageWrapper >}}';
        $md_file_content .= "\n";
        $md_file_content .= '<!-- ProductPageContent-->';
        $md_file_content .= "\n";
        $md_file_content .= '{{< Common/wrapper class="col-md-12" >}}';
        $md_file_content .= "\n";
        $md_file_content .= '{{< Common/wrapper class="panel-body downloadfilebody" >}}';
        $md_file_content .= "\n";
       
        // ACTUAL CONTENT HERE
    
        $md_file_content .= $pagecontent;


        $md_file_content .= "\n";
        $md_file_content .= "{{< /Common/wrapper >}}";
        $md_file_content .= "\n";
        $md_file_content .= "{{< /Common/wrapper >}}";
        $md_file_content .= "\n";
        $md_file_content .= '<!-- /ProductPageContent-->';
        $md_file_content .= "\n";

        $md_file_content .= "\n";
        $md_file_content .= "<!-- ReleasesListProductPage-->";
        $md_file_content .= "\n";
        $md_file_content .= '{{< Releases/ReleasesListProductPage shownested="false"  directdownload="true" family="'.$productfamily_path.'" product="'.$productfoldername.'" >}}';
        $md_file_content .= "\n";
        $md_file_content .= "<!-- /ReleasesListProductPage-->";
        $md_file_content .= "\n";

        $md_file_content .= "\n";
        $md_file_content .= '{{< /ProductPageWrapper >}}';
        //echo $md_file_content;


        return array(
            'file_path' => "/" . $productfamily_path.'/'.$productfoldername,
            'file_name' => "_index.md",
            'data' => $md_file_content
        );
    }

    public function AddFileToRepo( $content, $filename, $file_path, $host) {
        
        $file_to_commit = $filename; 
        $filename_info = $file_path.'/'.$file_to_commit;
        Storage::put('/public/newproduct/content' . $filename_info, $content);
        $download_path = ( storage_path() . '/app/public/newproduct/content'.$filename_info);
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
                    $posted_by_email = Auth::user()->email ;
                    $commit_msg = "'new Product added by $posted_by_email'";
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
