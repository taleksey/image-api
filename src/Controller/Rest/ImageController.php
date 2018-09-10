<?php

namespace App\Controller\Rest;

use App\Image\Service\ImageService;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Image;

class ImageController extends FOSRestController
{

    /**
     * @var ImageService
     */
    private $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * @Rest\Get("/image/{imageId}/{width}/{height}")
     * @param int $imageId
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function getAction(int $imageId, int $width, int $height)
    {
        try {
            return $this->imageService->getById($imageId, $width, $height);
        } catch (\Exception $e){
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @Rest\Post("/image")
     * @param Request $request
     * @return View
     */
    public function postAction(Request $request)
    {
        $file = $request->files->get('filename');
        if (null === $file) {
            throw new \InvalidArgumentException('Filename cannot be empty.');
        }

        try {
            $image = $this->imageService->saveImage($file);
        } catch (\Exception $e){
            throw new \InvalidArgumentException($e->getMessage());
        }

        return $this->view($image, Response::HTTP_CREATED);
    }

    /**
     * @Rest\Put("/image/{imageId}")
     * @param Request $request
     * @param int $imageId
     * @return View
     */
    public function putAction(Request $request, int $imageId)
    {
        $contentType = $request->server->get('CONTENT_TYPE');
        $content = $request->getContent();


        try {
            $this->imageService->update($content, $contentType, $imageId);
        } catch (\Exception $e){
            throw new \InvalidArgumentException($e->getMessage());
        }

        return $this->view('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Delete("/image/{imageId}")
     * @param int $imageId
     * @return View
     */
    public function deleteAction(int $imageId)
    {
        try {
            $this->imageService->delete($imageId);
        } catch (\Exception $e){
            throw new \InvalidArgumentException($e->getMessage());
        }

        return $this->view('', Response::HTTP_NO_CONTENT);
    }
}