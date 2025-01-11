<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Media Package Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all the settings for the waad/media package.
    | Customize these values according to your application's needs.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Media Model Configuration
    |--------------------------------------------------------------------------
    */
    'model' => Waad\Media\Media::class,

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the table name used for storing media records.
    */
    'table_name' => 'media',

    /*
    |--------------------------------------------------------------------------
    | UUID Configuration
    |--------------------------------------------------------------------------
    |
    | Enable UUID for media records. When enabled, media records will use UUID
    | for their primary keys instead of auto-incrementing integers.
    */
    'uuid' => false,

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'disk' => env('MEDIA_DISK', 'public'),
    'bucket' => env('MEDIA_BUCKET', 'upload'),
    'default_collection' => env('MEDIA_DEFAULT_COLLECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | S3 Configuration
    |--------------------------------------------------------------------------
    */
    's3' => [
        // Default TTL for temporary URLs in minutes
        'default_ttl_temporary_url' => env('MEDIA_DEFAULT_S3_TTL_TEMPORARY_URL', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable appends full URL and temporary URL for media
    |--------------------------------------------------------------------------
    */
    'enable_full_url' => env('MEDIA_ENABLE_FULL_URL', true),
    'enable_temporary_url' => env('MEDIA_ENABLE_TEMPORARY_URL', true),

    /*
    |--------------------------------------------------------------------------
    | File Management Configuration
    |--------------------------------------------------------------------------
    */
    'delete_file_after_day' => env('MEDIA_DELETE_FILE_AFTER_DAY', 30),
    'default_approved' => env('MEDIA_DEFAULT_APPROVED', true),

    /*
    |--------------------------------------------------------------------------
    | Date Format Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the date format for created_at and updated_at timestamps.
    | Set to null to use raw timestamps.
    */
    'format_date' => env('MEDIA_DATE_FORMAT', null),
];
