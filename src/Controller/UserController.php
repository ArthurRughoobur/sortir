<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FileUploadError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'update', methods: ['POST', 'GET'])]
    public function updateUser(
        User                        $user,
        int                         $id,
        UserRepository              $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        Request                     $request,
        EntityManagerInterface      $entityManager,
        FileUploader                $fileUploader,
    ): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $file = $userForm->get('photo')->getData();
            if ($file) {
                $user->setPhoto(
                    $fileUploader->upload($file, 'img', $user->getName()));
            }
            //hash du mdp
            $plainPassword = $userForm->get('password')->getData();
            if ($plainPassword) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }
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

    #[Route('/user/detail/{id}', name: 'user_detail_id', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function userDetailById
    (
        int            $id,
        UserRepository $userRepository,
    ): Response
    {
        // Vérifie que l'utilisateur possède le rôle requis
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userById = $userRepository->find($id);
        return $this->render('user/userDetailById.html.twig', [
                'userById' => $userById,
            ]
        );
    }

    #[Route("/create_user", name: 'create_user')]
    public function createUser(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,


    ): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Accès refusé : vous devez être admin.');
        }
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setActive(true);
            if ($user->getPhoto() === null) {
                $user->setPhoto("portrait.png");
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Profil crée avec succès !');
            return $this->redirectToRoute('main_event');
        }
        return $this->render('user/create.html.twig', [
            'form' => $form,
        ]);

    }
}
