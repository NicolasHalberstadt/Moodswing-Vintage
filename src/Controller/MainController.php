<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage",
     * options={"sitemap" = true})
     */
    public function homepage()
    {
        // get all products
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $user = $this->getUser();

        // send them to homepage
        return $this->render('homepage/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'user' => $user
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
        $user = $this->getUser();

        return $this->render('homepage/index.html.twig', [
            'category' => $category,
            'categories' => $categories,
            'products' => $products,
            'user' => $user
        ]);
    }

    /**
     * @Route("/product/{product_id}", name="product_details")
     */
    public function productDetails(int $product_id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($product_id);
        $user = $this->getUser();

        return $this->render('product/details.html.twig', [
            'product' => $product,
            'user' => $user
        ]);
    }

    /**
     * @Route("/legal/fr", name="legal_notice_fr")
     */
    public function legalNoticeFr() {
        return $this->render('legal/legal_fr.html.twig');
    }

    /**
     * @Route("/legal/en", name="legal_notice_en")
     */
    public function legalNoticeEn() {
        return $this->render('legal/legal_en.html.twig');
    }
}
