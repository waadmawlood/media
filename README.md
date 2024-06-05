
![Logo](https://firebasestorage.googleapis.com/v0/b/beauty-jewel.appspot.com/o/github%2Fmedia%20logo.jpg?alt=media&token=a8be132e-94c5-4d31-8cd6-e17e57727dfb)


# 🔥 Media Files Package

A Package to save your files Many Disks, Many Directories By Same Model



## ❤️ Authors

- [Waad Mawlood](https://www.github.com/waadmawlood)
- waad_mawlood@outlook.com


## ⚠️ Mini Requirement

  #### * version >= 2.0.0
- PHP >= 8.0.0
- Laravel 8 , 9 , 10


&nbsp;

  #### * version < 2.0.0
- PHP 7.4
- Laravel 7 , 8


## 💯 Installation

To install

```sh
composer require waad/media
```

**first** :
publish the `config` with the command:

```sh
php artisan vendor:publish --provider="Waad\Media\MediaServiceProvider" --tag="media-config"
```

configuration from `config/media.php` sure from `uuid`, `shortcut` in config media

⚠️ clear cache is important, before publishing migrations
```sh
php artisan optimize
```


**Second** :
publish the `migrations` with the command:

```sh
php artisan vendor:publish --provider="Waad\Media\MediaServiceProvider" --tag="media-migrations"
```

#### You can migration

```sh
php artisan migrate
```

#### You can make a link shortcut by disk from `config.media.shortcut` array of disks

```js
'shortcut' => [
        'public' => 'media',
        // disk => shortcut name
    ],
```


```sh
php artisan media:link
```
## 🧰 Usage / Example

In **Model**
```js
<?php

namespace App\Models;

use Waad\Media\Traits\HasOneMedia;
// or
use Waad\Media\Traits\HasManyMedia;

class Post extends Model
{
    use HasOneMedia;       <<------ return one last record of media 
    // or
    use HasManyMedia;      <<------ return list of media


    // $media_disk
    // $media_directory 
    // if not define will get default `disk,directory`  in `config/media.php`
    public $media_disk = 'public';
    public $media_directory = 'posts/images';
    ......
```

&nbsp;

You Can get media

```js
$post->media;
```
&nbsp;

- **`Upload Files`** eg. Use in controller `store` method to add One or Many Files
```js
$post = Post::create([
  ...........
]);

$files = $request->file('image'); // one image
$files = $request->file('images'); // many images

// version < 2 
// will return an array of file names
$media = $post->addMedia($files); 
$media = $post->addMedia($files, $index = 1, $label = 'cover'); 


// ***************************************************


// version >= 2 
// will return the Media model or array of Media models on the Relationship
$media = $post->addMedia($files)->upload();
$media = $post->addMedia($files)->label('cover')->index(3)->upload();
$media = $post->addMedia($files)->disk('public')->directory('posts/video')->label('cover')->index(3)->upload();


return $media;
```
&nbsp;

- **`Sync Files`** eg. Use in controller `update` method to add One or Many Files
```js
$post = Post::find(1);
$post->update([
  ...........
]);

$files = $request->file('image'); // one image
$files = $request->file('images'); // many images

// version < 2 
// will return an array of file names
$media = $post->syncMedia($files);
$media = $post->syncMedia($files, $index = 2);


// ***************************************************


// version >= 2 
// will return the Media model or array of Media Models on the Relationship
$media = $post->syncMedia($files)->sync();
$media = $post->syncMedia($files, $ids = [1,3])->sync(); // delete only these $ids and upload new files
$media = $post->syncMedia($files)->label('cover')->index(3)->sync();
$media = $post->syncMedia($files)->disk('public')->directory('posts/video')->label('cover')->index(3)->sync();

return $media;
```

- **`Delete Files`** eg. Use in controller `destroy` method to delete all or specific ids
```js

$post = Post::find(1);

// version < 2 
// will return an array of file names
$media = $post->deleteMedia($files);
$media = $post->deleteMedia($files, $index = 2);


// ***************************************************


// version >= 2 
// will return a bool or array of bool or null by on Relationship
$media = $post->deleteMedia()->delete();
$media = $post->deleteMedia($medias_model)->delete();
$media = $post->deleteMedia([1,3])->delete(); // delete only these ids

$lastMedia = $post->media->last(); // return Collection Media Model 
$media = $post->deleteMedia($lastMedia)->delete(); // delete only this media

$media2 = $post->mediaById(8);
$media = $post->deleteMedia($media2)->delete(); 

$mediaList = $post->mediaByMimeType('image/png');
$media = $post->deleteMedia($mediaList)->delete(); 

$post->delete();
```

&nbsp;

- Other Helper **`Only version >= 2`**
```js
// get sum files size of post object (bytes)
$post->mediaTotalSize();

//**********************************************

// get count media of post object
$post->mediaTotalCount();

// get count media with soft delete of post object
$post->mediaTotalCount($withTrashed = true);  

//**********************************************

// get media by id of the post object
$post->mediaById(17);

// get media with soft delete by id of the post object
$post->mediaById(17, $withTrashed = true);    

//**********************************************

// get media by mime_type of post object
$post->mediaByMimeType('image/png');

// get media with soft delete by mime_type of the post object
$post->mediaByMimeType('image/png', $withTrashed = true);

//**********************************************

// get media by approved boolean of the post object
$post->mediaApproved();      // default true
$post->mediaApproved(false);

// get media with soft delete by approved boolean of the post object
$post->mediaApproved(false, $withTrashed = true);

```

&nbsp;

- You can update `approved` all media of the object
```js
$post->media->approve();   // put approved = true

$post->media->disApprove();   // put approved = false
```

&nbsp;

- You can get the `user` to upload that media
```js
// if was HasOneMedia
1 - optional($post->media)->user;
2 - Post::with('media.user')->find(1);

//**********************************************

// if was HasManyMedia
1 - Post::with('media.user')->get();
2 - $post->media->load('user');
```

### 🎀 Scope

You can get only approved equal true

```js
$post->media->approved();  // approved = true
```

## 🍔 Permanently delete files

Determine `delete_file_after_day` from `config/media.php` must be integer

⭕️ Add Command to the crontab of the project to implement automatically

in `app/Console/Kernel.php` add this:

```js
protected function schedule(Schedule $schedule)
    {
        // .....................

        $schedule->command('media:prune')->daily();
    }
```

⭕️ implemented manually

```sh
php artisan media:prune
```

## 🎯 License

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

## Star History

[![Star History Chart](https://api.star-history.com/svg?repos=waadmawlood/media&type=Date)](https://star-history.com/#waadmawlood/media&Date)
