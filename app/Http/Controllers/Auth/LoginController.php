<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
//use Illuminate\Http\Client\Request;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;
use Jumbojett\OpenIDConnectClient;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Custom credentials to validate the status of user. is_active
     */
    public function credentials(Request $request)
    {
        return [
            'email'     => $request->email,
            'password'  => $request->password,
            'is_active' => '1'
        ];
    }


    public function openidlogin(){

        $oidc = new OpenIDConnectClient('https://id.containerize.com',
                                'prod.wordpress.containerize',
                                '');
       $response =  $oidc->authenticate();
        if($response){
            $name = $oidc->requestUserInfo('given_name');
            $email = $oidc->requestUserInfo('given_email');
            $email = "fahad.adeel@aspose.com";
            $user = User::where('email', $email)->first();
             if ($user) {
                 Auth::login($user, $remember = true);
                 if (Auth::check()) {
                     return redirect('/admin');
                 }
             }else{
                 // user not exists
             }
        }
    }
}
//ALTER TABLE `users` ADD `is_active` TINYINT(1) NULL DEFAULT '1' AFTER `reset_key`;