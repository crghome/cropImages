<?php

namespace CropImageCrghome\Operation;

use CropImageCrghome\Contract\ImageOperationInterface;
use CropImageCrghome\ImageResource;

class AspectRatioOperation implements ImageOperationInterface
{
    public function __construct(private array $params) {}
    
    public function apply(ImageResource $resource): ImageResource
    {
        // Логика изменения соотношения сторон
        return $processedResource;
    }
    
    public function getKey(): string
    {
        return 'aspect_' . implode('_', $this->params);
    }
}
