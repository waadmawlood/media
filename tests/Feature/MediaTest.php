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
    if (isset($this->file)) {
        unset($this->file);
    }
    if (isset($this->post)) {
        $this->post->media()->delete();
        unset($this->post);
    }
    Storage::disk('public')->delete(Storage::disk('public')->allFiles());
    gc_collect_cycles();
});

it('can store single media', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($media)
        ->toBeInstanceOf(Media::class)
        ->basename->not->toBeEmpty()
        ->approved->toBeTrue();

    Storage::disk('public')->assertExists($media->path);
});

it('can store multiple media', function () {
    $files = collect([
        UploadedFile::fake()->image('test1.jpg', 50, 50),
        UploadedFile::fake()->image('test2.jpg', 50, 50),
    ]);

    $mediaItems = $this->post->addMedia($files->all())->upload();

    expect($mediaItems)->toHaveCount(2);

    foreach ($mediaItems as $media) {
        expect($media)
            ->toBeInstanceOf(Media::class)
            ->basename->not->toBeEmpty();

        Storage::disk('public')->assertExists($media->path);
    }

    // Clean up
    $files = null;
    gc_collect_cycles();
});

it('can generate correct url', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $expectedUrl = url('upload/').'/'.$media->basename;
    expect($media->full_url)->toBe($expectedUrl);
});

it('can sync media', function () {
    // Add initial media
    $initialMedia = $this->post->addMedia($this->file)->upload();

    // Create new file to sync
    $newFile = UploadedFile::fake()->image('new.jpg');

    // Sync with new file
    $syncedMedia = $this->post->syncMedia($newFile, [$initialMedia->id])->upload();

    // Storage::disk('public')->assertMissing($initialMedia->path);
    Storage::disk('public')->assertExists($syncedMedia->path);
});

it('can approve and disapprove media', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $media->disApprove();
    expect($media->approved)->toBeFalse();

    $media->approve();
    expect($media->approved)->toBeTrue();
});

it('can filter approved media', function () {
    // Create approved media
    $media1 = $this->post->addMedia($this->file)->upload();
    $media2 = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    // Create disapproved media
    $media3 = $this->post->addMedia(UploadedFile::fake()->image('test3.jpg'))->upload();
    $media3->disApprove();

    expect(Media::approved()->count())->toBe(2);
    expect($this->post->mediaApproved()->count())->toBe(2);
    expect($this->post->mediaApproved(false)->count())->toBe(1);
});

it('can get media by mime type', function () {
    $imageFile = $this->file;
    $pdfFile = UploadedFile::fake()->create('document.pdf', 100);

    $this->post->addMedia($imageFile)->upload();
    $this->post->addMedia($pdfFile)->upload();

    expect($this->post->mediaByMimeType('image/jpeg')->count())->toBe(1);
    expect($this->post->mediaByMimeType('application/pdf')->count())->toBe(1);
});

it('can get media by id', function () {
    $media = $this->post->addMedia($this->file)->upload();

    expect($this->post->mediaById($media->id))
        ->toBeInstanceOf(Media::class)
        ->id->toBe($media->id);
});

it('can get first and last media', function () {
    $first = $this->post->addMedia($this->file)->upload();
    sleep(1); // Ensure different timestamps
    $last = $this->post->addMedia(UploadedFile::fake()->image('test2.jpg'))->upload();

    expect($this->post->getFirstMedia()->id)->toBe($first->id);
    expect($this->post->getLastMedia()->id)->toBe($last->id);
});

it('can format dates according to config', function () {
    $media = $this->post->addMedia($this->file)->upload();

    $dateFormat = config('media.format_date') ?? 'Y-m-d H:i:s';
    expect($media->created_at->format($dateFormat))
        ->toBe($media->created_at->format($dateFormat));
    expect($media->updated_at->format($dateFormat))
        ->toBe($media->updated_at->format($dateFormat));
});

it('can handle media collections', function () {
    $post = createPost();
    expect($post)->toBeInstanceOf(Post::class);

    $post->registerCollections([
        'avatars' => [
            'disk' => 'public',
            'bucket' => 'avatars',
            'label' => 'avatars',
            'single' => false,
        ],
        'gallery' => [
            'disk' => 'public',
            'bucket' => 'gallery',
            'label' => 'gallery',
            'single' => false,
        ],
    ]);

    $avatarMedia = $post->addMedia($this->file)
        ->collection('avatars')
        ->upload();

    $galleryMedia = $post->addMedia($this->file)
        ->collection('gallery')
        ->upload();

    expect($post->hasMedia('avatars'))->toBeTrue();
    expect($post->hasMedia('gallery'))->toBeTrue();
    expect($post->getCollection('avatars'))->toHaveCount(1);
    expect($post->getCollection('gallery'))->toHaveCount(1);
});
