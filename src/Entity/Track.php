<?php

namespace App\Entity;

use App\Entity\Traits\TimeEntityTrait;
use App\Repository\TrackRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrackRepository::class)
 */
class Track
{
    use TimeEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $artist;

    /**
     * @ORM\OneToMany(targetEntity=Word::class, mappedBy="track")
     */
    private $words;

    /**
     * @ORM\OneToOne(targetEntity=File::class, mappedBy="track", cascade={"persist", "remove"})
     */
    private $file;

    /**
     * Track constructor.
     */
    public function __construct()
    {
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
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @return $this
     */
    public function setFile(File $file): self
    {
        // set the owning side of the relation if necessary
        if ($file->getTrack() !== $this) {
            $file->setTrack($this);
        }

        $this->file = $file;

        return $this;
    }


}
