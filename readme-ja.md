#Laravel 5.2のMulti AuthのSTEP BY STEP DEMO

**Laravel動作環境が整っている前提です**

##1:Laravelプロジェクトの作成
```
$ laravel new laravel-multi-auth-demo
$ cd laravel-multi-auth-demo
```

##2:Laravelのバージョンチェック
```
$ php artisan --version
Laravel Framework version 5.2.31
```
**このデモはv5.2.27~5.2.31以降で動作確認しています**

##3:ビルドインのUsersテーブルを作成する

###3-1:マイグレーション
```
$ php artisan migrate
Migration table created successfully.
Migrated: 2014_10_12_000000_create_users_table
Migrated: 2014_10_12_100000_create_password_resets_table
```

###3-2:UsersテーブルのSeederを作成
```
$ php artisan make:seeder UsersTableSeeder
Seeder created successfully.
```

###3-3:UsersテーブルのSeederファイルを編集
```
/database/seeds/UsersTableSeeder.php
<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();
        DB::table('users')->insert([
            'name' => 'user1',
            'email' => 'user1@example.com',
            'password' => bcrypt('user1pass'),
        ]);
    }
}
```

###3-4:Seederの実行設定を追加
```
/database/seeds/DatabaseSeeder.php
<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
    }
}
```

###3-5:Seederを実行する
```
$ php artisan db:seed
Seeded: UsersTableSeeder
```

##4:makeコマンドでauthをセットアップする
```
$ php artisan make:auth
Created View: /laravel52-multi-auth-demo/resources/views/auth/login.blade.php
Created View: /laravel52-multi-auth-demo/resources/views/auth/register.blade.php
Created View: /laravel52-multi-auth-demo/resources/views/auth/passwords/email.blade.php
Created View: /laravel52-multi-auth-demo/resources/views/auth/passwords/reset.blade.php
Created View: /laravel52-multi-auth-demo/resources/views/auth/emails/password.blade.php
Created View: /laravel52-multi-auth-demo/resources/views/layouts/app.blade.php
Created View: /laravel52-multi-auth-demo/resources/views/home.blade.php
Created View: /laravel52-multi-auth-demo/resources/views/welcome.blade.php
Installed HomeController.
Updated Routes File.
Authentication scaffolding generated successfully!
```

Usersテーブルを使ったログインができるようになっている。

以下のコマンドでPHPビルドインサーバーを立ち上げて、/loginにアクセスして確認できる。

```
$ php artisan serve
Laravel development server started on http://localhost:8000/
```

その他のルーティングについては以下のコマンドで確認できる。

```
$ php artisan route:list
```

| Domain | Method   | URI                     | Name | Action                                                          | Middleware |
|        |          |                         |      |                                                                 |            | 
|        | GET/HEAD | /                       |      | Closure                                                         | web        |
|        | GET/HEAD | home                    |      | App\Http\Controllers\HomeController@index                       | web,auth   |
|        | GET/HEAD | login                   |      | App\Http\Controllers\Auth\AuthController@showLoginForm          | web,guest  |
|        | POST     | login                   |      | App\Http\Controllers\Auth\AuthController@login                  | web,guest  |
|        | GET/HEAD | logout                  |      | App\Http\Controllers\Auth\AuthController@logout                 | web        |
|        | POST     | password/email          |      | App\Http\Controllers\Auth\PasswordController@sendResetLinkEmail | web,guest  |
|        | POST     | password/reset          |      | App\Http\Controllers\Auth\PasswordController@reset              | web,guest  |
|        | GET/HEAD | password/reset/{token?} |      | App\Http\Controllers\Auth\PasswordController@showResetForm      | web,guest  |
|        | GET/HEAD | register                |      | App\Http\Controllers\Auth\AuthController@showRegistrationForm   | web,guest  |
|        | POST     | register                |      | App\Http\Controllers\Auth\AuthController@register               | web,guest  |


##5:Adminsテーブルを作成する

###5-1:Adminモデルを作成する
```
$ php artisan make:model Admin -m
Model created successfully.
Created Migration: 2016_05_09_132034_create_admins_table
```

###5-2:Adminモデルの内容を編集する
```
/app/Admin.php
<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
```

###5-3:Adminsのcreate tableファイルを編集する
```
/database/migrations/2016_05_09_132034_create_admins_table.php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('admins');
    }
}

```

###5-4:AdminsのSeederを作成する
```
$ php artisan make:seeder AdminsTableSeeder
Seeder created successfully.
```

