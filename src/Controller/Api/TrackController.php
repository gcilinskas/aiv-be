<?php

namespace App\Controller\Api;

use App\Controller\ApiResponseController;
use App\Entity\File;
use App\Entity\Track;
use App\Form\Track\CreateType;
use App\Response\ApiResponse;
use App\Response\TranscriptionResponse;
use App\Service\AwsService;
use App\Factory\FileFactory;
use App\Service\FileService;
use App\Service\S3Service;
use App\Service\TrackService;
use App\Util\AWS;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * @var AwsService
     */
    private $awsService;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var S3Service
     */
    private $s3Service;

    /**
     * TrackController constructor.
     *
     * @param TrackService $trackService
     * @param AwsService   $awsService
     * @param FileFactory  $fileFactory
     * @param FileService  $fileService
     * @param S3Service    $s3Service
     */
    public function __construct(
        TrackService $trackService,
        AwsService $awsService,
        FileFactory $fileFactory,
        FileService $fileService,
        S3Service $s3Service
    ) {
        $this->trackService = $trackService;
        $this->awsService = $awsService;
        $this->fileFactory = $fileFactory;
        $this->fileService = $fileService;
        $this->s3Service = $s3Service;
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

    /**
     * @Route("/transcribe/{track}", name="api_track_transcription_job", methods={"POST"})
     * @param Track $track
     *
     * @return Response
     */
    public function transcriptionJob(Track $track): Response
    {
        $audioFile = $track->getFileByType(File::TYPE_AUDIO);
        if (!$audioFile) {
            throw new BadRequestHttpException('This track entity does not contain audio file');
        }
        $jobName = pathinfo($audioFile->getFilename(), PATHINFO_FILENAME) . time();

        try {
            $this->awsService->startTranscriptionJobForAudioFile(
                $track->getFileByType(File::TYPE_AUDIO),
                $jobName,
                $track->getFileByType(File::TYPE_VOCABULARY)
            );
            $this->awsService->processTranscriptionState($jobName);

            $transcriptionUrl = $this->s3Service->getObjectUrl(AWS::TRANSCRIPTIONS_DIR . $jobName . '.json');
            $transcriptionFile = $this->fileService->removeAwsPunctuation($transcriptionUrl, $jobName);
            $track->addFile($transcriptionFile);

            $this->trackService->update($track);
            $this->fileService->removeOldestByTrackAndType($track, File::TYPE_TRANSCRIPTION);
        } catch (Exception $e) {
            return $this->apiFailedResponse(ApiResponseController::WRONG_DATA, $e->getMessage());
        }

        return $this->apiSuccessResponse();
    }

    /**
     * @Route("/lyrics/{track}", name="api_track_lyrics", methods={"GET"})
     * @param Track $track
     *
     * @return Response
     */
    public function trackLyrics(Track $track): Response
    {
        //TODO VOTER

        $transcriptionFile = $track->getFileByType(File::TYPE_TRANSCRIPTION);
        if (!$transcriptionFile) {
            throw new BadRequestHttpException('This track does not have extracted lyrics');
        }

        $words = $this->trackService->formatWords($track);

        return $this->apiSuccessResponse(
            [
                'data' => $words,
                'url' => $track->getFileByType(File::TYPE_AUDIO)->getUrl(),
            ]
        );
    }

    /**
     * @Route("/", name="api_track_list", methods={"GET"})
     *
     * @return Response
     */
    public function list(): Response
    {
        /** @var Track $track */
        $track = $this->trackService->getOneById(11);

        $audioFile = $track->getFileByType(File::TYPE_AUDIO);

        return $this->apiSuccessResponse(ApiResponse::format([$audioFile]), ['api_track']);
    }

    /**
     * @Route("/vocabulary/{track}", name="api_track_vocabulary_fix", methods={"POST"})
     *
     * @param Track   $track
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function vocabulary(Track $track, Request $request): Response
    {
        //TODO VOTER
        try {
            $txtFilepath = $this->fileService->createTxtFile( 'VOCABULARY_FILE_' . time(), $request->get('lyrics'));
            $vocabularyFile = $this->fileService->createAndUpload($txtFilepath, File::TYPE_VOCABULARY);
            $track->addFile($vocabularyFile);
            $this->awsService->createVocabulary($vocabularyFile);
            $this->trackService->update($track);

            $this->fileService->removeOldestByTrackAndType($track, File::TYPE_VOCABULARY);
        } catch (Exception $e) {
            return $this->apiFailedResponse(ApiResponseController::WRONG_DATA, $e->getMessage());
        }

        return $this->apiSuccessResponse();
    }
}
