<?php

namespace App\Form;

use App\Entity\Promotion;
use App\Entity\Reservation;
use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class Reservation1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Salle — affiche le nom au lieu de l'ID
            ->add('room', EntityType::class, [
                'class'        => Room::class,
                'choice_label' => 'name',
                'label'        => 'Salle',
                'placeholder'  => '-- Choisir une salle --',
                'constraints'  => [new NotBlank(message: 'Veuillez choisir une salle')],
            ])

            // Date de début
            ->add('startDatetime', null, [
                'widget' => 'single_text',
                'label'  => 'Date et heure de début',
            ])

            // Date de fin
            ->add('endDatetime', null, [
                'widget' => 'single_text',
                'label'  => 'Date et heure de fin',
            ])

            // Promotion concernée (remplace bookedFor)
            ->add('bookedFor', EntityType::class, [
                'class'        => Promotion::class,
                'choice_label' => 'name',
                'label'        => 'Promotion concernée',
                'placeholder'  => '-- Aucune promotion --',
                'required'     => false,
            ])

        // createdAt → géré automatiquement dans le controller
        // cancelledAt → géré lors de l'annulation uniquement
        // owner → assigné automatiquement à l'utilisateur connecté
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}