<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\OrderAdminType;
use App\Form\OrderType;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route("/orders/{page}", name: "orders", requirements: ['page' => '\d+'])]
    public function index(OrderRepository $repo, int $page) : Response
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
        
        $orders = $repo->findByPage($page, $nbPages);

        return $this->render("order/index.html.twig",
        [
            "orders" => $orders,
            "page" => $page,
            "nbPages" => $nbPages
        ]);
    }
    
    #[Route('/order/add', name: 'add_order')]
    public function add(Request $req, ProductRepository $repo, EntityManagerInterface $em): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        $order = new Order();
        $order->setUser($this->getUser())
            ->setAddress($this->getUser()->getAddress())
            ->setZipCode($this->getUser()->getZipCode())
            ->setCity($this->getUser()->getCity())
            ->setPhoneNumber($this->getUser()->getPhoneNumber())
            ->setCreatedAt(new \DateTime());
        
        $session = $req->getSession();
        $cart = $session->get("cart");
        $products = [];

        foreach($cart as $key => $value)
        {
            $product = $repo->find($key);

            $products[] = ["product" => $product, "quantity" => $value];

            $orderItem = new OrderItem();
            $orderItem->setProduct($product)
                ->setQuantity($value)
                ->setUnitPrice($product->getPrice());
            
            $order->addOrderItem($orderItem);
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($order);

            $session->set("cart", []);

            foreach($products as $product)
            {
                $product["product"]->setNbCopies($product["product"]->getNbCopies() - $product["quantity"]);
            }

            $em->flush();

            $this->addFlash("success", "Commande passée !");

            return $this->redirectToRoute("app_shop");
        }
        
        return $this->render('order/add.html.twig',
        [
            "form" => $form,
            "products" => $products
        ]);
    }

    #[Route("/order/view/{id}", name: "view_order")]
    public function view(OrderRepository $repo, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        $order = $repo->find($id);
        
        return $this->render('order/view.html.twig',
        [
            "order" => $order
        ]);
    }

    #[Route("/order/view_admin/{id}", name: "view_admin_order")]
    public function viewAdmin(OrderRepository $repo, int $id): Response
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
        
        $order = $repo->find($id);
        
        return $this->render('order/view_admin.html.twig',
        [
            "order" => $order
        ]);
    }

    #[Route('/order/add_admin', name: 'add_admin_order')]
    public function addAdmin(Request $req, UserRepository $userRepo, ProductRepository $prodRepo, EntityManagerInterface $em): Response
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

        $users = $userRepo->findAll();
        
        $order = new Order();

        $form = $this->createForm(OrderAdminType::class, $order);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $username = $form->get("userInput")->getData();
            $user = $userRepo->findOneBy(["username" => $username]);

            if(!$user)
            {
                $this->addFlash("error", "Client introuvable");
                return $this->redirectToRoute("orders");
            }

            $selectedIds = $form->get("selectProducts")->getData();

            $idsProd = explode(",", $selectedIds);

            foreach($idsProd as $idProd)
            {
                $product = $prodRepo->find($idProd);
                $orderItem = new OrderItem();

                $orderItem->setProduct($product)
                    ->setQuantity(1)
                    ->setUnitPrice($product->getPrice());
                
                $order->addOrderItem($orderItem);
            }
            
            $order->setUser($user);
            
            $em->persist($order);
            $em->flush();

            $this->addFlash("success", "Commande ajoutée");

            return $this->redirectToRoute("orders", ["page" => 1]);
        }
        
        return $this->render('order/add_admin.html.twig',
        [
            "form" => $form,
            "users" => $users
        ]);
    }

    #[Route('/order/edit_admin/{id}', name: 'edit_admin_order')]
    public function editAdmin(Request $req, UserRepository $userRepo, ProductRepository $prodRepo, OrderRepository $orderRepo, int $id, EntityManagerInterface $em): Response
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

        $users = $userRepo->findAll();
        
        $order = $orderRepo->find($id);

        $form = $this->createForm(OrderAdminType::class, $order);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $username = $form->get("userInput")->getData();
            $user = $userRepo->findOneBy(["username" => $username]);

            if(!$user)
            {
                $this->addFlash("error", "Client introuvable");
                return $this->redirectToRoute("orders");
            }

            $selectedIds = $form->get("selectProducts")->getData();

            $idsProd = explode(",", $selectedIds);

            foreach($idsProd as $idProd)
            {
                $product = $prodRepo->find($idProd);
                $orderItem = new OrderItem();

                if (!$order->getOrderItems()->contains($orderItem))
                {
                    $orderItem->setProduct($product)
                        ->setQuantity(1)
                        ->setUnitPrice($product->getPrice());
                    
                    $order->addOrderItem($orderItem);
                }
            }

            foreach ($order->getOrderItems() as $item)
            {
                if(!in_array($item->getProduct()->getId(), $idsProd))
                {
                    $order->removeOrderItem($item);
                }
            }
            
            $order->setUser($user);
            
            $em->persist($order);
            $em->flush();

            $this->addFlash("success", "Commande mise à jour");

            return $this->redirectToRoute("orders", ["page" => 1]);
        }
        
        return $this->render('order/edit_admin.html.twig',
        [
            "form" => $form,
            "users" => $users,
            "order" => $order
        ]);
    }

    #[Route("/order/delete_admin/{id}", name: "delete_admin_order")]
    public function deleteAdmin(OrderRepository $repo, int $id, EntityManagerInterface $em): Response
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

        $order = $repo->find($id);

        $em->remove($order);
        $em->flush();

        $this->addFlash("success", "Commande supprimée");

        return $this->redirectToRoute("orders", ["page" => 1]);
    }
}
