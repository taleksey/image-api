<?php
/**
 * Created by PhpStorm.
 * User: aleksey
 * Date: 05.09.18
 * Time: 23:46
 */

namespace App\Image\Repository;


use App\Image\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class MysqlImageRepository implements ImageRepository
{
    /**
     * @var ImageFileRepository
     */
    private $imageFileRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(ImageFileRepository $imageFileRepository, EntityManagerInterface $entityManager)
    {
        $this->imageFileRepository = $imageFileRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param UploadedFile $file
     * @return Image
     */
    public function save(UploadedFile $file): Image
    {
        $pathFilename = $this->imageFileRepository->saveImage($file);
        $fileExtension = $this->imageFileRepository->getImageExtension($file);
        $image = new Image();

        $image->setPath($pathFilename);
        $image->setExtension($fileExtension);
        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $image;
    }

    /**
     * @param int $imageId
     * @param int $width
     * @param int $height
     * @return \Imagine\Gd\Image|\Imagine\Image\ImageInterface|mixed
     */
    public function getById($imageId, int $width, int $height)
    {
        $image = $this->getImageById($imageId);

        return $this->imageFileRepository->getImage($image, $width, $height);
    }

    /**
     * @param UploadedFile $file
     * @param int $imageId
     * @return Image|mixed
     */
    public function update(UploadedFile $file, int $imageId)
    {

        $image = $this->getImageById($imageId);
        $this->imageFileRepository->remove($image->getPath());

        $fileExtension = $this->imageFileRepository->getImageExtension($file);
        $pathFilename = $this->imageFileRepository->saveImage($file);

        $image->setPath($pathFilename);
        $image->setExtension($fileExtension);
        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $image;
    }

    /**
     * @param string $content
     * @param string $contentType
     * @return array|bool
     */
    public function getImageByContent($content, $contentType)
    {
        return $this->imageFileRepository->getImageByContent($content, $contentType);
    }

    /**
     * @param int $imageId
     * @return mixed|void
     */
    public function delete(int $imageId)
    {
        $image = $this->getImageById($imageId);
        $this->imageFileRepository->remove($image->getPath());
        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }
    /**
     * @param int $imageId
     * @return Image
     */
    private function getImageById($imageId)
    {
        $image =$this->entityManager->find(Image::class, $imageId);

        if (null === $image) {
            throw new NotFoundResourceException('Image is not found.');
        }

        return $image;
    }
}