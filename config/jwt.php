<?php

return [
    'key' => env('JWT_KEY'),
    'expire' => env('JWT_EXPIRE', 3600),
    'query' => env('JWT_QUERY', '_token'),
];