<?php

namespace App\Controller\Api;

use App\Controller\ApiResponseController;
use App\Entity\Track;
use App\Factory\FileFactory;
use App\Response\ApiResponse;
use App\Service\AwsService;
use App\Service\GoogleCloudService;
use App\Service\S3Service;
use App\Service\WordService;
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
     * @var S3Service
     */
    private $s3Service;

    /**
     * @var AwsService
     */
    private $awsService;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * LyricsController constructor.
     *
     * @param GoogleCloudService $googleCloudService
     * @param WordService        $wordService
     * @param S3Service          $s3Service
     * @param AwsService         $awsService
     * @param FileFactory        $fileFactory
     */
    public function __construct(GoogleCloudService $googleCloudService, WordService $wordService, S3Service $s3Service, AwsService $awsService, FileFactory $fileFactory)
    {
        $this->googleCloudService = $googleCloudService;
        $this->wordService = $wordService;
        $this->s3Service = $s3Service;
        $this->awsService = $awsService;
        $this->fileFactory = $fileFactory;
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

//        try {
//            $track = $this->googleCloudService->extractWordsFromTrack($track);
//        } catch (Exception $exception){
//            return $this->apiFailedResponse($exception->getMessage(), 'Failed to add words');
//        }

//        return $this->apiSuccessResponse(ApiResponse::format(), ['api_word']);
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

        $words = $this->wordService->getBy(['track' => $track]);

        return $this->apiSuccessResponse(ApiResponse::format($words), ['api_word']);
    }
}
