<?php

namespace App\Form;

use App\Entity\Adress;
use App\Entity\City;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class CityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('zipCode')
        ;
    }
//    public function buildForm(FormBuilderInterface $builder, array $options): void
//    {   $builder = new DynamicFormBuilder($builder);
//        $builder
//            ->add('city', EntityType::class, [
//                'class' => City::class,
//                'choice_label' => static fn (City $city): string => $city->getName(),
//                'placeholder' => 'Choisissez une ville',
//                'autocomplete' => 'true',
//            ])
////            ->add('name')
////            ->add('zipCode')
//            ->addDependent('adress', 'city', static function (DependentField $field, ?City $city) {
//                $field->add(EntityType::class, [
//                    'class' => Adress::class,
//                    'placeholder' => 'Choisissez un lieu',
//                    'choices' => $city->getAdresses(),
//                    'choice_label' => static fn (Adress $adress): string => $adress->getName(),
//                    'disabled' => null === $city,
//                    'autocomplete' => 'true',
//                ]);
//            })
//        ;
//    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
