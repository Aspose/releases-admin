<?php

namespace App\Http\Controllers\Admin;
use App\Http\Requests\CreateUser;
use App\Http\Requests\UpdateUser;
use App\Http\Requests\ResetPassword;
use App\Http\Controllers\Controller;
use App\Models\Release;
use App\Models\Role;
use App\Notifications\WelcomeEmailNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\File;

class ManageTotalNetReleasesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $family_name = "net";
        if(!empty($request->get('family'))){
            $family_name = $request->get('family');

        }
        //DB::enableQueryLog();
        $zipfolderpath = "/zipfiles/total-$family_name/" .date('y-m-d');
        $zipfolderpath_fullpath = ( storage_path() . '/app/public'. $zipfolderpath);

        //Remove all files in folder prevent duplicate
       // array_map( 'unlink', array_filter((array) glob($zipfolderpath_fullpath."/*") ) );

       if($family_name == 'java'){
        $netrelease = Release::select(DB::raw('t.*'))
            ->from(DB::raw("(SELECT * FROM releases WHERE `product` LIKE '%/java/%' AND product != '/total/java/' AND filename LIKE '%zip%' ORDER BY weight DESC) t"))
            ->groupBy('t.product')
            ->get();
       }else{
        $netrelease  = Release::where('product' , 'LIKE', '%/'.$family_name.'/%')
        //->where('s3_path' , 'LIKE', '%.msi%')
        ->where('product' , '!=', '/total/'.$family_name.'/') // exclude total
        ->where(function($query) {
            $query->where('filetitle' , 'LIKE', '%dll%')
                ->orWhere('filetitle', 'LIKE', '%DLL%')
                ->orWhere('folder_link', 'LIKE', '%dll%');
        })
        ->groupBy('product')
        ->orderBy('weight', 'desc')->get();
       // $quries = DB::getQueryLog();
       }
        
  
       // dd($quries);
      
        $progress =false;
       /*if(!empty($request->get('generate')) && $request->get('generate') == 'true'){
            $progress = true;
            $zipfolderpath = "/zipfiles/" .date('y-m-d');
        foreach($netrelease as $release){
            echo $release->s3_path;
            echo "<br>";
            $s3filename = str_replace('https://s3-us-west-2.amazonaws.com/aspose.files/', '', $release->s3_path);
            $s3_file_info = pathinfo($release->s3_path);
            # rename file name to random name
            $local_file_name = $s3_file_info['basename'];
            echo"Remote file name: ".$s3filename;
            echo "<br>";
            echo "Local file Name: " . $local_file_name; 
            echo "<hr>";
            //Storage::disk('public')->put($zipfolderpath.'/'.$local_file_name , Storage::disk('s3')->get($s3filename));
            
        }
        //$this->createzip($zipfolderpath);
        //Storage::disk('public')->put($zipfolderpath.'/amjadtest-001.txt', Storage::disk('s3')->get('2022/06/24/test-am-file.txt'));

       }*/
       $family_name = ucfirst($family_name);
        $title = "Manage Total.$family_name Release";
        return view('admin.totalnetrelease.index', compact('title', 'netrelease', 'progress' , 'zipfolderpath_fullpath', 'zipfolderpath'));
    }


    public function uploadfileform(Request $request){
        //dd($request->all());
        if(!empty($request->get('path'))){
            $path = $request->get('path');
            $title = "Upload File to .$path ";
            $existing_files_full_path = storage_path('app/public'.$path);
            $files = scandir($existing_files_full_path);
            return view('admin.totalnetrelease.uploadform', compact('title', 'path', 'files'));
        }else{
            dd('path missing');
        }
    }
    public function removefilesinpath(Request $request){
        if(!empty($request->get('path'))){
            $path = $request->post('path');
           
            $existing_files_full_path = storage_path('app/public'.$path);
            $files = scandir($existing_files_full_path);
           // dd($files);
            foreach($files as $file){
                if($file != "." && $file != ".." ) {
                    unlink($existing_files_full_path .'/'. $file);
                    echo $existing_files_full_path .'/'. $file .' Deleted <br>';
                }
                
            }
            return back()
            ->with('success','Files deleted.')
            ->with('file',"");
            
        }else{
            dd('path missing');
        }
    }
    public function fileUploadPost(Request $request){
        $request->validate([
            'file' => 'required',
        ]);
        $fileName = $request->file->getClientOriginalName();  
        $path = $request->path;
         $uploadpath = storage_path('app/public'.$path);
        $request->file->move($uploadpath, $fileName);
        return back()
            ->with('success','You have successfully upload file.')
            ->with('file',$fileName);
    }

    /*public function createzip($zipfolderpath)
    {
        $path = ( storage_path() . '/app/public'. $zipfolderpath);
        $zipfilename = "new-zip". time();
        $zip_file = $path.'/'.$zipfilename.'.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($files as $name => $file)
        {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                if( !stristr($name, '.DS_Store')){
                $filePath     = $file->getRealPath();
                $actual_file_name = basename($file);
                $zip->addFile($filePath, $actual_file_name);
                }
            }
        }
        $zip->close();

        echo $zip_file;  // new created zip file path

        $AWS_ACCESS_KEY_ID = env('AWS_ACCESS_KEY_ID');
        $AWS_SECRET_ACCESS_KEY = env('AWS_SECRET_ACCESS_KEY');
        $AWS_DEFAULT_REGION = env('AWS_DEFAULT_REGION');
        $AWS_BUCKET = env('AWS_BUCKET');

        $s3filepath = date('y/m/d').'/'.$zipfilename.'.zip';
        
        $filesorcepath = "'$zip_file'";
        $s3filepath = "'$s3filepath'";
        $bucket = "'$AWS_BUCKET'";
        $s3Key = "'$AWS_ACCESS_KEY_ID'";
        $s3Secret = "'$AWS_SECRET_ACCESS_KEY'";

        echo $filesorcepath;
        echo "<br>";
        echo $s3filepath;
        echo "<br>";
        echo $bucket;
        echo "<br>";
        echo $s3Key;
        echo "<br>";
        echo $s3Secret;

        $public_path = getcwd();
        $bash_script_path = str_replace('/public', '/.scripts/', $public_path );
        chdir($bash_script_path);

        echo "<hr> -----------";
       // $output = shell_exec('./uploadzip.sh '.$filesorcepath.' '.$s3filepath.' '.$bucket.' '.$s3Key.' '.$s3Secret.' ');
       // print_r($output);
        echo "-------------- <hr> ";
        chdir($public_path);
    }*/




    public function downloadandcompress(Request $request){
        if(!empty($request->ids) && !empty($request->newzipname)){
            $newzipname = $request->newzipname;
            $ids = $request->ids;
            //$zipfolderpath = "/zipfiles/" .date('y-m-d');
            //$zipfolderpath_fullpath = ( storage_path() . '/app/public'. $zipfolderpath);
            
            $zipfolderpath = $request->zipfolderpath;
            $zipfolderpath_fullpath = $request->zipfolderpath_fullpath;
            $s3_links = Release::select('s3_path')->whereIn('id', $ids)->get(); 
           // print_r($s3_links);
            if($s3_links){
                foreach($s3_links as $release){
                    // download 
                    //echo $release->s3_path;
                    $s3filename = str_replace('https://s3-us-west-2.amazonaws.com/aspose.files/', '', $release->s3_path);
                    $s3_file_info = pathinfo($release->s3_path);
                    $local_file_name = $s3_file_info['basename'];
                    $res = Storage::disk('public')->put($zipfolderpath.'/'.$local_file_name , Storage::disk('s3')->get($s3filename));
                 }
                 $final_array = array(
                    'srcfile'=> $zipfolderpath_fullpath,
                    'zipname'=> $newzipname,
                );
                $json =  json_encode($final_array);
                return $json;
            }
             
        }

    }

    public function compressfiles(Request $request)
    {
        if(!empty($request->srcfile) && !empty($request->newzipname) ){
            $srcfile = $request->srcfile;
            $zipfilename = $request->newzipname;
            $zipfolderpath = $request->zipfolderpath;
            $path = $srcfile;
            $zip_file = $path.'/'.$zipfilename.'.zip';
            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            foreach ($files as $name => $file)
            {
                // We're skipping all subfolders
                if (!$file->isDir()) {
                    if( !stristr($name, '.DS_Store')){
                    $filePath     = $file->getRealPath();
                    $actual_file_name = basename($file);
                    $zip->addFile($filePath, $actual_file_name);
                    }
                }
            }
            $zip->close();
           // echo $zip_file;  // new created zip file path
            $download_path = ( storage_path() . '/app/public'.$zipfolderpath.'/'.$zipfilename.'.zip');
            $final_array = array(
                'zip_file'=> $zip_file,
                'download_path'=> $download_path
            );
            $json =  json_encode($final_array);
            return $json;
        }else{
            echo "srcfile or newzipname missing";
        }
    }
    

    public function progressdownload(Request $request){
        $zipfolderpath = $request->zipfolderpath;
        $zipfolderpath_fullpath = $request->zipfolderpath_fullpath;
        $directory = $zipfolderpath_fullpath;
        // Simple Call: List all files
        //var_dump(getDirContents('/xampp/htdocs/WORK'));
        // Regex Call: List md files only
        $downloaded =  ($this->getDirContents($directory, ''));
        $final_array = array(
            'downloaded'=> $downloaded,
        );
        $json =  json_encode($final_array);
        return $json;
    }

   
    

    public function getDirContents($dir, $filter = '', &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                if (empty($filter) || preg_match($filter, $path)) $results[] = $path;
            } elseif ($value != "." && $value != "..") {
                $this->getDirContents($path, $filter, $results);
            }
        }

        return count($results);
    }


    public function uploadziptos3(Request $request){
        $host = $request->getHttpHost();
        if(!empty($request->srcfile) ){
            $zipfolderpath_fullpath = $request->zipfolderpath_fullpath;
            if(in_array($host, array('admindemo.aspose', 'admindemo.groupdocs'))){  //local
                // $AWS_ACCESS_KEY_ID = "AKIATVR5O2PZLLT7UT6B";
                // $AWS_SECRET_ACCESS_KEY = "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";
                // $AWS_DEFAULT_REGION = "us-west-2";
                // $AWS_BUCKET = "releases-qa.aspose.com";
                $AWS_ACCESS_KEY_ID = env('AWS_ACCESS_KEY_ID');
                $AWS_SECRET_ACCESS_KEY = env('AWS_SECRET_ACCESS_KEY');
                $AWS_DEFAULT_REGION = env('AWS_DEFAULT_REGION');
                $AWS_BUCKET = env('AWS_BUCKET');
            }else{
                $AWS_ACCESS_KEY_ID = env('AWS_ACCESS_KEY_ID');
                $AWS_SECRET_ACCESS_KEY = env('AWS_SECRET_ACCESS_KEY');
                $AWS_DEFAULT_REGION = env('AWS_DEFAULT_REGION');
                $AWS_BUCKET = env('AWS_BUCKET');
            }

            $s3_file_info = pathinfo($request->srcfile);
            $s3_new_file_name = $s3_file_info['basename'];

            $s3filepath = date('y/m/d').'/'.$s3_new_file_name; // bucket file path
            $s3filepath_ini = $s3filepath;
            
            $zip_file = $request->srcfile; // local src file full path
            
            $filesorcepath = "'$zip_file'";
            $s3filepath = "'$s3filepath'";
            $bucket = "'$AWS_BUCKET'";
            $s3Key = "'$AWS_ACCESS_KEY_ID'";
            $s3Secret = "'$AWS_SECRET_ACCESS_KEY'";
            $region = "'$AWS_DEFAULT_REGION'";


            if(in_array($host, array('admindemo.aspose', 'admindemo.groupdocs'))){  //local
                $public_path = getcwd();
                $bash_script_path = str_replace('/public', '/.scripts/', $public_path );
                chdir($bash_script_path);
                //$output = shell_exec('./uploadzip.sh '.$filesorcepath.' '.$s3filepath.' '.$bucket.' '.$s3Key.' '.$s3Secret.' '.$region.' ');
                chdir($public_path);
            }else{
                $output = shell_exec('/var/www/scripts/uploadzip.sh '.$filesorcepath.' '.$s3filepath.' '.$bucket.' '.$s3Key.' '.$s3Secret.' '.$region.' ');
            }


           
            //remove all files in folder prevent dupicate
           // array_map( 'unlink', array_filter((array) glob($zipfolderpath_fullpath."/*") ) );

            $final_array = array(
                's3_file_link'=> "Copy Url : https://s3.us-west-2.amazonaws.com/$AWS_BUCKET/$s3filepath_ini",
                'bashresponse'=> $output
            );
            $json =  json_encode($final_array);
            return $json;

        }
    }

}
