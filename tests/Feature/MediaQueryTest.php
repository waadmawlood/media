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

// --- mediaById ---

it('can get media by id', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($this->post->mediaById($media->id))
        ->toBeInstanceOf(Media::class)
        ->id->toBe($media->id);

    expect($this->post->mediaById(999))->toBeNull();
});

it('can get media by id with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaById($media->id))->toBeNull();
    expect($this->post->mediaById($media->id, withTrashed: true))
        ->toBeInstanceOf(Media::class)
        ->id->toBe($media->id);
});

// --- mediaByMimeType ---

it('can get media by mime type', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->create('document.pdf', 100))->upload();

    expect($this->post->mediaByMimeType('image/jpeg')->count())->toBe(1);
    expect($this->post->mediaByMimeType('application/pdf')->count())->toBe(1);
    expect($this->post->mediaByMimeType('video/mp4'))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('can get media by mime type with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaByMimeType('image/jpeg')->count())->toBe(0);
    expect($this->post->mediaByMimeType('image/jpeg', withTrashed: true)->count())->toBe(1);
});

// --- mediaByMimeTypeByCollection ---

it('can get media by mime type and collection', function () {
    $this->post->registerCollections([
        'avatars' => ['disk' => 'public', 'bucket' => 'avatars', 'label' => 'avatars', 'single' => false],
        'gallery' => ['disk' => 'public', 'bucket' => 'gallery', 'label' => 'gallery', 'single' => false],
    ]);

    $this->post->addMedia($this->file)->collection('avatars')->upload();
    $this->post->addMedia(UploadedFile::fake()->create('doc.pdf', 100))->collection('gallery')->upload();
    $this->post->addMedia(UploadedFile::fake()->image('g.jpg'))->collection('gallery')->upload();

    expect($this->post->mediaByMimeTypeByCollection('image/jpeg', 'avatars'))->toHaveCount(1);
    expect($this->post->mediaByMimeTypeByCollection('image/jpeg', 'gallery'))->toHaveCount(1);
    expect($this->post->mediaByMimeTypeByCollection('application/pdf', 'gallery'))->toHaveCount(1);
    expect($this->post->mediaByMimeTypeByCollection('video/mp4'))
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('can get media by mime type and collection with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->deleteMedia($media->id)->delete();
    $defaultCollection = config('media.default_collection');

    expect($this->post->mediaByMimeTypeByCollection('image/jpeg', $defaultCollection))->toBeEmpty();
    expect($this->post->mediaByMimeTypeByCollection('image/jpeg', $defaultCollection, withTrashed: true))->toHaveCount(1);
});

// --- mediaApproved ---

it('can filter approved media', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();
    $media3 = $this->post->addMedia(UploadedFile::fake()->image('test3.jpg'))->upload();
    $media3->disApprove();

    expect($this->post->mediaApproved()->count())->toBe(2);
    expect($this->post->mediaApproved(false)->count())->toBe(1);
});

it('can filter approved media with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaApproved(true)->count())->toBe(0);
    expect($this->post->mediaApproved(true, withTrashed: true)->count())->toBe(1);
});

// --- getFirstMedia / getLastMedia ---

it('can get first and last media', function () {
    $first = $this->post->addMedia($this->file)->upload();
    sleep(1);
    $last = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    expect($this->post->getFirstMedia()->id)->toBe($first->id);
    expect($this->post->getLastMedia()->id)->toBe($last->id);
});

it('returns null when no first or last media', function () {
    expect($this->post->getFirstMedia())->toBeNull();
    expect($this->post->getLastMedia())->toBeNull();
});

// --- getFirstMediaByCollection / getLastMediaByCollection ---

it('can get first and last media by collection', function () {
    $this->post->registerCollections([
        'gallery' => ['disk' => 'public', 'bucket' => 'gallery', 'label' => 'gallery', 'single' => false],
    ]);

    $first = $this->post->addMedia($this->file)->collection('gallery')->upload();
    sleep(1);
    $last = $this->post->addMedia(UploadedFile::fake()->image('second.jpg'))->collection('gallery')->upload();

    expect($this->post->getFirstMediaByCollection('gallery'))
        ->toBeInstanceOf(Media::class)
        ->id->toBe($first->id);

    expect($this->post->getLastMediaByCollection('gallery'))
        ->toBeInstanceOf(Media::class)
        ->id->toBe($last->id);
});

it('returns null when no media in collection for first or last', function () {
    expect($this->post->getFirstMediaByCollection('gallery'))->toBeNull();
    expect($this->post->getLastMediaByCollection('gallery'))->toBeNull();
});

