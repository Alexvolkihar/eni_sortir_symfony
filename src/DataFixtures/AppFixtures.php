<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Site;
use App\Entity\State;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Time;

use function Symfony\Component\Clock\now;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $this->addSites($manager);
        $this->addStates($manager);
        $this->addCities(10, $manager);
        $this->addPlaces(5, $manager);
        $this->addUsers(50, $manager);
        $this->addEvents(20, $manager);
    }

    public function addSites(ObjectManager $manager)
    {
        $sitesNames = ["Chartres de Bretange", "Saint-Herblain", "La-Roche-sur-Yon", "Quimper", "Niort", "Angers"];
        foreach ($sitesNames as $name) {
            $site  = new Site();
            $site->setName($name);
            $manager->persist($site);
        }
        $manager->flush();
    }

    public function addStates(ObjectManager $manager)
    {
        $statesLabels = ["Créée", "Ouverte", "Clôturée", "Activité en cours", "Passée", "Annulée"];
        foreach ($statesLabels as $label) {
            $state  = new State();
            $state->setLabel($label);
            $manager->persist($state);
        }
        $manager->flush();
    }

    public function addCities(int $number, ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < $number; $i++) {
            $city = new City();
            $city
                ->setName($faker->city())
                ->setPostalCode(intval($faker->postcode()));
            $manager->persist($city);
        }
        $manager->flush();
    }

    public function addPlaces(int $number, ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $cities = $manager->getRepository(City::class)->findAll();
        for ($i = 0; $i < $number; $i++) {
            $place = new Place();
            $place
                ->setName($faker->words($faker->numberBetween(1, 5), true))
                ->setStreet($faker->streetName())
                ->setLatitude($faker->latitude())
                ->setLongitude($faker->longitude())
                ->setCity($faker->randomElement($cities));
            $manager->persist($place);
        }
        $manager->flush();
    }

    public function addUsers(int $number, ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $sites = $manager->getRepository(Site::class)->findAll();
        for ($i = 0; $i < $number; $i++) {
            $user = new User();
            $user
                ->setName($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPseudo($faker->userName())
                ->setEmail($faker->email())
                ->setPassword($this->userPasswordHasher->hashPassword($user, "123"))
                ->setPseudo($faker->userName())
                ->setPhone($faker->phoneNumber())
                ->setSite($faker->randomElement($sites))
                ->setIsAdmin($faker->boolean(30))
                ->setIsActive($faker->boolean(70));
            if ($user->isAdmin()) {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }
            $manager->persist($user);
        }
        $manager->flush();
    }

    public function addEvents(int $number, ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $sites = $manager->getRepository(Site::class)->findAll();
        $places = $manager->getRepository(Place::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();
        $states = $manager->getRepository(State::class)->findAll();
        for ($i = 0; $i < $number; $i++) {
            $event = new Event();
            $event
                ->setName($faker->words($faker->numberBetween(1, 5), true))
                ->setStartDateTime($faker->dateTimeBetween(new \DateTime("-2 month"), new \DateTime("+6 month")))
                ->setDuration(new \DateTime($faker->time()))
                ->setNbMaxSub($faker->numberBetween(2, 20))
                ->setSubDateLimit($faker->dateTimeBetween(new \DateTime("-3 month"), $event->getStartDateTime()))
                ->setEventInfo($faker->text())
                ->setSite($faker->randomElement($sites))
                ->setHost($faker->randomElement($users))
                ->setPlace($faker->randomElement($places));
            if (date_diff($event->getStartDateTime(), now())->invert == 1) {
                $event->setState($faker->randomElement([$states[array_search('Passée', $states)], $states[array_search('Annulée', $states)]]));
            } else if (date_diff($event->getSubDateLimit(), now())->invert == 1) {
                $event->setState($states[array_search('Clôturée', $states)]);
            } else {
                $filteredStates = array_filter($states, function ($state) {
                    return $state->getLabel() != 'Passée' && $state->getLabel() != 'Clôturée';
                });
                $event->setState($faker->randomElement($filteredStates));
            }
            $nbMember = $faker->numberBetween(2, $event->getNbMaxSub());
            for ($j = 0; $j < $nbMember; $j++) {
                $event->addMember($faker->randomElement($users));
            }
            $manager->persist($event);
        }
        $manager->flush();
    }
}
