<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
class SwapDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //dd($request->getHttpHost());
        $host = $request->getHttpHost();

        $database = "";
        
        if($host == "admindemo.aspose"){
            $database = "admindemo_aspose";
            $username = "root";
            $password = "root";
        } else if($host == "admindemo.groupdocs"){
            $database = "admindemo_groupdocs";
            $username = "root";
            $password = "root";
        }else{
            $database = str_replace('-', '_', $host);
            $database = str_replace('.', '_', $database);
            $username = "root";
            $password = "root";
        }
        if(empty($database)){
            dd('db not assiged check SwapDatabase middleware');
        }

        //connect db
        DB::disconnect('mysql');
        Config::set('database.connections.mysql.database', $database);
        Config::set('database.connections.mysql.username', $username);
        Config::set('database.connections.mysql.password', $password);
        DB::reconnect();

        return $next($request);
    }
}
