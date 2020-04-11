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
        return $this->render('homepage/index.html.twig', [
            'products' => $products,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/category/{category_id}", name="products_by_category")
     */
    public function productsByCategory(int $category_id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($category_id);
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();

        return $this->render('homepage/index.html.twig', [
            'category' => $category,
            'categories' => $categories,
            'products' => $products
        ]);
    }

    /**
     * @Route("/product/{product_id}", name="product_details")
     */
    public function productDetails($product_id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($product_id);

        return $this->render('product/details.html.twig', [
            'product' => $product
        ]);
    }
}
