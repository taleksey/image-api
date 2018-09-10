<?php
/**
 * Created by PhpStorm.
 * User: aleksey
 * Date: 05.09.18
 * Time: 23:45
 */

namespace App\Image\Service;


use App\Image\Repository\ImageRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ImageService
{
    const IMAGEMIMETYPE = [
        'image/gif',
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/tiff',
        'image/webp'
    ];
    /**
     * @var ImageRepository
     */
    private $imageRepository;

    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    /**
     * @param UploadedFile $filename
     * @return mixed
     */
    public function saveImage(UploadedFile $filename)
    {
        if (!$filename->isValid()) {
            throw new UploadException('File was not uploaded correctly.');
        }

        $this->isValidImage($filename->getMimeType());

        return $this->imageRepository->save($filename);
    }

    /**
     * @param $mimeType
     * @return bool
     */
    public function isImage($mimeType): bool
    {
        return \in_array($mimeType, self::IMAGEMIMETYPE, true);
    }

    /**
     * @param int $imageId
     * @param $width
     * @param $height
     * @return mixed
     */
    public function getById(int $imageId, int $width, int $height)
    {
        return $this->imageRepository->getById($imageId, $width, $height);
    }

    /**
     * @param string $content
     * @param string $contentType
     * @param int $imageId
     * @return mixed
     */
    public function update($content, $contentType,int $imageId)
    {
        $totalImageInformation = $this->imageRepository->getImageByContent($content, $contentType);
        $image = array_shift($totalImageInformation);
        $this->isValidImage($image->getMimeType());

        return $this->imageRepository->update($image, $imageId);
    }

    /**
     * @param $imageId
     * @return mixed
     */
    public function delete(int $imageId)
    {
        return $this->imageRepository->delete($imageId);
    }

    /**
     * @param string $mimeType
     * @return bool
     */
    private function isValidImage($mimeType)
    {
        if (!$this->isImage($mimeType)) {
            throw new ExtensionFileException('File is not image.');
        }

        return true;
    }

}