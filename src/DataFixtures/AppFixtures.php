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
        $eventsPerUser = 4;
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
                $durationInMinutes = $faker->numberBetween(30, 360);

                // On choisit un type d'event
                $scenario = $faker->randomElement([
                    'past_old',      // passé depuis +30 jours
                    'past_finished', // passé récent terminé
                    'current',       // en cours
                    'future_open',   // futur ouvert
                    'future_closed', // futur mais deadline dépassée
                    'cancelled',     // annulé
                ]);

                switch ($scenario) {
                    case 'past_old':
                        // Event commencé il y a plus de 30 jours et déjà terminé
                        $dateStart = $faker->dateTimeBetween('-4 months', '-31 days');
                        $dateEnd = (clone $dateStart)->modify("+{$durationInMinutes} minutes");
                        $deadline = (clone $dateStart)->modify('-' . rand(2, 10) . ' days');
                        $status = $faker->randomElement([
                            $statusMap['Terminée'],
                            $statusMap['Historisée'],
                        ]);
                        break;

                    case 'past_finished':
                        // Event terminé récemment
                        $dateStart = $faker->dateTimeBetween('-30 days', '-2 hours');
                        $dateEnd = (clone $dateStart)->modify("+{$durationInMinutes} minutes");

                        // Si jamais la fin tombe encore dans le futur, on force la fin dans le passé
                        if ($dateEnd > $now) {
                            $dateEnd = (clone $now)->modify('-30 minutes');
                            $dateStart = (clone $dateEnd)->modify("-{$durationInMinutes} minutes");
                        }

                        $deadline = (clone $dateStart)->modify('-' . rand(1, 5) . ' days');
                        $status = $statusMap['Terminée'];
                        break;

                    case 'current':
                        // Event en cours maintenant
                        $dateStart = (clone $now)->modify('-' . rand(10, 90) . ' minutes');
                        $dateEnd = (clone $now)->modify('+' . rand(10, 180) . ' minutes');
                        $durationInMinutes = (int) $dateStart->diff($dateEnd)->format('%h') * 60
                            + (int) $dateStart->diff($dateEnd)->format('%i');
                        $deadline = (clone $dateStart)->modify('-' . rand(1, 3) . ' days');
                        $status = $statusMap['En cours'];
                        break;

                    case 'future_closed':
                        // Event futur mais inscriptions closes
                        $dateStart = $faker->dateTimeBetween('+2 days', '+2 months');
                        $dateEnd = (clone $dateStart)->modify("+{$durationInMinutes} minutes");
                        $deadline = $faker->dateTimeBetween('-10 days', '-1 day');
                        $status = $statusMap['Clôturée'];
                        break;

                    case 'cancelled':
                        // Event annulé, futur ou récent
                        $dateStart = $faker->dateTimeBetween('-15 days', '+2 months');
                        $dateEnd = (clone $dateStart)->modify("+{$durationInMinutes} minutes");
                        $deadline = (clone $dateStart)->modify('-' . rand(1, 5) . ' days');
                        $status = $statusMap['Annulée'];
                        break;

                    case 'future_open':
                    default:
                        // Event futur encore ouvert ou en création
                        $dateStart = $faker->dateTimeBetween('+1 day', '+2 months');
                        $dateEnd = (clone $dateStart)->modify("+{$durationInMinutes} minutes");
                        $deadline = $faker->dateTimeBetween('now', (clone $dateStart)->modify('-1 day'));
                        $status = $faker->randomElement([
                            $statusMap['En création'],
                            $statusMap['Ouverte'],
                        ]);
                        break;
                }

                $event->setName($baseName);
                $event->setDateStart($dateStart);
                $event->setDeadline($deadline);
                $event->setDuration($durationInMinutes);
                $event->setStatus($status);
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

        // Chaque utilisateur s'inscrit à quelques events qui ne sont pas les siens
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
                if (
                    !$event->getRegistred()->contains($user)
                    && $event->getRegistred()->count() < $event->getMaxIscription()
                ) {
                    $event->addRegistred($user);
                }
            }
        }

        // Quelques inscrits aléatoires supplémentaires
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
