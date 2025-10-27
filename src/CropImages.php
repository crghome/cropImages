<?php
/* README
VERSION 3.3.1

require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/CropImages.php');
$cropImages = new \CropImages();

$imgCache = $cropImages->cropImages(
    String $imagePath,              // original path image
    String|Array $createWidth,      // size new
    Int $quality = 0,               // quality, default 90
    String $dirCache = '',          // directory cache, default by directory in cache original
    string $aspectRatio = [],       // set aspect ratio [ <X axios>, Y <axios> ]
    String $figure = 'cover',       // set figure, default proportional by size width, height|width|cropMinHeight
    Int $height = 0,                // set height if figure cropMinHeight
    Bool $originExt = false         // if need original extension file, default webp format
)

// examples
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800);
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800, figure: 'width');
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800, figure: 'height');
$imgCache = $cropImages->cropImages(imagePath: $image, createWidth: 800, figure: 'cropMinHeight', height: 600);

$arrImgCache = $cropImages->cropImages(imagePath: $image, createWidth: ['4800', '1920', '1200', '800', '600']);
*/
// namespace App\Helpers;

class CropImages{
    private $documentRoot;
    private $cacheBase = '/cache';
    private $directoryCache;
    private $quality = 92;
    private $originExt = false;
    private $defaultExt = 'webp';
    private $aspectRatioImage;
    private $typeImg = [1 => 'gif', 2 => 'jpeg', 3 => 'png', 18 => 'webp'];
    // private $typeImgAll = [0=>'UNKNOWN',1=>'GIF',2=>'JPEG',3=>'PNG',4=>'SWF',5=>'PSD',6=>'BMP',7=>'TIFF_II',8=>'TIFF_MM',9=>'JPC',10=>'JP2',11=>'JPX',12=>'JB2',13=>'SWC',14=>'IFF',15=>'WBMP',16=>'XBM',17=>'ICO',18=>'COUNT'];
    private $imageInit = [
        'imageBaseName' => '',
        'imageFileName' => '',
        'imageFilePath' => '',
        'imageFilePathAbs' => '',
        'dirFilePath' => '',
        'dirFilePathAbs' => '',
        'imageWidth' => '',
        'imageHeight' => '',
        'imageTypeFile' => '',
        'attrFile' => '',
        'extensionFile' => '',
        'filemtime' => '',
    ];
    private $imageNew = [
        'dirRoot' => '',
        'dirAbs' => '',
        'name' => '',
    ];
    
    public function __construct(){
        $this->documentRoot = preg_replace('/\/$/', '', $_SERVER['DOCUMENT_ROOT']);
        $this->createDirectory($this->cacheBase);
        $this->aspectRatioImage = (object)[];
    }

    /**
     * create path
     * @param string $path
     * @return string absolute path
     */
    private function createDirectory(string $path): string
    {
        $path = preg_match('/^\/.+/', $path) ? $path : '/' . $path;
        $directoryAbsolute = $this->documentRoot . $path;
        if(!file_exists($directoryAbsolute)){
            mkdir($directoryAbsolute, 0777, true);
            file_put_contents($directoryAbsolute . '/index.html', '<html><head></head><body></body></html>');
        }
        return $directoryAbsolute;
    }

    /** generate name file
     * @return string name
     */
    private function getNewNameFile(): string
    {
        $fileName = $this->quality . '_' . $this->imageInit['filemtime'] . '_' . preg_replace('/[\ \.]+/', '', $this->imageInit['imageBaseName']) . '.';
        $fileName .= $this->originExt ? $this->imageInit['extensionFile'] : $this->defaultExt;
        return $fileName;
    }

