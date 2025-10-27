// Пример использования
$storage = new FileSystemImageStorage($_SERVER['DOCUMENT_ROOT']);
$processor = new ImageProcessor($storage);
$cache = new ImageCache($storage, '/cache/images');
$imageService = new ImageService($processor, $cache, $storage);

// Создание вариантов изображения
$operations = [
    ImageOperationFactory::create('aspect_ratio', [16, 9]),
    ImageOperationFactory::create('resize', [800, 600]),
    ImageOperationFactory::create('crop', [400, 300])
];

$result = $imageService->createImageVariation(
    '/uploads/image.jpg',
    $operations,
    'webp',
    90
);