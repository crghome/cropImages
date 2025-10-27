<?php

namespace CropImageCrghome;

use CropImageCrghome\Contract\ImageStorageInterface;

class FileSystemImageStorage implements ImageStorageInterface
{
    public function __construct(private string $basePath) {}
    
    public function exists(string $path): bool
    {
        return file_exists($this->getAbsolutePath($path));
    }
    
    public function get(string $path): ImageResource
    {
        $absolutePath = $this->getAbsolutePath($path);
        
        if (!$this->exists($path)) {
            throw new \RuntimeException("Image not found: $path");
        }
        
        $imageInfo = getimagesize($absolutePath);
        $resource = $this->createImageResource($absolutePath, $imageInfo[2]);
        
        return new ImageResource(
            $absolutePath,
            $imageInfo[0],
            $imageInfo[1],
            $imageInfo['mime'],
            $resource
        );
    }
    
    public function save(ImageResource $resource, string $path): bool
    {
        $absolutePath = $this->getAbsolutePath($path);
        $this->ensureDirectoryExists(dirname($absolutePath));
        
        return $this->saveImageResource($resource, $absolutePath);
    }
    
    private function createImageResource(string $path, int $type)
    {
        return match($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            default => throw new \RuntimeException("Unsupported image type")
        };
    }
    
    private function saveImageResource(ImageResource $resource, string $path): bool
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        return match(strtolower($extension)) {
            'jpg', 'jpeg' => imagejpeg($resource->getResource(), $path, 85),
            'png' => imagepng($resource->getResource(), $path, 8),
            'webp' => imagewebp($resource->getResource(), $path, 85),
            'gif' => imagegif($resource->getResource(), $path),
            default => throw new \RuntimeException("Unsupported output format: $extension")
        };
    }
    
    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
    
    private function getAbsolutePath(string $path): string
    {
        return $this->basePath . '/' . ltrim($path, '/');
    }
}
