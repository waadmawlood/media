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

it('uses table name from config', function () {
    $media = new Media;

    expect($media->getTable())->toBe(config('media.table_name'));
});

it('uses custom table name when overridden in subclass', function () {
    $media = new class extends Media
    {
        protected $table = 'custom_media';
    };

    expect($media->getTable())->toBe('custom_media');
});

it('can approve media', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $media->disApprove();

    expect($media->approved)->toBeFalse();

    $media->approve();
    expect($media->approved)->toBeTrue();
});

it('can disapprove media', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $media->disApprove();

    expect($media->approved)->toBeFalse();
});

it('approve returns the media instance', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $result = $media->approve();

    expect($result)->toBeInstanceOf(Media::class);
    expect($result->id)->toBe($media->id);
});

it('disapprove returns the media instance', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $result = $media->disApprove();

    expect($result)->toBeInstanceOf(Media::class);
    expect($result->id)->toBe($media->id);
});

it('can scope to approved media only', function () {
    $media1 = $this->post->addMedia($this->file)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();
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

    expect($media->full_url)->not->toBeNull();
    expect($media->full_url)->toBeString();
});

it('returns null for full_url when disabled in config', function () {
    config(['media.enable_full_url' => false]);

    $media = $this->post->addMedia($this->file)->upload();

    expect($media->full_url)->toBeNull();
});

it('returns null for full_url when path is empty', function () {
    $media = new Media;
    $media->path = null;

    expect($media->full_url)->toBeNull();
});

it('has temporary_url attribute', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media->temporary_url)->not->toBeNull();
});

it('returns null for temporary_url when disabled in config', function () {
    config(['media.enable_temporary_url' => false]);

    $media = $this->post->addMedia($this->file)->upload();

    expect($media->full_url)->not->toBeNull();
    expect($media->temporary_url)->toBeNull();
});

it('can format dates according to config', function () {
    date_default_timezone_set('Asia/Baghdad');
    config(['media.format_date' => 'Y-m-d H:i:s']);

    $media = $this->post->addMedia($this->file)->upload();
    $dateFormat = config('media.format_date');

    $formattedCreatedAt = $media->created_at->format($dateFormat);
    expect($formattedCreatedAt)->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');

    $formattedUpdatedAt = $media->updated_at->format($dateFormat);
    expect($formattedUpdatedAt)->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
});

it('uses default date serialization when format_date is null', function () {
    config(['media.format_date' => null]);

    $media = $this->post->addMedia($this->file)->upload();

    $serialized = $media->serializeDate($media->created_at);
    expect($serialized)->toBeString();
});

it('has correct fillable attributes', function () {
    $media = new Media;
    $fillable = $media->getFillable();

    expect($fillable)->toContain('basename');
    expect($fillable)->toContain('filename');
    expect($fillable)->toContain('path');
    expect($fillable)->toContain('index');
    expect($fillable)->toContain('label');
    expect($fillable)->toContain('collection');
    expect($fillable)->toContain('disk');
    expect($fillable)->toContain('bucket');
    expect($fillable)->toContain('mimetype');
    expect($fillable)->toContain('filesize');
    expect($fillable)->toContain('approved');
    expect($fillable)->toContain('metadata');
});

it('has correct casts', function () {
    $media = new Media;
    $casts = $media->getCasts();

    expect($casts['approved'])->toBe('boolean');
    expect($casts['metadata'])->toBe('json');
});

it('hides disk and bucket in serialization', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $array = $media->toArray();

    expect($array)->not->toHaveKey('disk');
    expect($array)->not->toHaveKey('bucket');
});

it('appends full_url and temporary_url in serialization', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $array = $media->toArray();

    expect($array)->toHaveKey('full_url');
    expect($array)->toHaveKey('temporary_url');
});

it('uses soft deletes', function () {
    $media = $this->post->addMedia($this->file)->upload();
    $media->delete();

    expect(Media::find($media->id))->toBeNull();
    expect(Media::withTrashed()->find($media->id))->not->toBeNull();
    expect(Media::withTrashed()->find($media->id)->deleted_at)->not->toBeNull();
});
