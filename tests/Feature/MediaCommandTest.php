<?php

use Illuminate\Http\UploadedFile;
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

// --- media:prune command ---

it('prunes old soft-deleted media via command', function () {
    config(['media.prune_media_after_day' => 30]);

    $media = $this->post->addMedia($this->file)->upload();
    $path = $media->path;
    $media->delete();

    Media::withTrashed()->where('id', $media->id)->update([
        'deleted_at' => now()->subDays(31),
    ]);

    Storage::disk('public')->assertExists($path);

    $this->artisan('media:prune')
        ->expectsOutputToContain('Successfully pruned')
        ->assertExitCode(0);

    Storage::disk('public')->assertMissing($path);
});

it('does not prune recently deleted media via command', function () {
    config(['media.prune_media_after_day' => 30]);

    $media = $this->post->addMedia($this->file)->upload();
    $path = $media->path;
    $media->delete();

    $this->artisan('media:prune')
        ->assertExitCode(0);

    Storage::disk('public')->assertExists($path);
});

it('force deletes old records via prune command', function () {
    config(['media.prune_media_after_day' => 30]);

    $media = $this->post->addMedia($this->file)->upload();
    $media->delete();

    Media::withTrashed()->where('id', $media->id)->update([
        'deleted_at' => now()->subDays(31),
    ]);

    $this->artisan('media:prune')->assertExitCode(0);

    expect(Media::withTrashed()->find($media->id))->toBeNull();
});

it('fails when prune_media_after_day is not numeric', function () {
    config(['media.prune_media_after_day' => 'invalid']);

    $this->artisan('media:prune')
        ->expectsOutputToContain('Failed to prune')
        ->assertExitCode(1);
});

// --- media:link command ---

it('warns when no shortcuts are configured', function () {
    config(['media.shortcut' => []]);

    $this->artisan('media:link')
        ->expectsOutputToContain('No media shortcuts configured');
});

it('reports error for invalid link configuration', function () {
    config(['media.shortcut' => ['nonexistent_disk' => 'storage']]);

    $this->artisan('media:link')
        ->assertExitCode(0);
});
