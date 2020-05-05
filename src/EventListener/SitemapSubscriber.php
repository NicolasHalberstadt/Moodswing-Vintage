<?php

namespace App\EventListener;

use App\Repository\ProductRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Presta\SitemapBundle\Sitemap\Url\GoogleImageUrlDecorator;
use Presta\SitemapBundle\Sitemap\Url\GoogleImage;

class SitemapSubscriber implements EventSubscriberInterface
{
    public function __construct(UrlGeneratorInterface $urlGenerator, ProductRepository $productRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'populate',
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerBlogPostsUrls($event->getUrlContainer());
    }

    /**
     * @param UrlContainerInterface $urls
     */
    public function registerBlogPostsUrls(UrlContainerInterface $urls): void
    {
        $products = $this->productRepository->findAll();

        foreach ($products as $product) {
            $url = new UrlConcrete(
                $this->urlGenerator->generate(
                    'product_details',
                    ['product_id' => $product->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
            $images = $product->getPictures();
            $decoratedUrl = new GoogleImageUrlDecorator($url);
            foreach ($images as $image) {
                $decoratedUrl->addImage(new GoogleImage("/uploads/files/" . $image->getPath()));
            }
            $urls->addUrl($decoratedUrl, 'product');
        }
    }
}
