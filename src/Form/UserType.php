<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
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
{    public function __construct(private Security $security) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Pseudo : '
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => false,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'first_options' => [
                    'label' => 'Mot de passe : ',
                    'required' => false,
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe : ',
                    'required' => false,
                ],
            ]);
        $builder
            ->add('lastname', TextType::class, [
                'label' => 'Nom : '
            ])
            ->add('name', TextType::class, [
                'label' => 'Prenom : '
            ])
            ->add('phone', TextType::class, [
                'label' => 'Numéro de téléphone : '
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email : '
            ])
            ->add('student', ChoiceType::class, [
                'label'=> false,
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
                'placeholder' => false,
            ])
            ->add('photo', FileType::class, [
                'label' => 'Ma photo : ',
                'mapped' => false,
                'required' => false,
            ]);

            if ($this->security->isGranted('ROLE_ADMIN')) {
                $builder->add('roles', ChoiceType::class, [
                    'choices' => [
                        'Admin' => 'ROLE_ADMIN',
                        'Utilisateur' => 'ROLE_USER',
                    ],
                    'label' => false,
                    'multiple' => true,
                    'expanded' => true,

                ]);
            }


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'required' => false,
        ]);
    }
}
