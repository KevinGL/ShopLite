<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class,
            [
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer un nom d\'utilisateur'])
                ]
            ])
            ->add('email', EmailType::class,
            [
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email']),
                    new Email(['message' => 'Veuillez entrer une adresse email valide'])
                ]
            ])
            ->add('address', TextType::class,
            [
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer une adresse postale'])
                ]
            ])
            ->add('zipCode', TextType::class,
            [
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer un code postal']),
                    new Regex([
                        'pattern' => '/^\d{5}$/',
                        'message' => 'Veuillez entrer un code postal valide (5 chiffres)',
                    ]),
                ]
            ])
            ->add('city', TextType::class,
            [
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer une ville'])
                ]
            ])
            ->add('phoneNumber', TextType::class,
            [
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer un numéro de téléphone']),
                    new Regex([
                        'pattern' => '/^\d{10}$/',
                        'message' => 'Veuillez entrer un numéro valide (10 chiffres)',
                    ]),
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez approuver les termes',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez centrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 4,
                        'minMessage' => 'Votre mot de passe doit comporter au minimum 4 caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
