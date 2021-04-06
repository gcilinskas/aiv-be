<?php

namespace App\Entity;

use App\Entity\Traits\TimeEntityTrait;
use App\Entity\Traits\WithFilesEntityTrait;
use App\Repository\TrackRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TrackRepository::class)
 */
class Track
{
    use TimeEntityTrait, WithFilesEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("api_track")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("api_track")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("api_track")
     */
    private $artist;

    /**
     * @ORM\OneToMany(targetEntity=Word::class, mappedBy="track")
     */
    private $words;

    /**
     * @var File[]|ArrayCollection
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="track", cascade={"persist"})
     */
    private $files;

    /**
     * Track constructor.
     */
    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->words = new ArrayCollection();
        $this->updatedAt = new DateTime();
        $this->createdAt = new DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     *
     * @return $this
     */
    public function setTitle(?string $title): Track
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getArtist(): ?string
    {
        return $this->artist;
    }

    /**
     * @param string|null $artist
     *
     * @return $this
     */
    public function setArtist(?string $artist): Track
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return Collection|Word[]
     */
    public function getWords(): Collection
    {
        return $this->words;
    }

    /**
     * @param Word $word
     *
     * @return $this
     */
    public function addWord(Word $word): self
    {
        if (!$this->words->contains($word)) {
            $this->words[] = $word;
            $word->setTrack($this);
        }

        return $this;
    }

    /**
     * @param Word $word
     *
     * @return $this
     */
    public function removeWord(Word $word): self
    {
        if ($this->words->removeElement($word)) {
            if ($word->getTrack() === $this) {
                $word->setTrack(null);
            }
        }

        return $this;
    }

    /**
     * @return File[]|ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param File[]|ArrayCollection $files
     *
     * @return Track
     */
    public function setFiles($files): Track
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @param File $file
     *
     * @return $this
     */
    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setTrack($this);
        }

        return $this;
    }

    /**
     * @param File $file
     *
     * @return $this
     */
    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            if ($file->getTrack() === $this) {
                $file->setTrack(null);
            }
        }

        return $this;
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
}
