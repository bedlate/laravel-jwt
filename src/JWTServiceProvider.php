<?php

namespace bedlate\JWT;

use bedlate\JWT\Console\JWTKengen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class JWTServiceProvider extends ServiceProvider {

    public function boot() {
        $this->publishConfig();
        $this->extendGuard();
    }

    public function register()
    {
        $this->registerJWT();
        $this->commands(JWTKengen::class);
    }

    /**
     * register JWT to singleton
     */
    protected function registerJWT()
    {
        $this->app->singleton(JWT::class, function ($app) {
            return new JWT();
        });
    }

    /**
     * Write config file
     */
    protected function publishConfig() {
        $path = realpath(__DIR__.'/../config/jwt.php');
        $this->publishes([$path => config_path('jwt.php')], 'config');
        $this->mergeConfigFrom($path, 'jwt');
    }

    /**
     * Add JWT guard
     */
    protected function extendGuard() {
        Auth::extend('jwt', function ($app, $name, array $config) {
            return new JwtGuard($app[JWT::class], $app['request'], Auth::createUserProvider($config['provider']));
        });
    }

}