<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Waad\Media\Media;

beforeEach(function () {
    Storage::fake('public');
    $this->post = createPost();
    $this->file = UploadedFile::fake()->image('test.jpg', 50, 50);
});

afterEach(function () {
    if (isset($this->post)) {
        $this->post->media()->forceDelete();
    }
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
    $files = [
        UploadedFile::fake()->image('test1.jpg', 50, 50),
        UploadedFile::fake()->image('test2.jpg', 50, 50),
    ];

    $mediaItems = $this->post->addMedia($files)->upload();

    expect($mediaItems)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);

    foreach ($mediaItems as $media) {
        expect($media)
            ->toBeInstanceOf(Media::class)
            ->basename->not->toBeEmpty();

        Storage::disk('public')->assertExists($media->path);
    }
});

it('returns null when uploading null files', function () {
    $result = $this->post->addMedia(null)->upload();

    expect($result)->toBeNull();
});

it('returns null when uploading empty array', function () {
    $result = $this->post->addMedia([])->upload();

    expect($result)->toBeNull();
});

it('can upload with custom label', function () {
    $media = $this->post->addMedia($this->file)
        ->label('profile-picture')
        ->upload();

    expect($media->label)->toBe('profile-picture');
});

it('can upload with custom index', function () {
    $media = $this->post->addMedia($this->file)
        ->index(5)
        ->upload();

    expect($media->index)->toBe(5);
});

it('can upload with custom label and index', function () {
    $media = $this->post->addMedia($this->file)
        ->label('profile-picture')
        ->index(5)
        ->upload();

    expect($media->label)->toBe('profile-picture');
    expect($media->index)->toBe(5);
});

it('auto-increments index for multiple uploads', function () {
    $files = [
        UploadedFile::fake()->image('test1.jpg', 50, 50),
        UploadedFile::fake()->image('test2.jpg', 50, 50),
        UploadedFile::fake()->image('test3.jpg', 50, 50),
    ];

    $mediaItems = $this->post->addMedia($files)->index(10)->upload();

    expect($mediaItems[0]->index)->toBe(10);
    expect($mediaItems[1]->index)->toBe(11);
    expect($mediaItems[2]->index)->toBe(12);
});

it('can upload with collection parameter in upload method', function () {
    $this->post->registerCollections([
        'avatars' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatars',
            'single' => false,
        ],
    ]);

    $media = $this->post->addMedia($this->file)->upload('avatars');

    expect($media->collection)->toBe('avatars');
});

it('can upload with disk parameter in upload method', function () {
    Storage::fake('custom');
    $media = $this->post->addMedia($this->file)->upload(disk: 'custom');

    expect($media->disk)->toBe('custom');
});

it('can upload with custom bucket', function () {
    $media = $this->post->addMedia($this->file)
        ->bucket('custom-bucket')
        ->upload();

    expect($media->bucket)->toBe('custom-bucket');
    expect(str_starts_with($media->path, 'custom-bucket/'))->toBeTrue();
});

it('stores correct file metadata for images', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media->mimetype)->toBe('image/jpeg');
    expect($media->filesize)->toBeGreaterThan(0);
    expect($media->filename)->toBe('test.jpg');
    expect($media->metadata)->toBeArray();
    expect($media->metadata)->toHaveKeys(['width', 'height']);
});

it('stores correct file metadata for non-image files', function () {
    $pdfFile = UploadedFile::fake()->create('document.pdf', 100);
    $media = $this->post->addMedia($pdfFile)->upload();

    expect($media->mimetype)->toBe('application/pdf');
    expect($media->filesize)->toBeGreaterThan(0);
    expect($media->filename)->toBe('document.pdf');
});

it('defaults to approved true on upload', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media->approved)->toBeTrue();
});

it('respects default_approved config set to false', function () {
    config(['media.default_approved' => false]);

    $media = $this->post->addMedia($this->file)->upload();

    expect($media->approved)->toBeFalse();
});

it('uses default collection when none specified', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media->collection)->toBe(config('media.default_collection'));
});

it('uses default disk when none specified', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media->disk)->toBe(config('media.disk'));
});

it('uses default bucket when none specified', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media->bucket)->toBe(config('media.bucket'));
});

it('can sync media by replacing old files', function () {
    $initialMedia = $this->post->addMedia($this->file)->upload();
    $newFile = UploadedFile::fake()->image('new.jpg');

    $syncedMedia = $this->post->syncMedia($newFile, [$initialMedia->id])->upload();

    Storage::disk('public')->assertExists($syncedMedia->path);
    expect($this->post->mediaTotalCount())->toBe(1);
});

it('can sync media with null files and still delete old ones', function () {
    $initialMedia = $this->post->addMedia($this->file)->upload();

    $this->post->syncMedia(null, [$initialMedia->id])->upload();

    expect($this->post->mediaTotalCount())->toBe(0);
    expect($this->post->mediaTotalCount(withTrashed: true))->toBe(1);
});

it('replaces old media in single collection on upload', function () {
    $this->post->registerCollections([
        'avatar' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatar',
            'single' => true,
        ],
    ]);

    $first = $this->post->addMedia($this->file)->collection('avatar')->upload();
    $second = $this->post->addMedia(UploadedFile::fake()->image('new.jpg'))->collection('avatar')->upload();

    expect($this->post->media()->where('collection', 'avatar')->count())->toBe(1);
    expect($this->post->media()->where('collection', 'avatar')->first()->id)->toBe($second->id);
});

it('assigns collection disk and bucket from registered collection', function () {
    $this->post->registerCollections([
        'gallery' => [
            'disk' => 'public',
            'bucket' => 'gallery-files',
            'label' => 'Gallery Item',
            'single' => false,
        ],
    ]);

    $media = $this->post->addMedia($this->file)->collection('gallery')->upload();

    expect($media->disk)->toBe('public');
    expect($media->bucket)->toBe('gallery-files');
    expect($media->label)->toBe('Gallery Item');
    expect($media->collection)->toBe('gallery');
});
