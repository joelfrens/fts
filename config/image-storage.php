<?php

return [

    'driver' => env('FTS_STORAGE_DRIVER', 'local'),

    'azure' => [
        'blob_sas_url' => env('AZURE_BLOB_SAS_URL'),
    ],

];
