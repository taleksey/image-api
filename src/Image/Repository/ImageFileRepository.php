<?php

namespace App\Image\Repository;


use App\Image\Entity\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageFileRepository
{
    /**
     * @var Imagine
     */
    private $imagine;
    /**
     * @var int
     */
    private $maxSizeImage;
    /**
     * @var string
     */
    private $uploadPathDirectory;

    public function __construct(Imagine $imagine, $maxSizeImage, $uploadPathDirectory)
    {
        $this->imagine = $imagine;
        $this->maxSizeImage = $maxSizeImage;
        $this->uploadPathDirectory = $uploadPathDirectory;

    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function saveImage(UploadedFile $file): string
    {
        $filePath = $file->getRealPath();
        $image = $this->imagine->open($filePath);
        $imageGetSize = $image->getSize();

        if ($this->isResizeImage($imageGetSize)) {
            $getImageSizes = $this->getSizeProcessedImage($imageGetSize);
            $width = $getImageSizes['width'];
            $height = $getImageSizes['height'];
            $image->resize(new Box($width, $height));
        }

        $filename = $this->generateFileName($file);
        $pathToFile = $this->getFullPathFile($filename);

        $image->save($pathToFile);

        return $filename;
    }

    /**
     * @param Image $image
     * @param int $width
     * @param int $height
     * @return \Imagine\Gd\Image|ImageInterface
     */
    public function getImage(Image $image, $width, $height)
    {
        $fullPath = $this->getFullPathFile($image->getPath());
        $options = array(
            'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH
        );

        if ($image->getExtension() === 'jpg') {
            $options['jpeg_quality'] = 100;
        }

        return $this->imagine->open($fullPath)
            ->resize(new Box($width, $height))
            ->show($image->getExtension(), $options);
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function getImageExtension(UploadedFile $file): string
    {
        return $file->getClientOriginalExtension();
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function remove($filename): bool
    {
        $fullPath = $this->getFullPathFile($filename);

        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }

        return false;
    }

    /**
     * @param $content
     * @param $contentType
     * @return array|bool
     */
    public function getImageByContent($content, $contentType)
    {
        preg_match('/boundary=(.*)$/', $contentType, $matches);

        if (empty($matches)) {
            throw new UploadException('Error: Please input file');
        }
        $result = preg_split("/-+$matches[1]/", $content);

        array_pop($result);

        $findImage = false;
        foreach ($result as $row) {
            if (strpos($row, 'filename') !== FALSE)
            {
                $findImage = $this->parseImageFromContent($row);
            }
        }

        if (!$findImage) {
            throw new UploadException('Error: Please input file');
        }

        return $findImage;
    }

    /**
     * @param Box $imageBox
     * @return bool
     */
    private function isResizeImage(Box $imageBox): bool
    {
        $width = $imageBox->getWidth();
        $height = $imageBox->getHeight();

        return $width > $this->maxSizeImage || $height > $this->maxSizeImage;
    }

    /**
     * @param Box $imageBox
     * @return array
     */
    private function getSizeProcessedImage(Box $imageBox): array
    {
        $width = $imageBox->getWidth();
        $height = $imageBox->getHeight();

        if ($width > $this->maxSizeImage && $height > $this->maxSizeImage) {
            if ($width > $height) {
                $height = $this->getCalculateHeight($width, $height);
                $width = $this->maxSizeImage;
            } else {
                $width = $this->getCalculateWidth($width, $height);
                $height = $this->maxSizeImage;
            }
        } elseif($width > $this->maxSizeImage) {
            $height = $this->getCalculateHeight($width, $height);
            $width = $this->maxSizeImage;
        } elseif($height > $this->maxSizeImage) {
            $width = $this->getCalculateWidth($width, $height);
            $height = $this->maxSizeImage;
        }

        return ['width' => $width, 'height' => $height];

    }

    /**
     * @param int $width
     * @param int $height
     * @return float
     */
    private function getCalculateHeight($width, $height): float
    {
        $multiplier = $width/$height;

        return round($this->maxSizeImage/$multiplier);
    }

    /**
     * @param int $width
     * @param int $height
     * @return float
     */
    private function getCalculateWidth($width, $height): float
    {
        $multiplier = $height/$width;

        return round($this->maxSizeImage/$multiplier);
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    private function generateFileName(UploadedFile $file): string
    {

        $prefix = $this->getOriginalFilename($file);
        return uniqid($prefix, false) . '.' . $file->getClientOriginalExtension();
    }

    /**
     * @param UploadedFile $file
     * @return mixed
     */
    private function getOriginalFilename(UploadedFile $file)
    {
        return str_replace('.' . $file->getClientOriginalExtension(), '', $file->getClientOriginalName());
    }

    /**
     * @param string $filename
     * @return string
     */
    private function getFullPathFile($filename): string
    {
        return $this->uploadPathDirectory . '/' . $filename;
    }

    /**
     * @param string $data
     * @return array
     */
    private function parseImageFromContent($data): array
    {
        $result = [];
        $data = ltrim($data);
        $idx = strpos( $data, "\r\n\r\n" );

        if ( $idx === FALSE ) {
            throw new UploadException('Error: Please check file');
        }

        $headers = substr( $data, 0, $idx );
        $content = substr( $data, $idx + 4, -2 ); // Skip the leading \r\n and strip the final \r\n
        $name = '-unknown-';
        $filename = '-unknown-';
        $filetype = 'application/octet-stream';
        $header = strtok( $headers, "\r\n" );
        while ( $header !== FALSE ) {
            if ( substr($header, 0, \strlen('Content-Disposition: ')) === 'Content-Disposition: ' ) {
                if ( preg_match('/name=\"([^\"]*)\"/', $header, $nmatch ) ) {
                    $name = $nmatch[1];
                }
                if ( preg_match('/filename=\"([^\"]*)\"/', $header, $nmatch ) ) {
                    $filename = $nmatch[1];
                }
            } elseif ( substr($header, 0, \strlen('Content-Type: ')) === 'Content-Type: ' ) {
                $filetype = trim( substr($header, \strlen('Content-Type: ')) );
            } else {
                throw new UploadException('Skipping Header: ' . $header);
            }
            $header = strtok("\r\n");
        }

        $path = sys_get_temp_dir() . '/php' . substr( sha1(mt_rand()), 0, 6 );
        $bytes = file_put_contents( $path, $content );
        if ( $bytes !== FALSE ) {
            $file = new UploadedFile( $path, $filename, $filetype, $bytes, UPLOAD_ERR_OK );
            $result = array( $name => $file );
        }

        return $result;
    }
}