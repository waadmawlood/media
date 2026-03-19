![Logo](lm.jpg)

# Media Files Package

A Laravel package for managing media files across multiple storage drivers (local, S3, and any Laravel filesystem disk) with Eloquent model relationships. An alternative to [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary).

## Authors

- [Waad Mawlood](https://www.github.com/waadmawlood)
- waad_mawlood@outlook.com

## Requirements

- PHP >= 8.1
- Laravel 9, 10, 11, 12, or 13

## Installation

1. Install via Composer:

```bash
composer require waad/media
```

2. Publish the configuration and migration files:

```bash
php artisan vendor:publish --provider="Waad\Media\MediaServiceProvider"
```

3. Edit the config file at `config/media.php`

4. Run the migrations:

```bash
php artisan migrate
```

5. (Optional) Create storage symbolic links:

```bash
php artisan media:link
```

## Usage

### Setup the Trait

Add the `HasMedia` trait to any Eloquent model:

```php
use Waad\Media\HasMedia;

class Post extends Model
{
    use HasMedia;
}
```

### Uploading Media

```php
// Upload a single file
$media = $post->addMedia($request->file('image'))->upload();

// Upload multiple files
$mediaItems = $post->addMedia($request->file('images'))->upload();

// Upload to a specific collection
$media = $post->addMedia($request->file('image'))
    ->collection('avatars')
    ->upload();

// Upload with custom options
$media = $post->addMedia($request->file('image'))
    ->collection('gallery')
    ->disk('s3')
    ->bucket('photos')
    ->label('Profile Picture')
    ->index(3)
    ->upload();
```

The upload service works with **any** Laravel filesystem disk — local, S3, GCS, or any custom driver. Just set the `disk()` and it handles the rest.

### Media Collections

Register custom collections in your model to define specific media groups with their own storage configuration:

```php
use Waad\Media\HasMedia;

class User extends Model
{
    use HasMedia;

    public function registerCollections(array $attributes = []): array
    {
        return [
            'avatars' => [
                'disk' => 's3',
                'bucket' => 'profile-pictures',
                'label' => 'User Avatars',
                'single' => true,   // Only keeps one file
            ],
            'gallery' => [
                'disk' => 'public',
                'bucket' => 'photos',
                'label' => 'Photo Gallery',
                'single' => false,  // Allows multiple files
            ],
        ];
    }
}
```

When uploading to a registered collection, its disk, bucket, and label are applied automatically:

```php
$user->addMedia($file)->collection('avatars')->upload();
```

### Retrieving Media

```php
// Get all media
$allMedia = $post->getMedia();

// Get media from a specific collection
$avatars = $post->getCollection('avatars');

// Get collection as array
$array = $post->getCollectionArray('avatars');

// Get first or last media
$first = $post->getFirstMedia();
$last  = $post->getLastMedia();

// Check if model has media in a collection
$post->hasMedia('avatars'); // true / false

// Find by ID (supports withTrashed)
$media = $post->mediaById($id);
$media = $post->mediaById($id, withTrashed: true);

// Filter by MIME type
$images = $post->mediaByMimeType('image/jpeg');

// Filter by approval status
$approved    = $post->mediaApproved();
$disapproved = $post->mediaApproved(false);

// Statistics
$totalSize  = $post->mediaTotalSize();
$totalCount = $post->mediaTotalCount();
$totalCount = $post->mediaTotalCount(withTrashed: true);
```

### Syncing Media

Replace existing media with new files. Pass IDs of media records to remove before uploading the new files:

```php
// Sync: delete old media by IDs, then upload new files
$post->syncMedia($request->file('images'), [$oldMediaId1, $oldMediaId2])->upload();

// Sync with a specific collection
$post->syncMedia($request->file('image'), [$oldMediaId])->collection('avatars')->upload();
```

### Deleting Media

```php
// Delete specific media by ID (soft delete)
$post->deleteMedia($mediaId)->delete();

// Delete specific media by model
$post->deleteMedia($mediaModel)->delete();

// Delete multiple media by IDs
$post->deleteMedia([$id1, $id2, $id3])->delete();

// Delete all media for the model
$post->deleteMedia()->delete();
```

### Approving Media

```php
$media->approve();     // Mark as approved
$media->disApprove();  // Mark as disapproved

// Query approved media globally
$approved = \Waad\Media\Media::approved()->get();
```

### File Utilities

The upload service provides disk-aware utility methods that work with any storage driver:

```php
$service = $post->addMedia(null);

// Check if a file exists
$service->fileExists('upload/photo.jpg');

// Get file size in bytes
$service->fileSize('upload/photo.jpg');

// Get file metadata (size, mimetype, last_modified)
$service->fileMetadata('upload/photo.jpg');

// Delete a file from disk
$service->deleteFile('upload/photo.jpg');

// Generate a temporary URL (S3 and compatible disks)
$service->disk('s3')->temporaryUrl('photos/secret.jpg', minutes: 10);
```

## Configuration

Customize the package in `config/media.php`:

```php
return [
    // Media model class
    'model' => \Waad\Media\Media::class,

    // Database table name
    'table_name' => 'media',

    // Default storage settings
    'disk' => env('MEDIA_DISK', 'public'),
    'bucket' => env('MEDIA_BUCKET', 'upload'),
    'default_collection' => env('MEDIA_DEFAULT_COLLECTION', 'default'),

    // S3 configuration
    's3' => [
        'default_ttl_temporary_url' => env('MEDIA_DEFAULT_S3_TTL_TEMPORARY_URL', 5),
    ],

    // Map disk names to public URL prefixes (used by media:link and full_url)
    'shortcut' => [
        // 'public' => 'storage',
    ],

    // Append full_url and temporary_url attributes to the Media model
    'enable_full_url' => env('MEDIA_ENABLE_FULL_URL', true),
    'enable_temporary_url' => env('MEDIA_ENABLE_TEMPORARY_URL', true),

    // Auto-prune soft-deleted media after N days (via media:prune)
    'delete_file_after_day' => env('MEDIA_DELETE_FILE_AFTER_DAY', 30),

    // Default approval status for newly uploaded media
    'default_approved' => env('MEDIA_DEFAULT_APPROVED', true),

    // Date format for created_at / updated_at serialization (null = Y-m-d H:i:s)
    'format_date' => env('MEDIA_DATE_FORMAT', null),
];
```

## Media Model Attributes

| Attribute | Type | Description |
|---|---|---|
| `basename` | string | Hashed file name on disk |
| `filename` | string | Original uploaded file name |
| `path` | string | Full path in storage |
| `index` | int | Order index (default 1) |
| `label` | string | Custom label |
| `collection` | string | Collection name |
| `disk` | string | Storage disk (hidden) |
| `bucket` | string | Storage bucket/folder (hidden) |
| `mimetype` | string | File MIME type |
| `filesize` | int | File size in bytes |
| `approved` | bool | Approval status |
| `metadata` | json | Additional metadata (e.g. image dimensions) |
| `full_url` | string | Appended: public URL to the file |
| `temporary_url` | string | Appended: S3 presigned temporary URL |

## Artisan Commands

| Command | Description |
|---|---|
| `php artisan media:link` | Create symbolic links from disk roots to public paths based on the `shortcut` config. Use `--force` to recreate existing links. |
| `php artisan media:prune` | Permanently delete soft-deleted media records (and their files) older than `delete_file_after_day`. |

## Features

- Multiple file upload support
- Works with any Laravel filesystem disk (local, S3, GCS, etc.)
- Collection management with per-collection disk/bucket configuration
- Soft deletes with `withTrashed` support
- File approval system
- Automatic image metadata extraction (width, height)
- File utilities (exists, size, metadata, delete, temporary URL)
- Automatic file cleanup via `media:prune`
- Polymorphic media relationships
- File statistics (total size, total count)
- Customizable date serialization format

## Testing

```bash
composer install
vendor/bin/pest
```

## License

[MIT](LICENSE.md) &copy; Waad Mawlood
