
![Logo](example/logo.jpg)

<p align="center">
<a href="https://packagist.org/packages/waad/media"><img src="https://img.shields.io/packagist/dt/waad/media" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/waad/media"><img src="https://img.shields.io/packagist/v/waad/media" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/waad/media"><img src="https://img.shields.io/packagist/l/waad/media" alt="License"></a>
</p>

# Media Package

A Package to save your files in one place can object has many or one media record. for laravel 6 and above. 



## Authors

- [@Waad Mawlood](https://www.github.com/waadmawlood)
- waad_mawlood@outlook.com


## Requirement

 - PHP >= 7.2
 - laravel 6 | 7 | 8


## Installation

To install

```sh
composer require waad/media
```

You can publish the migration and config with:

```sh
php artisan vendor:publish --provider="Waad\Media\MediaServiceProvider"
```

configration from `config/media.php`

----

#### You can migration

```sh
php artisan migrate
```

#### You can make a link shortcut

```sh
php artisan media:link
```
## Usage / Example

In Model
```js
namespace App\Models;


use Waad\Media\Traits\HasOneMedia;
or
use Waad\Media\Traits\HasManyMedia;

class Post extends Model
{
    
    use HasManyMedia;

    ......
```


You Can get media

```js
$post->media;
```

use in controller `store` function to add One or Many images
```js
$post = Post::create([
  ...........
]);

$files = $request->file('image'); // one image
$files = $request->file('images'); // many images
$basename = $post->addMedia($files);  // return array of file names

return $basename;
```

use in controller `update` function to add One or Many images
```js
$post = Post::find(1);
$post->update([
  ...........
]);

$files = $request->file('image'); // one image
$files = $request->file('images'); // many images
$basename = $post->syncMedia($files);  // return array of file names

return $basename;
```

use in controller `destroy` function to add One or Many images
```js

$post = Post::find(1);

$post->deleteMedia(); // delete all media from this object

$ids = [1,3];
$post->deleteMedia($ids); // delete specific media by id from object

$post->delete();
```

Other Helpers

```js
// get sum files size of post object (bytes)
$post->mediaTotalSize();

//**********************************************
// get count media of post object
$post->mediaTotalCount();

//**********************************************
// get media by id of post object
$post->mediaById(17);   

//**********************************************
// get media by mime_type of post object
$post->mediaByMimeType('image/png');

//**********************************************
// get media by approved boolean of post object
$post->mediaApproved();      // default true
$post->mediaApproved(false); // false
```



You can update approved all media of object
```js
$post->media->approve();   // put approved = true

$post->media->disApprove();   // put approved = false
```


You can get the user to upload that media
```js
$post->media->user; 
or
Post::with('media.user')->find(1);
Post::with('media.user')->get();
```

### Scope

You can get only approved equal true

```js
$post->media->approved();  // approved = true
```

## License

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)
