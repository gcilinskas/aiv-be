<?php

namespace App\Service;

use App\Entity\Track;
use App\Entity\Word;
use Exception;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;

/**
 * Class GoogleCloudService
 */
class GoogleCloudService
{
    /**
     * @var StorageClient
     */
    private $storageClient;

    /**
     * @var string
     */
    private $serviceAccountDir;

    /**
     * @var string
     */
    private $aivBucket;

    /**
     * @var WordService
     */
    private $wordService;

    /**
     * GoogleCloudService constructor.
     *
     * @param string      $serviceAccountDir
     * @param string      $aivBucket
     * @param WordService $wordService
     */
    public function __construct(string $serviceAccountDir, string $aivBucket, WordService $wordService)
    {
        $this->serviceAccountDir = $serviceAccountDir;
        $this->aivBucket = $aivBucket;
        $this->storageClient = new StorageClient(['keyFilePath' => $this->serviceAccountDir . $_ENV['SERVICE_ACCOUNT']]);
        $this->wordService = $wordService;
    }

    /**
     * @param string $objectName
     */
    public function deleteObject(string $objectName)
    {
        $object = $this->getBucket()->object($objectName);
        $object->delete();
    }

    /**
     * @param string $filepath
     *
     * @return StorageObject|null
     */
    public function upload(string $filepath): ?StorageObject
    {
        $file = fopen($filepath, 'r');

        return $file ? $this->getBucket()->upload($file) : null;
    }

    /**
     * @return Bucket
     */
    public function getBucket(): Bucket
    {
        return $this->storageClient->bucket($this->aivBucket);
    }

    /**
     * @param Track $track
     *
     * @return Track
     * @throws ApiException
     * @throws ValidationException
     * @throws Exception
     */
    public function extractWordsFromTrack(Track $track): Track
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->serviceAccountDir . $_ENV['SERVICE_ACCOUNT']);

        $encoding = AudioEncoding::ENCODING_UNSPECIFIED;
        $sampleRateHertz = 44100;
        $languageCode = 'en-US';

        $audio = (new RecognitionAudio())->setUri($track->getFile()->getUri());

        $config = (new RecognitionConfig())
            ->setEncoding($encoding)
            ->setSampleRateHertz($sampleRateHertz)
            ->setLanguageCode($languageCode)
            ->setEnableWordTimeOffsets(true);

        $client = new SpeechClient();

        $operation = $client->longRunningRecognize($config, $audio);
        $operation->pollUntilComplete();

        if ($operation->operationSucceeded()) {
            $response = $operation->getResult();

            foreach ($response->getResults() as $result) {
                $alternatives = $result->getAlternatives();
                $mostLikely = $alternatives[0];

                foreach ($mostLikely->getWords() as $wordInfo) {
                    $startTimeString = sprintf(
                        '%s.%s',
                        $wordInfo->getStartTime()->getSeconds(),
                        $wordInfo->getStartTime()->getNanos()
                    );
                    $endTimeString = sprintf(
                        '%s.%s',
                        $wordInfo->getEndTime()->getSeconds(),
                        $wordInfo->getEndTime()->getNanos()
                    );

                    $word = new Word();
                    $word->setWord($wordInfo->getWord())
                        ->setStartTime(floatval($startTimeString))
                        ->setEndTime(floatval($endTimeString))
                        ->setTrack($track)
                        ->setWord($wordInfo->getWord());

                    $this->wordService->create($word, false);
                }
            }
        } else {
            print_r($operation->getError());
        }

        $client->close();
        $this->wordService->flush();

        return $track;
    }
}
