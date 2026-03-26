<?php

namespace App\Controller;

use App\Entity\Adress;
use App\Form\AdressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdressController extends AbstractController
{
    #[Route('/create_adress', name: 'create_adress', methods: ['POST','GET'])]
    public function createAdress(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $adress = new Adress();
        $adressForm = $this->createForm(AdressType::class, $adress);

        $adressForm->handleRequest($request);

        if ($adressForm->isSubmitted() && $adressForm->isValid()) {
            $entityManager->persist($adress);
            $entityManager->flush();
            $this->addFlash('success', "Adresse " .$adress->getName() ." ajoutée avec success" );
            return $this->redirectToRoute('create_event');
        }

        return $this->render('adress/adress.html.twig', [
            'adressForm'=> $adressForm,
        ]);
    }
}
