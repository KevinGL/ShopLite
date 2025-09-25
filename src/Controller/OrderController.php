<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\OrderType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
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

        foreach($cart as $key => $value)
        {
            $product = $repo->find($key);

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
            $em->flush();

            $session->set("cart", []);

            $this->addFlash("success", "Commande passée !");

            return $this->redirectToRoute("app_shop");
        }
        
        return $this->render('order/add.html.twig',
        [
            "form" => $form
        ]);
    }
}
