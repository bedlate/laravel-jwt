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

todo
