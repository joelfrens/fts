<?php

return [

    'driver' => env('FTS_STORAGE_DRIVER', 'local'),

    'azure' => [
        'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),

        'container' => env('AZURE_STORAGE_CONTAINER'),
    ],

];
