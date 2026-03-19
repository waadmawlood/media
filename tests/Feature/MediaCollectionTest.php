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

it('can register and use media collections', function () {
    $this->post->registerCollections([
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

    $this->post->addMedia($this->file)->collection('avatars')->upload();
    $this->post->addMedia(UploadedFile::fake()->image('g.jpg'))->collection('gallery')->upload();

    expect($this->post->hasMedia('avatars'))->toBeTrue();
    expect($this->post->hasMedia('gallery'))->toBeTrue();
    expect($this->post->getCollection('avatars'))->toHaveCount(1);
    expect($this->post->getCollection('gallery'))->toHaveCount(1);
});

it('returns empty collection for unregistered collection name', function () {
    $result = $this->post->getCollection('nonexistent');

    expect($result)->toBeEmpty();
});

it('returns default collection when no name provided', function () {
    $this->post->addMedia($this->file)->upload();

    $collection = $this->post->getCollection();
    expect($collection)->toHaveCount(1);
});

it('can get single collection (returns Media instance)', function () {
    $this->post->registerCollections([
        'avatar' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatar',
            'single' => true,
        ],
    ]);

    $media = $this->post->addMedia($this->file)->collection('avatar')->upload();
    $result = $this->post->getCollection('avatar');

    expect($result)->toBeInstanceOf(Media::class);
    expect($result->id)->toBe($media->id);
});

it('returns null for empty single collection', function () {
    $this->post->registerCollections([
        'avatar' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatar',
            'single' => true,
        ],
    ]);

    $result = $this->post->getCollection('avatar');

    expect($result)->toBeNull();
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

it('returns empty array for collection with no media', function () {
    $this->post->registerCollections([
        'empty' => [
            'disk' => 'public',
            'bucket' => 'empty',
            'label' => null,
            'single' => false,
        ],
    ]);

    $array = $this->post->getCollectionArray('empty');
    expect($array)->toBeArray()->toBeEmpty();
});

// --- Collection URLs ---

it('can get collection urls for multiple collection', function () {
    $this->post->registerCollections([
        'gallery' => [
            'disk' => 'public',
            'bucket' => 'gallery',
            'label' => 'gallery',
            'single' => false,
        ],
    ]);

    $media1 = $this->post->addMedia($this->file)->collection('gallery')->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg', 50, 50))
        ->collection('gallery')
        ->upload();

    $urls = $this->post->getCollectionUrls('gallery');

    expect($urls)->toHaveCount(2);
    expect($urls->first())->toBe($media1->temporary_url);
    expect($urls->last())->toBe($media2->temporary_url);
});

it('can get collection urls for single collection', function () {
    $this->post->registerCollections([
        'avatar' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatar',
            'single' => true,
        ],
    ]);

    $media = $this->post->addMedia($this->file)->collection('avatar')->upload();
    $url = $this->post->getCollectionUrls('avatar');

    expect($url)->toBe($media->temporary_url);
});

it('returns empty collection for empty multiple collection urls', function () {
    $this->post->registerCollections([
        'gallery' => [
            'disk' => 'public',
            'bucket' => 'gallery',
            'label' => 'gallery',
            'single' => false,
        ],
    ]);

    $urls = $this->post->getCollectionUrls('gallery');

    expect($urls)->toBeEmpty();
});

it('returns null for empty single collection urls', function () {
    $this->post->registerCollections([
        'avatar' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatar',
            'single' => true,
        ],
    ]);

    $url = $this->post->getCollectionUrls('avatar');

    expect($url)->toBeNull();
});

// --- Collection Groups ---

