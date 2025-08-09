<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CropImageTest extends TestCase
{
    private $cropImages;
    private $image = '/images/1.jpg';

    protected function setUp(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = 'C:/SERVER/data/www/crop-image';
        $this->cropImages = new CropImages();
    }

    public function testCropImageWidth(): void
    {
        $imgCache = $this->cropImages->cropImages(imagePath: $this->image, createWidth: 400);

        $this->assertIsString($imgCache, 'No string $imgCache');
        $this->assertNotEmpty($imgCache, 'Empty result $imgCache');
    }

    public function testCropImageArrayWidth(): void
    {
        $keys = [1200, 600];
        $imgCache = $this->cropImages->cropImages(imagePath: $this->image, createWidth: $keys);

        $this->assertIsArray($imgCache, 'No array $imgCache');
        $this->assertArrayHasKey(1200, $imgCache, 'No keys $imgCache');
        $this->assertArrayHasKey(600, $imgCache, 'No keys $imgCache');
    }
}
