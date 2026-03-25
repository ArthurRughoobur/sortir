<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Pseudo : '
            ])

//            ->add('password', passwordType::class, [
//                'label1' => 'Mot de passe : ',
//                'label2' => 'Vérification du mot de passe : ',
//
//            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => 'Mot de passe : '],
                'second_options' => ['label' => 'Mot de passe : '],

            ]);
        $builder
            ->add('lastname', TextType::class, [
                'label' => 'Nom : '
            ])
            ->add('name', TextType::class, [
                'label' => 'Prenom : '
            ])
            ->add('phone', TextType::class, [
                'label'=> 'Numéro de téléphone : '
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email : '
            ])

            ->add('student', ChoiceType::class, [
                'choices' => [
                    'Elève' => true,
                    'Ancien élève' => false,
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'placeholder' => 'Campus',
            ])
            ->add('photo', FileType::class, [
                'label' => 'Ma photo : ',
                'mapped' => false,
                'required' => false,
            ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
