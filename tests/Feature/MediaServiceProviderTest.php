<?php

use Illuminate\Support\Facades\Artisan;
use Waad\Media\Commands\MediaLinkCommand;
use Waad\Media\Commands\MediaPrune;
use Waad\Media\Media;

it('merges package config', function () {
    expect(config('media'))->toBeArray();
    expect(config('media.model'))->toBe(Media::class);
    expect(config('media.table_name'))->toBe('media');
});

it('has default disk configuration', function () {
    expect(config('media.disk'))->not->toBeNull();
});

it('has default bucket configuration', function () {
    expect(config('media.bucket'))->not->toBeNull();
});

it('has default collection configuration', function () {
    expect(config('media.default_collection'))->not->toBeNull();
});

it('has s3 ttl configuration', function () {
    expect(config('media.s3.default_ttl_temporary_url'))->not->toBeNull();
});

it('has prune_media_after_day configuration', function () {
    expect(config('media.prune_media_after_day'))->not->toBeNull();
});

it('has default_approved configuration', function () {
    expect(config('media.default_approved'))->not->toBeNull();
});

it('has url toggle configurations', function () {
    expect(config('media.enable_full_url'))->toBeBool();
    expect(config('media.enable_temporary_url'))->toBeBool();
});

it('registers media:prune command', function () {
    $commands = Artisan::all();

    expect($commands)->toHaveKey('media:prune');
    expect($commands['media:prune'])->toBeInstanceOf(MediaPrune::class);
});

it('registers media:link command', function () {
    $commands = Artisan::all();

    expect($commands)->toHaveKey('media:link');
    expect($commands['media:link'])->toBeInstanceOf(MediaLinkCommand::class);
});
