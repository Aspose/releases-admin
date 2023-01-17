<?php
namespace App\Classes;

class PrepareSheetData
{
     /**
     * Create a new message instance.
     *
     * @return void
     */
    // public function __construct($details)
    // {
    //  //
    // }

public function prepare_data_for_sheet($javahomepage, $linenum, $line, $place_holders_array){
    //echo $linenum . " ==== ". $line . "<hr>";
    $data = array();
    
    if($this->startendsymbols($linenum, $line, true)){
        $data = array(
            'src_line_text'=> "{{_STARTEND_}}",
            'place_holder'=> "{{_STARTEND_}}",
            'text_not_to_translate'=> "",
            'text_to_translate'=> "",
            'add_to_sheet' => 0,
            'type' => 'startendsymbols',
            'line_num' => $linenum,
            'line' => $place_holders_array['{{_STARTEND_}}']
        );
    } else if($this->banner_image($linenum, $line, true)){
        $data = array(
            'src_line_text'=> "{{_BANNER_}}",
            'place_holder'=> "{{_BANNER_}}",
            'text_not_to_translate'=> "",
            'text_to_translate'=> "",
            'add_to_sheet' => 0,
            'type' => 'banner_image',
            'line_num' => $linenum,
            'line' => $place_holders_array['{{_BANNER_}}']
        );
    } else if($this->code_snippt($linenum, $line, true)){
        $data = array(
            'src_line_text'=> "{{_CODE_}}",
            'place_holder'=> "{{_CODE_}}",
            'text_not_to_translate'=> "",
            'text_to_translate'=> "",
            'add_to_sheet' => 0,
            'type' => 'code_snippt',
            'line_num' => $linenum,
            'line' => $place_holders_array['{{_CODE_}}']
        );
    }else if($this->meta_title($linenum, $line, true)){
        $data = $this->meta_title($linenum, $line, false);
    }else if($this->family_listing_page_title($linenum, $line, true)){
        $data = $this->family_listing_page_title($linenum, $line, false);  
    }else if($this->family_listing_page_description($linenum, $line, true)){
        $data = $this->family_listing_page_description($linenum, $line, false); 
    }else if($this->meta_description($linenum, $line, true)){
        $data = $this->meta_description($linenum, $line, false);  
    }else if($this->meta_keywords($linenum, $line, true)){
        $data = $this->meta_keywords($linenum, $line, false);  
    }else if($this->intro_text($linenum, $line, true)){
        $data = $this->intro_text($linenum, $line, false); 
    }else if($this->download_text($linenum, $line, true)){
        $data = $this->download_text($linenum, $line, false);     
    //}else if($this->file_size($linenum, $line, true)){
        //$data = $this->file_size($linenum, $line, false);       
    }else if($this->folder_name($linenum, $line, true)){
        $data = $this->folder_name($linenum, $line, false);  
    }else if($this->exclude_shortcodes($linenum, $line, true)){
        $data = $this->exclude_shortcodes($linenum, $line, false);  
    }else if($this->commonexcludetranslation($linenum, $line, true)){
        $data = $this->commonexcludetranslation($linenum, $line, false);
    }else if($this->gistexclude($linenum, $line, true)){
        $data = $this->gistexclude($linenum, $line, false);
    }else if($this->keywordsandreleases($javahomepage, $linenum, $line, true)){
        $data = $this->keywordsandreleases($javahomepage, $linenum, $line, false);
    }else if($this->md_links($linenum, $line, true)){
        $data = $this->md_links($linenum, $line, false);
    }else if($this->shortcodeheadingswithimage($linenum, $line, true)){
        $data = $this->shortcodeheadingswithimage($linenum, $line, false);
    }else if($this->shortcodeheadings($linenum, $line, true)){
        $data = $this->shortcodeheadings($linenum, $line, false);
    }else if($this->htmllink($linenum, $line, true)){
        $data = $this->htmllink($linenum, $line, false);
    }else if($this->divtextcontent($linenum, $line, true)){
        $data = $this->divtextcontent($linenum, $line, false);
    }else if($this->htmlheadingcontent($linenum, $line, true)){
        $data = $this->htmlheadingcontent($linenum, $line, false);
    }else{
        $data = $this->simpletext($linenum, $line, false);
    }
    
    return $data;
}
  
