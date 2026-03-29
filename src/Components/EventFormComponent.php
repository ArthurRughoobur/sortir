<?php

namespace App\Components;

use AllowDynamicProperties;
use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AllowDynamicProperties]
#[AsLiveComponent('event_form')]
final class EventFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public ?Event $initialFormData = null;

    #[LiveProp(fieldName: 'eventData')]
    public ?Event $event = null;

    #[LiveProp]
    public ?string $street = null;

    #[LiveProp]
    public ?int $id = null;
    #[LiveProp]
    public ?string $city = null;

    #[LiveProp]
    public ?float $latitude = null;

    #[LiveProp]
    public ?float $longitude = null;
    #[LiveProp(writable: true)]
    public ?string $cancelReason = null;

    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface   $formFactory,
        private StatusRepository       $statusRepository,
        private Security               $security,
        private RequestStack           $requestStack,
        private UserRepository         $userRepository,
        private EventRepository        $eventRepository,
    )
    {
    }

    protected function instantiateForm(): FormInterface
    {

        $event = $this->initialFormData ?? new Event();

        $this->city = $event->getAdress()?->getCity()->getName();
        $this->street = $event->getAdress()?->getStreet();
        $this->latitude = $event->getAdress()?->getLatitude();
        $this->longitude = $event->getAdress()?->getLongitude();
        return $this->formFactory->create(EventType::class, $event);
    }


    #[PreReRender(priority: -10)]
    public function updateStreetAfterAutoSubmit(): void
    {
        $event = $this->getForm()->getData();
        $this->city = $event->getAdress()?->getCity()->getName();
        $this->street = $event->getAdress()?->getStreet();
        $this->latitude = $event->getAdress()?->getLatitude();
        $this->longitude = $event->getAdress()?->getLongitude();

    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        $event = $this->getForm()->getData();
        $user = $this->security->getUser();
        if ($user) {
            $event->setCampus($user->getCampus());
            $event->setOrganizer($user);
        }

        $event->setStatus($this->statusRepository->findOneBy(['name' => 'En création']));

        $this->submitForm();

        if ($this->id !== null) {
            $event = $this->eventRepository->find($this->id);
            $allowedStatuses = ['En création', 'Ouverte'];
            if ($event && !in_array($event->getStatus()->getName(), $allowedStatuses)) {
                $this->addFlash("error", "Vous ne pouvez pas Sauvegarder un événement avec le statut : " . $event->getStatus()->getName());
                return $this->redirectToRoute('main_event');
            }
        }

        $this->em->persist($event);
        $this->em->flush();

        $this->addFlash('success', 'Événement sauvegardé !');


        return $this->redirectToRoute('main_event');
    }

    #[LiveAction]
    public function publish(): RedirectResponse
    {
        $event = $this->getForm()->getData();
        $user = $this->security->getUser();
        if ($user) {
            $event->setCampus($user->getCampus());
            $event->setOrganizer($user);
            $event->addRegistred($user);
        }
        $event->setStatus($this->statusRepository->findOneBy(['name' => 'Ouverte']));

        $this->submitForm();

        if ($this->id !== null) {
            $event = $this->eventRepository->find($this->id);
            $allowedStatuses = ['En création', 'Ouverte'];
            if ($event && !in_array($event->getStatus()->getName(), $allowedStatuses)) {
                $this->addFlash("error", "Vous ne pouvez pas publier  un événement avec le statut : " . $event->getStatus()->getName());
                return $this->redirectToRoute('main_event');
            }
        }
        $this->em->persist($event);
        $this->em->flush();
        $this->addFlash('success', 'Événement publié !');


        return $this->redirectToRoute('main_event');
    }

    #[LiveAction]
    public function cancel(): RedirectResponse
    {
        //Vérifie qu'il y a un motif d'annul
        if (empty($this->cancelReason)) {
            $this->addFlash("error", "Merci de donner un peu d'informations ! ");
            if ($this->id !== null) {
                return $this->redirectToRoute('update_event', ['id' => $this->id]);
            } else {
                return $this->redirectToRoute('main_event');
            }
        }

        $event = $this->getForm()->getData();
        if (!$event) {
            $this->addFlash("error", "L'événement n'existe pas.");
            return $this->redirectToRoute('main_event');
        }
        $allowedStatus = ['En création', 'Ouverte'];
        if (!in_array($event->getStatus()->getName(), $allowedStatus)) {
            $this->addFlash("error", "Vous ne pouvez pas annuler un événement avec le statut : " . $event->getStatus()->getName());
            return $this->redirectToRoute('main_event');
        }
        $status = $this->statusRepository->findOneBy(['name' => 'Annulée']);
        $event->setStatus($status);
        $event->setCanceledInfo($this->cancelReason);
        $this->em->persist($event);
        $this->em->flush();

        $this->addFlash('success', 'Événement annulé !');
        return $this->redirectToRoute('main_event');

    }

}
