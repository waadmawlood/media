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

it('can check file exists on disk', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->upload();

    expect($service->fileExists($media->path))->toBeTrue();
    expect($service->fileExists('nonexistent/file.jpg'))->toBeFalse();
});

it('can get file size', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->upload();

    expect($service->fileSize($media->path))->toBeGreaterThan(0);
});

it('returns null for file size of non-existent file', function () {
    $service = $this->post->addMedia($this->file);
    $service->upload();

    expect($service->fileSize('nonexistent/file.jpg'))->toBeNull();
});

it('can get file metadata', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->upload();

    $metadata = $service->fileMetadata($media->path);

    expect($metadata)->toBeArray()
        ->toHaveKeys(['size', 'mimetype', 'last_modified']);
    expect($metadata['size'])->toBeGreaterThan(0);
    expect($metadata['mimetype'])->toBe('image/jpeg');
});

it('returns null for metadata of non-existent file', function () {
    $service = $this->post->addMedia($this->file);
    $service->upload();

    expect($service->fileMetadata('nonexistent/file.jpg'))->toBeNull();
});

it('can delete file from disk', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->upload();

    expect($service->fileExists($media->path))->toBeTrue();
    expect($service->deleteFile($media->path))->toBeTrue();
    expect($service->fileExists($media->path))->toBeFalse();
});

it('returns false for fileExists when checking non-existent path', function () {
    $service = $this->post->addMedia($this->file);
    $service->upload();

    expect($service->fileExists('does/not/exist.jpg'))->toBeFalse();
});

it('can set disk via fluent api', function () {
    Storage::fake('custom');
    $service = $this->post->addMedia($this->file);
    $media = $service->disk('custom')->upload();

    expect($media->disk)->toBe('custom');
});

it('can set bucket via fluent api', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->bucket('my-bucket')->upload();

    expect($media->bucket)->toBe('my-bucket');
});

it('can chain multiple fluent methods', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service
        ->label('test-label')
        ->index(42)
        ->bucket('chain-bucket')
        ->upload();

    expect($media->label)->toBe('test-label');
    expect($media->index)->toBe(42);
    expect($media->bucket)->toBe('chain-bucket');
});

it('sync method delegates to upload', function () {
    $service = $this->post->addMedia($this->file);
    $media = $service->sync();

    expect($media)->not->toBeNull();
    expect($media->basename)->not->toBeEmpty();
});
