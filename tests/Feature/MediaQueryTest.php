<?php

use Illuminate\Database\Eloquent\Builder;
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

it('can get media by id', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($this->post->mediaById($media->id))
        ->toBeInstanceOf(Media::class)
        ->id->toBe($media->id);
});

it('returns null for non-existent media id', function () {
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

it('can get media by mime type', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->create('document.pdf', 100))->upload();

    expect($this->post->mediaByMimeType('image/jpeg')->count())->toBe(1);
    expect($this->post->mediaByMimeType('application/pdf')->count())->toBe(1);
});

it('returns empty collection for non-matching mime type', function () {
    $this->post->addMedia($this->file)->upload();

    $result = $this->post->mediaByMimeType('video/mp4');

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toBeEmpty();
});

it('can get media by mime type with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaByMimeType('image/jpeg')->count())->toBe(0);
    expect($this->post->mediaByMimeType('image/jpeg', withTrashed: true)->count())->toBe(1);
});

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

it('can get total media count', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg', 50, 50))->upload();

    expect($this->post->mediaTotalCount())->toBe(2);
});

it('can get total media count with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg', 50, 50))->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaTotalCount())->toBe(1);
    expect($this->post->mediaTotalCount(withTrashed: true))->toBe(2);
});

it('can get total media size', function () {
    $this->post->addMedia($this->file)->upload();

    expect($this->post->mediaTotalSize())->toBeGreaterThan(0);
});

it('returns zero size when no media', function () {
    expect($this->post->mediaTotalSize())->toBe(0);
});

it('can get total media size with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaTotalSize())->toBe(0);
    expect($this->post->mediaTotalSize(withTrashed: true))->toBeGreaterThan(0);
});

it('can check if model has media', function () {
    expect($this->post->hasMedia())->toBeFalse();

    $this->post->addMedia($this->file)->upload();

    expect($this->post->hasMedia())->toBeTrue();
});

it('can check if model has media in specific collection', function () {
    $this->post->registerCollections([
        'avatars' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatars',
            'single' => false,
        ],
    ]);

    expect($this->post->hasMedia('avatars'))->toBeFalse();

    $this->post->addMedia($this->file)->collection('avatars')->upload();

    expect($this->post->hasMedia('avatars'))->toBeTrue();
});

it('can check hasMedia with withTrashed', function () {
    $this->post->registerCollections([
        'avatars' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatars',
            'single' => false,
        ],
    ]);

    $media = $this->post->addMedia($this->file)->collection('avatars')->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->hasMedia('avatars'))->toBeFalse();
    expect($this->post->hasMedia('avatars', withTrashed: true))->toBeTrue();
});

it('can get all media', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    $media = $this->post->getMedia();

    expect($media)->toBeInstanceOf(Collection::class);
    expect($media)->toHaveCount(2);
});

it('returns empty collection when no media', function () {
    $media = $this->post->getMedia();

    expect($media)->toBeInstanceOf(Collection::class);
    expect($media)->toBeEmpty();
});

it('can get media query builder', function () {
    $query = $this->post->getMediaQuery();

    expect($query)->toBeInstanceOf(Builder::class);
});

it('can use media query builder for custom queries', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->create('doc.pdf', 100))->upload();

    $images = $this->post->getMediaQuery()
        ->where('mimetype', 'like', 'image/%')
        ->get();

    expect($images)->toHaveCount(1);
});
