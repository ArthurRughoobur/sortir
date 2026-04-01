<?php

namespace App\Entity;

use App\Repository\EventRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Merci de saisir un non d'évènement ! ")]
    #[Assert\Length(max: 255, maxMessage: "Max {{ limit }} characters !")]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\GreaterThan(propertyPath: "deadline", message: "La date de début ne peut être avant la date de fin d'inscription ! ")]
    #[Assert\GreaterThanOrEqual('today')]
    private ?DateTime $dateStart = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Merci de renseigner une durée d'évènement ! ")]
    #[Assert\GreaterThan(value: 0, message: "Votre durée de l'évènement doit être supérieur à 0 !")]

    private ?int $duration = null;

    #[ORM\Column]
    #[Assert\LessThan(propertyPath: "dateStart", message: "La date de fin d'inscription doit être antérieur à la du début de l'évènement ! ")]
    private ?DateTime $deadline = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Merci de saisir un nombre d'inscrits max !")]
    #[Assert\GreaterThan(value: 1, message: "Votre nombre d'inscription doit être supérieur à 1 !")]
    private ?int $maxIscription = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Merci de donner un peu d'informations ! ")]
    private ?string $eventInfo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
//    #[Assert\NotBlank (message : "Merci de donner un peu d'informations sur votre annulation event ! ")]

    private ?string $canceledInfo = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'eventList')]
    private Collection $registred;

    #[ORM\ManyToOne(inversedBy: 'organizer')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $organizer = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Adress $adress = null;

    public function __construct()
    {
        $this->registred = new ArrayCollection();
    }

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

    public function getDateStart(): ?DateTime
    {
        return $this->dateStart;
    }

    public function setDateStart(?DateTime $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getDeadline(): ?DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(?DateTime $deadline): static
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getRegistred(): Collection
    {
        return $this->registred;
    }

    public function addRegistred(User $registred): static
    {
        if (!$this->registred->contains($registred)) {
            $this->registred->add($registred);
        }

        return $this;
    }

    public function removeRegistred(User $registred): static
    {
        $this->registred->removeElement($registred);

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getAdress(): ?Adress
    {
        return $this->adress;
    }

    public function setAdress(?Adress $adress): static
    {
        $this->adress = $adress;

        return $this;
    }
    public function getDurationAsHoursAndMinutes(): string
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        return sprintf('%dh%02d', $hours, $minutes);
    }
}
