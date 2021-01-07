<?php
/* README
VERSION 2.0.1

require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/cropImages.php');
$cropImages = new \cropImages();

// return link img
$imgBackC = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80);
$imgBackS = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80, 'square');
$imgBackW = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80, 'width');
$imgBackH = $cropImages->cropImages($imgBack, '/cache/image-header', 200, 80, 'height');
$arrImgBack = $cropImages->cropImages($imgBack, '/cache/image-header', array(), 80, 'maxheight', 850);

// return arr`s link`s img array('Resize' => 'Link')
$arrImgBack = $cropImages->cropImages($imgBack, '/cache/image-header', array('4800', '1920', '1200', '800', '600'), 80, 'height');
*/

class cropImages{
    private $typeImg = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');
    private $typeImgAll = array(0=>'UNKNOWN',1=>'GIF',2=>'JPEG',3=>'PNG',4=>'SWF',5=>'PSD',6=>'BMP',7=>'TIFF_II',8=>'TIFF_MM',9=>'JPC',10=>'JP2',11=>'JPX',12=>'JB2',13=>'SWC',14=>'IFF',15=>'WBMP',16=>'XBM',17=>'ICO',18=>'COUNT');
    private $imageInit = array(
        'imageFilePath' => '',
        'imageFilePathAbs' => '',
        'imageWidth' => '',
        'imageHeight' => '',
        'imageTypeFile' => '',
    );
    private $imageNew = array(
        'dirRoot' => '',
        'dirAbs' => '',
        'name' => '',
    );
    private $directoryCache = '/cache';
    private $quality = 90;
    
    public function __construct(){
        
    }
    
    private function setNormalizedImageData($imageInitial){
        $result = '';
        $dirRoot = '/cache/img-normalized';
        $dirAbs = $_SERVER['DOCUMENT_ROOT'] . $dirRoot;
        if(!file_exists($dirAbs)){
            mkdir($dirAbs, 0777, true);
        }
        $imageFilePath = preg_match('/^\/.+/', $imageInitial) ? preg_replace('/^\/(.+)?/', "$1", $imageInitial) : $imageInitial;
        $imageFilePathAbs = $_SERVER['DOCUMENT_ROOT'] . '/' . $imageFilePath;
        $fileName = array(
            'name' => preg_replace('/(.+)\..+/', "$1", preg_replace('/.*\/(.+)/', "$1", $imageFilePath)),
            'type' => preg_replace('/.+\.(.+)/', "$1", preg_replace('/.*\/(.+)/', "$1", $imageFilePath)),
        );
        $newImgName = filemtime($imageFilePathAbs) . preg_replace('/[\.\ \(\)\/\'\"]/', '', $imageFilePath) . '.' . $fileName['type'];
        $imageFilePath_normal = $dirRoot . '/' . $newImgName;
        $imageFilePathAbs_normal = $dirAbs . '/' . $newImgName;
        
        // rotate
        $image = imagecreatefromjpeg($imageFilePathAbs);
        $exif = exif_read_data($imageFilePathAbs);
        if(isset($exif['Orientation']) && !empty($exif['Orientation']) && in_array($exif['Orientation'], array(3,6,8)) && $fileName['type'] == 'jpg'){
            if(!file_exists($imageFilePathAbs_normal)){
                switch ($exif['Orientation']) {
                    // Поворот на 180 градусов
                    case 3: {
                        $result = imagerotate($image,180,0);
                        break;
                    }
                    // Поворот вправо на 90 градусов
                    case 6: {
                        $result = imagerotate($image,-90,0);
                        break;
                    }
                    // Поворот влево на 90 градусов
                    case 8: {
                        $result = imagerotate($image,90,0);
                        break;
                    }
                }
                //echo $imageFilePathAbs_normal . '<br>';
                //copy($imageFilePathAbs_normal, $result);
                imagejpeg($result, $imageFilePathAbs_normal);
                $result = $imageFilePath_normal;
            } else {
                $result = $imageFilePath_normal;
            }
        }
        return $result;
    }
    
    private function getImageInitData($imageInitial){
        $this->imageInit['imageFilePath'] = preg_match('/^\/.+/', $imageInitial) ? preg_replace('/^\/(.+)?/', "$1", $imageInitial) : $imageInitial;
        $this->imageInit['imageFilePathAbs'] = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->imageInit['imageFilePath'];
        if(file_exists($this->imageInit['imageFilePathAbs']) || true){
            // Получаем размеры и тип изображения в виде числа
            list($this->imageInit['imageWidth'], $this->imageInit['imageHeight'], $this->imageInit['imageTypeFile']) = getimagesize($this->imageInit['imageFilePathAbs']); 
            // проверка на доступ
            return array_key_exists($this->imageInit['imageTypeFile'], $this->typeImg);
        } else {
            return false;
        }
    }
    
