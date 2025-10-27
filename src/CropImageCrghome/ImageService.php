<?php

namespace CropImageCrghome;

use CropImageCrghome\Contract\ImageCacheInterface;
use CropImageCrghome\Contract\ImageProcessorInterface;
use CropImageCrghome\Contract\ImageStorageInterface;

class ImageService
{
    public function __construct(
        private ImageProcessorInterface $processor,
        private ImageCacheInterface $cache,
        private ImageStorageInterface $storage
    ) {}
    
    public function createImageVariation(
        string $sourcePath,
        array $operations,
        string $outputFormat = 'webp',
        int $quality = 85
    ): string {
        $cacheKey = $this->generateCacheKey($sourcePath, $operations, $outputFormat, $quality);
        
        // Пытаемся получить из кеша
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Загружаем исходное изображение
        $resource = $this->storage->get($sourcePath);
        
        // Обрабатываем
        $resultPath = $this->processor->process($resource, $operations);
        
        // Сохраняем в кеш
        $this->cache->set($cacheKey, $resultPath);
        
        return $resultPath;
    }
    
    private function generateCacheKey(
        string $sourcePath,
        array $operations,
        string $outputFormat,
        int $quality
    ): string {
        $operationKeys = array_map(fn($op) => $op->getKey(), $operations);
        $sourceMtime = filemtime($this->storage->getAbsolutePath($sourcePath));
        
        return implode('_', [
            md5($sourcePath),
            $sourceMtime,
            implode('_', $operationKeys),
            $outputFormat,
            $quality
        ]);
    }
}
