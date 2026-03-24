<?php

namespace App\Controller;

use App\Form\UserType;
use App\Repository\UserRepository;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FileUploadError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'update', methods: ['POST', 'GET'])]
    public function updateUser(
        int                         $id,
        UserRepository              $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        Request                     $request,
        EntityManagerInterface      $entityManager,
        FileUploader                $fileUploader,
    ): Response
    {
        $user = $userRepository->find($id);
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $file = $userForm->get('photo')->getData();
            $user->setPhoto(
                $fileUploader->upload($file, 'img', $user->getName()));


            //hash du mdp

            $plainPassword = $userForm->get('password')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Mise à jour du profil effectuée !');

           return $this->redirectToRoute('main_event');
       }
           return $this->render('user/update.html.twig', [
               'userForm2' => $userForm,
               'user' => $user,
           ]);

    }
}
