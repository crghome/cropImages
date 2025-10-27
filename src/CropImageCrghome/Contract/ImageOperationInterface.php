<?php

namespace CropImageCrghome\Contract;

use CropImageCrghome\ImageResource;

interface ImageOperationInterface
{
    public function apply(ImageResource $resource): ImageResource;
    public function getKey(): string;
}
