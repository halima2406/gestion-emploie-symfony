<?php

namespace App\DataFixtures;

use App\Entity\Departements;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DepartememtsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $departements = ["Informatique","Ressources Humaines","Finance","Marketing"];

        foreach($departements as $name ){

            $departement=new Departements();
            $departement->setNom($name);
            $departement->setCreateAt(new \DateTimeImmutable());
            $departement->setIsDeleted(false);

            $manager->persist($departement);
            //$this->$manager->remove($departement);


        }
        for ($i = 5; $i <= 14; $i++) {
            $departement = new Departements();
            $departement->setNom("Departement" . $i);
            $departement->setCreateAt(new \DateTimeImmutable());
            $departement->setIsDeleted(false);
            $manager->persist($departement);
        }

        $manager->flush();
    }
}
