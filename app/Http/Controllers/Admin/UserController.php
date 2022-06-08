<?php

namespace App\Http\Controllers\Admin;
use App\Http\Requests\CreateUser;
use App\Http\Requests\UpdateUser;
use App\Http\Requests\ResetPassword;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Notifications\WelcomeEmailNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

    public function createuser(){
        $users  = User::all();
        $title = "Create User";
        if(Auth::user()->is_admin == 1){ // superadmin
            $roles = Role::all();
        }else{ //admin
            $roles = Role::where('name', '!=', 'superadmin')->where('name', '!=', 'admin')->get();
        }
        
        return view('admin.users.addnewuser', compact('title', 'users', 'roles'));
    }

    public function addnewuser(CreateUser $request){
        if (!empty($request->all())) {
            
            //$is_admin = 0;
            $userrole = $request->userrole;
            $username = trim($request->username);
            $useremail = trim($request->email);
            $password_string = str_random(8);
            $hashed_random_password = Hash::make($password_string);
            $user = User::create([
                'name'=> $username,
                'email'=> $useremail,
                'password'=> $hashed_random_password,
                'is_admin'=> $userrole,
            ]);

            if($user){
                
                $details = [
                    'newpassword' => $password_string
                ];
                
                $user->notify(new WelcomeEmailNotification($user, $details));
                return redirect('/admin/manage-users')->with('success', 'New User Added...');
            }else{
                return redirect('/admin/manage-users')->with('success', 'Error while Adding new User ...');
            }
            
                
            
        
        }
    }

    public function edituser(Request $request, $id){
        $user = User::find($id);
        $title = "Edit User";
        if(Auth::user()->is_admin == 1){ // superadmin
            $roles = Role::all();
        }else{ //admin
            $roles = Role::where('name', '!=', 'superadmin')->where('name', '!=', 'admin')->get();
        }
        
        return view('admin.users.edituser', compact('title', 'user', 'roles'));
    }

    public function updateuser(Request $request){
        
        if (!empty($request->all())) {
            if ( User::where('id', $request->user_id)->exists()) {

            $userrole = $request->userrole;
            $username = trim($request->username);
            $useremail = trim($request->email);

            $user = User::find($request->user_id);

            $request->validate([
                'email' => 'required|email|unique:users,email,'.$user->id,
                'username' => 'required',
            ]);
            $user->name = trim($username);
            $user->email = trim($useremail);
            $user->is_admin = trim($userrole);
            $user->is_active = $request->is_active;
            $updated = $user->save();

            if($updated){
                
                return redirect('/admin/manage-users')->with('success', 'New Updated Successfully...');
            }else{
                return redirect('/admin/manage-users')->with('success', 'Error while Updating User ...');
            }
            
        }else{
            return redirect('/admin/manage-users')->with('success', 'User Not Exists ...');
        }
            
        
        }
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
