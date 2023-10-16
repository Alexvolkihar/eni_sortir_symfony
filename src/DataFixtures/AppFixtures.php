<?php

namespace App\DataFixtures;

use App\Entity\Site;
use App\Entity\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->addSites($manager);
        $this->addStates($manager);
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
}
