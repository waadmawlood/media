<?php

use Waad\Media\Media;

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
    'model' => Media::class,

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
    | Storage Shortcut Configuration
    |--------------------------------------------------------------------------
    |
    | Map disk names to their public path shortcuts used for URL generation
    | and symbolic link creation via `media:link` command.
    | Example: 'public' => 'storage' maps the public disk to /storage/ URL prefix.
    |
    */
    'shortcut' => [
        // 'public' => 'storage',
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
    'prune_media_after_day' => env('MEDIA_PRUNE_MEDIA_AFTER_DAY', 30),
    'default_approved' => env('MEDIA_DEFAULT_APPROVED', true),

    /*
    |--------------------------------------------------------------------------
    | Date Format Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the date format for created_at and updated_at timestamps.
    | Set to null to use raw timestamps.
    | Default: null return example `2026-03-19T12:03:15.000000Z`
    |
    | Example: 'Y-m-d H:i:s'
    | Supported formats:
    | - Y-m-d H:i:s return example `2026-03-19 12:03:15`
    | - Y-m-d return example `2026-03-19`
    | - Y-m-d H:i return example `2026-03-19 12:03`
    | - Y-m-d H:i:s.u return example `2026-03-19 12:03:15.000000`
    | - Y-m-d H:i:s.u return example `2026-03-19 12:03:15.000000`
    */
    'format_date' => env('MEDIA_DATE_FORMAT', null),
];
