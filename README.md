# cropImages
---
```php
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/cropImages.php');
$cropImages = new \cropImages();

// return link img
// $cropImages->cropImages(<image link : string>, <link directory cashe with directory pict : string>, <size : array|int>, <quality : int>);

// crop default of proportional max-width
$imgBackC = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80);
// crop square after crop to max-width
$imgBackS = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80, 'square');
// crop width no resize height
$imgBackW = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80, 'width');
// crop height no resize width
$imgBackH = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80, 'height');
// crop width set and maxheight set
$arrImgBack = $cropImages->cropImages($imgBack, '/cache/image-header', array(), 80, 'maxheight', 850);
```

return LINK or array 'Resize' => 'Link'
