<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $repo): Response
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
        
        $categories = $repo->findAll();
        
        return $this->render('category/index.html.twig',
        [
            'categories' => $categories
        ]);
    }

    #[Route("/category/add", name: "add_category")]
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

        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($category);
            $em->flush();

            $this->addFlash("success", "Catégorie ajoutée");
            return $this->redirectToRoute("app_category");
        }

        return $this->render("category/add.html.twig",
        [
            "form" => $form
        ]);
    }

    #[Route("/category/edit/{id}", name: "edit_category")]
    public function edit(Request $req, CategoryRepository $repo, int $id, EntityManagerInterface $em): Response
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

        $category = $repo->find($id);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($category);
            $em->flush();

            $this->addFlash("success", "Catégorie mise à jour");
            return $this->redirectToRoute("app_category");
        }

        return $this->render("category/edit.html.twig",
        [
            "form" => $form
        ]);
    }

    #[Route("/category/delete/{id}", name: "delete_cat")]
    public function delete(CategoryRepository $repo, int $id, EntityManagerInterface $em): Response
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
        
        $cat = $repo->find($id);

        $em->remove($cat);
        $em->flush();

        $this->addFlash("success", "Catégorie supprimée");

        return $this->redirectToRoute("app_category");
    }
}
