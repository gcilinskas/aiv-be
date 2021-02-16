<?php

namespace App\Controller\Api;

use App\Controller\ApiResponseController;
use App\Entity\Track;
use App\Response\ApiResponse;
use App\Service\GoogleCloudService;
use App\Service\WordService;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WordController
 * @Route("/words")
 */
class WordController extends ApiResponseController
{
    /**
     * @var WordService
     */
    private $wordService;

    /**
     * @var GoogleCloudService
     */
    private $googleCloudService;

    /**
     * LyricsController constructor.
     *
     * @param GoogleCloudService $googleCloudService
     * @param WordService        $wordService
     */
    public function __construct(GoogleCloudService $googleCloudService, WordService $wordService)
    {
        $this->googleCloudService = $googleCloudService;
        $this->wordService = $wordService;
    }

    /**
     * @Route("/add/{track}/all", name="api_add_track_all_words")
     * @param Track        $track
     *
     * @return Response
     */
    public function addTrackAllWords(Track $track): Response
    {
        // TODO Voter

        try {
            $track = $this->googleCloudService->extractWordsFromTrack($track);
        } catch (Exception $exception){
            return $this->apiFailedResponse($exception->getMessage(), 'Failed to add words');
        }

        return $this->apiSuccessResponse(ApiResponse::format($track->getWords()), ['api_word']);
    }

    /**
     * @Route("/get/{track}/all", name="api_get_track_all_words")
     * @param Track $track
     *
     * @return Response
     */
    public function wordsByTrack(Track $track): Response
    {
        // TODO Voter

        return $this->wordService->getBy(['track' => $track]);
    }
}
