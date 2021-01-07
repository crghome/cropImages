# cropImages
---
```php
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/cropImages.php');
$cropImages = new \cropImages();

// return link img
// $cropImages->cropImages(<image link : string>, <link directory cashe with directory pict : string>, <size : array|int>, <quality : int>);
$imgBackC = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80);
$imgBackS = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80, 'square');
$imgBackW = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80, 'width');
$imgBackH = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80, 'height');
$arrImgBack = $cropImages->cropImages($imgBack, '/cache/image-header', array(), 80, 'maxheight', 850);

// return arr`s link`s img array('Resize' => 'Link')
$arrImgBack = $cropImages->cropImages($imgBack, '/cache/image-header', array('4800', '1920', '1200', '800', '600'), 80, 'height');
```
