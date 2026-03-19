<?php

use Waad\Media\Helpers\FileSystem;

beforeEach(function () {
    config([
        'filesystems.default' => 'local',
        'filesystems.disks' => [
            'local' => [
                'driver' => 'local',
                'root' => storage_path('app'),
            ],
            'public' => [
                'driver' => 'local',
                'root' => storage_path('app/public'),
                'url' => '/storage',
                'visibility' => 'public',
            ],
            's3' => [
                'driver' => 's3',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'region' => 'us-east-1',
                'bucket' => 'test-bucket',
            ],
        ],
    ]);
});

it('can get all disks', function () {
    $disks = FileSystem::getDisks();

    expect($disks)->toBeArray();
    expect($disks)->toHaveKeys(['local', 'public', 's3']);
});

it('can get default disk', function () {
    expect(FileSystem::getDefaultDisk())->toBe('local');
});

it('can get a specific disk config', function () {
    $disk = FileSystem::getDisk('public');

    expect($disk)->toBeArray();
    expect($disk['driver'])->toBe('local');
});

it('returns empty array for non-existent disk', function () {
    expect(FileSystem::getDisk('nonexistent'))->toBeEmpty();
});

it('can get driver for a disk', function () {
    expect(FileSystem::getDriverDisk('local'))->toBe('local');
    expect(FileSystem::getDriverDisk('s3'))->toBe('s3');
});

it('returns null for driver of non-existent disk', function () {
    expect(FileSystem::getDriverDisk('nonexistent'))->toBeNull();
});

it('can check if disk uses specific driver', function () {
    expect(FileSystem::isDiskDriver('local', 'local'))->toBeTrue();
    expect(FileSystem::isDiskDriver('local', 's3'))->toBeFalse();
    expect(FileSystem::isDiskDriver('s3', 's3'))->toBeTrue();
});

it('can check if disk is local', function () {
    expect(FileSystem::isDiskLocal('local'))->toBeTrue();
    expect(FileSystem::isDiskLocal('public'))->toBeTrue();
    expect(FileSystem::isDiskLocal('s3'))->toBeFalse();
});

it('can check if disk is s3', function () {
    expect(FileSystem::isDiskS3('s3'))->toBeTrue();
    expect(FileSystem::isDiskS3('local'))->toBeFalse();
    expect(FileSystem::isDiskS3('public'))->toBeFalse();
});

it('handles non-existent disk checks gracefully', function () {
    expect(FileSystem::isDiskLocal('nonexistent'))->toBeFalse();
    expect(FileSystem::isDiskS3('nonexistent'))->toBeFalse();
    expect(FileSystem::isDiskDriver('nonexistent', 'local'))->toBeFalse();
});
