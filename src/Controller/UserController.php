<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FileUploadError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\VarDumper\Cloner\Data;

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
            } elseif ($plainPassword === null) {
                /**
                 * @var User $user
                 */
                $user = $this->getUser();
                $user->setPassword($user->getPassword());
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
        $userById = $userRepository->find($id);
        if (!$userById) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        return $this->render('user/userDetailById.html.twig', [
                'userById' => $userById,
            ]
        );
    }

    #[Route("/admin/create_user", name: 'create_user', methods: ['POST', 'GET'])]
    public function createUser(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,


    ): Response
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $plainPassword)
                );
            }
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

    #[Route("/admin/deactivate_user/{id}", name: 'deactivate_user', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deactivateUser(
        int                    $id,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager,


    ): Response
    {
        $user = $userRepository->find($id);
        $user->setActive(false);
        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('success', 'Utilisateur désactivé !');
        return $this->redirectToRoute('user_list');
    }

    #[Route("/admin/activate_user/{id}", name: 'activate_user', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function activateUser(
        int                    $id,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager,


    ): Response
    {
        $user = $userRepository->find($id);
        $user->setActive(true);
        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('success', 'Utilisateur activé !');
        return $this->redirectToRoute('user_list');
    }

    #[Route("/admin/user_list", name: 'user_list', methods: ['GET'])]
    public function userList(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route("/admin/delete_user/{id}", name: 'delete_user', requirements: ['id' => '\d+'], methods: ['POST', 'GET'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {

        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable');
        }

        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', "Utilisateur supprimé avec succès !");
        return $this->redirectToRoute('user_list');
    }

    #[Route("/admin/create_users_csv", name: 'create_users_csv', methods: ['POST', 'GET'])]
    public function createUsersCsv(Request $request, UserRepository $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher,
    ): Response
    {

        $form = $this->createFormBuilder()
            ->add('submitFile', FileType::class, [])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('submitFile')->getData();

            //Ouverture du fichier
            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                $header = fgetcsv($handle);
                while (($data = fgetcsv($handle, 0, ';')) !== false) {
                    $user = new User();
                    $nom = mb_convert_encoding($data[1], 'UTF-8', 'ISO-8859-1');
                    $prenom = mb_convert_encoding($data[2], 'UTF-8', 'ISO-8859-1');
                    $user->setUsername($data[0]);
                    $user->setName($prenom);
                    $user->setLastname($nom);
                    $user->setEmail($data[3]);
                    $user->setPhone($data[4]);
                    $user->setPassword($userPasswordHasher->hashPassword($user, '123456'));
                    $user->setActive(true);
                    $user->setRoles(['ROLE_USER']);
                    $user->setPhoto("portrait.png");
                    $entityManager->persist($user);
                }
                fclose($handle);
                $entityManager->flush();
                $this->addFlash('success', 'Profils crées avec succès !');
                return $this->redirectToRoute('user_list');
            }

        }
        return $this->render('create_users_csv.html.twig', [
            'form2' => $form
        ]);

    }

}
