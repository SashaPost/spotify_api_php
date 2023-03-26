<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\SpotifyController;



class LoginController extends SpotifyController
{
    /**
     * Handle an authentication attempt.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @return \Illuminate\Http\RedirectResponse
     */



    /**
     * Instantiate a new LoginRegisterController instance.
     */
    // nahui; gives an error: "Call to undefined method App\Http\Controllers\LoginController::middleware()"
    // public function __construct()
    // {
    //     $this->middleware('guest')->except([
    //         'logout', 'dashboard'
    //     ]);
    // }

    public function showLoginForm(Request $request)
    {
        return view('auth.login');  // was not created yet 
    }

    public function loginRequest(Request $request)
    {
        $credentials = $request->validate(
            [
            'email' => ['required', 'email'],
            'password' => ['required'],
            ]
        );

        if (Auth::attempt($credentials))    
        {   
            // 
            $request->session()->regenerate();
            return redirect()->intended('dashboard')    // 'dashboard' was taken from the docs example
                ->withSuccess('You have successfully logged in!');   
        }

        return redirect()->back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        return view('auth.register');
    }

    public function registerRequest(Request $request)
    {   
        // test:
        // dd($request->all());

        $request->validate([
            'name' => 'required|string|max:250',  // need to fix this in the 'register.blade.php'
            'email' => 'required|email|max:250|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create([
            'username' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $credentials = $request->only('email', 'password');
        Auth::attempt($credentials);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->withSuccess('You have successfully registered & logged in!');
    }

    public function dashboard(Request $request)
    {
        if(Auth::check())
        {
            return view('auth.dashboard');
        }

        return redirect()->route('login')
            ->withErrors([
                'email' => 'Please login to access the dashboard.',
            ])->onlyInput('email');
    }

    public function logout(Request $request) 
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // return redirect('/');
        return redirect()->route('login')
            ->withSuccess('You have logged out successfully!');
    }


    


    // public function authenticate(Request $request)
    // {
    //     $credentials = $request->validate(
    //         [
    //         'email' => ['required', 'email'],
    //         'password' => ['required'],
    //         ]
    //     );

    //     // The values in the array will be used to find the user in your database table.
    //     // The user will be retrieved by the value of the email column.
    //     // If the user is found, the hashed password stored in the database will be compared 
    //     // with the password value passed to the method via the array.
    //     // ou should not hash the incoming request's password value.
    //     if (Auth::attempt($credentials))    
    //     {   
    //         // 
    //         $request->session()->regenerate();

    //         return redirect()->intended('dashboard');   // 'dashboard' was taken from the docs example
    //     }
        
    //     // For complex query conditions, you may provide a closure in your array of credentials. 
    //     // This closure will be invoked with the query instance, 
    //     // allowing you to customize the query based on your application's needs.
    //     // if (Auth::attempt([
    //     //     'email' => $credentials['email'],
    //     //     'password' => $credentials['password'],
    //     //     fn ($query) => $query->has('activeSubscription'),   //'fn()' is a valid shortcut for 'function()' when defining a closure
    //     // ])) {
    //     //     // Authentication was successful...
    //     // }

    //     // The attemptWhen method, which receives a closure as its second argument, 
    //     // may be used to perform more extensive inspection of the potential user before actually authenticating the user. 
    //     // The closure receives the potential user and should return true or false to indicate if the user may be authenticated.
    //     // if (Auth::attemptWhen([
    //     //     'email' => $email,
    //     //     'password' => $password,
    //     // ], function ($user) {
    //     //     return $user->isNotBanned();
    //     // })) {
    //     //     // Authentication was successful...
    //     // }

    //     // $dontKnow = Auth::attempt($credentials);    // don't know what i tried to do 

    //     return back()->withErrors([
    //         'email' => 'The provided credentials do not match our records.',
    //     ])->onlyInput('email');
    // }
}