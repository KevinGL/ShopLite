<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    private PasswordHasherFactoryInterface $hasher;

    public function __construct(PasswordHasherFactoryInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $req, OrderRepository $orderRepo, UserRepository $userRepo, EntityManagerInterface $em): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        $orders = $orderRepo->findByUser($this->getUser());

        $user = $userRepo->find($this->getUser()->getId());

        $profileForm = $this->createForm(UserType::class, $user);
        $profileForm->handleRequest($req);

        if($profileForm->isSubmitted() && $profileForm->isValid())
        {
            $em->persist($user);
            $em->flush();

            $this->addFlash("success", "Profil mis à jour");
        }

        $passwordForm = $this->createForm(ChangePasswordType::class);
        $passwordForm->handleRequest($req);

        if($passwordForm->isSubmitted() && $passwordForm->isValid())
        {
            $newPassword = $passwordForm->get('new_password')->getData();
            $confirmPassword = $passwordForm->get('confirm_password')->getData();
            $oldPassword = $passwordForm->get('old_password')->getData();
            
            if($newPassword != $confirmPassword)
            {
                $this->addFlash("error", "Les mots de passe ne correspondent pas");
            }

            else            
            if(!password_verify($oldPassword, $user->getPassword()))
            {
                $this->addFlash("error", "Mauvais mot de passe entré");
            }

            else
            {
                $user->setPassword($this->hasher->getPasswordHasher($user)->hash($newPassword));

                $em->persist($user);
                $em->flush();

                $this->addFlash("success", "Mot de passe mis à jour");
            }
        }
        
        return $this->render('profile/index.html.twig',
        [
            "profileForm" => $profileForm,
            "passwordForm" => $passwordForm,
            "orders" => $orders
        ]);
    }
}
