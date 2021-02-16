<?php

namespace App\Service;

use App\Entity\Track;
use App\Factory\FileFactory;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class TrackService
 */
class TrackService extends BaseService
{
    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var GoogleCloudService
     */
    private $googleCloudService;

    /**
     * @var string
     */
    private $googleCloudStorageUrl;

    /**
     * TrackService constructor.
     *
     * @param EntityManagerInterface   $em
     * @param EventDispatcherInterface $dispatcher
     * @param FileService              $fileService
     * @param FileFactory              $fileFactory
     * @param GoogleCloudService       $googleCloudService
     * @param string                   $googleCloudStorageUrl
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        FileService $fileService,
        FileFactory $fileFactory,
        GoogleCloudService $googleCloudService,
        string $googleCloudStorageUrl
    ) {
        parent::__construct($em, $dispatcher);
        $this->fileService = $fileService;
        $this->fileFactory = $fileFactory;
        $this->googleCloudService = $googleCloudService;
        $this->googleCloudStorageUrl = $googleCloudStorageUrl;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return Track::class;
    }

    /**
     * @param Track        $track
     * @param UploadedFile $audioFile
     *
     * @return Track
     * @throws Exception
     */
    public function createWithFile(Track $track, UploadedFile $audioFile): Track
    {
        $file = $this->fileFactory->create($audioFile);
        $storageObject = $this->googleCloudService->upload($file->getTmpPath());

        $url = $this->googleCloudStorageUrl . $this->googleCloudService->getBucket()->name(). '/' . $file->getFilename();
        $file->setUri($storageObject->gcsUri())->setUrl($url);
        $track->setFile($file);

        $this->fileService->update($file);

        return $this->create($track);
    }
}
