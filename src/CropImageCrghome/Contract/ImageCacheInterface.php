<?php

namespace CropImageCrghome\Contract;

interface ImageCacheInterface
{
    public function get(string $key): ?string;
    public function set(string $key, string $path): void;
    public function has(string $key): bool;
}
