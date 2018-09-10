<?php

namespace App\Image\Repository;


use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Image\Entity\Image;

interface ImageRepository
{
    /**
     * @param UploadedFile $filename
     * @return Image
     */
    public function save(UploadedFile $filename): Image;

    /**
     * @param int $imageId
     * @param int $width
     * @param int $height
     * @return \Imagine\Gd\Image|\Imagine\Image\ImageInterface|mixed
     */
    public function getById($imageId, int $width, int $height);

    /**
     * @param UploadedFile $filename
     * @param int $imageId
     * @return Image|mixed
     */
    public function update(UploadedFile $filename, int $imageId);

    /**
     * @param string $content
     * @param string $contentType
     * @return array
     */
    public function getImageByContent($content, $contentType);

    /**
     * @param int $imageId
     * @return mixed
     */
    public function delete(int $imageId);
}