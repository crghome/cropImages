<?php

namespace CropImageCrghome;

use CropImageCrghome\Contract\ImageProcessorInterface;
use CropImageCrghome\Contract\ImageStorageInterface;

class ImageProcessor implements ImageProcessorInterface
{
    public function __construct(
        private ImageStorageInterface $storage,
        private array $supportedFormats = ['jpeg', 'png', 'webp', 'gif']
    ) {}
    
    public function process(ImageResource $resource, array $operations): string
    {
        foreach ($operations as $operation) {
            $resource = $operation->apply($resource);
        }
        
        return $this->storage->save($resource);
    }
}
