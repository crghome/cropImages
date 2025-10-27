<?php

namespace CropImageCrghome;

use CropImageCrghome\Contract\ImageCacheInterface;
use CropImageCrghome\Contract\ImageStorageInterface;

class ImageCache implements ImageCacheInterface
{
    public function __construct(
        private ImageStorageInterface $storage,
        private string $cachePath
    ) {}
    
    public function get(string $key): ?string
    {
        $cacheFile = $this->getCacheFilePath($key);
        return $this->storage->exists($cacheFile) ? $cacheFile : null;
    }
    
    public function set(string $key, string $path): void
    {
        $cacheFile = $this->getCacheFilePath($key);
        copy($path, $this->storage->getAbsolutePath($cacheFile));
    }
    
    public function has(string $key): bool
    {
        return $this->storage->exists($this->getCacheFilePath($key));
    }
    
    private function getCacheFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->cachePath . '/' . substr($hash, 0, 2) . '/' . $hash;
    }
}