    /** create image path absolute
     * @param string $aNewImageFilePath
     * @param int $startX
     * @param int $startY
     * @param int $newWidth
     * @param int $newHeight
     * @param int $oldWidth
     * @param int $oldHeight
     * @return string abs path
     */
    private function cropping(string $aNewImageFilePath, int $startX, int $startY, int $newWidth, int $newHeight, int $oldWidth, int $oldHeight): string
    {
        try {
            $lImageExtension = $this->typeImg[$this->imageInit['imageTypeFile']];
            $funcCreate = 'imagecreatefrom' . $lImageExtension;

            // Создаём дескриптор исходного изображения
            $lInitialImageDescriptor = $funcCreate($this->imageInit['imageFilePathAbs']);

            // Создаём дескриптор для выходного изображения 
            $lNewImageDescriptor = imagecreatetruecolor($newWidth, $newHeight);
            imageAlphaBlending($lNewImageDescriptor, false);
            imageSaveAlpha($lNewImageDescriptor, true);
            imagecopyresampled($lNewImageDescriptor, $lInitialImageDescriptor, 0, 0, $startX, $startY, $newWidth, $newHeight, $oldWidth, $oldHeight);

            // сохраняем полученное изображение в указанный файл
            $funcImg = 'image' . ($this->originExt ? $lImageExtension : $this->defaultExt);
            $funcImg($lNewImageDescriptor, $aNewImageFilePath, $this->quality);

            imagedestroy($lNewImageDescriptor);
            imagedestroy($lInitialImageDescriptor);
        } catch (\Exception $e) {
            copy($this->imageInit['imageFilePathAbs'], $aNewImageFilePath);
            return $aNewImageFilePath;
        } catch (\Throwable $th) {
            copy($this->imageInit['imageFilePathAbs'], $aNewImageFilePath);
            return $aNewImageFilePath;
        }
        return $aNewImageFilePath;
    }

    /** initial old image data
     * @param string $imagePat
     * @return bool
     */
    private function getImageInitData(string $imagePath): bool
    {
        $result = false;
        try{
            $imagePath = '/' . preg_replace('/^\//', '', $imagePath);
            // $imagePath = preg_replace('/\ /', '_', $imagePath);
            $this->imageInit['imageFilePath'] = $imagePath;
            $this->imageInit['imageFilePathAbs'] = $this->documentRoot . $imagePath;
            if(!file_exists($this->imageInit['imageFilePathAbs'])){ throw new \Exception('not init file'); }
            // Получаем размеры и тип изображения в виде числа
            list($this->imageInit['imageWidth'], $this->imageInit['imageHeight'], $this->imageInit['imageTypeFile'], $this->imageInit['attrFile']) = getimagesize($this->imageInit['imageFilePathAbs']);
            $fileInfo = pathinfo($this->imageInit['imageFilePathAbs']);
            $this->imageInit['imageBaseName'] = $fileInfo['basename'];
            $this->imageInit['imageFileName'] = $fileInfo['filename'];
            $this->imageInit['extensionFile'] = $fileInfo['extension'];
            $this->imageInit['dirFilePath'] = preg_replace('/\ /', '_', preg_replace('/(.+)\/.+$/', "$1", $this->imageInit['imageFilePath']));
            $this->imageInit['dirFilePathAbs'] = $fileInfo['dirname'];
            $this->imageInit['filemtime'] = filemtime($this->imageInit['imageFilePathAbs']);
            // echo '<pre>'; var_dump($this->imageInit); echo '</pre>';
            // проверка на доступ
            $result = array_key_exists($this->imageInit['imageTypeFile'], $this->typeImg);
        } catch (\Exception $e) {
            return false;
        } catch (\Throwable $th) {
            return false;
        } finally {
            return $result;
        }
    }

