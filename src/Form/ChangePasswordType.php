<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('old_password', PasswordType::class,
            [
                'label' => 'Ancien mot de passe',
                'mapped' => false,
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer un nom d\'utilisateur'])
                ]
            ])
            ->add('new_password', PasswordType::class,
            [
                'label' => 'Nouveau mot de passe',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Assert\Length([
                        'min' => 4,
                        'minMessage' => 'Veuillez entrer au moins 4 caractères',
                    ]),
                ],
            ])
            ->add('confirm_password', PasswordType::class,
            [
                'label' => 'Confirmez le mot de passe',
                'mapped' => false,
            ])
            ->add('save', SubmitType::class,
            [
                'label' => 'Mettre à jour',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            //'data_class' => User::class,
        ]);
    }
}
