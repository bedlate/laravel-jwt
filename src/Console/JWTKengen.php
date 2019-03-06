<?php

namespace bedlate\JWT\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class JWTKengen extends Command {
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'jwt:keygen';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the JWT secret key used to sign the tokens';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() {
        if (file_exists($path = $this->envPath()) === false) {
            $this->error(".env file({$path}) not exists");
            return;
        }

        $key = Str::random(64);

        if (Str::contains(file_get_contents($path), 'JWT_KEY') === false) {
            // update existing entry
            file_put_contents($path, PHP_EOL."JWT_KEY=$key", FILE_APPEND);
        } else {
            // create new entry
            file_put_contents($path, str_replace(
                'JWT_KEY='.$this->laravel['config']['jwt.key'],
                'JWT_KEY='.$key, file_get_contents($path)
            ));
        }
    }

    /**
     * Get the .env file path.
     *
     * @return string
     */
    protected function envPath()
    {
        if (method_exists($this->laravel, 'environmentFilePath')) {
            return $this->laravel->environmentFilePath();
        }
        // check if laravel version Less than 5.4.17
        if (version_compare($this->laravel->version(), '5.4.17', '<')) {
            return $this->laravel->basePath().DIRECTORY_SEPARATOR.'.env';
        }
        return $this->laravel->basePath('.env');
    }

}