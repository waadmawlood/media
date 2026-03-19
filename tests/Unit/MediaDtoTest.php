<?php

use Waad\Media\Dto\MediaDto;

it('sets path and basename from constructor', function () {
    $dto = new MediaDto('upload/test-file.jpg');

    expect($dto->path)->toBe('upload/test-file.jpg');
    expect($dto->basename)->toBe('test-file.jpg');
});

it('extracts basename from nested path', function () {
    $dto = new MediaDto('some/deep/nested/path/image.png');

    expect($dto->basename)->toBe('image.png');
});

it('handles path with no directory', function () {
    $dto = new MediaDto('file.txt');

    expect($dto->path)->toBe('file.txt');
    expect($dto->basename)->toBe('file.txt');
});

it('allows setting all public properties', function () {
    $dto = new MediaDto('upload/file.jpg');

    $dto->filename = 'original.jpg';
    $dto->index = 3;
    $dto->extension = 'jpg';
    $dto->fileSize = 1024;
    $dto->mimeType = 'image/jpeg';
    $dto->label = 'profile';
    $dto->collection = 'avatars';
    $dto->disk = 'public';
    $dto->bucket = 'upload';
    $dto->metadata = ['width' => 100, 'height' => 200];

    expect($dto->filename)->toBe('original.jpg');
    expect($dto->index)->toBe(3);
    expect($dto->extension)->toBe('jpg');
    expect($dto->fileSize)->toBe(1024);
    expect($dto->mimeType)->toBe('image/jpeg');
    expect($dto->label)->toBe('profile');
    expect($dto->collection)->toBe('avatars');
    expect($dto->disk)->toBe('public');
    expect($dto->bucket)->toBe('upload');
    expect($dto->metadata)->toBe(['width' => 100, 'height' => 200]);
});

it('handles null metadata', function () {
    $dto = new MediaDto('upload/file.pdf');
    $dto->metadata = null;

    expect($dto->metadata)->toBeNull();
});