    /** create image of aspect ratio
     * @param string $imagePat
     * @return string
     */
    private function createImageAspectRatio(string $imagePath): string
    {
        try{
            if(empty($this->aspectRatioImage)){ throw new \Exception('empty aspectRatioImage'); }
            if(empty($this->aspectRatioImage->x)){ throw new \Exception('empty aspectRatioImage X'); }
            if(empty($this->aspectRatioImage->y)){ throw new \Exception('empty aspectRatioImage Y'); }

            $imagePath = '/' . preg_replace('/^\//', '', $imagePath);

            $imageFilePathAbs = $this->documentRoot . $imagePath;
            if(!file_exists($imageFilePathAbs)){ throw new \Exception('not init file'); }

            list($imageWidth, $imageHeight, $imageTypeFileCode, $attrFile) = getimagesize($imageFilePathAbs);

            $lImageExtension = $this->typeImg[$imageTypeFileCode] ?? '';
            if(empty($lImageExtension)){ throw new \Exception('none type image'); }

            $fileInfo = pathinfo($imageFilePathAbs);
            $imageBaseName = $fileInfo['basename'];
            $imageFileName = $fileInfo['filename'];
            $extensionFile = $fileInfo['extension'];
            $dirFilePath = preg_replace('/\ /', '_', preg_replace('/(.+)\/.+$/', "$1", $imagePath));
            $dirFilePathAbs = $fileInfo['dirname'];
            $filemtime = filemtime($imageFilePathAbs);

            $nameImg = $imageFileName . '_' . $this->aspectRatioImage->x . '_' . $this->aspectRatioImage->y . '_' . $filemtime . '.' . $extensionFile;
            $imageDirectory = $this->cacheBase . '/tmp/aspect-ratio' . $dirFilePath;
            $this->createDirectory($imageDirectory);
            $imagePath = $imageDirectory . '/' . $nameImg;
            $pathImgAbsolute = $this->documentRoot . $imagePath;

            if(file_exists($pathImgAbsolute)){ throw new \Exception('file isset'); }

            $kSetAspectRatio = $this->aspectRatioImage->x / $this->aspectRatioImage->y;
            $kImageAspectRatio = $imageWidth / $imageHeight;

            $newWidth = $imageWidth;
            $newHeight = $imageHeight;
            $deltaX = $deltaY = 0;
            $startX = $startY = 0;
            if($kSetAspectRatio >= $kImageAspectRatio){
                // обрезаем по высоте
                $newHeight = floor($imageHeight / $kSetAspectRatio * $kImageAspectRatio);
                $deltaY = $imageHeight - $newHeight;
                $startY = floor( $deltaY / 2 );
            } else {
                // обрезаем по ширине
                $newWidth = floor($imageWidth * $kSetAspectRatio / $kImageAspectRatio);
                $deltaX = $imageWidth - $newWidth;
                $startX = floor( $deltaX / 2 );
            }

            $funcCreate = 'imagecreatefrom' . $lImageExtension;

            // Создаём дескриптор исходного изображения
            $lInitialImageDescriptor = $funcCreate($imageFilePathAbs);

            // Создаём дескриптор для выходного изображения 
            $lNewImageDescriptor = imagecreatetruecolor($newWidth, $newHeight);
            imageAlphaBlending($lNewImageDescriptor, false);
            imageSaveAlpha($lNewImageDescriptor, true);
            imagecopyresampled($lNewImageDescriptor, $lInitialImageDescriptor, 0, 0, $startX, $startY, $newWidth, $newHeight, ($imageWidth - $deltaX), ($imageHeight - $deltaY));

            // сохраняем полученное изображение в указанный файл
            $funcImg = 'image' . $lImageExtension;
            $funcImg($lNewImageDescriptor, $pathImgAbsolute, 100);

            imagedestroy($lNewImageDescriptor);
            imagedestroy($lInitialImageDescriptor);

            // $this->cropping(aNewImageFilePath: $pathImgAbsolute, startX: $startX, startY: $startY, newWidth: $newWidth, newHeight: $newHeight, oldWidth: ($imageWidth - $deltaX), oldHeight: ($imageHeight - $deltaY));
        } catch (\Exception | \Throwable $e) {
            return $imagePath;
        } finally {
            return $imagePath;
        }
    }
    
    /** init new image
     * @return bool
     */
    private function initNewImage(): bool
    {
        $result = false;
        try{
            $this->imageNew['dirRoot'] = $this->directoryCache;
            $this->imageNew['dirAbs'] = $this->createDirectory($this->imageNew['dirRoot']);
            $this->imageNew['name'] = $this->getNewNameFile();
            $result = true;
        } catch (\Exception $e) {
        } catch (\Throwable $th) {
        } finally {
            return $result;
        }
    }
    

