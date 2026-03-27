<?php

namespace App\Form;

use App\Entity\Adress;
use App\Entity\Category;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie : ',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la sortie est requis']),
                ],
            ])
            ->add('dateStart', DateTimeType::class, [
                'label' => 'Date et heure de la sortie : ',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date de sortie est requise']),
                ],
            ])

            ->add('duration', IntegerType::class, [
                'label' => 'Durée : ',
                'constraints' => [
                    new NotBlank(['message' => 'Les minutes sont requises']),
                ],
            ])
            ->add('deadline', DateType::class, [
                'label' => "Date limite d'inscription : ",
                'constraints' => [
                    new NotBlank(['message' => 'La date limite est requise']),
                ],
            ])
            ->add('maxIscription', IntegerType::class, [
                'label' => "Nombre de places : ",
                'constraints' => [
                    new NotBlank(['message' => 'Le nombre de places est requis']),
                ],
            ])
            ->add('eventInfo', TextareaType::class, [
                'label' => "Déscription et infos : ",
                'constraints' => [
                    new NotBlank(['message' => 'La description est requise']),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie',
                'constraints' => [
                    new NotBlank(['message' => 'La catégorie est requise']),
                ],
            ])
            ->add('adress', EntityType::class, [
                'class' => Adress::class,
                'choice_label' => 'name',
                'label' => 'Lieu :',
                'placeholder' => 'Choisir une adresse',
                'constraints' => [
                    new NotBlank(['message' => 'L\'adresse est requise']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
