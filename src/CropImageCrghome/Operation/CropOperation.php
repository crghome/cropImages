<?php

namespace CropImageCrghome\Operation;

use CropImageCrghome\Contract\ImageOperationInterface;
use CropImageCrghome\ImageResource;

class CropOperation implements ImageOperationInterface
{
    public function __construct(private array $params) {}
    
    public function apply(ImageResource $resource): ImageResource
    {
        // Логика кадрирования
        return $processedResource;
    }
    
    public function getKey(): string
    {
        return 'crop_' . implode('_', $this->params);
    }
}
