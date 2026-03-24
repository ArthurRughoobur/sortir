<?php

namespace App\DataFixtures;

use App\Entity\Adress;
use App\Entity\Campus;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Event;
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

    public function load(ObjectManager $manager): void
    {
        $this->addCampus($manager);
        $this->addUsers($manager);
        $this->addStatus($manager);
        $this->addCity($manager);
        $this->addCategory($manager);
        $this->addAdresse($manager);
        $this->addEvent($manager);
    }

    public function addCampus(ObjectManager $manager)
    {
        $campusList = ['Nantes', 'Niort', 'Quimper', 'Rennes'];
        foreach ($campusList as $campus) {
            $newCampus = new Campus();
            $newCampus->setName($campus);
            $manager->persist($newCampus);
        }
        $manager->flush();
    }

    public function addStatus(ObjectManager $manager)
    {
        $statusList = ['En création', 'Ouverte', 'Clôturée', 'En cours', 'Terminée', 'Annulée', 'Historisée'];
        foreach ($statusList as $status) {
            $newStatus = new Status();
            $newStatus->setName($status);
            $manager->persist($newStatus);
        }
        $manager->flush();
    }

    public function addCity(ObjectManager $manager)
    {
        //Niort = 79000, Rennes = 35000, Nantes = 44000, Quimper = 29000
        $cities = [
            'Nantes' => '44000',
            'Niort' => '79000',
            'Quimper' => '29000',
            'Rennes' => '35000',
        ];

        foreach ($cities as $name => $zipcode) {
            $newCity = new City();
            $newCity->setName($name);
            $newCity->setZipcode($zipcode);
            $manager->persist($newCity);
        }

        $manager->flush();
    }

    public function addCategory(ObjectManager $manager)
    {
        $categories = ['Sorties gourmandes', 'Culture & divertissement',
            'Vie nocturne', 'Shopping & flânerie', 'Activités en plein air',
            'Loisirs & jeux', 'Sport & bien-être', 'Créatif & insolite', 'Social & rencontres'];
        foreach ($categories as $name) {
            $newCategory = new Category();
            $newCategory->setName($name);
            $manager->persist($newCategory);
        }
        $manager->flush();
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
            $user->setPhoto("portrait.png");
            $user->setCampus($faker->randomElement($campus));
            $manager->persist($user);

        }
        $manager->flush();
    }

    public function addAdresse(ObjectManager $manager)
    {
        $faker = Factory::create();
        $city = $manager->getRepository(City::class)->findAll();
        $activities = [
            'Randonnée',
            'Escalade',
            'Restaurant',
            'Cinéma',
            'Natation',
            'Football',
            'Jeux vidéo',
            'Bowling',
            'Karting',
            'Musculation'
        ];
        foreach ($activities as $activity) {
            $address = new Adress();
            $address->setName($faker->randomElement($activities));
            $address->setStreet($faker->streetAddress);
            $address->setCity($faker->randomElement($city));
            $address->setLatitude($faker->latitude);
            $address->setLongitude($faker->longitude);
            $manager->persist($address);
        }
        $manager->flush();
    }

    public function addEvent(ObjectManager $manager)
    {
        $faker = Factory::create();
        $campus = $manager->getRepository(Campus::class)->findAll();
        $adresses = $manager->getRepository(Adress::class)->findAll();
        $statuses = $manager->getRepository(Status::class)->findAll();
        $organizer = $manager->getRepository(User::class)->findAll();
        $registred = $manager->getRepository(User::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();

        $sortie =
            [
                'Rando forêt', 'Rando montagne', 'Balade nature', 'Randonnée bord de mer', 'Rando parc naturel',
                'Escalade en salle', 'Escalade en falaise', 'Bloc indoor', 'Via ferrata', 'Escalade débutant',
                'McDo', 'Burger King', 'Restaurant italien', 'Restaurant chinois', 'Restaurant gastronomique',
                'Pizzeria', 'Buffet à volonté', 'Cinéville', 'UGC', 'Pathé', 'Cinéma indépendant', 'Soirée Netflix',
                'Piscine municipale', 'Piscine olympique', 'Aqua gym', 'Natation en mer', 'Parc aquatique',
                'Match entre amis', 'Five (foot indoor)', 'Entraînement club', 'Match compétition', 'Foot loisir',
                'Session ranked', 'Session chill', 'Tournoi online', 'LAN entre amis', 'Speedrun', 'Bowling entre amis',
                'Soirée bowling', 'Compétition bowling', 'Bowling + arcade', 'Karting indoor', 'Karting outdoor',
                'Course entre amis', 'Session chrono', 'Grand prix', 'Séance haut du corps', 'Séance jambes',
                'Full body', 'Cardio training', 'Cross training',
            ];
        foreach ($sortie as $name) {
            $event = new Event();
            $event->setName($name);
            $event->setDateStart(($faker->dateTimeBetween('-1 months', 'now')));
            $event->setDeadline($faker->dateTimeBetween($event->getDateStart()));
            $event->setDuration($faker->numberBetween(30, 60));
            $event->setMaxIscription($faker->numberBetween(5, 15));
            $event->setEventInfo($faker->text(25));
            $event->setCategory($faker->randomElement($categories));
            $event->setAdress($faker->randomElement($adresses));
            $event->setStatus($faker->randomElement($statuses));
            $event->setCampus($faker->randomElement($campus));
            $event->addRegistred($faker->randomElement($registred));
            $event->setOrganizer($faker->randomElement($organizer));
            $manager->persist($event);
        }
        $manager->flush();
    }

}
