<?php

namespace App\Service;

use App\Entity\File;
use App\Util\AWS;
use Aws\Result;
use Aws\S3\S3Client;
use Exception;
use LogicException;

/**
 * Class s3Service
 */
class S3Service
{
    /**
     * @var S3Client
     */
    private $client;

    /**
     * @var array
     */
    private $awsConfig;

    /**
     * s3Service constructor.
     *
     * @param array $awsConfig
     */
    public function __construct(array $awsConfig)
    {
        $this->awsConfig = $awsConfig;
        $this->client = new S3Client($this->awsConfig);
    }

    /**
     * @param File $file
     *
     * @return Result
     * @throws Exception
     */
    public function uploadByType(File $file): Result
    {
        if (!$file->getType()) {
            throw new \Exception('File type is not defined');
        }

        switch ($file->getType()) {
            case File::TYPE_AUDIO:
                return $this->upload(AWS::AUDIO_DIR . $file->getFilename(), $file->getTmpPath());
            case File::TYPE_TRANSCRIPTION:
                return $this->upload(AWS::TRANSCRIPTIONS_DIR . $file->getFilename(), $file->getTmpPath());
            case File::TYPE_VOCABULARY:
                return $this->upload(AWS::VOCABULARY_DIR . $file->getFilename(), $file->getTmpPath());
            case File::TYPE_VIDEO:
                return $this->upload(AWS::VIDEO_DIR . $file->getFilename(), $file->getTmpPath());
        }

        throw new LogicException('Type ' . $file->getType() . ' is not defined');
    }

    /**
     * @param File $file
     *
     * @return Result
     */
    public function uploadTrack(File $file): Result
    {
        return $this->upload(AWS::tracksPath($file->getFilename()), $file->getTmpPath());
    }

    /**
     * @param File $file
     *
     * @return Result
     */
    public function uploadTranscription(File $file): Result
    {
        return $this->upload(AWS::tracksPath($file->getFilename()), $file->getTmpPath());
    }

    /**
     * @param File $file
     *
     * @return Result
     */
    public function uploadVocabulary(File $file): Result
    {
        return $this->upload(AWS::vocabularyPath($file->getFilename()), $file->getTmpPath());
    }

    /**
     * @param string $filename
     * @param string $filepath
     *
     * @return Result
     */
    public function upload(string $filename, string $filepath): Result
    {
        return $this->client->upload($this->awsConfig['bucket'], $filename, fopen($filepath, 'r'), 'public-read');
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getObjectUrl(string $key): string
    {
        return $this->client->getObjectUrl($this->awsConfig['bucket'], $key);
    }

    /**
     * @param string $key
     *
     * @return Result
     */
    public function getObject(string $key): Result
    {
        return $this->client->getObject([
            'Bucket' => $this->awsConfig['bucket'],
            'Key' => $key
        ]);
    }

    /**
     * @param string $key
     */
    public function delete(string $key)
    {
        if ($this->client->doesObjectExist($this->awsConfig['bucket'], $key)) {
            $this->client->deleteObject([
                'Bucket' => $this->awsConfig['bucket'],
                'Key' => $key,
            ]);
        }
    }
}
