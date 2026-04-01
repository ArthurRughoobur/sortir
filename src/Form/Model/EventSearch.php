<?php

namespace App\Form\Model;

use App\Entity\Campus;
use App\Entity\Category;

class EventSearch
{
    private ?Campus $campus = null;
    private ?Category $category = null;
    private ?string $name = null;
    private ?\DateTimeInterface $dateStart = null;
    private ?\DateTimeInterface $deadline = null;

    private ?bool $organizer = false;
    private ?bool $registered = false;
    private ?bool $notRegistered = false;

    private ?bool $terminee = false;
    private ?bool $enCreation = false;

    public function getEnCreation(): ?bool
    {
        return $this->enCreation;
    }

    public function setEnCreation(?bool $enCreation): void
    {
        $this->enCreation = $enCreation;
    }
    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getOrganizer(): ?bool
    {
        return $this->organizer;
    }

    public function setOrganizer(?bool $organizer): self
    {
        $this->organizer = $organizer;
        return $this;
    }

    public function getRegistered(): ?bool
    {
        return $this->registered;
    }

    public function setRegistered(?bool $registered): self
    {
        $this->registered = $registered;
        return $this;
    }

    public function getNotRegistered(): ?bool
    {
        return $this->notRegistered;
    }

    public function setNotRegistered(?bool $notRegistered): self
    {
        $this->notRegistered = $notRegistered;
        return $this;
    }
    public function getTerminee(): ?bool
    {
        return $this->terminee;
    }

    public function setTerminee(?bool $terminee): self
    {
        $this->terminee = $terminee;
        return $this;
    }


}