    /** rotate only JPEG with saving
     * @return object <bool status, bool error>
     */
    private function setNormalizedImageData(): object
    {
        $result = (object)['status' => false, 'error' => false];
        try{
            $exifReadData = $this->imageInit['imageTypeFile'] == 2 ? exif_read_data($this->imageInit['imageFilePathAbs']) : [];
            // echo '<pre>'; var_dump($exifReadData); echo '</pre>';
            if($exifReadData && ($exifReadData['Orientation']??false)){
                // echo '<pre>'; var_dump($exifReadData['Orientation']); echo '</pre>';
                $lImageExtension = $this->typeImg[$this->imageInit['imageTypeFile']];
                $funcCreate = 'imagecreatefrom' . $lImageExtension;
                $lInitialImageDescriptor = $funcCreate($this->imageInit['imageFilePathAbs']);
                $imageRes = '';
                switch ($exifReadData['Orientation']) {
                    // Поворот на 180 градусов
                    case 3: {
                        $imageRes = imagerotate($lInitialImageDescriptor, 180, 0);
                        break;
                    }
                    // Поворот вправо на 90 градусов
                    case 6: {
                        $imageRes = imagerotate($lInitialImageDescriptor, -90, 0);
                        break;
                    }
                    // Поворот влево на 90 градусов
                    case 8: {
                        $imageRes = imagerotate($lInitialImageDescriptor, 90, 0);
                        break;
                    }
                }
                if(!empty($imageRes)){
                    $funcImg = 'image' . $lImageExtension;
                    $result->status = $funcImg($imageRes, $this->imageInit['imageFilePathAbs'], 92);
                }
            }
        } catch (\Exception $e) {
            $result->error = true;
        } catch (\Throwable $th) {
            $result->error = true;
        } finally {
            return $result;
        }
    }
    
    /**
     * crop image proportional or old height or width
     * @param string $aNewImageFilePath
     * @param int $size
     * @param string $figure set cover|height|width
     */
    private function cropWidthImage(string $aNewImageFilePath, int $size, string $figure = 'cover'): void
    {
        if($size > $this->imageInit['imageWidth']) $size = $this->imageInit['imageWidth'];
        // if($size <= $this->imageInit['imageWidth']){
            // initialize crop data
            $cropData = array(
                'newWidth' => $size,
                'newHeight' => $this->imageInit['imageHeight'],
                'oldWidth' => $this->imageInit['imageWidth'],
                'oldHeight' => $this->imageInit['imageHeight'],
                'src_x' => 0,
                'src_y' => 0,
            );
            // Определяем отображаемую область
            if($figure == 'height'){
                // crop on height
                $cropData['oldWidth'] = $cropData['newWidth'];
                $cropData['src_x'] = ceil(($this->imageInit['imageWidth'] - $cropData['newWidth']) / 2);
            } elseif($figure == 'width'){
                // crop on width
                $cropData['newWidth'] = $this->imageInit['imageWidth'];
                $cropData['newHeight'] = $size;
                $cropData['oldHeight'] = $size;
                $cropData['src_y'] = ceil(($this->imageInit['imageHeight'] - $cropData['newHeight']) / 2);
            } else {
                // transition on proportions
                $kWidth = $this->imageInit['imageHeight'] / $this->imageInit['imageWidth'];
                $cropData['newHeight'] = floor($cropData['newWidth'] * $kWidth);
            }
            $this->cropping(aNewImageFilePath: $aNewImageFilePath, startX: $cropData['src_x'], startY: $cropData['src_y'], newWidth: $cropData['newWidth'], newHeight: $cropData['newHeight'], oldWidth: $cropData['oldWidth'], oldHeight: $cropData['oldHeight']);
        // } else {
        //     copy($this->imageInit['imageFilePathAbs'], $aNewImageFilePath);
        // }
    }

    /** generate transition image
     * @param string $nameImg
     * @param int $newHeight
     * @return string
     */
    private function transitionHeightImage(string $nameImg, int $newHeight): string
    {
        try{
            $pathDirTmp = $this->cacheBase . '/tmp/transition-height' . $this->imageInit['dirFilePath'];
            $pathDirRecTmp = $this->createDirectory($pathDirTmp);
            $pathImg = $pathDirTmp . '/' . $nameImg;
            $pathImgAbs = $this->documentRoot . '/' . $pathImg;
            // dump($pathImgAbs);
            // echo '<pre>'; var_dump('file transition path ' . (file_exists($pathImgAbs)?'exist':'no')); echo '</pre>';
            if(!file_exists($pathImgAbs)){
                if($newHeight > $this->imageInit['imageHeight']) $newHeight = $this->imageInit['imageHeight'];
                // if($newHeight <= $this->imageInit['imageHeight']){
                    // initialize crop data
                    $cropData = array(
                        'newWidth' => $this->imageInit['imageWidth'],
                        'newHeight' => $newHeight,
                        'oldWidth' => $this->imageInit['imageWidth'],
                        'oldHeight' => $this->imageInit['imageHeight'],
                        'src_x' => 0,
                        'src_y' => 0,
                    );
                    $kHeight = $this->imageInit['imageHeight'] / $newHeight;
                    $cropData['newWidth'] = floor($cropData['newWidth'] / $kHeight);
                    // echo '<pre>'; var_dump($cropData); echo '</pre>';
                    $this->cropping(aNewImageFilePath: $pathImgAbs, startX: $cropData['src_x'], startY: $cropData['src_y'], newWidth: $cropData['newWidth'], newHeight: $cropData['newHeight'], oldWidth: $cropData['oldWidth'], oldHeight: $cropData['oldHeight']);
                // } else {
                //     // echo '<pre>'; var_dump('copy transition ' . $pathImgAbs); echo '</pre>';
                //     copy($this->imageInit['imageFilePathAbs'], $pathImgAbs);
                // }
            }
        } catch (\Exception $e) {
            //
        } catch (\Throwable $th) {
            //
        } finally {
            return $pathImg;
        }
    }
    
