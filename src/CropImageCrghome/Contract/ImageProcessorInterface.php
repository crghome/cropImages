<?php

namespace CropImageCrghome\Contract;

use CropImageCrghome\ImageResource;

interface ImageProcessorInterface
{
    public function process(ImageResource $resource, array $operations): string;
}
