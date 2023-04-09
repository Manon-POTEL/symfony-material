<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Materiel;

class MaterielFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 10; $i++){
            $materiel = new Materiel();
            $materiel->setNom("Nom du matériel $i")
                     ->setPrix("1.5")
                     ->setQuantite("1")
                     ->setCreatedAt(new \DateTime());
                        
            $manager -> persist($materiel);
        }

        $manager->flush();
    }
}
