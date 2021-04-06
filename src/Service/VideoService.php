<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\Video;
use App\Factory\FileFactory;
use App\Repository\VideoRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class VideoService
 */
class VideoService extends BaseService
{
    /**
     * @var VideoRepository
     */
    protected $repository;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * VideoService constructor.
     *
     * @param EntityManagerInterface   $em
     * @param EventDispatcherInterface $dispatcher
     * @param FileFactory              $fileFactory
     * @param FileService              $fileService
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        FileFactory $fileFactory,
        FileService $fileService
    ) {
        parent::__construct($em, $dispatcher);
        $this->fileFactory = $fileFactory;
        $this->fileService = $fileService;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return Video::class;
    }

    /**
     * @param Video        $video
     * @param UploadedFile $videoFile
     *
     * @return Video
     * @throws Exception
     */
    public function createWithFile(Video $video, UploadedFile $videoFile): Video
    {
        $tmpPath = $this->fileService->saveTmpUploadedFile($videoFile);
        $file = $this->fileService->createAndUpload($tmpPath, File::TYPE_VIDEO);

        return $this->update($video->addFile($file));
    }

    /**
     * @param Video $entity
     * @param bool  $flush
     *
     * @return Video
     * @throws Exception
     */
    public function update($entity, bool $flush = true): Video
    {
        $entity->setUpdatedAt(new DateTime());

        return parent::update($entity, $flush);
    }
}
