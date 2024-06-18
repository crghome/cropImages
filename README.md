# cropImages
## V.3.2
---
```php
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/CropImages.php');
$cropImages = new \CropImages();

$imgCache = $cropImages->cropImages(
    String $imagePath,              // original path image
    String|Array $createWidth,      // size new
    Int $quality = 0,               // quality, default 90
    String $dirCache = '',          // directory cache, default by directory in cache original
    String $figure = 'cover',       // set figure, default proportional by size width, height|width|cropMinHeight
    Int $height = 0,                // set height if figure cropMinHeight
    Bool $originExt = false         // if need original extension file, default webp format
)

// examples
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800);
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800, figure: 'width');
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800, figure: 'height');
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800, figure: 'cropMinHeight', height: 600);

$arrImgCache = $cropImages->cropImages(imagePath: $image, createWidth: ['4800', '1920', '1200', '800', '600']);
```

return LINK or array 'Resize' => 'Link'
