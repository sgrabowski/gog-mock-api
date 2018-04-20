<?php

namespace App\DataFixtures;

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
        $fallout->setPrice(199);

        $dontstarve = new Product();
        $dontstarve->setTitle("Don’t Starve");
        $dontstarve->setPrice(299);

        $bgate = new Product();
        $bgate->setTitle("Baldur’s Gate");
        $bgate->setPrice(399);

        $idale = new Product();
        $idale->setTitle("Icewind Dale");
        $idale->setPrice(499);

        $youdied = new Product();
        $youdied->setTitle("Bloodborne");
        $youdied->setPrice(599);

        $manager->persist($fallout);
        $manager->persist($dontstarve);
        $manager->persist($bgate);
        $manager->persist($idale);
        $manager->persist($youdied);

        $manager->flush();
    }
}