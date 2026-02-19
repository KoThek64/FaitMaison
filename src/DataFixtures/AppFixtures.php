<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $plat = New Category();
        $dessert = new Category();
        $soupe = new Category();
        $entree = new Category();

        $plat->setName('Plat principal');
        $plat->setSlug('plat-principal');
        $dessert->setName('Dessert');
        $dessert->setSlug('dessert');
        $soupe->setName('Soupe');
        $soupe->setSlug('soupe');
        $entree->setName('Entrée');
        $entree->setSlug('entree');

        $manager->persist($plat);
        $manager->persist($soupe);
        $manager->persist($dessert);
        $manager->persist($entree);

        $manager->flush();
    }
}
