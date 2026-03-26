<?php

namespace App\Components;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('event_form')]
final class EventFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?Event $initialFormData = null;

    #[LiveProp]
    public ?string $street = null;

    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface   $formFactory,
        private StatusRepository       $statusRepository,
        private Security               $security,
    )
    {
    }

    protected function instantiateForm(): FormInterface
    {
        $event = $this->initialFormData ?? new Event();
        $this->street = $event->getAdress()?->getStreet();
        return $this->formFactory->create(EventType::class, $event);
    }


    #[PreReRender(priority: -10)]
    public function updateStreetAfterAutoSubmit(): void
    {
        $event = $this->getForm()->getData();
        $this->street = $event->getAdress()?->getStreet();
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
        }
        $event->setStatus($status);
        $this->em->persist($event);
        $this->em->flush();

        $this->addFlash('success', 'Événement publié !');
        return $this->redirectToRoute('main_event');

    }
}
