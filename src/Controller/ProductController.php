<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/products/{page}', name: 'app_products', requirements: ['page' => '\d+'])]
    public function index(ProductRepository $repo, int $page): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Cette page est réservée aux admins");
            return $this->redirectToRoute("app_shop");
        }

        $nbPages = 0;

        $products = $repo->findByPage($page, $nbPages);
        
        return $this->render('product/index.html.twig',
        [
            'products' => $products,
            'page' => $page,
            'nbPages' => $nbPages
        ]);
    }

    #[Route("/products/add", name: "add_product")]
    public function add(Request $req, EntityManagerInterface $em): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Cette page est réservée aux admins");
            return $this->redirectToRoute("app_shop");
        }
        
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($product);
            $em->flush();

            $this->addFlash("success", "Produit ajouté");
            return $this->redirectToRoute("app_products", ["page" => 1]);
        }

        return $this->render("product/add.html.twig",
        [
            "form" => $form
        ]);
    }

    #[Route("/products/edit/{id}", name: "edit_product")]
    public function edit(Request $req, ProductRepository $repo, int $id, EntityManagerInterface $em): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Cette page est réservée aux admins");
            return $this->redirectToRoute("app_shop");
        }
        
        $product = $repo->find($id);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($product);
            $em->flush();

            $this->addFlash("success", "Produit mis à jour");
            return $this->redirectToRoute("app_products", ["page" => 1]);
        }

        return $this->render("product/edit.html.twig",
        [
            "form" => $form
        ]);
    }

    #[Route("/products/delete/{id}", name: "delete_product")]
    public function delete(ProductRepository $repo, int $id, EntityManagerInterface $em): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Cette page est réservée aux admins");
            return $this->redirectToRoute("app_shop");
        }
        
        $product = $repo->find($id);

        foreach ($product->getCategories() as $category)
        {
            $product->removeCategory($category);
        }

        $em->flush();
        $em->remove($product);
        $em->flush();

        $this->addFlash("success", "Produit supprimé");

        return $this->redirectToRoute("app_products", ["page" => 1]);
    }

    #[Route("/api/products/search_by_word/{words}", name: "api_search_product")]
    public function searchByWord(ProductRepository $repo, string $words): Response
    {
        if(!$this->getUser())
        {
            return $this->json(["message" => "Not authenticated"], 401);
        }

        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            return $this->json(["message" => "Not admin"], 403);
        }

        $products = $repo->findByWordsNoPage(explode(" ", $words));

        return $this->json(["message" => $products], 200, [], ['groups' => 'product_search']);
    }
}
