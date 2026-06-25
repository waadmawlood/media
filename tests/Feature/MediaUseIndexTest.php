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

// --- orderByIndex basic ---

it('returns the model instance for fluent chaining', function () {
    expect($this->post->orderByIndexMedia())->toBeInstanceOf(\Tests\App\Models\Post::class);
});

it('defaults to true', function () {
    expect($this->post->useIndexMedia)->toBeTrue();
});

it('can be set to false', function () {
    $this->post->orderByIndexMedia(false);
    expect($this->post->useIndexMedia)->toBeFalse();
});

it('can be re-enabled', function () {
    $this->post->orderByIndexMedia(false)->orderByIndexMedia();
    expect($this->post->useIndexMedia)->toBeTrue();
});

// --- getMedia ---

it('orders getMedia by index when orderByIndex is enabled', function () {
    $media1 = $this->post->addMedia($this->file)->index(3)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->index(1)->upload();
    $media3 = $this->post->addMedia(UploadedFile::fake()->image('test3.jpg'))->index(2)->upload();

    $media = $this->post->orderByIndexMedia()->getMedia();

    expect($media)->toHaveCount(3);
    expect($media->first()->id)->toBe($media2->id);
    expect($media->last()->id)->toBe($media1->id);
});

it('does not order getMedia by index when orderByIndex is not enabled', function () {
    $media1 = $this->post->addMedia($this->file)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    $media = $this->post->getMedia();

    expect($media)->toHaveCount(2);
    expect($media->first()->id)->toBe($media1->id);
    expect($media->last()->id)->toBe($media2->id);
});

// --- getCollection ---

it('orders getCollection by index when orderByIndex is enabled', function () {
    $media1 = $this->post->addMedia($this->file)->index(2)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->index(1)->upload();

    $media = $this->post->orderByIndexMedia()->getCollection();

    expect($media)->toHaveCount(2);
    expect($media->first()->id)->toBe($media2->id);
    expect($media->last()->id)->toBe($media1->id);
});

// --- getCollectionArray ---

it('orders getCollectionArray by index when orderByIndex is enabled', function () {
    $media1 = $this->post->addMedia($this->file)->index(2)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->index(1)->upload();

    $media = $this->post->orderByIndexMedia()->getCollectionArray();

    expect($media)->toHaveCount(2);
    expect($media[0]['id'])->toBe($media2->id);
    expect($media[1]['id'])->toBe($media1->id);
});

// --- getCollectionUrls ---

it('orders getCollectionUrls by index when orderByIndex is enabled', function () {
    $media1 = $this->post->addMedia($this->file)->index(2)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->index(1)->upload();

    $urls = $this->post->orderByIndexMedia()->getCollectionUrls();

    expect($urls)->toHaveCount(2);
    expect($urls->first())->toBe($media2->full_url);
    expect($urls->last())->toBe($media1->full_url);
});

// --- mediaByMimeType ---

it('orders mediaByMimeType by index when orderByIndex is enabled', function () {
    $media1 = $this->post->addMedia($this->file)->index(3)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->index(1)->upload();
    $media3 = $this->post->addMedia(UploadedFile::fake()->image('test3.jpg'))->index(2)->upload();

    $media = $this->post->orderByIndexMedia()->mediaByMimeType('image/jpeg');

    expect($media)->toHaveCount(3);
    expect($media->first()->id)->toBe($media2->id);
    expect($media->last()->id)->toBe($media1->id);
});

// --- mediaByMimeTypeByCollection ---

it('orders mediaByMimeTypeByCollection by index when orderByIndex is enabled', function () {
    $this->post->registerCollections([
        'gallery' => ['disk' => 'public', 'bucket' => 'gallery', 'label' => 'gallery', 'single' => false],
    ]);

    $media1 = $this->post->addMedia($this->file)->collection('gallery')->index(2)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->collection('gallery')->index(1)->upload();

    $media = $this->post->orderByIndexMedia()->mediaByMimeTypeByCollection('image/jpeg', 'gallery');

    expect($media)->toHaveCount(2);
    expect($media->first()->id)->toBe($media2->id);
    expect($media->last()->id)->toBe($media1->id);
});

// --- mediaApproved ---

it('orders mediaApproved by index when orderByIndex is enabled', function () {
    $media1 = $this->post->addMedia($this->file)->index(3)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->index(1)->upload();
    $media3 = $this->post->addMedia(UploadedFile::fake()->image('test3.jpg'))->index(2)->upload();
    $media3->disApprove();

    $media = $this->post->orderByIndexMedia()->mediaApproved();

    expect($media)->toHaveCount(2);
    expect($media->first()->id)->toBe($media2->id);
    expect($media->last()->id)->toBe($media1->id);
});

// --- getCollectionGroups ---

it('orders getCollectionGroups by index when orderByIndex is enabled', function () {
    $this->post->registerCollections([
        'gallery' => ['disk' => 'public', 'bucket' => 'gallery', 'label' => 'gallery', 'single' => false],
    ]);

    $media1 = $this->post->addMedia($this->file)->collection('gallery')->index(2)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->collection('gallery')->index(1)->upload();

    $groups = $this->post->orderByIndexMedia()->getCollectionGroups();

    expect($groups['gallery'])->toHaveCount(2);
    expect($groups['gallery'][0]->id)->toBe($media2->id);
    expect($groups['gallery'][1]->id)->toBe($media1->id);
});

// --- getCollectionGroupUrls ---

it('orders getCollectionGroupUrls by index when orderByIndex is enabled', function () {
    $this->post->registerCollections([
        'gallery' => ['disk' => 'public', 'bucket' => 'gallery', 'label' => 'gallery', 'single' => false],
    ]);

    $media1 = $this->post->addMedia($this->file)->collection('gallery')->index(2)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->collection('gallery')->index(1)->upload();

    $urls = $this->post->orderByIndexMedia()->getCollectionGroupUrls();

    expect($urls)->toHaveCount(2);
    expect($urls->first())->toBe($media2->full_url);
    expect($urls->last())->toBe($media1->full_url);
});

// --- single collection ---

it('returns single collection media by index when orderByIndex is enabled', function () {
    $this->post->registerCollections([
        'avatar' => ['disk' => 'public', 'bucket' => 'avatars', 'label' => 'avatar', 'single' => true],
    ]);

    $media1 = $this->post->addMedia($this->file)->collection('avatar')->index(2)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->collection('avatar')->index(1)->upload();

    $media = $this->post->orderByIndexMedia()->getCollection('avatar');

    expect($media)->toBeInstanceOf(Media::class);
    expect($media->id)->toBe($media2->id);
});
