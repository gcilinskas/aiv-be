<?php

namespace App\Entity;

use App\Entity\Traits\TimeEntityTrait;
use App\Entity\Traits\WithFilesEntityTrait;
use App\Repository\VideoRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VideoRepository::class)
 */
class Video
{
    use TimeEntityTrait, WithFilesEntityTrait;

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
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="video")
     */
    private $files;

    /**
     * Video constructor.
     */
    public function __construct()
    {
        $this->files = new ArrayCollection();
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
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
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
            $file->setVideo($this);
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
            // set the owning side to null (unless already changed)
            if ($file->getVideo() === $this) {
                $file->setVideo(null);
            }
        }

        return $this;
    }
}
