<?php

namespace App\Form;

use App\Form\Model\CitySearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Rechercher une ville ...',
                ],
                'required' => false,
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Rechercher',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CitySearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
