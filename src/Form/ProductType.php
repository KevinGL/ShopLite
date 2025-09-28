<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,
            [
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer un nom'])
                ]
            ])
            ->add('image', HiddenType::class)
            ->add('price', NumberType::class,
            [
                'html5' => true,
                'attr' => ['min' => 0, "step" => 0.01],
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer un prix']),
                    new Type(["type" => "float", "message" => "Le prix doit être un nombre décimal"])
                ]
            ])
            ->add('description', TextareaType::class)
            ->add('nbCopies', NumberType::class,
            [
                'html5' => true,
                'attr' => ['min' => 0],
                "constraints" =>
                [
                    new NotBlank(['message' => 'Veuillez entrer un nombre d\'exemplaires'])
                ]
            ])
            ->add('categories', EntityType::class,
            [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
