<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Picture;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\PictureRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Y0lk\OAuth1\Client\Server\Etsy;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage",
     * options={"sitemap" = true})
     */
    public function homepage(ProductRepository $productRepo, CategoryRepository $categoryRepo)
    {
        $categories = $categoryRepo->findAll();
        $products = $productRepo->findAll();
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
     * @Route("/categories/update", name="update_categories")
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateCategories(CategoryRepository $categoryRepo)
    {

        $client = HttpClient::create();
        $apiKey = "2bqddccebry4nblkj11o6ugv";
        $apiResponse = $client->request('GET', 'https://openapi.etsy.com/v2/shops/moodswingvintage/sections?api_key=' . $apiKey)->toArray();
        $apiCategories = $apiResponse['results'];
        foreach ($apiCategories as $apiCategory) {
            $category = $categoryRepo->findOneBy(['etsy_id' => $apiCategory['shop_section_id']]);

            if ($category === null) {
                $category = new Category();
                $category->setName($apiCategory['title']);
                $category->setEtsyId($apiCategory['shop_section_id']);
                $category->setCreatedAt(new \DateTime());
                $em = $this->getDoctrine()->getManager();
                $em->persist($category);
                $em->flush();
            }
        }
        $this->addFlash('success', 'THe categories have been updated');
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/products/update", name="update_products")
     */
    public function updateProducts(ProductRepository $productRepo, CategoryRepository $categoryRepo)
    {
        // // Solution 1 : vider bdd et re-remplir BOF BOF
        // $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
       
        // foreach ($products as $product) {
        //     $manager->remove($product);
        // }

        $client = HttpClient::create();
        $apiKey = "2bqddccebry4nblkj11o6ugv";
        // get all the products from the shop
        $apiProductsResponse = $client->request('GET', 'https://openapi.etsy.com/v2/shops/moodswingvintage/listings/active?includes=MainImage&limit=70&api_key=' . $apiKey)->toArray();
        $apiProducts = $apiProductsResponse['results'];

        $manager = $this->getDoctrine()->getManager();
        // PRODUCTS
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
       
        foreach ($apiProducts as $apiProduct) {
            // Solution 2 : prendre tous les produits -> comparer titre avec résultats api -> si false -> suppr from bdd
            foreach ($products as $product) {
                $productName = $product->getName();
                if ($productName !== $apiProduct['title']) {
                    $manager->remove($product);
                }
            }
            // does the product already exists in db ?
            $product = $productRepo->findOneBy(['etsy_id' => $apiProduct['listing_id']]);

            if ($product !== null) {
                // update info
                $product->setName($apiProduct['title']);
                $product->setDescription($apiProduct['description']);
                $product->setPrice($apiProduct['price']);
                $product->setEtsyLink($apiProduct['url']);
                $product->setUpdatedAt(new \DateTime('now'));
            } else {
                // add new product
                $product = new Product();
                $product->setName($apiProduct['title']);
                $product->setDescription($apiProduct['description']);
                $product->setPrice($apiProduct['price']);
                $product->setEtsyLink($apiProduct['url']);
                $product->setEtsyId($apiProduct['listing_id']);
                $product->setCreatedAt(new \DateTime('now'));

                $category = $categoryRepo->findOneBy(['etsy_id' => $apiProduct['shop_section_id']]);
                $product->setCategory($category);
            }
            // make get request to get all pictures from product

            $manager->persist($product);
            $manager->flush();
        }
        $this->updatePictures();
        $this->addFlash('success', 'The products have been updated');
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/pictures/update", name="update_pictures")
     */
    public function updatePictures()
    {
        $productRepo = $this->getDoctrine()->getRepository(Product::class);
        $pictureRepo = $this->getDoctrine()->getRepository(Picture::class);
        $client = HttpClient::create();
        $apiKey = "2bqddccebry4nblkj11o6ugv";
        $products = $productRepo->findAll();
        foreach ($products as $product) {
            // get product images from api
            $apiProductPicturesResponse = $client->request('GET', 'https://openapi.etsy.com/v2/listings/' . $product->getEtsyId() . '/images?api_key=' . $apiKey)->toArray();
            $apiProductPictures = $apiProductPicturesResponse['results'];
            foreach ($apiProductPictures as $apiProductPicture) {
                $productPicture = $pictureRepo->findOneBy(['etsy_id' => $apiProductPicture['listing_image_id']]);

                if ($productPicture === null) {
                    $picture = new Picture();
                    $picture->setPath($apiProductPicture['url_fullxfull']);
                    $picture->setEtsyId($apiProductPicture['listing_image_id']);
                    $picture->setCreatedAt(new \DateTime());
                    $picture->setRank($apiProductPicture['rank']);
                    $product->addPicture($picture);
                    $manager = $this->getDoctrine()->getManager();
                    $manager->persist($picture);
                    $manager->flush();
                }
            }
        }
        // $this->addFlash('success', 'The pictures have been updated');
        // return $this->redirectToRoute('homepage');
    }


    /**
     * @Route("/legal/fr", name="legal_notice_fr")
     */
    public function legalNoticeFr()
    {
        return $this->render('legal/legal_fr.html.twig');
    }

    /**
     * @Route("/legal/en", name="legal_notice_en")
     */
    public function legalNoticeEn()
    {
        return $this->render('legal/legal_en.html.twig');
    }

    /**
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('about.html.twig');
    }
}