###5-5:AdminsテーブルのSeederファイルを編集
```
/database/seeds/AdminsTableSeeder.php
<?php

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->truncate();
        DB::table('admins')->insert([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
        ]);
    }
}
```

###5-6:マイグレーションしてAdminsテーブルを作成
```
$ php artisan migrate
Migrated: 2016_05_09_132034_create_admins_table
```

###5-7:Seederの実行設定を追加
```
/database/seeds/DatabaseSeeder.php
<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
    }
}
```

###5-8:Seederを実行する
```
$ php artisan db:seed
Seeded: UsersTableSeeder
Seeded: AdminsTableSeeder
```

##6:認証設定の編集
```
/config/auth.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'webadmin' => [
            'driver' => 'session',
            'provider' => 'adminusers',
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],

        'adminusers' => [
            'driver' => 'eloquent',
            'model' => App\Admin::class,
        ],
        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Here you may set the options for resetting passwords including the view
    | that is your password reset e-mail. You may also set the name of the
    | table that maintains all of the reset tokens for your application.
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'email' => 'auth.emails.password',
            'table' => 'password_resets',
            'expire' => 60,
        ],
        'adminusers' => [
            'provider' => 'adminusers',
            'email' => 'auth.emails.password',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],

];

```

##7:ミドルウェアの編集

###7-1:認証済みユーザー用ミドルウェアの編集
```
/app/Http/Middleware/Authenticate.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                if($guard == 'webadmin'){
                    return redirect()->guest('/admin/login');
                }
                return redirect()->guest('login');
            }
        }

        return $next($request);
    }
}

```

###7-2:ゲストユーザー用ミドルウェアの編集
```
/app/Http/Middleware/RedirectIfAuthenticated.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            if($guard == 'webadmin'){
                return redirect('/admin/home');
            }
            return redirect('/');
        }

        return $next($request);
    }
}

```


##8:Admin用のコントロラーを作成

###8-1:Adminの認証用のコントロラーを作成
```
$ php artisan make:controller AdminAuthController
Controller created successfully.
```

###8-2:Adminの認証用のコントロラーを編集
```
app/Http/Controllers/AdminAuthController.php
<?php

namespace App\Http\Controllers;

use App\Admin;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AdminAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * @var string
     */
    protected $guard = 'webadmin';

    /**
     * Where to redirect admins after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/admin/home';

    /**
     * admin login view
     * @var string
     */
    protected $loginView = 'admin.login';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:admins',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new admin instance after a valid registration.
     *
     * @param  array  $data
     * @return Admin
     */
    protected function create(array $data)
    {
        return Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

}
```

###8-3:Adminのホーム用のコントロラーを作成
```
$ php artisan make:controller AdminHomeController
Controller created successfully.
```

###8-4:Adminのホーム用コントロラーを編集
```
/app/Http/Controllers/AdminHomeController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

class AdminHomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:webadmin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.home');
    }
}
```

##9:ルーティングの追加
```
/app/Http/routes.php
<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::auth();
Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index');

Route::get('/admin/login','AdminAuthController@showLoginForm');
Route::post('/admin/login','AdminAuthController@login');
Route::get('/admin','AdminHomeController@index');
Route::get('/admin/home','AdminHomeController@index');
Route::get('/admin/logout','AdminAuthController@logout');

```

##10:Admin用のビューを作成

###10-1:Admin用のレイアウトを作成
```
/resources/views/layouts/admin.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}

    <style>
        body {
            font-family: 'Lato';
        }

        .fa-btn {
            margin-right: 6px;
        }
    </style>
</head>
<body id="app-layout">
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/admin') }}">
                    Laravel
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    <li><a href="{{ url('/admin/home') }}">Home</a></li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    @if (Auth::guard('webadmin')->guest())
                        <li><a href="{{ url('/admin/login') }}">Login</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ Auth::guard('webadmin')->user()->name }} <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ url('/admin/logout') }}"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- JavaScripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
</body>
</html>
```

###10-2:Admin用のログインビューを追加
```
/resources/views/admin/login.blade.php
@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Login</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/admin/login') }}">
                        {!! csrf_field() !!}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Admin E-Mail Address</label>

                            <div class="col-md-6">
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Admin Password</label>

                            <div class="col-md-6">
                                <input type="password" class="form-control" name="password">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember"> Remember Me
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-sign-in"></i>Login
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

###10-3:Admin用のホームビューを追加
```
/resources/views/admin/home.blade.php
@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    You(Admin) are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