 public function simpletext($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $placehoder = "";
    $meta_name = "simpletext";


    
    if($check){
        $is_check = true;
    }else{
        if(strlen(trim($line)) > 2){
            $return_array = array(
                'src_line_text'=> $line,
                'place_holder'=> "{{_LINE_".$linenum."_}}",
                'text_not_to_translate'=> "",
                'text_to_translate'=> $line,
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => "{{_LINE_".$linenum."_}}"
            );
        }else{
            $return_array = array(
                'src_line_text'=> $line,
                'place_holder'=> "{{_LINE_".$linenum."_}}",
                'text_not_to_translate'=> "",
                'text_to_translate'=> $line,
                'add_to_sheet' => 0,
                'type' => "emptyline",
                'line_num' => $linenum,
                'line' => "{{_LINE_".$linenum."_}}"
            );
        }
        
    }
        
    
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}
public function meta_title($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = 'title:';
    $reg = '#^(title:)(.*)#m'; 
    $placehoder = "{{_META_TITLE_}}";
    $meta_name = "meta_title";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function linktitle($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = 'linktitle:';
    $reg = '#^(linktitle:)(.*)#m'; 
    $placehoder = "{{_LINK_TITLE_}}";
    $meta_name = "link_title";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                //'text_to_translate'=> trim($text_matches[2][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => 0,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}
public function family_listing_page_title($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = 'family_listing_page_title:';
    $reg = '#^(family_listing_page_title:)(.*)#m'; 
    $placehoder = "{{_FAMILY_LISTING_PAGE_TITLE_}}";
    $meta_name = "meta_family_listing_page_title";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}
public function family_listing_page_description($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = 'family_listing_page_description:';
    $reg = '#^(family_listing_page_description:)(.*)#m'; 
    $placehoder = "{{_FAMILY_LISTING_PAGE_DESCRIPTION_}}";
    $meta_name = "meta_family_listing_page_description";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}


public function meta_description($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = 'description:';
    $reg = '#^(description:)(.*)#m'; 
    $placehoder = "{{_META_DESCRIPTION_}}";
    $meta_name = "meta_description";
    $add_to_sheet = 0;
    if(!empty($line) && strlen($line) > 3){
        $add_to_sheet = 1;
    }
    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                //'text_to_translate'=> trim($text_matches[2][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => $add_to_sheet,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function meta_keywords($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = 'keywords:';
    $reg = '#^(keywords:)(.*)#m'; 
    $placehoder = "{{_META_KEYWORDS_}}";
    $meta_name = "meta_keywords";
    $add_to_sheet = 1;
    if(empty($line) || strlen($line) <= 13){
        $add_to_sheet = 0;
        return  false;
    }
    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                //'text_to_translate'=> trim($text_matches[2][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function intro_text($linenum, $line, $check){

    $return_array = array();
    $is_check = false;
    $format = 'intro_text:';
    $reg = '#^(intro_text:)(.*)#m'; 
    $placehoder = "{{_INTRO_TEXT_}}";
    $meta_name = "intro_text";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                //'text_to_translate'=> trim($text_matches[2][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }

}
public function download_text($linenum, $line, $check){

    $return_array = array();
    $is_check = false;
    $format = 'download_text:';
    $reg = '#^(download_text:)(.*)#m'; 
    $placehoder = "{{_DOWNLOAD_TEXT_}}";
    $meta_name = "download_text";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                //'text_to_translate'=> trim($text_matches[2][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }

}
public function file_size($linenum, $line, $check){

    $return_array = array();
    $is_check = false;
    $format = 'file_size:';
    $reg = '#^(file_size:)(.*)#m'; 
    $placehoder = "{{_FILESIZE_}}";
    $meta_name = "file_size";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                //'text_to_translate'=> trim($text_matches[2][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }

}


public function folder_name($linenum, $line, $check){

    $return_array = array();
    $is_check = false;
    $format = 'folder_name:';
    $reg = '#^(folder_name:)(.*)#m'; 
    $placehoder = "{{_FILE_NAME_}}";
    $meta_name = "folder_name";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> trim($text_matches[1][0]),
                //'text_to_translate'=> trim($text_matches[2][0]),
                'text_to_translate'=> str_replace('"', "", trim($text_matches[2][0])),
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => preg_replace($reg, "$format \"$placehoder\" ", $line)
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }

}

public function startendsymbols($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg = '#{{_STARTEND_}}#m';
    $placehoder = "";
    $meta_name = "";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function code_snippt($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg = '#{{_CODE_}}#m';
    $placehoder = "";
    $meta_name = "";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}


public function banner_image($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg = '#{{_BANNER_}}#m';
    $placehoder = "";
    $meta_name = "";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function commonexcludetranslation($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    //$re = '/^-\s[^,*][^.$]{2,32}$/m';
    $reg = '#^(layout:)(.*)|^(file_size:)(.*)|^(publishdate:)(.*)|^(publishDate:)(.*)|^(type:)(.*)|^(forumLink:)(.*)|^(productLink:)(.*)|^(dataFolder:)(.*)|^(packages_refs:)(.*)|^(dataFolders:)(.*)|^(weight:)(.*)|^(homepage_package_link:)(.*)|^(homepage_package_type:)(.*)|^(family_listing_page_package:)(.*)|^(categories:)(.*)|^(family_listing_page_type:)(.*)|^(family_listing_page_venture:)(.*)|^(family_listing_page_selfHosted:)(.*)|^(family_listing_page_iconurl:)(.*)|^(keywords:)(.*)|(- fundamentals)(.*)|^(linktitle:)(.*)|^(page_type:)(.*)|^(folder_link:)(.*)|^(download_link:)(.*)|^(image_link:)(.*)|^(download_count:)(.*)|^(parent_path:)(.*)|^(section_parent_path:)(.*)|^(tags:)(.*)|^(release_notes_url:)(.*)|^(weight:)(.*)#m';
    $placehoder = "";
    $meta_name = "";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
              if( $text_matches[0][0] == '- fundamentals'){
                  $text_matches[0][0] = '  - fundamentals';
              }
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> $text_matches[1][0],
                'text_to_translate'=> $text_matches[2][0],
                'add_to_sheet' => 0,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => $text_matches[0][0]
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function exclude_shortcodes($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    //$re = '/^-\s[^,*][^.$]{2,32}$/m';
    $reg = '#({{<.*?(|/)Releases/ReleasesWapper.*(|\s)>}}$)$|({{<.*?(|/)Releases/ReleasesFileFeatures.*(|\s)>}}$)|({{<.*?(|/)Releases/ReleasesButtons.*(|\s)>}}$)|({{<.*?(|/)Releases/ReleasesFileArea.*(|\s)>}}$)|({{<.*?(|/)Releases/ReleasesDetailsUl.*>(|\s)}}$)|({{<.*?(|/)Common/li.*>(|\s)}}$)|{{<(|\s)(|/)Common/wrapper .*?(|\s)>}}|{{%(|\s)(|/)Releases/ReleasesFileFeatures(|\s)%}}|{{<(|\s)Releases/ReleasesSingleButtons .*(|\s)>}}$#m';
    $placehoder = "";
    $meta_name = "shortcode";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> $text_matches[0][0],
                'text_to_translate'=> "",
                'add_to_sheet' => 0,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => $text_matches[0][0]
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function gistexclude($linenum, $line, $check){
  $return_array = array();
  $is_check = false;
  $format = '';
  $reg = '#{{< gist .* >}}#m';
  $placehoder = "";
  $meta_name = "";
  
  $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
 
  if($res && !empty($text_matches)){
      if($check){
          $is_check = true;
      }else{
          $return_array = array(
              'src_line_text'=> $text_matches[0][0],
              'place_holder'=> $placehoder,
              'text_not_to_translate'=> $text_matches[0][0],
              'text_to_translate'=> $text_matches[0][0],
              'add_to_sheet' => 0,
              'type' => $meta_name,
              'line_num' => $linenum,
              'line' => $text_matches[0][0]
          );
      }
      
  }
  if($check){
      return $is_check;
  }else{
      return $return_array;
  }
}

public function keywordsandreleases($javahomepage, $linenum, $line, $check){
  if(!$javahomepage){
      return false;
  }
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg = '#^-{1}\s[^,*](.*)$(?<!\.)#m';
    $placehoder = "";
    $meta_name = "";

    $res = preg_match($reg, $line, $text_matches, PREG_OFFSET_CAPTURE, 0);
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            $return_array = array(
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> $text_matches[0][0],
                'text_to_translate'=> $text_matches[0][0],
                'add_to_sheet' => 0,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => $text_matches[0][0]
            );
        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}


public function md_links($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg ='/\[(.*?)\]\s*\(((?:.+?))\)/';
    $placehoder = "";
    $meta_name = "link";
    $links_array =[];
    $res = preg_match_all($reg, $line, $text_matches, PREG_SET_ORDER, 0);
    $line_text_to_translate = $line;
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            if(!empty($text_matches)){
                foreach($text_matches as $key=>$single){
                    $link_regx = $single[0];
                    $link_regx = str_replace("[","\[", $link_regx);
                    $link_regx = str_replace("]","\]", $link_regx);
                    $link_regx = str_replace("(","\(", $link_regx);
                    $link_regx = str_replace(")","\)", $link_regx);
                    $link_regx_final = "~" . $link_regx . "~m";
                    $line = preg_replace($link_regx_final, $linenum."_".$key, $line);
                    $links_array[$linenum."_".$key] = $single[0];
                }
            }

            $return_array = array(
                'src_line_text'=> $line_text_to_translate,
                'place_holder'=> "{{_LINE_".$linenum."_}}",
                'text_not_to_translate'=> "",
                'text_to_translate'=> $line,
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => "{{_LINE_".$linenum."_}}",
                'links_array' => serialize($links_array)
            );

        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function htmllink($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg ='#<a href=([^\"]*)>(.*)<\/a>#';
    $placehoder = "";
    $meta_name = "htmllink";
    $links_array =[];
    $res = preg_match_all($reg, $line, $text_matches, PREG_SET_ORDER, 0);
    $line_text_to_translate = $line;
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            if(!empty($text_matches)){
                foreach($text_matches as $key=>$single){
                    $link_regx = $single[0];
                    // $link_regx = str_replace("[","\[", $link_regx);
                    // $link_regx = str_replace("]","\]", $link_regx);
                    // $link_regx = str_replace("(","\(", $link_regx);
                    // $link_regx = str_replace(")","\)", $link_regx);
                    // $link_regx_final = "~" . $link_regx . "~m";
                    // $line = preg_replace($link_regx_final, $linenum."_".$key, $line);
                    // $links_array[$linenum."_".$key] = $single[0];
                }
            }

            $return_array = array(
                // 'src_line_text'=> $line_text_to_translate,
                // 'place_holder'=> "{{_LINE_".$linenum."_}}",
                // 'text_not_to_translate'=> "",
                // 'text_to_translate'=> $line,
                // 'add_to_sheet' => 0,
                // 'type' => $meta_name,
                // 'line_num' => $linenum,
                // 'line' => "{{_LINE_".$linenum."_}}",
                // 'links_array' => serialize($links_array)
                'src_line_text'=> $text_matches[0][0],
                'place_holder'=> $placehoder,
                'text_not_to_translate'=> $text_matches[0][0],
                'text_to_translate'=> $text_matches[0][0],
                'add_to_sheet' => 0,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => $text_matches[0][0]
            );

        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function shortcodeheadings($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg ='#{{< Releases/ReleasesHeading(|\s)(h[1,2,3,4,5,6]txt)=(|\s)"(.*?)"(|\s)>}}#mi';
    $placehoder = "";
    $meta_name = "shortcodeheadings";
    $res = preg_match_all($reg, $line, $text_matches, PREG_SET_ORDER, 0);
    $line_text_to_translate = $line;
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            if(!empty($text_matches)){
                foreach($text_matches as $key=>$single){
                    $link_regx = $single[0];
                    $text_not_to_translate = $single[2];
                    $text_to_translate = $single[4];
                    $pattern = '#'.$text_not_to_translate.'="(.*?)"#';
                    $replacement = ''.$text_not_to_translate.'="HeadingTxt"';
                    $HeadingTxt_Placeholder =  preg_replace($pattern, $replacement, $link_regx);
                    $links_array = $HeadingTxt_Placeholder;
                }
            }

            $return_array = array(
                'src_line_text'=> $line_text_to_translate,
                'place_holder'=> "{{_LINE_".$linenum."_}}",
                'text_not_to_translate'=> $text_not_to_translate,
                'text_to_translate'=> $text_to_translate,
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => "{{_LINE_".$linenum."_}}",
                'links_array' => $links_array
            );

        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function divtextcontent($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg ='#<div class="HTMLDescription">(.*?)</div>#';
    $placehoder = "";
    $meta_name = "divtextcontent";
    $res = preg_match_all($reg, $line, $text_matches, PREG_SET_ORDER, 0);
    $line_text_to_translate = $line;
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            if(!empty($text_matches)){
                foreach($text_matches as $key=>$single){
                    $link_regx = $single[0];
                    $text_not_to_translate = "";
                    $text_to_translate = $single[1];
                    $links_array = '<div class="HTMLDescription">DivContent</div>';
                }
            }

            $return_array = array(
                'src_line_text'=> $line_text_to_translate,
                'place_holder'=> "{{_LINE_".$linenum."_}}",
                'text_not_to_translate'=> $text_not_to_translate,
                'text_to_translate'=> $text_to_translate,
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => "{{_LINE_".$linenum."_}}",
                'links_array' => $links_array
            );

        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}
public function htmlheadingcontent($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg = '#<h[\d]>(.*)</h[\d]>#iUms';
    $placehoder = "";
    $meta_name = "htmlheadingcontent";
    $res = preg_match_all($reg, $line, $text_matches, PREG_SET_ORDER, 0);
    $line_text_to_translate = $line;
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            if(!empty($text_matches)){
                foreach($text_matches as $key=>$single){
                    $link_regx = $single[0];
                    $text_not_to_translate = $single[0];
                    $text_to_translate = $single[1];
                    $pattern = '#'.$text_to_translate.'#';
                    $replacement = 'HeadingText';
                    $HeadingTxt_Placeholder =  preg_replace($pattern, $replacement, $link_regx);

                    $links_array = $HeadingTxt_Placeholder;
                }
            }

            $return_array = array(
                'src_line_text'=> $line_text_to_translate,
                'place_holder'=> "{{_LINE_".$linenum."_}}",
                'text_not_to_translate'=> $text_not_to_translate,
                'text_to_translate'=> $text_to_translate,
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => "{{_LINE_".$linenum."_}}",
                'links_array' => $links_array
            );

        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}

public function shortcodeheadingswithimage($linenum, $line, $check){
    $return_array = array();
    $is_check = false;
    $format = '';
    $reg ='#{{< Releases/ReleasesHeading(|\s)(h[1,2,3,4,5,6]txt)=(|\s)"(.*?)"(|\s)imagelink="(.*)">}}#mi';
    $placehoder = "";
    $meta_name = "shortcodeheadingswithimage";
    $res = preg_match_all($reg, $line, $text_matches, PREG_SET_ORDER, 0);
    $line_text_to_translate = $line;
    if($res && !empty($text_matches)){
        if($check){
            $is_check = true;
        }else{
            if(!empty($text_matches)){
                foreach($text_matches as $key=>$single){
                    $link_regx = $single[0];
                    $text_not_to_translate = $single[2];
                    $text_to_translate = $single[4];
                    $pattern = '#'.$text_not_to_translate.'="(.*?)"#';
                    $replacement = ''.$text_not_to_translate.'="HeadingTxt"';
                    $HeadingTxt_Placeholder =  preg_replace($pattern, $replacement, $link_regx);
                    $links_array = $HeadingTxt_Placeholder;
                }
            }

            $return_array = array(
                'src_line_text'=> $line_text_to_translate,
                'place_holder'=> "{{_LINE_".$linenum."_}}",
                'text_not_to_translate'=> $text_not_to_translate,
                'text_to_translate'=> $text_to_translate,
                'add_to_sheet' => 1,
                'type' => $meta_name,
                'line_num' => $linenum,
                'line' => "{{_LINE_".$linenum."_}}",
                'links_array' => $links_array
            );

        }
        
    }
    if($check){
        return $is_check;
    }else{
        return $return_array;
    }
}
public function replace_translated_placeholders($javahomepage, $mdfile, $translatedcontent, $target_lanaguage){
  /*================================ GENERATER CONTENT FILE PLACEHODERS ================================*/
  $PLACEHODER_ARRAY = $this->getfilecontent($javahomepage, $mdfile, true);
  $MD_CONTENT_PLACEHOLDER = "";
  $re_heading = '/^#/m';
  foreach($PLACEHODER_ARRAY as $key=>$line){
      if( $line['type'] != 'emptyline'){
          $res_m = preg_match($re_heading, $line['text_to_translate'], $heading_matches, PREG_OFFSET_CAPTURE, 0);
          if(($res_m && !empty($heading_matches) || ($line['place_holder'] == '{{_STARTEND_}}' && $key != 0) || $line['place_holder'] == '{{_BANNER_}}')){
              $new_line_after = "\n";
              $new_line_before = "";
          }else{
              $new_line_after = "\n";
              $new_line_before = "";
          }
          if($res_m && !empty($heading_matches)){
              $new_line_before = "\n";
              $new_line_after = "\n";
          }
          if($line['type'] == 'link' ){
              $new_line_before = "\n";
              $new_line_after = "\n";
          }
          if($line['type'] == 'simpletext' ){
            $new_line_after = "\n";
            $new_line_before = "\n";
          }

          if($line['place_holder'] == '{{_CODE_}}'){
              $new_line_before = "\n\n";
              $new_line_before = "\n";
          }
          
      
          $MD_CONTENT_PLACEHOLDER .= $new_line_before . $line['line'] . $new_line_after;
      }
  }

  /*================================ /GENERATER CONTENT FILE PLACEHODERS ================================*/
  foreach($translatedcontent as $place_holder_key => $line){
      //shortcodeheadings
      if($line['type'] == 'shortcodeheadings'){
        $pattern = '#HeadingTxt#';
        $replacement = $line[$target_lanaguage];;
        $HeadingTxt_Placeholder_Translated =  preg_replace($pattern, $replacement, $line['replacement_array']);
        $MD_CONTENT_PLACEHOLDER = str_replace($place_holder_key,  $HeadingTxt_Placeholder_Translated, $MD_CONTENT_PLACEHOLDER);
     }

     //shortcodeheadingswithimage
     if($line['type'] == 'shortcodeheadingswithimage'){
        $pattern = '#HeadingTxt#';
        $replacement = $line[$target_lanaguage];;
        $HeadingTxt_Placeholder_Translated =  preg_replace($pattern, $replacement, $line['replacement_array']);
        $MD_CONTENT_PLACEHOLDER = str_replace($place_holder_key,  $HeadingTxt_Placeholder_Translated, $MD_CONTENT_PLACEHOLDER);
     }
     //divtextcontent

     if($line['type'] == 'divtextcontent'){
        $pattern = '#DivContent#';
        $replacement = $line[$target_lanaguage];;
        $HeadingTxt_Placeholder_Translated =  preg_replace($pattern, $replacement, $line['replacement_array']);
        $MD_CONTENT_PLACEHOLDER = str_replace($place_holder_key,  $HeadingTxt_Placeholder_Translated, $MD_CONTENT_PLACEHOLDER);
     }

     //htmlheadingcontent
     if($line['type'] == 'htmlheadingcontent'){
        $pattern = '#HeadingText#';
        $replacement = $line[$target_lanaguage];;
        $HeadingTxt_Placeholder_Translated =  preg_replace($pattern, $replacement, $line['replacement_array']);
        $MD_CONTENT_PLACEHOLDER = str_replace($place_holder_key,  $HeadingTxt_Placeholder_Translated, $MD_CONTENT_PLACEHOLDER);
     }

      //link_text_common
      if($line['type'] != 'link_text_common'){
          $translated_traget_language_text = $line[$target_lanaguage];
          $MD_CONTENT_PLACEHOLDER = str_replace($place_holder_key, $translated_traget_language_text, $MD_CONTENT_PLACEHOLDER);
          if($line['type'] == 'link'){
              $urls_replacement_array = unserialize($line['replacement_array']);
              if(!empty($urls_replacement_array)){
                  foreach($urls_replacement_array as $url_key => $url_value){
                      $MD_CONTENT_PLACEHOLDER = str_replace($url_key, $url_value, $MD_CONTENT_PLACEHOLDER);
                  }
              }
          }
      }
      
      
    

  }

  foreach($translatedcontent as $place_holder_key => $line){
      if($line['type'] == 'link_text_common'){
          //link_text_common
          $MD_CONTENT_PLACEHOLDER = str_replace($line['place_holder'], "[". $line[$target_lanaguage] . "]", $MD_CONTENT_PLACEHOLDER);
      }
  }


  return $MD_CONTENT_PLACEHOLDER;
}


public function fix_zh_specfic($translated_md_file){
  $translated_md_file = str_replace("-[", "- [", $translated_md_file); // -14_0（矩形） >> - 14_0（矩形）// space after -    
  return $translated_md_file;
}
public function fix_zh_ja_specfic($translated_md_file){

  $translated_md_file = str_replace("＃", "#", $translated_md_file); // ＃


  $re_nospace_chines = '~^(#+)(\S+)~m'; // ##矩形 > ## 矩形
  $res = preg_match_all($re_nospace_chines, $translated_md_file, $text_matches, PREG_SET_ORDER, 0);
   if($res && !empty($text_matches)){ // no space after - found
      foreach($text_matches as $match){
          if(strlen($match[0]) >= 5){
              $translated_md_file = str_replace($match[0], $match[1]. " ". $match[2], $translated_md_file);
          }
      }
  }
  return $translated_md_file;
}

public function getfilecontent($javahomepage, $new_file_to_trandslate, $return_placehoder){
        $file_handle = fopen($new_file_to_trandslate, 'r');
        $contents = fread($file_handle, filesize($new_file_to_trandslate));
        fclose($file_handle);

        $place_holders_array =[];
        $re_start_end = '#^---$#m';
        $re_code = '#^```(.*)```$#sm';
        //$re_banner = '#\[!\[banner\](.*)\]\(./\)$#m';
        $re_banner = '#\[!\[.*\](.*)\]\(./\)#m';
        $re_links = '/\[(.*?)\]\s*\(((?:.+?))\)/';
        $re_split = '/\s*(\n)/m';
        
        //echo $contents;
        //echo " ================================ =========================================";
        $se_res = preg_match($re_start_end, $contents, $sm_matches, PREG_OFFSET_CAPTURE, 0);
        if($se_res && !empty($sm_matches)){
            $place_holders_array['{{_STARTEND_}}'] = $sm_matches[0][0];
            $contents = preg_replace($re_start_end, "{{_STARTEND_}}", $contents);
        }
        $b_res = preg_match($re_banner, $contents, $banner_matches, PREG_OFFSET_CAPTURE, 0);
        if($b_res && !empty($banner_matches)){
            $place_holders_array['{{_BANNER_}}'] = $banner_matches[0][0];
            $contents = preg_replace($re_banner, "{{_BANNER_}}", $contents);
        }
        $m_res = preg_match($re_code, $contents, $code_matches, PREG_OFFSET_CAPTURE, 0);
        if($m_res && !empty($code_matches)){
            $place_holders_array['{{_CODE_}}'] = $code_matches[0][0];
            $contents = preg_replace($re_code, "{{_CODE_}}", $contents);
        }
        
        $l_res = preg_match_all($re_links, $contents, $links_matches, PREG_SET_ORDER, 1);
        $links_matches_unique = array();
        if($l_res && !empty($links_matches)){
            foreach($links_matches as $key=>$single){
                $links_matches_unique[$single[1]] = array(
                    'place_holder' => "[" .$single[1]. "]",
                    "type" => "link_text_common",
                    //'md_link' => $single[0],
                    'replacement_array' => "",
                    'text_to_translate' => preg_replace("/[^A-Za-z0-9 ]/", '', $single[1]),
                    //'link' => $single[2],
                    
                );
            }
        }
        $sections = preg_split($re_split, $contents);
        $formatted_data_array = [];
        foreach($sections as $key=>$section){
            $formatted_data_array[] = $this->prepare_data_for_sheet($javahomepage, $key, $section, $place_holders_array);
        }
        
        if($return_placehoder){
            return $formatted_data_array;
        }


        $data_to_sheet = array();
        $place_holder = "";
        $re_heading = '/^#/m';
        foreach($formatted_data_array as $key=>$line){
            
            $res_m = preg_match($re_heading, $line['text_to_translate'], $heading_matches, PREG_OFFSET_CAPTURE, 0);
            if(($res_m && !empty($heading_matches) || ($line['place_holder'] == '{{_STARTEND_}}' && $key != 0) || $line['place_holder'] == '{{_BANNER_}}' || $line['place_holder'] == '{{_CODE_}}')){
                $new_line = "\n\n";
            }else{
                $new_line = "\n";
            }
            $place_holder .= $line['line'] . $new_line;

            if($line['add_to_sheet']){
                if(isset($line['links_array'])){
                    $replacement_array = $line['links_array'];
                }else{
                    $replacement_array = "";
                }
                $data_to_sheet[$line['place_holder']] = array(
                    "place_holder" => $line['place_holder'],
                    "type" => $line['type'],
                    "replacement_array" => $replacement_array,
                    "text_to_translate" => $line['text_to_translate'],
                );
            }
        }

    $final_sheet_data = array_merge($data_to_sheet, $links_matches_unique);

    return $final_sheet_data;
}
}
