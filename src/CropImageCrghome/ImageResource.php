<?php

namespace CropImageCrghome;

class ImageResource
{
    public function __construct(
        private string $path,
        private int $width,
        private int $height,
        private string $mimeType,
        private $resource
    ) {}

    // Геттеры и методы для работы с ресурсом
}
