<?php
namespace App\Components;

use App\Entity\Adress;
use App\Entity\Event;
use App\Form\EventType;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PostHydrate;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('event_form')]
class EventFormComponent
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public ?Adress $adress = null;

    #[LiveProp]
    public ?string $street = null;

     public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private StatusRepository $statusRepository,
        private Security $security,

    ) {
    }
    protected function instantiateForm(): FormInterface
    {

        return $this->formFactory->create(EventType::class, new Event());
    }
    #[PostHydrate]
    public function onAdressChange(): void
    {
        $adress = $this->getForm()->get('adress')->getData();
        $this->street = $this->adress?->getStreet();
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        if(!$this->getForm()->isValid()) {
            return;
        }

        $event = $this->getForm()->getData();

        $status = $this->statusRepository->findOneBy(['name' => "En création"]);
        $user = $this->security->getUser();
        if ($user) {
            $event->setCampus($user->getCampus());
            $event->setOrganizer($user);
        }
        $event->setStatus($status);
        $this->em->persist($event);
        $this->em->flush();

        $this->addFlash('success', 'Événement sauvegardé !');
    }

    #[LiveAction]
    public function publish(): void
    {

        $this->submitForm();
        if (!$this->getForm()->isValid()) {
            return;
        }


        $event = $this->getForm()->getData();
        $status = $this->statusRepository->findOneBy(['name' => "Ouverte"]);
        $user = $this->security->getUser();
        if ($user) {
            $event->setCampus($user->getCampus());
            $event->setOrganizer($user);
        }
        $event->setStatus($status);
        $this->em->persist($event);
        $this->em->flush();
        $this->addFlash('success', 'Événement publié !');
    }

}