it('uses default collection when no name provided for first and last by collection', function () {
    $first = $this->post->addMedia($this->file)->upload();
    sleep(1);
    $last = $this->post->addMedia(UploadedFile::fake()->image('second.jpg'))->upload();

    expect($this->post->getFirstMediaByCollection()->id)->toBe($first->id);
    expect($this->post->getLastMediaByCollection()->id)->toBe($last->id);
});

// --- mediaTotalCount / mediaTotalSize ---

it('can get total media count and size', function () {
    expect($this->post->mediaTotalCount())->toBe(0);
    expect($this->post->mediaTotalSize())->toBe(0);

    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg', 50, 50))->upload();

    expect($this->post->mediaTotalCount())->toBe(2);
    expect($this->post->mediaTotalSize())->toBeGreaterThan(0);
});

it('can get total media count and size with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg', 50, 50))->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaTotalCount())->toBe(1);
    expect($this->post->mediaTotalCount(withTrashed: true))->toBe(2);
    expect($this->post->mediaTotalSize(withTrashed: true))->toBeGreaterThan($this->post->mediaTotalSize());
});

// --- mediaTotalCountByCollection / mediaTotalSizeByCollection ---

it('can get total media count and size by collection', function () {
    $this->post->registerCollections([
        'avatars' => ['disk' => 'public', 'bucket' => 'avatars', 'label' => 'avatars', 'single' => false],
        'gallery' => ['disk' => 'public', 'bucket' => 'gallery', 'label' => 'gallery', 'single' => false],
    ]);

    $this->post->addMedia($this->file)->collection('avatars')->upload();
    $this->post->addMedia(UploadedFile::fake()->image('g1.jpg'))->collection('gallery')->upload();
    $this->post->addMedia(UploadedFile::fake()->image('g2.jpg'))->collection('gallery')->upload();

    expect($this->post->mediaTotalCountByCollection('avatars'))->toBe(1);
    expect($this->post->mediaTotalCountByCollection('gallery'))->toBe(2);
    expect($this->post->mediaTotalSizeByCollection('avatars'))->toBeGreaterThan(0);
    expect($this->post->mediaTotalSizeByCollection('gallery'))->toBeGreaterThan(0);
});

it('returns zero for collection with no media', function () {
    expect($this->post->mediaTotalCountByCollection('avatars'))->toBe(0);
    expect($this->post->mediaTotalSizeByCollection('avatars'))->toBe(0);
});

it('can get total media count and size by collection with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->deleteMedia($media->id)->delete();
    $defaultCollection = config('media.default_collection');

    expect($this->post->mediaTotalCountByCollection($defaultCollection))->toBe(0);
    expect($this->post->mediaTotalCountByCollection($defaultCollection, withTrashed: true))->toBe(1);
    expect($this->post->mediaTotalSizeByCollection($defaultCollection))->toBe(0);
    expect($this->post->mediaTotalSizeByCollection($defaultCollection, withTrashed: true))->toBeGreaterThan(0);
});

it('returns zero when collection is blank', function () {
    config(['media.default_collection' => null]);

    expect($this->post->mediaTotalCountByCollection(null))->toBe(0);
    expect($this->post->mediaTotalSizeByCollection(null))->toBe(0);
});

// --- hasMedia ---

it('can check if model has media', function () {
    expect($this->post->hasMedia())->toBeFalse();

    $this->post->addMedia($this->file)->upload();

    expect($this->post->hasMedia())->toBeTrue();
});

it('can check if model has media in specific collection', function () {
    $this->post->registerCollections([
        'avatars' => ['disk' => 'public', 'bucket' => 'avatars', 'label' => 'avatars', 'single' => false],
    ]);

    expect($this->post->hasMedia('avatars'))->toBeFalse();

    $this->post->addMedia($this->file)->collection('avatars')->upload();

    expect($this->post->hasMedia('avatars'))->toBeTrue();
});

it('can check hasMedia with withTrashed', function () {
    $this->post->registerCollections([
        'avatars' => ['disk' => 'public', 'bucket' => 'avatars', 'label' => 'avatars', 'single' => false],
    ]);

    $media = $this->post->addMedia($this->file)->collection('avatars')->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->hasMedia('avatars'))->toBeFalse();
    expect($this->post->hasMedia('avatars', withTrashed: true))->toBeTrue();
});

// --- getMedia ---

it('can get all media', function () {
    expect($this->post->getMedia())
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();

    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    expect($this->post->getMedia())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
});
