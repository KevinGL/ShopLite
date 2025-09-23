<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(ProductRepository $repo): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        $randomProducts = $repo->findRandom(20);
        
        return $this->render('shop/index.html.twig',
        [
            'randomProducts' => $randomProducts,
        ]);
    }

    #[Route("/shop/by_cat/{id}/{page}", name: "app_by_cat")]
    public function byCat(CategoryRepository $catRepo, int $id, int $page): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        $cat = $catRepo->find($id);

        $products = [];

        for($i = 0 ; $i < count($cat->getProducts()) ; $i++)
        {
            if($i >= ($page - 1) * $_ENV["LIMIT_PAGES"] && $i < $page * $_ENV["LIMIT_PAGES"])
            {
                $products[] = $cat->getProducts()[$i];
            }
        }

        $nbPages = ceil(count($cat->getProducts()) / $_ENV["LIMIT_PAGES"]);

        return $this->render("shop/byCat.html.twig",
        [
            "cat" => $cat,
            "products" => $products,
            "nbPages" => $nbPages
        ]);
    }

    #[Route("/shop/search/{page}", name: "app_by_search")]
    public function search(Request $req, ProductRepository $repo, int $page): Response
    {
        $nbPages = 0;
        
        $products = $repo->findByWords(explode(" ", $req->get("words")), $page, $nbPages);

        return $this->render("shop/search.html.twig",
        [
            "products" => $products,
            "words" => $req->get("words"),
            "nbPages" => $nbPages
        ]);
    }
}
