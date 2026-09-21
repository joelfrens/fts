<?php

return [

    'driver' => env('FTS_STORAGE_DRIVER', 'local'),

    'azure' => [
        'blob_sas_url' => env('AZURE_BLOB_SAS_URL'),
        'blob_container' => env('AZURE_BLOB_CONTAINER'),
        'public_url' => env('AZURE_BLOB_PUBLIC_URL'),
    ],

];
