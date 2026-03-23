<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $dateStart = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column]
    private ?\DateTime $deadline = null;

    #[ORM\Column]
    private ?int $maxIscription = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $eventInfo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $canceledInfo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDateStart(): ?\DateTime
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTime $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTime $deadline): static
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getMaxIscription(): ?int
    {
        return $this->maxIscription;
    }

    public function setMaxIscription(int $maxIscription): static
    {
        $this->maxIscription = $maxIscription;

        return $this;
    }

    public function getEventInfo(): ?string
    {
        return $this->eventInfo;
    }

    public function setEventInfo(string $eventInfo): static
    {
        $this->eventInfo = $eventInfo;

        return $this;
    }

    public function getCanceledInfo(): ?string
    {
        return $this->canceledInfo;
    }

    public function setCanceledInfo(?string $canceledInfo): static
    {
        $this->canceledInfo = $canceledInfo;

        return $this;
    }
}
