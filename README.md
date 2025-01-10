![Logo](lm.jpg)

# 🔥 Media Files Package

A Laravel package for managing media files across multiple disks and buckets with model relationships. An alternative to [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary)

## ❤️ Authors

- [Waad Mawlood](https://www.github.com/waadmawlood)
- waad_mawlood@outlook.com

## ⚠️ Requirements

- PHP >= 8.0.0 
- Laravel 8, 9, 10, 11

## 💯 Installation

1. Install via Composer:
    ```bash
    composer require waad/media
    ```

2. Publish the configuration file:
    ```bash
    php artisan vendor:publish --provider="Waad\Media\MediaServiceProvider"
    ```

3. Edit the config file: `config/media.php`


4. Run the migrations:
    ```bash
    php artisan migrate
    ```

## 🚀 Usage

### Adding Media to a Model

1. Use the `HasMedia` trait in your model:
```php
use Waad\Media\HasMedia;

class Post extends Model
{
    use HasMedia;

    // Default collection with depend on default value in config/media.php
    public function registerCollections(array $attributes = []): array
    {
        return [
            'default' => [
                'disk' => 'public',
                'bucket' => 'upload',
                'label' => null,
                'single' => false,
            ]
        ];
    }
}
```

2. Basic media operations:
```php
// Add single file
$post->addMedia($request->file('image'))->upload();

// Add multiple files
$post->addMedia($request->file('images'))->upload();

// Add with custom collection
$post->addMedia($request->file('image'))
    ->collection('avatars')
    ->upload();

// Add with custom label
$post->addMedia($request->file('image'))
    ->label('Profile Picture')
    ->upload();

// Add with custom collection and label and index
$post->addMedia($request->file('image'))
    ->collection('avatars')
    ->label('Profile Picture')
    ->index(3)
    ->upload();
```

### Media Collections

Can register custom collections in your model to define specific media groups:
```php
public function registerCollections(array $attributes = []): array
{
    return [
        'avatars' => [
            'disk' => 's3',
            'bucket' => 'profile-pictures',
            'label' => 'User Avatars',
            'single' => true, // Only one file allowed
        ],
        'gallery' => [
            'disk' => 'public',
            'bucket' => 'photos',
            'label' => 'Photo Gallery',
            'single' => false, // Multiple files allowed
        ]
    ];
}
```

### Retrieving Media

```php
// Get all media
$allMedia = $post->getMedia();

// Get media from specific collection
$avatars = $post->getCollection('avatars');

// Get first or last media
$firstMedia = $post->getFirstMedia();
$lastMedia = $post->getLastMedia();

// Check if has media
$hasAvatars = $post->hasMedia('avatars');

// Get by ID
$media = $post->mediaById($id);

// Get by mime type
$images = $post->mediaByMimeType('image/jpeg');

// Get approved media
$approved = $post->mediaApproved();

// Get media stats
$totalSize = $post->mediaTotalSize();
$totalCount = $post->mediaTotalCount();
```

### Managing Media

```php
****** Sync Media ******
// Sync media (replace existing)
$post->syncMedia($request->file('images'))->sync();
// Sync media (replace existing) with specific ids
$post->syncMedia($request->file('images'), ids: [1, 2, 3])->sync();
// Sync media (replace existing) with specific models
$post->syncMedia($request->file('images'), models: [$mediaModel1, $mediaModel2])->sync();

****** Delete Media ******
// Delete specific media
$post->deleteMedia($mediaId)->delete();
// Delete media by model
$post->deleteMedia($mediaModel)->delete();
// Delete all media
$post->deleteMedia()->delete();

// Approve/Disapprove media
$media->approve();
$media->disApprove();
```

## ⚙️ Configuration

You can customize the package behavior in `config/media.php`:

```php
return [
    // Media model class
    'model' => Waad\Media\Media::class,

    // Database table name
    'table_name' => 'media',

    // Enable UUID for media records
    'uuid' => false,

    // Default storage settings
    'disk' => env('MEDIA_DISK', 'public'),
    'bucket' => env('MEDIA_BUCKET', 'upload'),
    'default_collection' => env('MEDIA_DEFAULT_COLLECTION', 'default'),

    // File management
    'delete_file_after_day' => env('MEDIA_DELETE_FILE_AFTER_DAY', 30),
    'default_approved' => env('MEDIA_DEFAULT_APPROVED', true),

    // Date format for timestamps
    'format_date' => env('MEDIA_DATE_FORMAT', null),
];
```

## 🔒 Media Model Attributes

The Media model includes the following attributes:

- `basename`: The base name of the file
- `filename`: Original file name
- `path`: File path in storage
- `index`: Order index
- `label`: Custom label
- `collection`: Collection name
- `disk`: Storage disk
- `bucket`: Storage bucket
- `mimetype`: File MIME type
- `filesize`: File size in bytes
- `approved`: Approval status
- `metadata`: Additional JSON metadata
- `full_url`: Complete URL to access the file

## 🌟 Features

- ✅ Multiple file upload support
- ✅ Support storage configuration (local)
- ☑️ Support storage configuration (S3) (coming soon)
- ✅ Collection management
- ✅ Soft deletes
- ✅ File approval system
- ✅ UUID support
- ✅ Custom metadata for Image (ex: width, height, etc.)
- ✅ Automatic file cleanup
- ✅ Media relationships
- ✅ File statistics


## 🧪 Testing

```bash
composer install
./vendor/bin/pest
```

## 📝 License

[MIT](LICENSE.md) © Waad Mawlood
