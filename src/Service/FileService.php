<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\Track;
use App\Entity\Video;
use App\Factory\FileFactory;
use App\Repository\FileRepository;
use App\Util\AWS;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class FileService
 */
class FileService extends BaseService
{
    /**
     * @var FileRepository
     */
    protected $repository;

    /**
     * @var string
     */
    private $serviceAccountDir;

    /**
     * @var string
     */
    private $tempUploadDir;

    /**
     * @var SluggerInterface
     */
    private $slugger;

    /**
     * @var GoogleCloudService
     */
    private $googleCloudService;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var S3Service
     */
    private $s3Service;

    /**
     * @var string
     */
    private $audioDir;

    /**
     * FileService constructor.
     *
     * @param EntityManagerInterface   $em
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $serviceAccountDir
     * @param string                   $tempUploadDir
     * @param SluggerInterface         $slugger
     * @param GoogleCloudService       $googleCloudService
     * @param FileFactory              $fileFactory
     * @param S3Service                $s3Service
     * @param string                   $audioDir
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        string $serviceAccountDir,
        string $tempUploadDir,
        SluggerInterface $slugger,
        GoogleCloudService $googleCloudService,
        FileFactory $fileFactory,
        S3Service $s3Service,
        string $audioDir
    ) {
        parent::__construct($em, $dispatcher);
        $this->serviceAccountDir = $serviceAccountDir;
        $this->tempUploadDir = $tempUploadDir;
        $this->slugger = $slugger;
        $this->googleCloudService = $googleCloudService;
        $this->fileFactory = $fileFactory;
        $this->s3Service = $s3Service;
        $this->audioDir = $audioDir;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return File::class;
    }

    public function convertToFlac(string $filepath, string $filename)
    {
        echo shell_exec('ffmpeg -i music.mp3 -ac 1 test1.flac');
    }

    /**
     * @param string $jsonFileUrl
     * @param string $jobName
     *
     * @return mixed
     * @throws Exception
     */
    public function removeAwsPunctuation(string $jsonFileUrl, string $jobName): File
    {
        // open json file
        $json = file_get_contents($jsonFileUrl);
        $jsonFile = json_decode($json);
        $words = $jsonFile->results->items;

        // remove punctuation items
        foreach ($words as $index => $item) {
            if ($item->type === 'punctuation') {
                unset($jsonFile->results->items[$index]);
            }
        }

        // create new transcription file
        $tmpPath = $this->saveTmpDecodedFile($jobName . '-exclude_punctuation.json', $jsonFile);

        return $this->createAndUpload($tmpPath, File::TYPE_TRANSCRIPTION);
    }

    /**
     * @param string $filename
     * @param        $valueToEncode
     *
     * @return string
     */
    public function saveTmpDecodedFile(string $filename, $valueToEncode): string
    {
        $tmpPath = $this->tempUploadDir . '/' . $filename;
        $fp = fopen($tmpPath, 'w');
        fwrite($fp, json_encode($valueToEncode));
        fclose($fp);

        return $tmpPath;
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return string
     */
    public function saveTmpUploadedFile(UploadedFile $uploadedFile): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_EXTENSION);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $ext;
        $tmpPath = $this->tempUploadDir . '/' . $fileName;
        $uploadedFile->move($this->tempUploadDir, $fileName);

        return $tmpPath;
    }

    /**
     * @param string $filename
     * @param string $text
     *
     * @return string
     */
    public function createTxtFile(string $filename, string $text): string
    {
        $fileLocation = $this->tempUploadDir . '/' . $filename . '.txt' ;
        $file = fopen($fileLocation,"w");
        $arrayWords = preg_split('/[\s]+/', $text);
        foreach ($arrayWords as $index => $word) {
            $word = preg_replace("/[^a-zA-Z0-9]+/", "", $word);
            if (strlen($word) > 0) {
                $separator = $index + 1 === count($arrayWords) ? '' : ' ';
                fwrite($file, $word . $separator );
            }
        }
        fclose($file);

        return $fileLocation;
    }

    /**
     * @param File $file
     *
     * @throws Exception
     */
    public function delete(File $file)
    {
        $this->s3Service->delete($file->getAwsKey());
        $this->remove($file);
    }

    /**
     * @param string $url
     *
     * @return array
     */
    public function splitTextToArrayFromFileUrl(string $url): array
    {
        $data = file($url);

        return explode(' ', $data[0]);
    }

    /**
     * @param Track  $track
     * @param string $type
     *
     * @throws Exception
     */
    public function removeOldestByTrackAndType(Track $track, string $type)
    {
        $files = $this->repository->findBy(['type' => $type, 'track' => $track], ['createdAt' => 'DESC']);

        foreach ($files as $index => $file) {
            if ($index === 0) {
                continue;
            }

            $this->delete($file);
        }
    }

    /**
     * @param File $videoFile
     *
     * @return File
     * @throws Exception
     */
    public function createAudioFromVideo(File $videoFile): File
    {
        $videoUrl = str_replace('https://', 'http://', $videoFile->getUrl());
        $explodedVideoUrl = explode("/", $videoUrl);
        $videoAudioFilename = pathinfo(end($explodedVideoUrl), PATHINFO_FILENAME) . '-Audio_' . time() . '.mp3';

        $process = Process::fromShellCommandline('ffmpeg -i "$videoUrl" "$videoAudioFilename"');
        $process->run(null, [
            'videoUrl' => $videoUrl,
            'videoAudioFilename' => $this->audioDir . $videoAudioFilename
        ]);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this->createAndUpload($this->audioDir . $videoAudioFilename, File::TYPE_AUDIO);
    }

    /**
     * @param string $path
     * @param string $type
     *
     * @return File
     * @throws Exception
     */
    public function createAndUpload(string $path, string $type): File
    {
        $file = $this->fileFactory->createFile($path, $type);
        $awsResult = $this->s3Service->uploadByType($file);
        $file = $this->setAwsDataToFile($file, $awsResult);

        return $file;
    }

    /**
     * @param File   $file
     * @param Result $awsResult
     * @param bool   $flush
     *
     * @return File
     * @throws Exception
     */
    public function setAwsDataToFile(File $file, Result $awsResult, bool $flush = true): File
    {
        $url = $awsResult['ObjectURL'] ? $awsResult['ObjectURL'] : $awsResult['@metadata']['effectiveUri'];

        if (!$url) {
            throw new Exception('No URL found for AWS File with File ID: ' . $file->getId());
        }

        $file->setUrl($url)
            ->setUri(AWS::formatS3Uri($file))
            ->setAwsKey(AWS::formatFolderWithFilename($file));

        return $this->update($file, $flush);
    }
}
