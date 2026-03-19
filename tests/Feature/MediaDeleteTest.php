<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

it('can delete media by id', function () {
    $media = $this->post->addMedia($this->file)->upload();
    expect($this->post->mediaTotalCount())->toBe(1);

    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaTotalCount())->toBe(0);
});

it('soft deletes media record', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaTotalCount())->toBe(0);
    expect($this->post->mediaTotalCount(withTrashed: true))->toBe(1);
});

it('can find soft deleted media with withTrashed', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $this->post->deleteMedia($media->id)->delete();

    expect($this->post->mediaById($media->id))->toBeNull();
    expect($this->post->mediaById($media->id, withTrashed: true))->not->toBeNull();
});

it('can delete multiple media by ids', function () {
    $media1 = $this->post->addMedia($this->file)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    $this->post->deleteMedia([$media1->id, $media2->id])->delete();

    expect($this->post->mediaTotalCount())->toBe(0);
    expect($this->post->mediaTotalCount(withTrashed: true))->toBe(2);
});

it('can delete media by Media instance', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $this->post->deleteMedia($media)->delete();

    expect($this->post->mediaTotalCount())->toBe(0);
});

it('returns null when deleting null', function () {
    $result = $this->post->deleteMedia(null)->delete();

    expect($result)->toBeNull();
});

it('does not delete media that belongs to another model', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $otherPost = createPost();
    $otherPost->deleteMedia($media->id)->delete();

    expect($this->post->mediaTotalCount())->toBe(1);
});

it('can delete all media for a model', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test3.jpg'))->upload();

    expect($this->post->mediaTotalCount())->toBe(3);

    $this->post->deleteMedia()->delete();

    expect($this->post->mediaTotalCount())->toBe(0);
    expect($this->post->mediaTotalCount(withTrashed: true))->toBe(3);
});
