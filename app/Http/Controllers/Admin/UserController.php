<?php

namespace App\Http\Controllers\Admin;
use App\Http\Requests\ResetPassword;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $user_id = Auth::user()->id;
        $user  = User::find($user_id);
        $title = "Update Password";
        return view('admin.users.resetpwd', compact('title', 'user'));
    }

    public function resetpassword(ResetPassword $request)
    {
        
        if (!empty($request->all())) {
            //dd($request->all());
            if(!empty($request->password)){
                // 
                if(!empty($request->password) && !empty($request->confpassword)){ // both set 
                    if($request->password == $request->confpassword ){ // both equal
                        $user = User::find($request->user_id);
                        $user->name = trim($request->username);
                        $user->email = trim($request->email);
                        $pwd = Hash::make(trim($request->password));
                        $user->password = $pwd;
                        $user->save();
                        return redirect('/admin/resetpwd')->with('success', 'Updated...');
                    }else{
                        return redirect('/admin/resetpwd')->with('alert', ' Password & Con. Password Didnt match');
                    }
                }else{
                    return redirect('/admin/resetpwd')->with('alert', 'Both Password & Con. Password Fileds are Required');
                }
            }else{
                $user = User::find($request->user_id);
                $user->name = trim($request->username);
                $user->email = trim($request->email);
                $user->save();
                return redirect('/admin/resetpwd')->with('success', 'Updated...');
            }
        }

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /*public function destroy(User $user)
    {
        if (auth()->user() == $user) {
            flash()->overlay("You can't delete yourself.");

            return redirect('/admin/users');
        }

        $user->delete();
        flash()->overlay('User deleted successfully.');

        return redirect('/admin/users');
    }*/
}
