<?php

namespace App\Entity;

use App\Entity\Traits\TimeEntityTrait;
use App\Repository\FileRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use \Symfony\Component\HttpFoundation\File\File as HttpFile;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
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
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uri;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sampleRateHertz;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $encodingType;

    /**
     * @var HttpFile|null
     */
    private $file;

    /**
     * @ORM\OneToOne(targetEntity=Track::class, inversedBy="file", cascade={"persist", "remove"})
     */
    private $track;

    /**
     * @var string|null
     */
    private $tmpPath;

    /**
     * File constructor.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
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
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     *
     * @return $this
     */
    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     *
     * @return $this
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * @param string|null $uri
     *
     * @return $this
     */
    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSampleRateHertz(): ?int
    {
        return $this->sampleRateHertz;
    }

    /**
     * @param int|null $sampleRateHertz
     *
     * @return $this
     */
    public function setSampleRateHertz(?int $sampleRateHertz): self
    {
        $this->sampleRateHertz = $sampleRateHertz;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEncodingType(): ?int
    {
        return $this->encodingType;
    }

    /**
     * @param int|null $encodingType
     *
     * @return $this
     */
    public function setEncodingType(?int $encodingType): self
    {
        $this->encodingType = $encodingType;

        return $this;
    }

    /**
     * @param HttpFile|null $file
     *
     * @return $this
     */
    public function setFile(?HttpFile $file = null): Track
    {
        $this->file = $file;
        if ($file) {
            $this->updatedAt = new DateTime();
        }

        return $this;
    }

    /**
     * @return HttpFile|null
     */
    public function getFile(): ?HttpFile
    {
        return $this->file;
    }

    /**
     * @return Track|null
     */
    public function getTrack(): ?Track
    {
        return $this->track;
    }

    /**
     * @param Track|null $track
     *
     * @return $this
     */
    public function setTrack(?Track $track): self
    {
        $this->track = $track;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTmpPath(): ?string
    {
        return $this->tmpPath;
    }

    /**
     * @param string|null $tmpPath
     *
     * @return File
     */
    public function setTmpPath(?string $tmpPath): File
    {
        $this->tmpPath = $tmpPath;

        return $this;
    }
}