it('can get collection groups with all collections', function () {
    $this->post->registerCollections([
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

    $this->post->addMedia($this->file)->collection('avatars')->upload();
    $this->post->addMedia(UploadedFile::fake()->image('gallery1.jpg', 50, 50))
        ->collection('gallery')
        ->upload();

    $groups = $this->post->getCollectionGroups();

    expect($groups)->toBeArray()->toHaveKeys(['avatars', 'gallery']);
    expect($groups['avatars'])->toHaveCount(1);
    expect($groups['gallery'])->toHaveCount(1);
});

it('can get collection groups with only filter', function () {
    $this->post->registerCollections([
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

    $this->post->addMedia($this->file)->collection('avatars')->upload();
    $this->post->addMedia(UploadedFile::fake()->image('gallery1.jpg', 50, 50))
        ->collection('gallery')
        ->upload();

    $groups = $this->post->getCollectionGroups(only: ['avatars']);

    expect($groups)->toBeArray()
        ->toHaveKey('avatars')
        ->not->toHaveKey('gallery');
});

it('can get collection groups with except filter', function () {
    $this->post->registerCollections([
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

    $this->post->addMedia($this->file)->collection('avatars')->upload();
    $this->post->addMedia(UploadedFile::fake()->image('gallery1.jpg', 50, 50))
        ->collection('gallery')
        ->upload();

    $groups = $this->post->getCollectionGroups(except: ['gallery']);

    expect($groups)->toBeArray()
        ->toHaveKey('avatars')
        ->not->toHaveKey('gallery');
});

it('can get collection groups with single collection', function () {
    $this->post->registerCollections([
        'avatar' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatar',
            'single' => true,
        ],
        'gallery' => [
            'disk' => 'public',
            'bucket' => 'gallery',
            'label' => 'gallery',
            'single' => false,
        ],
    ]);

    $media = $this->post->addMedia($this->file)->collection('avatar')->upload();
    $this->post->addMedia(UploadedFile::fake()->image('gallery1.jpg', 50, 50))
        ->collection('gallery')
        ->upload();
    $this->post->addMedia(UploadedFile::fake()->image('gallery2.jpg', 50, 50))
        ->collection('gallery')
        ->upload();

    $groups = $this->post->getCollectionGroups();

    expect($groups)->toBeArray()->toHaveKeys(['avatar', 'gallery']);
    expect($groups['avatar'])->toBeInstanceOf(Media::class);
    expect($groups['avatar']->id)->toBe($media->id);
    expect($groups['gallery'])->toBeArray()->toHaveCount(2);
});

it('returns empty arrays for collection groups with no media', function () {
    $this->post->registerCollections([
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

    $groups = $this->post->getCollectionGroups();

    expect($groups)->toBeArray()->toHaveKeys(['avatars', 'gallery']);
    expect($groups['avatars'])->toBeEmpty();
    expect($groups['gallery'])->toBeEmpty();
});

// --- Collection Group URLs ---

it('can get collection group urls across multiple collections', function () {
    $this->post->registerCollections([
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

    $avatar = $this->post->addMedia($this->file)->collection('avatars')->upload();
    $galleryItem = $this->post->addMedia(UploadedFile::fake()->image('gallery1.jpg', 50, 50))
        ->collection('gallery')
        ->upload();

    $urls = $this->post->getCollectionGroupUrls();

    expect($urls)->toHaveCount(2);
    expect($urls)->toContain($avatar->temporary_url);
    expect($urls)->toContain($galleryItem->temporary_url);
});

it('can get collection group urls with only filter', function () {
    $this->post->registerCollections([
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

    $avatar = $this->post->addMedia($this->file)->collection('avatars')->upload();
    $this->post->addMedia(UploadedFile::fake()->image('gallery1.jpg', 50, 50))
        ->collection('gallery')
        ->upload();

    $urls = $this->post->getCollectionGroupUrls(only: ['avatars']);

    expect($urls)->toHaveCount(1);
    expect($urls)->toContain($avatar->temporary_url);
});

it('can get collection group urls with except filter', function () {
    $this->post->registerCollections([
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

    $this->post->addMedia($this->file)->collection('avatars')->upload();
    $galleryItem = $this->post->addMedia(UploadedFile::fake()->image('gallery1.jpg', 50, 50))
        ->collection('gallery')
        ->upload();

    $urls = $this->post->getCollectionGroupUrls(except: ['avatars']);

    expect($urls)->toHaveCount(1);
    expect($urls)->toContain($galleryItem->temporary_url);
});

it('returns empty collection for collection group urls with no media', function () {
    $this->post->registerCollections([
        'avatars' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatars',
            'single' => false,
        ],
    ]);

    $urls = $this->post->getCollectionGroupUrls();

    expect($urls)->toBeEmpty();
});

it('can get collection group urls with single collection', function () {
    $this->post->registerCollections([
        'avatar' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatar',
            'single' => true,
        ],
        'gallery' => [
            'disk' => 'public',
            'bucket' => 'gallery',
            'label' => 'gallery',
            'single' => false,
        ],
    ]);

    $avatar = $this->post->addMedia($this->file)->collection('avatar')->upload();
    $galleryItem = $this->post->addMedia(UploadedFile::fake()->image('gallery1.jpg', 50, 50))
        ->collection('gallery')
        ->upload();

    $urls = $this->post->getCollectionGroupUrls();

    expect($urls)->toHaveCount(2);
    expect($urls)->toContain($avatar->temporary_url);
    expect($urls)->toContain($galleryItem->temporary_url);
});

it('returns default collection from registerCollections when none set', function () {
    $collections = $this->post->registerCollections();

    expect($collections)->toBeArray();
    expect($collections)->toHaveKey(config('media.default_collection'));

    $defaultCollection = $collections[config('media.default_collection')];
    expect($defaultCollection['disk'])->toBe(config('media.disk'));
    expect($defaultCollection['bucket'])->toBe(config('media.bucket'));
    expect($defaultCollection['single'])->toBeFalse();
});

it('can overwrite registered collections', function () {
    $this->post->registerCollections([
        'first' => ['disk' => 'public', 'bucket' => 'first', 'label' => null, 'single' => false],
    ]);

    $this->post->registerCollections([
        'second' => ['disk' => 'public', 'bucket' => 'second', 'label' => null, 'single' => false],
    ]);

    $collections = $this->post->registerCollections();
    expect($collections)->toHaveKey('second');
    expect($collections)->not->toHaveKey('first');
});
