<?php

namespace App\Entity;

use App\Entity\Traits\TimeEntityTrait;
use App\Repository\FileRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use \Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
{
    use TimeEntityTrait;

    const TYPE_AUDIO = 'TYPE_AUDIO';
    const TYPE_VIDEO = 'TYPE_VIDEO';
    const TYPE_TRANSCRIPTION = 'TYPE_TRANSCRIPTION';
    const TYPE_VOCABULARY = 'TYPE_VOCABULARY';

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
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("api_track")
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
     * @var Track|null
     * @ORM\ManyToOne(targetEntity=Track::class, inversedBy="file")
     */
    private $track;

    /**
     * @var string|null
     */
    private $tmpPath;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $awsKey;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $extension;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jobName;

    /**
     * @var Video|null
     * @ORM\ManyToOne(targetEntity=Video::class, inversedBy="files")
     */
    private $video;

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

    /**
     * @return string|null
     */
    public function getAwsKey(): ?string
    {
        return $this->awsKey;
    }

    /**
     * @param string|null $awsKey
     *
     * @return File
     */
    public function setAwsKey(?string $awsKey): File
    {
        $this->awsKey = $awsKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @param string|null $extension
     *
     * @return File
     */
    public function setExtension(?string $extension): File
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     *
     * @return File
     */
    public function setType(?string $type): File
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getJobName(): ?string
    {
        return $this->jobName;
    }

    /**
     * @param string|null $jobName
     *
     * @return File
     */
    public function setJobName(?string $jobName): File
    {
        $this->jobName = $jobName;

        return $this;
    }

    /**
     * @return Video|null
     */
    public function getVideo(): ?Video
    {
        return $this->video;
    }

    /**
     * @param Video|null $video
     *
     * @return $this
     */
    public function setVideo(?Video $video): self
    {
        $this->video = $video;

        return $this;
    }
}
