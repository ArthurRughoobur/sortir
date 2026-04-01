<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Category;
use App\Form\Model\EventSearch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'label' => 'Campus : ',
                'required' => false,
                'placeholder' => 'Tous les campus',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => false,
                'placeholder' => 'Choisissez une catégorie',
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'Sortie : ',
                'attr' => [
                    'placeholder' => 'Rechercher....',
                ],
                'required' => false,
            ])
            ->add('dateStart', DateType::class, [
                'label' => 'Date de la sortie : ',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('deadline', DateType::class, [
                'label' => "Date limite : ",
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('organizer', CheckboxType::class, [
                'label' => "J'organise la sortie ",
                'required' => false,
            ])
            ->add('registered', CheckboxType::class, [
                'label' => 'Je suis inscrit(e) ',
                'required' => false,
            ])
            ->add('notRegistered', CheckboxType::class, [
                'label' => 'Je ne suis pas inscrit(e) ',
                'required' => false,
            ])
            ->add('terminee', CheckboxType::class, [
                'label' => 'Sorties passées ',
                'required' => false,
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Rechercher',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
