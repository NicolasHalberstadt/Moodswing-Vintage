<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Category;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 1; $i < 20; $i++) {
            $category = new Category();
            $category->setName('category ' . $i);
            $category->setCreatedAt(new \DateTime());
            $manager->persist($category);
        }

        for ($i = 1; $i < 20; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setDescription($faker->text);
            $product->setPrice(mt_rand(10, 100));
            $product->setSize(mt_rand(36, 48));
            $product->setCategory($category);
            $product->setCreatedAt(new \DateTime());
            $manager->persist($product);
        }

        $manager->flush();
    }
}
