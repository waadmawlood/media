<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Waad\Media\Media;
use Waad\Media\Services\MediaPrunableService;

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

it('can fetch soft-deleted media older than specified date', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $media->delete();

    Media::withTrashed()->where('id', $media->id)->update([
        'deleted_at' => now()->subDays(31),
    ]);

    $model = DB::table((new Media)->getTable());
    $service = new MediaPrunableService($model, now()->subDays(30));

    $result = $service->all();

    expect($result)->toBeInstanceOf(MediaPrunableService::class);
});

it('can group paths by disk', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $media->delete();

    Media::withTrashed()->where('id', $media->id)->update([
        'deleted_at' => now()->subDays(31),
    ]);

    $model = DB::table((new Media)->getTable());
    $service = new MediaPrunableService($model, now()->subDays(30));

    $result = $service->all()->paths();

    expect($result)->toBeInstanceOf(MediaPrunableService::class);
});

it('can delete prunable files from disk', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $path = $media->path;
    $media->delete();

    Media::withTrashed()->where('id', $media->id)->update([
        'deleted_at' => now()->subDays(31),
    ]);

    Storage::disk('public')->assertExists($path);

    $model = DB::table((new Media)->getTable());
    $service = new MediaPrunableService($model, now()->subDays(30));
    $service->prune();

    Storage::disk('public')->assertMissing($path);
});

it('can run full prune pipeline', function () {
    $media1 = $this->post->addMedia($this->file)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    $media1->delete();
    $media2->delete();

    Media::withTrashed()->whereIn('id', [$media1->id, $media2->id])->update([
        'deleted_at' => now()->subDays(31),
    ]);

    $model = DB::table((new Media)->getTable());
    $service = new MediaPrunableService($model, now()->subDays(30));
    $result = $service->prune();

    expect($result)->toBeInstanceOf(MediaPrunableService::class);
    Storage::disk('public')->assertMissing($media1->path);
    Storage::disk('public')->assertMissing($media2->path);
});

it('does not prune recently deleted media', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $path = $media->path;
    $media->delete();

    $model = DB::table((new Media)->getTable());
    $service = new MediaPrunableService($model, now()->subDays(30));
    $service->prune();

    Storage::disk('public')->assertExists($path);
});

it('handles empty result set gracefully', function () {
    $model = DB::table((new Media)->getTable());
    $service = new MediaPrunableService($model, now()->subDays(30));
    $result = $service->prune();

    expect($result)->toBeInstanceOf(MediaPrunableService::class);
});

it('paths returns self when called without all()', function () {
    $model = DB::table((new Media)->getTable());
    $service = new MediaPrunableService($model, now()->subDays(30));

    $result = $service->paths();

    expect($result)->toBeInstanceOf(MediaPrunableService::class);
});
