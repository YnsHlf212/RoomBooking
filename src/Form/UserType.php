<?php

namespace App\Form;

use App\Entity\Promotion;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('roles', ChoiceType::class, [
                'label'    => 'Rôle',
                'choices'  => [
                    'Étudiant'      => 'ROLE_STUDENT',
                    'Coordinateur'  => 'ROLE_COORDINATOR',
                    'Administrateur'=> 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('promotion', EntityType::class, [
                'class'        => Promotion::class,
                'choice_label' => 'name',
                'required'     => false,
                'placeholder'  => '-- Aucune promotion --',
                'label'        => 'Promotion',
            ]);

        if ($options['require_password']) {
            $builder->add('plainPassword', PasswordType::class, [
                'mapped'      => false,
                'label'       => 'Mot de passe',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8]),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'       => User::class,
            'require_password' => true,
        ]);
    }
}