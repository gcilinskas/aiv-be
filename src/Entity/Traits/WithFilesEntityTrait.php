<?php

namespace App\Entity\Traits;

use App\Entity\File;
use Doctrine\Common\Collections\ArrayCollection;

trait WithFilesEntityTrait
{
    /**
     * @var File[]|ArrayCollection
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="track", cascade={"persist"})
     */
    private $files;

    /**
     * @return File[]|ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param string $type
     *
     * @return File|null
     */
    public function getFileByType(string $type): ?File
    {
        foreach ($this->getFiles() as $file) {
            if ($file->getType() === $type) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @return File|null
     */
    public function getVideoFile(): ?File
    {
        return $this->getFileByType(File::TYPE_VIDEO);
    }

    /**
     * @return File|null
     */
    public function getAudioFile(): ?File
    {
        return $this->getFileByType(File::TYPE_AUDIO);
    }

    /**
     * @return File|null
     */
    public function getTranscriptionFile(): ?File
    {
        return $this->getFileByType(File::TYPE_TRANSCRIPTION);
    }

    /**
     * @return File|null
     */
    public function getVocabularyFile(): ?File
    {
        return $this->getFileByType(File::TYPE_VOCABULARY);
    }
}