    /** create image of max height with set width
     * @param string $fullNameImg
     * @param string $pathImg
     * @param int $newWidth
     * @param int $minHeight
     * @return string
     */
    private function cropMinHeightImage(string $fullNameImg, string $pathImg, int $newWidth, int $minHeight): string
    {
        try{
            $pathImgRec = $this->imageNew['dirAbs'] . '/' . $fullNameImg;
            // echo '<pre>'; var_dump('pathImg ' . $pathImg); echo '</pre>';
            if(!file_exists($pathImgRec)){
                // echo '<pre>'; var_dump('newWidth ' . $newWidth); echo '</pre>';
                // echo '<pre>'; var_dump('imageInit newWidth ' . $this->imageInit['imageWidth']); echo '</pre>';
                if($newWidth > $this->imageInit['imageWidth']) $newWidth = $this->imageInit['imageWidth'];
                if($minHeight > $this->imageInit['imageHeight']) $minHeight = $this->imageInit['imageHeight'];
                // if($newWidth <= $this->imageInit['imageWidth'] || $minHeight < $this->imageInit['imageHeight']){
                    $tmpImage = $this->transitionHeightImage(nameImg: $fullNameImg, newHeight: (int)$minHeight);
                    $tmpImageAbs = $this->documentRoot . $tmpImage;
                    // echo '<pre>'; var_dump('tmpImage ' . $tmpImage); echo '</pre>';
                    // echo '<pre>'; var_dump('file exists tmpImage ' . (file_exists($tmpImageAbs)?'exist':'no')); echo '</pre>';
                    $cropImages = new CropImages();
                    $tmpPathImage = file_exists($tmpImageAbs) ? $tmpImage : $pathImgRec;
                    // echo '<pre>'; var_dump('pathImgRec ' . $pathImgRec); echo '</pre>';
                    // $pathImg = $cropImages->cropImages(imagePath: $tmpPathImage, dirCache: $this->imageInit['dirFilePath'], imageName: $fullNameImg, createWidth: $newWidth, figure: 'height', quality: $this->quality, originExt: $this->originExt);
                    $pathImg = $cropImages->cropImages(imagePath: $tmpPathImage, dirCache: ltrim($this->directoryCache, '/'.$this->cacheBase), imageName: $fullNameImg, createWidth: $newWidth, figure: 'height', quality: $this->quality, originExt: $this->originExt);
                    // echo '<pre>'; var_dump($this->directoryCache, $this->imageInit['dirFilePath']); echo '</pre>';
                    // echo '<pre>'; var_dump($pathImg); echo '</pre>';
                // } else {
                //     // echo '<pre>'; var_dump('copy crop-min ' . $pathImgRec); echo '</pre>';
                //     copy($this->imageInit['imageFilePathAbs'], $pathImgRec);
                // }
            }
        } catch (\Exception $e) {
            //
        } catch (\Throwable $th) {
            //
        } finally {
            return $pathImg;
        }
    }

    public function prepareAspectRatio(array $aspectRatio): void
    {
        if(empty($aspectRatio)){ return; }
        if(empty($aspectRatio[0]) || (int)($aspectRatio[0] ?? 0) <= 0){ return; }
        if(empty($aspectRatio[1]) || (int)($aspectRatio[1] ?? 0) <= 0){ return; }

        $this->aspectRatioImage = (object)[
            'x' => (int)$aspectRatio[0],
            'y' => (int)$aspectRatio[1],
        ];
    }
    

