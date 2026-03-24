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
        $this->addStatus($manager);
        $this->addCity($manager);
        $this->addCategory($manager);
        $manager->flush();

        $this->addUsers($manager);
        $this->addAdresse($manager);
        $manager->flush();

        $this->addEvent($manager);
        $manager->flush();
    }

    public function addCampus(ObjectManager $manager): void
    {
        $campusList = ['Nantes', 'Niort', 'Quimper', 'Rennes'];
        foreach ($campusList as $campus) {
            $newCampus = new Campus();
            $newCampus->setName($campus);
            $manager->persist($newCampus);
        }

    }

    public function addStatus(ObjectManager $manager): void
    {
        $statusList = ['En création', 'Ouverte', 'Clôturée', 'En cours', 'Terminée', 'Annulée', 'Historisée'];
        foreach ($statusList as $status) {
            $newStatus = new Status();
            $newStatus->setName($status);
            $manager->persist($newStatus);
        }

    }

    public function addCity(ObjectManager $manager): void
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

    }

    public function addCategory(ObjectManager $manager): void
    {
        $categories = ['Sorties gourmandes', 'Culture & divertissement',
            'Vie nocturne', 'Shopping & flânerie', 'Activités en plein air',
            'Loisirs & jeux', 'Sport & bien-être', 'Créatif & insolite', 'Social & rencontres'];
        foreach ($categories as $name) {
            $newCategory = new Category();
            $newCategory->setName($name);
            $manager->persist($newCategory);
        }

    }

    public function addUsers(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $campus = $manager->getRepository(Campus::class)->findAll();
        $usersData = [
            ['username' => 'Admin', 'roles' => ['ROLE_ADMIN']],
            ['username' => 'Arthur', 'roles' => ['ROLE_USER']],
            ['username' => 'Adrien', 'roles' => ['ROLE_USER']],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setUsername($data['username']);
            $user->setRoles($data['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $user->setName($faker->userName());
            $user->setLastname($faker->lastName());
            $user->setPhone($faker->phoneNumber());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setActive(true);
            $user->setStudent($data['username'] !== 'Admin');
            $user->setPhoto("portrait.png");
            $user->setCampus($faker->randomElement($campus));
            $manager->persist($user);

        }

    }

    public function addAdresse(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
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
            $address->setName($activity);
            $address->setStreet($faker->streetAddress);
            $address->setCity($faker->randomElement($city));
            $address->setLatitude($faker->latitude);
            $address->setLongitude($faker->longitude);
            $manager->persist($address);
        }
    }

    public function addEvent(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $campus = $manager->getRepository(Campus::class)->findAll();
        $adresses = $manager->getRepository(Adress::class)->findAll();
        $statuses = $manager->getRepository(Status::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();

        $statusMap = [];
        foreach ($statuses as $status) {
            $statusMap[$status->getName()] = $status;
        }

        $sortie = [
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

        $now = new \DateTime();

        foreach ($sortie as $name) {
            $event = new Event();
            $dateStart = $faker->dateTimeBetween('-1 month', '+2 months');
            $deadline = (clone $dateStart)->modify('-' . rand(1, 5) . ' days');

            $event->setName($name);
            $event->setDateStart($dateStart);
            $event->setDeadline($deadline);

            $duration = $faker->numberBetween(30, 180);
            $event->setDuration($duration);

            $dateEnd = (clone $dateStart)->modify('+' . $duration . ' minutes');

            if (rand(1, 15) === 1) {
                $event->setStatus($statusMap['Annulée']);
            } elseif ($now < $dateStart) {
                if ($deadline < $now) {
                    $event->setStatus($statusMap['Clôturée']);
                } else {
                    $event->setStatus($faker->randomElement([
                        $statusMap['En création'],
                        $statusMap['Ouverte']
                    ]));
                }
            } elseif ($now >= $dateStart && $now <= $dateEnd) {
                $event->setStatus($statusMap['En cours']);
            } else {
                $event->setStatus($faker->randomElement([
                    $statusMap['Terminée'],
                    $statusMap['Historisée']
                ]));
            }

            $event->setMaxIscription($faker->numberBetween(5, 15));
            $event->setEventInfo($faker->sentence(8));
            $event->setEventInfo($faker->sentence(8));
            $event->setCategory($faker->randomElement($categories));
            $event->setAdress($faker->randomElement($adresses));
            $event->setCampus($faker->randomElement($campus));

            $selectedOrganizer = $faker->randomElement($users);
            $event->setOrganizer($selectedOrganizer);

            $availableParticipants = array_values(array_filter($users, function ($user) use ($selectedOrganizer) {
                return $user !== $selectedOrganizer && !in_array('ROLE_ADMIN', $user->getRoles());
            }));

            if (count($availableParticipants) > 0) {
                $participants = $faker->randomElements(
                    $availableParticipants,
                    rand(1, min(5, count($availableParticipants)))
                );

                foreach ($participants as $participant) {
                    $event->addRegistred($participant);
                }
            }

            $manager->persist($event);
        }
    }

}
