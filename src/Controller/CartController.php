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

    #[Route("/cart/view", name: "view_cart")]
    public function view(Request $req, ProductRepository $repo): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        $session = $req->getSession();

        $cart = $session->get("cart") ?? [];

        $products = [];

        foreach($cart as $key => $value)
        {
            $product = $repo->find($key);
            $product->count = $value;

            $products[] = $product;
        }

        return $this->render("cart/view.html.twig", ["products" => $products]);
    }

    #[Route("/cart/delete/{id}", name: "cart_delete")]
    public function delete(Request $req, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        $session = $req->getSession();

        $cart = $session->get("cart") ?? [];

        if (isset($cart[$id]))
        {
            unset($cart[$id]);
        }
        else
        {
            $this->addFlash("error", "Produit introuvable");

            return $this->redirectToRoute("view_cart");
        }

        $session->set("cart", $cart);

        return $this->redirectToRoute("view_cart");
    }

    #[Route("/cart/dec/{id}", name: "cart_dec")]
    public function dec(Request $req, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        $session = $req->getSession();

        $cart = $session->get("cart") ?? [];

        if (isset($cart[$id]))
        {
            $cart[$id]--;

            if ($cart[$id] <= 0)
            {
                unset($cart[$id]);
            }
        }
        else
        {
            $this->addFlash("error", "Produit introuvable");

            return $this->redirectToRoute("view_cart");
        }

        $session->set("cart", $cart);

        return $this->redirectToRoute("view_cart");
    }

    #[Route("/cart/inc/{id}", name: "cart_inc")]
    public function inc(Request $req, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        $session = $req->getSession();

        $cart = $session->get("cart") ?? [];

        $cart[$id]++;

        $session->set("cart", $cart);

        return $this->redirectToRoute("view_cart");
    }

    #[Route("/cart/erase", name: "cart_erase")]
    public function erase(Request $req): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        $session = $req->getSession();

        $session->set("cart", []);

        return $this->redirectToRoute("view_cart");
    }
}
