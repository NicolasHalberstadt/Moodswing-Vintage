<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage()
    {
        // get all products
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        
        // send them to homepage
        return $this->render('main/homepage.html.twig', [
            'products' => $products,
            'categories' => $categories
        ]);
    }
}
