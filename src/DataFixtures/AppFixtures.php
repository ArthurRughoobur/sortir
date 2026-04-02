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
            ['username' => 'Sly','lastname'=> 'Tropée', "name" => "Sylvain", 'roles' => ['ROLE_ADMIN']],
            ['username' => 'Arthur', 'lastname'=> 'Rughoobur', "name" => "Arthur", 'roles' => ['ROLE_ADMIN']],
            ['username' => 'Adrien','lastname'=> 'Le Clech', "name" => "Adrien", 'roles' => ['ROLE_ADMIN']],
            ['username' => 'AdrienQ','lastname'=> 'Quintard', "name" => "Adrien", 'roles' => ['ROLE_USER']],
            ['username' => 'Raman', 'lastname'=> 'Khaniakou', "name" => "Raman", 'roles' => ['ROLE_USER']],
            ['username' => 'Lena','lastname'=> 'Morfoisse', "name" => "Lena", 'roles' => ['ROLE_USER']],
            ['username' => 'Emilia', 'lastname'=> 'Resanovic', "name" => "Emilia",'roles' => ['ROLE_USER']],
            ['username' => 'Thomas', 'lastname'=> 'Danger', "name" => "Thomas",'roles' => ['ROLE_USER']],
            ['username' => 'Maud', 'lastname'=> 'Butin', "name" => "Maud", 'roles' => ['ROLE_USER']],
            ['username' => 'Nicolas', 'lastname'=> 'Tolantin', "name" => "Nicolas",'roles' => ['ROLE_USER']],
            ['username' => 'David', 'lastname'=> 'Thebault', "name" => "David",'roles' => ['ROLE_USER']],
            ['username' => 'Baptiste', 'lastname'=> 'Leblanc', "name" => "Baptiste",'roles' => ['ROLE_USER']],
            ['username' => 'Silvia', 'lastname'=> 'Bamas', "name" => "Sylvia",'roles' => ['ROLE_USER']],
            ['username' => 'Mathilde','lastname'=> 'Guedon', "name" => "Mathilde", 'roles' => ['ROLE_USER']],
            ['username' => 'Yasmine', 'lastname'=> 'Hailoul', "name" => "Yasmine",'roles' => ['ROLE_USER']],
            ['username' => 'Clara','lastname'=> 'Huet', "name" => "Clara", 'roles' => ['ROLE_USER']],
            ['username' => 'Antonin','lastname'=> 'Martel', "name" => "Antonin", 'roles' => ['ROLE_USER']],
            ['username' => 'Camille','lastname'=> 'Payen', "name" => "Camille", 'roles' => ['ROLE_USER']],
            ['username' => 'Vanina', 'lastname'=> 'Martignoni', "name" => "Vanina",'roles' => ['ROLE_USER']],
            ['username' => 'Mael','lastname'=> 'Seigneur', "name" => "Maël", 'roles' => ['ROLE_USER']],
            ['username' => 'Almokashfi','lastname'=> 'Kabbashi Ali Hamed', "name" => "Almokashfi", 'roles' => ['ROLE_USER']],
        ];

        // Users fixes
        foreach ($usersData as $data) {
            $user = new User();
            $user->setUsername($data['username']);
            $user->setRoles($data['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $user->setName($data['name']);
            $user->setLastname($data['lastname']);
            $user->setPhone($faker->phoneNumber());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setActive(true);
            $user->setStudent($data['username'] !== 'Admin');
            $user->setPhoto('portrait.png');
            $user->setCampus($faker->randomElement($campus));
            $manager->persist($user);
        }


        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setUsername($faker->unique()->userName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $user->setName($faker->firstName());
            $user->setLastname($faker->lastName());
            $user->setPhone($faker->phoneNumber());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setActive(true);
            $user->setStudent(true);
            $user->setPhoto('portrait.png');
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

        $sorties = [
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
        $eventsPerUser = 3;
        $registrationsPerUser = 3;

        $normalUsers = array_values(array_filter($users, function ($user) {
            return !in_array('ROLE_ADMIN', $user->getRoles(), true)
                && $user->getUsername() !== 'Admin';
        }));

        $allEvents = [];

        foreach ($normalUsers as $user) {
            for ($i = 1; $i <= $eventsPerUser; $i++) {
                $event = new Event();

                $baseName = $faker->randomElement($sorties);

                $dateStart = $faker->dateTimeBetween('-1 month', '+2 months');
                $deadline = (clone $dateStart)->modify('-' . rand(1, 5) . ' days');
                $durationInMinutes = $faker->numberBetween(30, 360);
                $dateEnd = (clone $dateStart)->modify('+' . $durationInMinutes . ' minutes');

                $event->setName($baseName);
                $event->setDateStart($dateStart);
                $event->setDeadline($deadline);
                $event->setDuration($durationInMinutes);

                if (rand(1, 15) === 1) {
                    $event->setStatus($statusMap['Annulée']);
                } elseif ($now < $dateStart) {
                    if ($deadline < $now) {
                        $event->setStatus($statusMap['Clôturée']);
                    } else {
                        $event->setStatus($faker->randomElement([
                            $statusMap['En création'],
                            $statusMap['Ouverte'],
                        ]));
                    }
                } elseif ($now >= $dateStart && $now <= $dateEnd) {
                    $event->setStatus($statusMap['En cours']);
                } else {
                    $event->setStatus($faker->randomElement([
                        $statusMap['Terminée'],
                        $statusMap['Historisée'],
                    ]));
                }

                $event->setMaxIscription($faker->numberBetween(6, 20));
                $event->setEventInfo($faker->realText(255));
                $event->setCategory($faker->randomElement($categories));
                $event->setAdress($faker->randomElement($adresses));
                $event->setCampus($faker->randomElement($campus));
                $event->setOrganizer($user);

                // L'organisateur est automatiquement inscrit
                $event->addRegistred($user);

                $manager->persist($event);
                $allEvents[] = $event;
            }
        }

        // 2) Chaque utilisateur est inscrit à au moins 5 events qui ne sont pas les siens
        foreach ($normalUsers as $user) {
            $otherEvents = array_values(array_filter($allEvents, function ($event) use ($user) {
                return $event->getOrganizer() !== $user;
            }));

            if (count($otherEvents) === 0) {
                continue;
            }

            $eventsToJoin = $faker->randomElements(
                $otherEvents,
                min($registrationsPerUser, count($otherEvents))
            );

            foreach ($eventsToJoin as $event) {
                if (!$event->getRegistred()->contains($user)
                    && $event->getRegistred()->count() < $event->getMaxIscription()) {
                    $event->addRegistred($user);
                }
            }
        }

        // 3) On ajoute encore quelques inscrits aléatoires pour enrichir les events
        foreach ($allEvents as $event) {
            $availableParticipants = array_values(array_filter($normalUsers, function ($user) use ($event) {
                return $user !== $event->getOrganizer()
                    && !$event->getRegistred()->contains($user);
            }));

            if (count($availableParticipants) === 0) {
                continue;
            }

            $remainingSpots = $event->getMaxIscription() - $event->getRegistred()->count();

            if ($remainingSpots <= 0) {
                continue;
            }

            $extraCount = rand(0, min(5, $remainingSpots, count($availableParticipants)));

            if ($extraCount > 0) {
                $extraParticipants = $faker->randomElements($availableParticipants, $extraCount);

                foreach ($extraParticipants as $participant) {
                    $event->addRegistred($participant);
                }
            }
        }
    }

}
