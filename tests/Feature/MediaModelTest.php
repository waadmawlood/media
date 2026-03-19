<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\App\Models\Post;
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

it('uses table name from config and allows override', function () {
    expect((new Media)->getTable())->toBe(config('media.table_name'));

    $custom = new class extends Media
    {
        protected $table = 'custom_media';
    };

    expect($custom->getTable())->toBe('custom_media');
});

it('can approve and disapprove media', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $result = $media->disApprove();
    expect($result)->toBeInstanceOf(Media::class)->id->toBe($media->id);
    expect($media->approved)->toBeFalse();

    $result = $media->approve();
    expect($result)->toBeInstanceOf(Media::class)->id->toBe($media->id);
    expect($media->approved)->toBeTrue();
});

it('can scope to approved media only', function () {
    $this->post->addMedia($this->file)->upload();
    $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();
    $media3 = $this->post->addMedia(UploadedFile::fake()->image('test3.jpg'))->upload();
    $media3->disApprove();

    expect(Media::approved()->count())->toBe(2);
});

it('can get mediable relationship', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media->mediable)->toBeInstanceOf(Post::class);
    expect($media->mediable->id)->toBe($this->post->id);
});

it('has full_url attribute', function () {
    $media = $this->post->addMedia($this->file)->upload();
    expect($media->full_url)->not->toBeNull()->toBeString();

    $emptyMedia = new Media;
    $emptyMedia->path = null;
    expect($emptyMedia->full_url)->toBeNull();
});

it('returns null for full_url when disabled in config', function () {
    config(['media.enable_full_url' => false]);
    $media = $this->post->addMedia($this->file)->upload();
    expect($media->full_url)->toBeNull();
});

it('can format dates according to config', function () {
    date_default_timezone_set('Asia/Baghdad');
    config(['media.format_date' => 'Y-m-d H:i:s']);

    $media = $this->post->addMedia($this->file)->upload();
    $dateFormat = config('media.format_date');

    expect($media->created_at->format($dateFormat))->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
    expect($media->updated_at->format($dateFormat))->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
});

it('uses default date serialization when format_date is null', function () {
    config(['media.format_date' => null]);

    $media = $this->post->addMedia($this->file)->upload();

    expect($media->serializeDate($media->created_at))->toBeString();
});

it('has correct fillable attributes and casts', function () {
    $media = new Media;

    expect($media->getFillable())->toContain(
        'basename', 'filename', 'path', 'index', 'label',
        'collection', 'disk', 'bucket', 'mimetype', 'filesize',
        'approved', 'metadata'
    );

    $casts = $media->getCasts();
    expect($casts['approved'])->toBe('boolean');
    expect($casts['metadata'])->toBe('json');
});

it('hides disk and bucket but appends full_url in serialization', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $array = $media->toArray();

    expect($array)->not->toHaveKey('disk')->not->toHaveKey('bucket');
    expect($array)->toHaveKey('full_url');
});

it('uses soft deletes', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $media->delete();

    expect(Media::find($media->id))->toBeNull();
    expect(Media::withTrashed()->find($media->id))->not->toBeNull();
    expect(Media::withTrashed()->find($media->id)->deleted_at)->not->toBeNull();
});
