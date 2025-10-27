<?php

namespace CropImageCrghome\Factory;

use CropImageCrghome\Contract\ImageOperationInterface;
use CropImageCrghome\Operation\AspectRatioOperation;
use CropImageCrghome\Operation\CropOperation;

class ImageOperationFactory
{
    public static function create(string $type, array $params): ImageOperationInterface
    {
        return match($type) {
            'crop' => new CropOperation($params),
            // 'resize' => new ResizeOperation($params),
            'aspect_ratio' => new AspectRatioOperation($params),
            // 'rotate' => new RotateOperation($params),
            default => throw new \InvalidArgumentException("Unknown operation: $type")
        };
    }
}
