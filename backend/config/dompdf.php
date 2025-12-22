<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default PDF Generator
    |--------------------------------------------------------------------------
    |
    | This option controls the default PDF generator that will be used.
    | You may set this to "dompdf" or "snappy".
    |
    */

    'default' => 'dompdf',

    /*
    |--------------------------------------------------------------------------
    | PDF Generators
    |--------------------------------------------------------------------------
    |
    | Here you may configure the PDF generators for your application.
    |
    */

    'pdf' => [
        'enabled' => true,
        'binary'  => env('WKHTMLTOPDF_BINARY', '/usr/local/bin/wkhtmltopdf'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Generator
    |--------------------------------------------------------------------------
    |
    | Here you may configure the image generators for your application.
    |
    */

    'image' => [
        'enabled' => true,
        'binary'  => env('WKHTMLTOIMG_BINARY', '/usr/local/bin/wkhtmltoimg'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],

];