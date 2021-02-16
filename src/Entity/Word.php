<?php

namespace App\Entity;

use App\Entity\Traits\TimeEntityTrait;
use App\Repository\WordRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=WordRepository::class)
 */
class Word
{
    use TimeEntityTrait;

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("api_word")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("api_word")
     */
    private $word;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     * @Groups("api_word")
     */
    private $startTime;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     * @Groups("api_word")
     */
    private $endTime;

    /**
     * @var Track|null
     * @ORM\ManyToOne(targetEntity=Track::class, inversedBy="words")
     */
    private $track;

    /**
     * Word constructor.
     */
    public function __construct()
    {
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
    public function getWord(): ?string
    {
        return $this->word;
    }

    /**
     * @param string|null $word
     *
     * @return $this
     */
    public function setWord(?string $word): self
    {
        $this->word = $word;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getStartTime(): ?float
    {
        return $this->startTime;
    }

    /**
     * @param float|null $startTime
     *
     * @return Word
     */
    public function setStartTime(?float $startTime): Word
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getEndTime(): ?float
    {
        return $this->endTime;
    }

    /**
     * @param float|null $endTime
     *
     * @return Word
     */
    public function setEndTime(?float $endTime): Word
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * @return Track|null
     */
    public function getTrack(): ?Track
    {
        return $this->track;
    }

    /**
     * @param Track $track
     *
     * @return $this
     */
    public function setTrack(Track $track): self
    {
        $this->track = $track;

        return $this;
    }
}
