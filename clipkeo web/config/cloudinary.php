<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    */
    'cloud_url' => env('CLOUDINARY_URL'),

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key'    => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    /**
    * Upload Preset From Cloudinary Dashboard
    */
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET', 'ml_default'),

    /**
    * Notification URL For Cloudinary Webhooks
    */
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    /**
    * Cloudinary Options
    * Library butuh ini di baris 64!
    */
    'options' => [
        'secure' => true,
    ],

    /**
    * Upload Routes & Actions
    */
    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),
];