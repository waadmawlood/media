<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media waad/media package of laravel Configuration
    |--------------------------------------------------------------------------
    |
    | meadia package save all your file in one place
    | any model will be in ralated has many of media
    |
    | To learn more: https://github.com/waadmawlood/media
    |
    */

    /*
     * The comment class that should be used to store and retrieve
     * the comments.
     */
    'media_class' => \Waad\Media\Media::class,

    /*
     * The user model that should be used when associating media with
     * mediators. If null, the default user provider from your
     * Laravel authentication configuration will be used.
     */
    'user_model' => null,

    /*
     * The disk configration of path in file system in config/filesystem.php
     */
    'disk' => 'public',

    /*
     * path direction to save media
     */
    'path' => 'upload',

    /*
     * Shortcut to make direct shortcut to access media
     */
    'shortcut' => 'media',

    /*
     * The delete file is flag to delete file from server when delete media from DB
     */
    'delete_file' => false,

    /*
     * The default value of approved in table before migrate table media
     */
    'default_approved' => true,

    /*
     * The order by of get media ASC or DESC
     * type => DESC, desc, ASC, asc
     * column => id, base_name, file_name, approved, mime_type, file_size, user_id, created_at, updated_at
     */
    'order' => [
        'type' => 'desc',
        'column' => 'created_at',
    ],

    /*
     * format dateTime of created_at and updated_at
     * e.g. 2022-09-15 07:25:13 pm
     */
    'format_date' => null,
];
