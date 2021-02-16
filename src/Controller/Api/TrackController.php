<?php

namespace App\Controller\Api;

use App\Controller\ApiResponseController;
use App\Entity\Track;
use App\Form\Track\CreateType;
use App\Service\TrackService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TrackController
 * @Route("/track")
 */
class TrackController extends ApiResponseController
{
    /**
     * @var TrackService
     */
    private $trackService;

    /**
     * TrackController constructor.
     *
     * @param TrackService $trackService
     */
    public function __construct(TrackService $trackService)
    {
        $this->trackService = $trackService;
    }

    /**
     * @Route("/new")
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function new(Request $request): JsonResponse
    {
        $track = new Track();
        $form = $this->createForm(CreateType::class, $track);
        $form->submit($request->files->all() + $request->request->all());

        if ($form->isValid()) {
            try {
                $this->trackService->createWithFile($track, $form->get('file')->getData());

                return $this->json('success');
            } catch (Exception $e) {
                return $this->json($e->getMessage(), 500);
            }
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json($errors, 400);
    }
}
