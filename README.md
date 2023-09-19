
![Logo](https://firebasestorage.googleapis.com/v0/b/beauty-jewel.appspot.com/o/github%2Fmedia%20logo.jpg?alt=media&token=a8be132e-94c5-4d31-8cd6-e17e57727dfb)


# Media Package

A Package to save your files in one place can object has many or one media record.



## Authors

- [@Waad Mawlood](https://www.github.com/waadmawlood)
- waad_mawlood@outlook.com


## Mini Requirement

 - PHP 7.4
 - laravel 5.5
 - illuminate/support 5.6 


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

---

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
