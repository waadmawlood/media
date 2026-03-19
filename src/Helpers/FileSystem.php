<?php

namespace Waad\Media\Helpers;

class FileSystem
{
    public static function getDisks(): array
    {
        return config('filesystems.disks', []);
    }

    public static function getDefaultDisk(): string
    {
        return config('filesystems.default', 'local');
    }

    public static function getDisk(string $disk): array
    {
        return self::getDisks()[$disk] ?? [];
    }

    public static function getDriverDisk(string $disk): ?string
    {
        return self::getDisk($disk)['driver'] ?? null;
    }

    public static function isDiskDriver(string $disk, string $driver): bool
    {
        return self::getDriverDisk($disk) === $driver;
    }

    public static function isDiskLocal(string $disk): bool
    {
        return self::isDiskDriver($disk, 'local');
    }

    public static function isDiskS3(string $disk): bool
    {
        return self::isDiskDriver($disk, 's3');
    }
}
