<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
