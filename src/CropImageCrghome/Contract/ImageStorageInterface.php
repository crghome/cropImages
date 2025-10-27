<?php

namespace CropImageCrghome\Contract;

use CropImageCrghome\ImageResource;

interface ImageStorageInterface
{
    public function exists(string $path): bool;
    public function get(string $path): ImageResource;
    public function save(ImageResource $resource, string $path): bool;
}