    private function initNewImage(){
        $this->imageNew['dirRoot'] = preg_match('/^\/.+/', $this->directoryCache) ? $this->directoryCache : '/' . $this->directoryCache;
        $this->imageNew['dirAbs'] = $_SERVER['DOCUMENT_ROOT'] . $this->imageNew['dirRoot'];
        if(!file_exists($this->imageNew['dirAbs'])){
            mkdir($this->imageNew['dirAbs'], 0777, true);
        }
        $fileName = array(
            'name' => preg_replace('/(.+)\..+/', "$1", preg_replace('/.*\/(.+)/', "$1", $this->imageInit['imageFilePath'])),
            'type' => preg_replace('/.+\.(.+)/', "$1", preg_replace('/.*\/(.+)/', "$1", $this->imageInit['imageFilePath'])),
        );
        $this->imageNew['name'] = filemtime($this->imageInit['imageFilePathAbs']) . preg_replace('/[\.\ \(\)\/\'\"]/', '', $this->imageInit['imageFilePath']) . '.' . $fileName['type'];
    }
    
    private function cropWidthImage($pathImgRec, $newWidth, $figure = 'cover'){
        $aNewImageFilePath = $pathImgRec;
        if($newWidth <= $this->imageInit['imageWidth']){
            // initialize crop data
            $cropData = array(
                'newWidth' => $newWidth,
                'newHeight' => $this->imageInit['imageHeight'],
                'oldWidth' => $this->imageInit['imageWidth'],
                'oldHeight' => $this->imageInit['imageHeight'],
                'src_x' => 0,
                'src_y' => 0,
            );
            // Определяем отображаемую область
            if($figure == 'height'){
                // on height
                $cropData['oldWidth'] = $cropData['newWidth'];
                $cropData['src_x'] = ceil(($this->imageInit['imageWidth'] - $cropData['newWidth']) / 2);
            } elseif($figure == 'width'){
                // on width
                $cropData['newWidth'] = $this->imageInit['imageWidth'];
                $cropData['newHeight'] = $newWidth;
                $cropData['oldHeight'] = $newWidth;
                $cropData['src_y'] = ceil(($this->imageInit['imageHeight'] - $cropData['newHeight']) / 2);
            } else {
                //on width
                $kWidth = $this->imageInit['imageHeight'] / $this->imageInit['imageWidth'];
                $cropData['newHeight'] = floor($cropData['newWidth'] * $kWidth);
            }
            $lImageExtension = $this->typeImg[$this->imageInit['imageTypeFile']];
            // Получаем название функции, соответствующую типу, для создания изображения
            $funcCreate = 'imagecreatefrom' . $lImageExtension; 
            // Создаём дескриптор исходного изображения
            $lInitialImageDescriptor = $funcCreate($this->imageInit['imageFilePathAbs']);
            // Создаём дескриптор для выходного изображения
            $lNewImageDescriptor = imagecreatetruecolor($cropData['newWidth'], $cropData['newHeight']);
            imageAlphaBlending($lNewImageDescriptor,false);
            imageSaveAlpha($lNewImageDescriptor,true);
            // imagecopyresampled( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
            imagecopyresampled($lNewImageDescriptor, $lInitialImageDescriptor, 0, 0, $cropData['src_x'], $cropData['src_y'], $cropData['newWidth'], $cropData['newHeight'], $cropData['oldWidth'], $cropData['oldHeight']);
            $funcImg = 'image' . $lImageExtension;
            
            // сохраняем полученное изображение в указанный файл
            if($this->imageInit['imageTypeFile'] == 2){
                $funcImg($lNewImageDescriptor, $aNewImageFilePath, $this->quality);
            } else {
                $funcImg($lNewImageDescriptor, $aNewImageFilePath);
            }
            imagedestroy($lNewImageDescriptor);
            imagedestroy($lInitialImageDescriptor);
        } else {
            copy($this->imageInit['imageFilePathAbs'], $aNewImageFilePath);
        }
    }
    
