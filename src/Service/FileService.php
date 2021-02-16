<?php

namespace App\Service;

use App\Entity\File;
use App\Factory\FileFactory;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class FileService
 */
class FileService extends BaseService
{
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
     * FileService constructor.
     *
     * @param EntityManagerInterface   $em
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $serviceAccountDir
     * @param string                   $tempUploadDir
     * @param SluggerInterface         $slugger
     * @param GoogleCloudService       $googleCloudService
     * @param FileFactory              $fileFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        string $serviceAccountDir,
        string $tempUploadDir,
        SluggerInterface $slugger,
        GoogleCloudService $googleCloudService,
        FileFactory $fileFactory
    ) {
        parent::__construct($em, $dispatcher);
        $this->serviceAccountDir = $serviceAccountDir;
        $this->tempUploadDir = $tempUploadDir;
        $this->slugger = $slugger;
        $this->googleCloudService = $googleCloudService;
        $this->fileFactory = $fileFactory;
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
}
