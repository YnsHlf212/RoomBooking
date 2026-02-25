<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'name',
                'label' => 'Salle',
                'constraints' => [new NotBlank(message: 'Veuillez choisir une salle')]
            ])
            ->add('startDatetime', DateTimeType::class, [
                'label' => 'Début',
                'widget' => 'single_text',
                'constraints' => [new NotBlank(message: 'Veuillez choisir une date de début')]
            ])
            ->add('endDatetime', DateTimeType::class, [
                'label' => 'Fin',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(message: 'Veuillez choisir une date de fin'),
                    new GreaterThan(
                        propertyPath: 'parent.all[startDatetime].data',
                        message: 'La fin doit être après le début'
                    )
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'reservation_item',
        ]);
    }
}