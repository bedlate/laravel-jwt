# laravel-jwt

JSON Web Token Authentication for Laravel

## Installation

1.Run the following command to pull in the latest version:

    composer require bedlate/laravel-jwt
    
2.Add service provider ( Laravel 5.4 or below )

    'providers' => [
    
        ...
    
        bedlate\JWT\JWTServiceProvider::class,
    ]
    
3.Publish the config

    php artisan vendor:publish --provider="bedlate\JWT\JWTServiceProvider"
    
4.Generate secret key

    php artisan jwt:keygen
    

## Usage

1.Configure Auth guard

Inside the config/auth.php file you will need to add a guard.

    'guards' => [
        ...
        
        'jwt' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],
    

2.Add some basic authentication routes
    
So that you can add `auth:jwt` middleware to any routes you like.

    Route::group([
        'middleware' => 'auth:jwt',
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('refresh', 'AuthController@refresh');
        Route::post('user', 'AuthController@user');
    });
    
    Route::post('login', 'AuthController@login');
    
3.Create the AuthController

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    
    class AuthController extends Controller
    {
        /**
         * which auth guard we use
         * @return mixed
         */
        private function guard() {
            return Auth::guard('jwt');
        }
    
        /**
         * Get token by credentials
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        public function login(Request $request) {
            $email = $request->get('email');
            $password = $request->get('password');
    
            if (! $token = $this->guard()->attempt(compact('email', 'password'))) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    
            return $token;
        }
    
        /**
         * refresh token
         * @return mixed
         */
        public function refresh() {
            return $this->guard()->refresh();
        }
    
        /**
         * get current login user
         * @return mixed
         */
        public function user() {
            return $this->guard()->user();
        }
    }

4.Get token by credentials

You should now be able to POST to the login endpoint (e.g. http://localhost:8000/auth/login) with some valid credentials and see a response like:

    {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE1NTE4NjUyNDgsInVpZCI6MX0.GaEEUgZF65r59lY4EicmHwg3ei61DQkZN1Zm_HzFPYg",
        "expire": 1551865248
    }

This token can then be used to make authenticated requests to your application.

And don't forget to refresh token before `expire` by POST to the login endpoint (e.g. http://localhost:8000/auth/refresh)

    {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE1NTE4NjYwNjUsInVpZCI6MX0.UZQNLqn9L6H7yF5m-GczxYgMCJyjGueI6CslMfokKLc",
        "expire": 1551866065
    }
    
5.Client authenticated requests

Your can use `Authorization header`

    Authorization: Bearer eyJ0eXAiOiJKV1QiLCJh...
    
or `Query string parameter`
 
    http://localhost/user?_token=eyJ0eXAiOiJKV1QiLCJh...
    
Your can change `_token` to others, just change `JWT_QUERY` in `.env`