    /** get crop cache image
     * @param string $imagePath original image
     * @param string|array $createWidth resolution cache
     * @param int $quality quality cache
     * @param string $dirCache directory in cache
     * @param string $figure if cropping
     * @param int $height if crop max height
     * @param bool $originExt is origin extension file
     * @return string|array
     */
    public function cropImages(
        string $imagePath, 
        int|array $createWidth, 
        int $quality = 0, 
        string $dirCache = '', 
        string $imageName = '', 
        string $figure = 'cover', 
        array $aspectRatio = [], 
        int $height = 0, 
        bool $originExt = false
    ): string|array
    {
        $result = array();
        try{
            // validate
            if(empty($createWidth)){ throw new \Exception('no createWidth'); }
            
            if(!empty($aspectRatio)){
                $this->prepareAspectRatio(aspectRatio: $aspectRatio);
            }
            if(!empty($aspectRatio)){
                $arrListPath = explode('/', preg_replace('/^\//', '', $imagePath));
                $arrListPath = array_slice($arrListPath, 0, count($arrListPath)-1);
                $dirCache = !empty($dirCache) 
                    ? $dirCache
                    : '/' . implode('/', $arrListPath);
                // echo '<pre>'; var_dump($imagePath, $dirCache); echo '</pre>';
                $imagePath = $this->createImageAspectRatio(imagePath: $imagePath);
            }

            // set $this->imageInit data from image
            if($this->getImageInitData($imagePath) !== true){ throw new \Exception('not init image'); }
            // echo '<pre>'; var_dump($this->imageInit); echo '</pre>';

            // rotate image with saving and re-init image
            $normalResponse = $this->setNormalizedImageData();
            if($normalResponse->status){ $this->getImageInitData($imagePath); }
            // echo '<pre>'; var_dump($this->imageInit); echo '</pre>';

            // set config
            if(!empty($quality) && (int)$quality > 0) $this->quality = (int)$quality;
            $this->originExt = $originExt??false;
            $this->directoryCache = !empty($dirCache) 
                ? $this->cacheBase . '/' . preg_replace('/^\//', '', $dirCache)
                : $this->cacheBase . $this->imageInit['dirFilePath'];

            // set new image
            $this->initNewImage();
            $arrWidth = is_array($createWidth) ? $createWidth : array($createWidth);
            // echo '<pre>'; var_dump($this->imageInit); echo '</pre>';
            // echo '<pre>'; var_dump($this->imageNew); echo '</pre>';
            foreach($arrWidth AS $rawWidth){
                $vWidth = (int)$rawWidth;
                if(empty($vWidth)) continue;
                if($figure == 'cropMinHeight' && (int)$height > 0){
                    $nameImg = empty($imageName) ? $vWidth . '_' . $height . '_' . $figure . '_' . $this->imageNew['name'] : $imageName;
                    $pathImg = $this->imageNew['dirRoot'] . '/' . $nameImg;
                    $pathImgRec = $this->imageNew['dirAbs'] . '/' . $nameImg;
                    // echo '<pre>'; var_dump($nameImg); echo '</pre>';
                    if(!file_exists($pathImgRec)) $this->cropMinHeightImage(fullNameImg: $nameImg, pathImg: $pathImg, newWidth: (int)$vWidth, minHeight: (int)$height);
                    $result[$vWidth] = $pathImg;
                    // echo '<pre>'; var_dump($result); echo '</pre>';
                } else {
                    $nameImg = empty($imageName) ? $vWidth . '_' . $figure . '_' . $this->imageNew['name'] : $imageName;
                    $pathImg = $this->imageNew['dirRoot'] . '/' . $nameImg;
                    $pathImgRec = $this->imageNew['dirAbs'] . '/' . $nameImg;
                    // echo '<pre>'; var_dump(file_exists($pathImgRec)); echo '</pre>';
                    if(!file_exists($pathImgRec)) $this->cropWidthImage(aNewImageFilePath: $pathImgRec, size: $vWidth, figure: $figure);
                    $result[$vWidth] = $pathImg;
                }
            }
        } catch (\Exception $e) {
            //
        } catch (\Throwable $th) {
            //
        } finally {
            if(count($result) <= 1) $result = implode('', $result);
            return $result;
        }
    }
}