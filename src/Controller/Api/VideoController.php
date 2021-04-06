<?php

namespace App\Controller\Api;

use App\Controller\ApiResponseController;
use App\Entity\File;
use App\Entity\Video;
use App\Factory\FileFactory;
use App\Form\Video\CreateType;
use App\Service\AwsService;
use App\Service\FileService;
use App\Service\S3Service;
use App\Service\VideoService;
use App\Util\AWS;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class VideoController
 * @Route("/video")
 */
class VideoController extends ApiResponseController
{
    /**
     * @var VideoService
     */
    private $videoService;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var AwsService
     */
    private $awsService;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var S3Service
     */
    private $s3Service;

    /**
     * VideoController constructor.
     *
     * @param VideoService $videoService
     * @param FileService  $fileService
     * @param AwsService   $awsService
     * @param FileFactory  $fileFactory
     * @param S3Service    $s3Service
     */
    public function __construct(
        VideoService $videoService,
        FileService $fileService,
        AwsService $awsService,
        FileFactory $fileFactory,
        S3Service $s3Service
    ) {
        $this->videoService = $videoService;
        $this->fileService = $fileService;
        $this->awsService = $awsService;
        $this->fileFactory = $fileFactory;
        $this->s3Service = $s3Service;
    }

    /**
     * @Route("/transcription", methods={"POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function createVideoTranscription(Request $request): Response
    {
        $video = new Video();
        $form = $this->createForm(CreateType::class, $video);
        $form->submit($request->files->all() + $request->request->all());

        if ($form->isValid()) {
            try {
                // Create Separate Video And Audio File And Upload To S3
                $video = $this->videoService->createWithFile($video, $form->get('file')->getData());
                $videoAudioFile = $this->fileService->createAudioFromVideo($video->getFileByType(File::TYPE_VIDEO));

                // Create Transcriptions
                $jobName = pathinfo($videoAudioFile->getFilename(), PATHINFO_FILENAME) . time();
                $this->awsService->startTranscriptionJobForAudioFile($videoAudioFile, $jobName);
                $this->awsService->processTranscriptionState($jobName);

                // Get Generated Transcription File And Create File Entity
                $transcriptionUrl = $this->s3Service->getObjectUrl(AWS::TRANSCRIPTIONS_DIR . $jobName . '.json');
                $videoAudioTranscriptionFile = $this->fileFactory->createFile($transcriptionUrl, File::TYPE_TRANSCRIPTION);
                // Set AWS Data To File
                $awsResult = $this->s3Service->getObject(AWS::TRANSCRIPTIONS_DIR . $jobName . '.json');
                $videoAudioTranscriptionFile = $this->fileService->setAwsDataToFile($videoAudioTranscriptionFile, $awsResult);

                // Add Files To Track And Save
                $video->addFile($videoAudioTranscriptionFile);
                $video->addFile($videoAudioFile);
                $this->videoService->update($video);

                return $this->apiSuccessResponse([
                    'words' => AWS::getWordsFromAwsTranscriptionFile(
                        $videoAudioTranscriptionFile->getUrl(),
                        $videoAudioTranscriptionFile->getExtension()
                    ),
                    'videoUrl' => $video->getFileByType(File::TYPE_VIDEO)->getUrl()
                ]);

            } catch (Exception $e) {
                return $this->json($e->getMessage(), 500);
            }
        }

        return $this->apiFailedResponse($this->view($form->getViewData()));
    }

    /**
     * @Route("/transcription/{video}", methods={"GET"})
     * @param Video $video
     *
     * @return Response
     */
    public function getVideoTranscription(Video $video): Response
    {
        $transcriptionFile = $video->getTranscriptionFile();
        $videoFile = $video->getVideoFile();

        return $this->apiSuccessResponse([
            'words' => $transcriptionFile
                ? AWS::getWordsFromAwsTranscriptionFile($transcriptionFile->getUrl(), $transcriptionFile->getExtension())
                : [],
            'videoUrl' => $videoFile ? $videoFile->getUrl() : null
        ]);
    }
}
