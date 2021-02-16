<?php

namespace App\Factory;

use App\Entity\File;
use App\Service\FileService;
use Exception;
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
     * FileFactory constructor.
     *
     * @param SluggerInterface $slugger
     * @param string           $tempUploadDir
     */
    public function __construct(SluggerInterface $slugger, string $tempUploadDir)
    {
        $this->slugger = $slugger;
        $this->tempUploadDir = $tempUploadDir;
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
     * @param UploadedFile $uploadedFile
     *
     * @return File
     * @throws Exception
     */
    public function create(UploadedFile $uploadedFile): File
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_EXTENSION);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $ext;
        $tmpPath = $this->tempUploadDir . '/' . $fileName;
        $uploadedFile->move($this->tempUploadDir, $fileName);

        $file = new File();
        $file->setFilename($fileName)->setTmpPath($tmpPath);

        return $this->fileService->create($file);
    }
}
