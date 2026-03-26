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

    #[LiveProp]
    public ?string $street = null;

    #[LiveProp]
    public ?int $id = null;
    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface   $formFactory,
        private StatusRepository       $statusRepository,
        private Security               $security,
        private RequestStack           $requestStack,
        private UserRepository         $userRepository,
        private EventRepository         $eventRepository,
    )
    {
    }

    protected function instantiateForm(): FormInterface
    {

        $event = $this->initialFormData ?? new Event();


        if ($this->id !== null) {
            $event = $this->eventRepository->find($this->id);
            if($event->getUser() != $this->getUser()) {
                throw $this->createAccessDeniedException("Ce n'est pas ton évènement");
            }
                }
        $this->city = $event->getAdress()?->getCity()->getName();
        $this->street = $event->getAdress()?->getStreet();
        $this->latitude = $event->getAdress()?->getLatitude();
        $this->longitude = $event->getAdress()?->getLongitude();

        return $this->formFactory->create(EventType::class, $event);
    }


    #[PreReRender(priority: -10)]
    public function updateStreetAfterAutoSubmit(): void
    {
        $event = $this->getForm()->getData(); // form déjà soumis par le trait
        $this->city = $event->getAdress()?->getCity()->getName();
        $this->street = $event->getAdress()?->getStreet();
        $this->latitude = $event->getAdress()?->getLatitude();
        $this->longitude = $event->getAdress()?->getLongitude();

    }

    #[LiveAction]
    public function save():RedirectResponse
    {
        $this->submitForm();
        $event = $this->getForm()->getData();

        $status = $this->statusRepository->findOneBy(['name' => 'En création']);
        $user = $this->security->getUser();
        if ($user) {
            $event->setCampus($user->getCampus());
            $event->setOrganizer($user);
        }
        $event->setStatus($status);
        $this->em->persist($event);
        $this->em->flush();

        $this->addFlash('success', 'Événement sauvegardé !');
        return $this->redirectToRoute('main_event');

    }


    #[LiveAction]
    public function publish(): RedirectResponse
    {
        $this->submitForm();
        $event = $this->getForm()->getData();
        $status = $this->statusRepository->findOneBy(['name' => 'Ouverte']);
        $user = $this->security->getUser();

        if ($user) {
            $event->setCampus($user->getCampus());
            $event->setOrganizer($user);
            $event->addRegistred($user);
        }
        $event->setStatus($status);
        $this->em->persist($event);
        $this->em->flush();

        $this->addFlash('success', 'Événement publié !');
        return $this->redirectToRoute('main_event');

    }

}
