# cropImages
## V.3.2.1
---
```php
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/CropImages.php');
$cropImages = new \CropImages();

$imgCache = $cropImages->cropImages(
    string $imagePath,              // original path image
    string|array $createWidth,      // size new
    int $quality = 0,               // quality, default 90
    string $dirCache = '',          // directory cache, default by directory in cache original
    string $figure = 'cover',       // set figure, default proportional by size width, height|width|cropMinHeight
    int $height = 0,                // set height if figure cropMinHeight
    bool $originExt = false         // if need original extension file, default webp format
)

// examples
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800);
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800, figure: 'width');
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800, figure: 'height');
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800, figure: 'cropMinHeight', height: 600);

$arrImgCache = $cropImages->cropImages(imagePath: $image, createWidth: ['4800', '1920', '1200', '800', '600']);
```
<p>return LINK or array 'Resize' => 'Link'</p>

## Example

### By default realization

```php
$image = '/images/image.jpg';
$imgCache = $cropImages->cropImages( imagePath: $image, $createWidth: 800 );
```

<p>Get <code>$imgCache</code> with width 800px, height proportional</p>

### By array realization

```php
$image = '/images/image.jpg';
$imgCache = $cropImages->cropImages( imagePath: $image, $createWidth: [1200, 800] );
```

<p>Get array <code>$imgCache</code></p>

```php
[
    '1200' => 'link_image_1200',
    '800' => 'link_image_800',
]
```

### By max height realization

```php
$image = '/images/image.jpg';
$imgCache = $cropImages->cropImages( imagePath: $image, $createWidth: 800, figure: 'cropMinHeight', height: 600 );
```

<p>Get image <code>$imgCache</code> of width 800px and height 600px by image full height. Crop left and center.</p>
