<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Form\DeleteProfileType;
use App\Form\UserType;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ProfileController extends AbstractController
{
    private PasswordHasherFactoryInterface $hasher;

    public function __construct(PasswordHasherFactoryInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $req, TokenStorageInterface $tokenStorage, OrderRepository $orderRepo, UserRepository $userRepo, EntityManagerInterface $em): Response
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

        $deleteProfileForm = $this->createForm(DeleteProfileType::class);
        $deleteProfileForm->handleRequest($req);

        if($deleteProfileForm->isSubmitted() && $deleteProfileForm->isValid())
        {
            $password = $deleteProfileForm->get("password")->getData();
            $passwordConfirm = $deleteProfileForm->get("confirm_password")->getData();
            
            if($password != $passwordConfirm)
            {
                $this->addFlash("error", "Les mots de passe ne concordent pas");
            }

            else            
            if(!password_verify($password, $user->getPassword()) || !password_verify($passwordConfirm, $user->getPassword()))
            {
                $this->addFlash("error", "Mauvais mot de passe entré");
            }
            
            else
            {
                $user = $this->getUser();

                $tokenStorage->setToken(null);
                $session = $req->getSession();
                $session->invalidate();

                $em->remove($user);
                $em->flush();

                return $this->redirectToRoute("app_home");
            }
        }
        
        return $this->render('profile/index.html.twig',
        [
            "profileForm" => $profileForm,
            "passwordForm" => $passwordForm,
            "deleteProfileForm" => $deleteProfileForm,
            "orders" => $orders
        ]);
    }
}
