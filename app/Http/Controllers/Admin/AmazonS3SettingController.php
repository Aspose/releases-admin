<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AmazonS3SettingRequest;
use App\Models\AmazonS3Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AmazonS3SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Amazon S3 Configuration";
        $settings = AmazonS3Setting::first();
        return view('admin.amazon.index', compact('settings', 'title'));
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AmazonS3SettingRequest $request)
    {
        if(!empty($request->edit_id)){
            if (AmazonS3Setting::where('id', $request->edit_id)->exists()) {
                $settings = AmazonS3Setting::find($request->edit_id);
                $settings->bucketname = is_null($request->bucketname) ? $settings->bucketname : $request->bucketname;
                $settings->apikey = is_null($request->apikey) ? $settings->apikey : $request->apikey;
                $settings->apisecret = is_null($request->apisecret) ? $settings->apisecret : $request->apisecret;
                $settings->hugositeurl = is_null($request->hugositeurl) ? $settings->hugositeurl : $request->hugositeurl;
                $settings->save();
            }
        }else{
            $settings = AmazonS3Setting::create([
                'bucketname'       => $request->bucketname,
                'apikey'        => $request->apikey,
                'apisecret' => $request->apisecret,
                'hugositeurl' => $request->hugositeurl,
            ]);
        }
        flash()->overlay('Settings Saved Successfully.');
        return redirect('/admin/ventures/amazon-s3-settings');
    }
    
}
