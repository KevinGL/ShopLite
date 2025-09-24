<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route("/api/cart/get_count", name: "api_cart_get_count")]
    public function getCount(Request $req): Response
    {
        if(!$this->getUser())
        {
            return $this->json(["message" => "Non authentifié", "success" => false], 401);
        }

        $session = $req->getSession();
        $cart = $session->get("cart") ?? [];

        return $this->json(["message" => count($cart), "success" => true]);
    }
    
    #[Route("/api/cart/add/{id}", name: "api_add_cart")]
    public function add(Request $req, ProductRepository $repo, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->json(["message" => "Non authentifié", "success" => false], 401);
        }
        
        $session = $req->getSession();
        $cart = $session->get("cart") ?? [];

        $product = $repo->find($id);
        if (!$product)
        {
            return $this->json(["message" => "Produit introuvable", "success" => false], 404);
        }

        $cart[$id] = ($cart[$id] ?? 0) + 1;

        $session->set("cart", $cart);

        return $this->json(["message" => "Ajouté au panier !", "success" => true, "count" => count($cart)], 200);
    }
}
