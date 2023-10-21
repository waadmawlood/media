<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media waad/media package of laravel Configuration
    |--------------------------------------------------------------------------
    |
    | Media package save all your file in one place
    | any model will be in ralated has many of media
    |
    | To learn more: https://github.com/waadmawlood/media
    |
    */

    /*
    * The model you want to use as a Media model
    */
    'model' => Waad\Media\Media::class,

    /*
    * Enable Uuid Type only migration related `Uuid`, `nullableUuidMorphs` and `uuidMorphs`
    */
    'uuid' => false,

    /*
     * Default disk configration of path in file system in config/filesystem.php
     */
    'disk' => 'public',

    /*
     * Default path direction to save media
     */
    'directory' => 'upload',

    /*
     * Shortcut of disks to make direct shortcut to access disks must contain `root`
     */
    'shortcut' => [
        'public' => 'media',
    ],

    /*
     * The delete file is flag to delete file from server when delete media from DB prune Model
     */
    'delete_file_after_day' => 30,

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
     * e.g. `Y-m-d h:i:s a` => 2022-09-15 07:25:13 pm
     * e.g. `null` => timestamp
     */
    'format_date' => null,
];