    private function cropMaxheightImage($pathImgRec, $newWidth, $maxHeight){
        // init
        $aNewImageFilePath = $pathImgRec;
        $figure = ''; // tmp zagl
        // no bed img
        if($newWidth <= $this->imageInit['imageWidth'] || $maxHeight <= $this->imageInit['imageHeight']){
            $newWidth > $this->imageInit['imageWidth'] ? $newWidth = $this->imageInit['imageWidth'] : false;
            $maxHeight > $this->imageInit['imageHeight'] ? $maxHeight = $this->imageInit['imageHeight'] : false;
            $kW = $this->imageInit['imageWidth'] / $newWidth;
            $kH = $this->imageInit['imageHeight'] / $maxHeight;
            $kResImg = $kW > $kH ? $kH : $kW;
            // initialize crop data
            $cropData = array(
                'newWidth' => $newWidth,
                'newHeight' => $maxHeight,
                'oldWidth' => $newWidth * $kResImg,
                'oldHeight' => $maxHeight * $kResImg,
                'src_x' => $kW > $kH ? ceil(($this->imageInit['imageWidth'] - ($newWidth * $kResImg)) / 2) : 0,
                'src_y' => $kW < $kH ? ceil(($this->imageInit['imageHeight'] - ($maxHeight * $kResImg)) / 2) : 0,
            );
            /*
            echo $newWidth . ' / ';
            echo $maxHeight . ' - ';
            echo $kW . ' - ';
            echo $kH . ' - ';
            echo $kResImg . ' - ';
            echo $newWidth * $kResImg . ' - ';
            echo $maxHeight * $kResImg . ' - ';
            echo ($kW > $kH ? ceil(($this->imageInit['imageWidth'] - ($newWidth * $kResImg)) / 2) : 0) . ' - ';
            echo ($kW > $kH ? 0 : ceil(($this->imageInit['imageHeight'] - ($maxHeight * $kResImg)) / 2)) . ' - ';
            echo ' <br> ';
            */
            $lImageExtension = $this->typeImg[$this->imageInit['imageTypeFile']];
            // Получаем название функции, соответствующую типу, для создания изображения
            $funcCreate = 'imagecreatefrom' . $lImageExtension; 
            // Создаём дескриптор исходного изображения
            $lInitialImageDescriptor = $funcCreate($this->imageInit['imageFilePathAbs']);
            // Создаём дескриптор для выходного изображения
            $lNewImageDescriptor = imagecreatetruecolor($cropData['newWidth'], $cropData['newHeight']);
            imageAlphaBlending($lNewImageDescriptor, false);
            imageSaveAlpha($lNewImageDescriptor, true);
            // imagecopyresampled( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
            imagecopyresampled($lNewImageDescriptor, $lInitialImageDescriptor, 0, 0, $cropData['src_x'], $cropData['src_y'], $cropData['newWidth'], $cropData['newHeight'], $cropData['oldWidth'], $cropData['oldHeight']);
            $funcImg = 'image' . $lImageExtension;
            
            // сохраняем полученное изображение в указанный файл
            if($this->imageInit['imageTypeFile'] == 2){
                $funcImg($lNewImageDescriptor, $aNewImageFilePath, $this->quality);
            } else {
                $funcImg($lNewImageDescriptor, $aNewImageFilePath);
            }
            imagedestroy($lNewImageDescriptor);
            imagedestroy($lInitialImageDescriptor);
        } else {
            copy($this->imageInit['imageFilePathAbs'], $aNewImageFilePath);
        }
    }
    
    public function cropImages($aInitialImageFilePath, $dirCache, $createWidth, $qual = 90, $figure = 'cover', $height = 0){
        $this->quality = $qual;
        $this->directoryCache = $dirCache;
        $result = array();
        $normalImgCopy = $this->setNormalizedImageData($aInitialImageFilePath);
        !empty($normalImgCopy) ? $aInitialImageFilePath = $normalImgCopy : false;
        //echo $normalImgCopy . '<br>';
        if($this->getImageInitData($aInitialImageFilePath)){
            $this->initNewImage();
            $arrWidth = is_array($createWidth) ? $createWidth : array($createWidth);
            if(!empty($arrWidth)){
                foreach($arrWidth AS $vWidth){
                    $vWidth = (int)$vWidth;
                    if($figure == 'square'){
                        $cropImages = new cropImages();
                        if($this->imageInit['imageWidth'] >= $this->imageInit['imageHeight']){
                            $tmpImage = $cropImages->cropImages($this->imageInit['imageFilePath'], $this->directoryCache . '/croper', $this->imageInit['imageHeight'], 100, 'height');
                        } else {
                            $tmpImage = $cropImages->cropImages($this->imageInit['imageFilePath'], $this->directoryCache . '/croper', $this->imageInit['imageWidth'], 100, 'width');
                        }
                        $tmpImageCrop = $cropImages->cropImages($tmpImage, $this->directoryCache, $vWidth, $this->quality);
                        $result[$vWidth] = $tmpImageCrop;
                    } elseif($figure == 'maxheight' && (int)$height > 0){
                        $nameImg = $vWidth . '_' . $figure . '_' . $this->imageNew['name'];
                        $pathImg = $this->imageNew['dirRoot'] . '/' . $nameImg;
                        $pathImgRec = $this->imageNew['dirAbs'] . '/' . $nameImg;
                        !file_exists($pathImgRec) ? $this->cropMaxheightImage($pathImgRec, $vWidth, (int)$height) : false;
                        $result[$vWidth] = $pathImg;
                    } else {
                        $nameImg = $vWidth . '_' . $figure . '_' . $this->imageNew['name'];
                        $pathImg = $this->imageNew['dirRoot'] . '/' . $nameImg;
                        $pathImgRec = $this->imageNew['dirAbs'] . '/' . $nameImg;
                        !file_exists($pathImgRec) ? $this->cropWidthImage($pathImgRec, $vWidth, $figure) : false;
                        //$this->cropWidthImage($pathImgRec, $vWidth, $figure);
                        $result[$vWidth] = $pathImg;
                    }
                }
                count($result) == 1 ? $result = implode('', $result) : false;
            }
        } else {
            echo '<script>console.log("Картинка битая: ' . $aInitialImageFilePath . '")</script>';
            echo '<script>console.log("Тип картинки: ' . $this->imageInit['imageTypeFile'] . '::' . $this->typeImgAll[$this->imageInit['imageTypeFile']] . '")</script>';
        }
        return $result;
    }
}




