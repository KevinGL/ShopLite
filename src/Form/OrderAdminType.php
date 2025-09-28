<?php

namespace App\Form;

use App\Entity\Order;
use App\Repository\ProductRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class OrderAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createdAt', DateType::class,
            [
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer une date'])
                ]
            ])
            ->add('userInput', TextType::class,
            [
                "attr" => ["list" => "list_users"],
                'mapped' => false,
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez sélectionner un client'])
                ]
            ])
            ->add("address", TextType::class)
            ->add("zipCode", TextType::class)
            ->add("city", TextType::class)
            ->add("phoneNumber", TextType::class)
            ->add("selectProducts", HiddenType::class,
            [
                'mapped' => false,
                "attr" => ["id" => "select_products"],
            ])
            ->add("save", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
