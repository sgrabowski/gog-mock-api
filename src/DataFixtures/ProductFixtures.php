<?php

namespace App\DataFixtures;

use App\Entity\Price;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $fallout = new Product();
        $fallout->setTitle("Fallout");
        $fallout->addPrice(new Price("USD", "1.99"));

        $dontstarve = new Product();
        $dontstarve->setTitle("Don’t Starve");
        $dontstarve->addPrice(new Price("USD", "2.99"));

        $bgate = new Product();
        $bgate->setTitle("Baldur’s Gate");
        $bgate->addPrice(new Price("USD", "3.99"));

        $idale = new Product();
        $idale->setTitle("Icewind Dale");
        $idale->addPrice(new Price("USD", "4.99"));

        $youdied = new Product();
        $youdied->setTitle("Bloodborne");
        $youdied->addPrice(new Price("USD", "5.99"));

        $manager->persist($fallout);
        $manager->persist($dontstarve);
        $manager->persist($bgate);
        $manager->persist($idale);
        $manager->persist($youdied);

        $manager->flush();
    }
}