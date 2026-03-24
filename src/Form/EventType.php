<?php

namespace App\Form;

use App\Entity\Adress;
use App\Entity\Campus;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie : ',
            ])
            ->add('dateStart', DateTimeType::class, [
                'label' => 'Date et heure de la sortie : ',
                'widget' => 'single_text',
            ])
            ->add('duration', IntegerType::class, [
                'label' => "Durée : ",
            ])
            ->add('deadline', DateTimeType::class, [
                'label' => "Date limite d'inscription : ",
            ])
            ->add('maxIscription', IntegerType::class, [
                'label' => "Nombre de places : "
            ])
            ->add('eventInfo', TextareaType::class, [
                'label' => "Déscription et infos : ",
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder'=> 'Choisir une catégorie',
            ])

            ->add('adress', EntityType::class, [
                'class' => Adress::class,
                'choice_label' => 'id',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
