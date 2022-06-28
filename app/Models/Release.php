<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    protected $fillable = ['family', 'product', 'folder', 'folder_link', 'etag_id', 's3_path', 'view_count', 'download_count', 'posted_by', 'description', 'release_notes_url', 'filesize', 'sha1', 'md5', 'date_added','is_new','folderId','filename','filetitle','weight', 'tags'];

    
}
