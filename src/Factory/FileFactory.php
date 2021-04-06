<?php

namespace App\Factory;

use App\Entity\File;
use App\Entity\Track;
use App\Service\FileService;
use App\Service\S3Service;
use App\Util\AWS;
use Aws\Result;
use Exception;
use LogicException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class FileFactory
 */
class FileFactory
{
    /**
     * @var SluggerInterface
     */
    private $slugger;

    /**
     * @var string
     */
    private $tempUploadDir;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var S3Service
     */
    private $s3Service;

    /**
     * FileFactory constructor.
     *
     * @param SluggerInterface $slugger
     * @param string           $tempUploadDir
     * @param S3Service        $s3Service
     */
    public function __construct(SluggerInterface $slugger, string $tempUploadDir, S3Service $s3Service)
    {
        $this->slugger = $slugger;
        $this->tempUploadDir = $tempUploadDir;
        $this->s3Service = $s3Service;
    }

    /**
     * @param FileService $fileService
     * @required
     */
    public function setFileService(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * @param string $path
     * @param string $type
     * @param bool   $flush
     *
     * @return File
     * @throws Exception
     */
    public function createFile(string $path, string $type, bool $flush = true): File
    {
        $file = (new File())->setFilename(basename($path))
            ->setTmpPath($path)
            ->setExtension(pathinfo(basename($path), PATHINFO_EXTENSION))
            ->setType($type);

        return $this->fileService->create($file, $flush);
    }
}
