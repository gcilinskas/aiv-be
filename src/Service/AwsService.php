<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\Track;
use Aws\Result;
use Aws\TranscribeService\TranscribeServiceClient;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AwsService
 */
class AwsService
{
    /**
     * @var TranscribeServiceClient
     */
    private $transcribeServiceClient;

    /**
     * @var array
     */
    private $awsConfig;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * AwsService constructor.
     *
     * @param array       $awsConfig
     * @param FileService $fileService
     */
    public function __construct(array $awsConfig, FileService $fileService)
    {
        $this->awsConfig = $awsConfig;
        $this->transcribeServiceClient = new TranscribeServiceClient($this->awsConfig);
        $this->fileService = $fileService;
    }

    /**
     * @param File      $audioFile
     * @param string    $jobName
     * @param File|null $vocabularyFile
     *
     * @return Result
     */
    public function startTranscriptionJobForAudioFile(File $audioFile, string $jobName, ?File $vocabularyFile = null): Result
    {
        if (!$vocabularyFile) {
            return $this->transcribeServiceClient->startTranscriptionJob(
                [
                    'LanguageCode' => 'en-US',
                    'Media' => [
                        'MediaFileUri' => $audioFile->getUri(),
                    ],
                    'MediaSampleRateHertz' => 44100,
                    'MediaFormat' => 'mp3',
                    'OutputBucketName' => $this->awsConfig['bucket'],
                    'TranscriptionJobName' => $jobName,
                    'OutputKey' => 'transcriptions/' . $jobName . '.json',
                ]
            );
        }

        return $this->transcribeServiceClient->startTranscriptionJob(
            [
                'LanguageCode' => 'en-US',
                'Media' => [
                    'MediaFileUri' => $audioFile->getUri(),
                ],
                'MediaSampleRateHertz' => 44100,
                'MediaFormat' => 'mp3',
                'OutputBucketName' => $this->awsConfig['bucket'],
                'TranscriptionJobName' => $jobName,
                'OutputKey' => 'transcriptions/' . $jobName . '.json',
                'Settings' => [
                    'VocabularyName' => $vocabularyFile->getJobName()
                ]
            ]
        );
    }

    /**
     * @param string $jobName
     *
     * @return Result
     */
    public function processTranscriptionState(string $jobName): Result
    {
        while(true) {
            $awsResult = $this->getTranscriptionJob($jobName);

            if ($awsResult->get('TranscriptionJob')['TranscriptionJobStatus'] == 'COMPLETED') {
                break;
            }

            if ($awsResult->get('TranscriptionJob')['TranscriptionJobStatus'] == 'FAILED') {
                throw new BadRequestHttpException('Track file is not valid to read');
            }

            sleep(5);
        }

        return $awsResult;
    }

    /**
     * @param string $jobName
     *
     * @return Result
     */
    public function getTranscriptionJob(string $jobName): Result
    {
        return $this->transcribeServiceClient->getTranscriptionJob(
            [
                'TranscriptionJobName' => $jobName
            ]
        );
    }

    public function getVocabularyJob(string $name): Result
    {
        return $this->transcribeServiceClient->getVocabulary(['VocabularyName' => $name]);
    }

    /**
     * @param File $vocabularyFile
     *
     * @return Result
     */
    public function createVocabulary(File $vocabularyFile)
    {
        $vocabularyName = 'Vocabulary_' . time();
        $phrases = $this->fileService->splitTextToArrayFromFileUrl($vocabularyFile->getUrl());

        $this->transcribeServiceClient->createVocabulary([
            'LanguageCode' => 'en-US',
            'Phrases' => $phrases,
//            'VocabularyFileUri' => $vocabularyFile->getUri(),
            'VocabularyName' => $vocabularyName,
        ]);

        while (true) {
            $result = $this->getVocabularyJob($vocabularyName);
            switch ($result->get('VocabularyState')) {
                case 'READY':
                    $vocabularyFile->setJobName($vocabularyName);
                    return $result;
                case 'PENDING':
                    sleep(5);
                    continue;
                case 'FAILED':
                    throw new \Exception('Failed to generate vocabulary file');
            }
        }
    }
}
