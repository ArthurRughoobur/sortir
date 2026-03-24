<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

    }

    public function addCampus(ObjectManager $manager)
    {
        $campusList = ['Nantes', 'Niort', 'Quimper', 'Rennes'];
        foreach ($campusList as $campus) {
            $campusNew = new Campus();
            $campusNew->setName($campus);
            $manager->persist($campusNew);
        }
        $manager->flush();
    }

    public function addStatus(ObjectManager $manager){
        $statusList = ['En création', 'Ouverte', 'Clôturée', 'En cours', 'Terminée', 'Annulée', 'Historisée'];
        foreach ($statusList as $status) {
            $newStatus = new Status();
            $newStatus->setName($status);
            $manager->persist($newStatus);
        }
        $manager->flush();
    }

    public function load(ObjectManager $manager): void
    {
        $this->addCampus($manager);
        $this->addUsers($manager);
        $this->addStatus($manager);
    }

    public function addUsers(ObjectManager $manager)
    {
        $faker = Factory::create();
        $campus = $manager->getRepository(Campus::class)->findAll();
        $usernames = ['Admin', 'Arthur', 'Adrien'];

        foreach ($usernames as $username) {
            $user = new User();
            $user->setUsername($username);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 123456));
            $user->setName($faker->userName());
            $user->setLastname($faker->lastName());
            $user->setPhone($faker->phoneNumber());
            $user->setEmail($faker->email);
            $user->setActive($faker->boolean());
            $user->setStudent($faker->boolean());
            $user->setPhoto($faker->imageUrl());
            $user->setCampus($faker->randomElement($campus));
            $manager->persist($user);

        }
        $manager->flush();
    }


}
