<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\App\Models\Post;
use Waad\Media\Media;

beforeEach(function () {
    Storage::fake('public');
    $this->post = createPost();
    $this->file = UploadedFile::fake()->image('test.jpg', 50, 50);
});

afterEach(function () {
    if (isset($this->file)) {
        unset($this->file);
    }
    if (isset($this->post)) {
        $this->post->media()->delete();
        unset($this->post);
    }
    Storage::disk('public')->delete(Storage::disk('public')->allFiles());
    gc_collect_cycles();
});

it('can store single media', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media)
        ->toBeInstanceOf(Media::class)
        ->basename->not->toBeEmpty()
        ->approved->toBeTrue();

    Storage::disk('public')->assertExists($media->path);
});

it('can store multiple media', function () {
    $files = collect([
        UploadedFile::fake()->image('test1.jpg', 50, 50),
        UploadedFile::fake()->image('test2.jpg', 50, 50),
    ]);

    $mediaItems = $this->post->addMedia($files->all())->upload();

    expect($mediaItems)->toHaveCount(2);

    foreach ($mediaItems as $media) {
        expect($media)
            ->toBeInstanceOf(Media::class)
            ->basename->not->toBeEmpty();

        Storage::disk('public')->assertExists($media->path);
    }

    // Clean up
    $files = null;
    gc_collect_cycles();
});

it('can sync media', function () {
    // Add initial media
    $initialMedia = $this->post->addMedia($this->file)->upload();

    // Create new file to sync
    $newFile = UploadedFile::fake()->image('new.jpg');

    // Sync with new file
    $syncedMedia = $this->post->syncMedia($newFile, [$initialMedia->id])->upload();

    // Storage::disk('public')->assertMissing($initialMedia->path);
    Storage::disk('public')->assertExists($syncedMedia->path);
});

it('can approve and disapprove media', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $media->disApprove();
    expect($media->approved)->toBeFalse();

    $media->approve();
    expect($media->approved)->toBeTrue();
});

it('can filter approved media', function () {
    // Create approved media
    $media1 = $this->post->addMedia($this->file)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    // Create disapproved media
    $media3 = $this->post->addMedia(UploadedFile::fake()->image('test3.jpg'))->upload();
    $media3->disApprove();

    expect(Media::approved()->count())->toBe(2);
    expect($this->post->mediaApproved()->count())->toBe(2);
    expect($this->post->mediaApproved(false)->count())->toBe(1);
});

it('can get media by mime type', function () {
    $imageFile = $this->file;
    $pdfFile = UploadedFile::fake()->create('document.pdf', 100);

    $this->post->addMedia($imageFile)->upload();
    $this->post->addMedia($pdfFile)->upload();

    expect($this->post->mediaByMimeType('image/jpeg')->count())->toBe(1);
    expect($this->post->mediaByMimeType('application/pdf')->count())->toBe(1);
});

it('can get media by id', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($this->post->mediaById($media->id))
        ->toBeInstanceOf(Media::class)
        ->id->toBe($media->id);
});

it('can get first and last media', function () {
    $first = $this->post->addMedia($this->file)->upload();
    sleep(1); // Ensure different timestamps
    $last = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    expect($this->post->getFirstMedia()->id)->toBe($first->id);
    expect($this->post->getLastMedia()->id)->toBe($last->id);
});

it('can format dates according to config', function () {
    date_default_timezone_set('Asia/Baghdad');
    config(['media.format_date' => 'Y-m-d H:i:s']);

    $media = $this->post->addMedia($this->file)->upload();

    // Verify that the date fields are formatted according to config
    $dateFormat = config('media.format_date');

    // Check created_at is formatted properly and matches regex pattern
    $formattedCreatedAt = $media->created_at->format($dateFormat);
    expect($formattedCreatedAt)->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');

    // Check updated_at is formatted properly and matches regex pattern
    $formattedUpdatedAt = $media->updated_at->format($dateFormat);
    expect($formattedUpdatedAt)->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
});

it('can handle media collections', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    $post->registerCollections([
        'avatars' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatars',
            'single' => false,
        ],
        'gallery' => [
            'disk' => 'public',
            'bucket' => 'gallery',
            'label' => 'gallery',
            'single' => false,
        ],
    ]);

    $avatarMedia = $post->addMedia($this->file)
        ->collection('avatars')
        ->upload();

    $galleryMedia = $post->addMedia($this->file)
        ->collection('gallery')
        ->upload();

    expect($post->hasMedia('avatars'))->toBeTrue();
    expect($post->hasMedia('gallery'))->toBeTrue();
    expect($post->getCollection('avatars'))->toHaveCount(1);
    expect($post->getCollection('gallery'))->toHaveCount(1);
});

it('can delete media', function () {
    $media = $this->post->addMedia($this->file)->upload();
    expect($this->post->mediaTotalCount())->toBe(1);

    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaTotalCount())->toBe(0);
    expect($this->post->mediaTotalCount(withTrashed: true))->toBe(1);
});

it('can get total media size', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($this->post->mediaTotalSize())->toBeGreaterThan(0);
});

it('can get total media count', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg', 50, 50))->upload();

    expect($this->post->mediaTotalCount())->toBe(2);
});

it('can check if model has media', function () {
    expect($this->post->hasMedia())->toBeFalse();

    $this->post->addMedia($this->file)->upload();

    expect($this->post->hasMedia())->toBeTrue();
});

it('can get all media', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    $media = $this->post->getMedia();
    expect($media)->toHaveCount(2);
});

it('returns null when uploading null files', function () {
    $result = $this->post->addMedia(null)->upload();

    expect($result)->toBeNull();
});

it('can upload with custom label and index', function () {
    $media = $this->post->addMedia($this->file)
        ->label('profile-picture')
        ->index(5)
        ->upload();

    expect($media->label)->toBe('profile-picture');
    expect($media->index)->toBe(5);
});

it('returns not null for temporary_url on local disk', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media->temporary_url)->not->toBeNull();
});

it('can get media collection as array', function () {
    $this->post->registerCollections([
        'default' => [
            'disk' => 'public',
            'bucket' => 'upload',
            'label' => null,
            'single' => false,
        ],
    ]);

    $this->post->addMedia($this->file)->upload();

    $array = $this->post->getCollectionArray('default');
    expect($array)->toBeArray()->not->toBeEmpty();
});

it('can soft delete and check with trashed', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaTotalCount())->toBe(0);
    expect($this->post->mediaTotalCount(withTrashed: true))->toBe(1);
    expect($this->post->mediaById($media->id))->toBeNull();
    expect($this->post->mediaById($media->id, withTrashed: true))->not->toBeNull();
});

it('can get mediable relationship', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media->mediable)->toBeInstanceOf(Post::class);
    expect($media->mediable->id)->toBe($this->post->id);
});

it('can check file exists on disk via upload service', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->upload();

    expect($service->fileExists($media->path))->toBeTrue();
    expect($service->fileExists('nonexistent/file.jpg'))->toBeFalse();
});

it('can get file size via upload service', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->upload();

    expect($service->fileSize($media->path))->toBeGreaterThan(0);
});

it('can get file metadata via upload service', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->upload();

    $metadata = $service->fileMetadata($media->path);
    expect($metadata)->toBeArray()
        ->toHaveKeys(['size', 'mimetype', 'last_modified']);
    expect($metadata['size'])->toBeGreaterThan(0);
});

it('can delete file from disk via upload service', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->upload();

    expect($service->fileExists($media->path))->toBeTrue();
    expect($service->deleteFile($media->path))->toBeTrue();
    expect($service->fileExists($media->path))->toBeFalse();
});
