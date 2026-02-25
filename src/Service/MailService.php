<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendReservationConfirmation(Reservation $reservation): void
    {
        $user = $reservation->getOwner();

        $email = (new Email())
            ->from('noreply@roombooking.fr')
            ->to($user->getEmail())
            ->subject('Confirmation de votre réservation — RoomBooking')
            ->html($this->buildReservationEmail($reservation));

        $this->mailer->send($email);
    }

    public function sendReservationCancellation(Reservation $reservation, User $cancelledBy): void
    {
        $user = $reservation->getOwner();

        $email = (new Email())
            ->from('noreply@roombooking.fr')
            ->to($user->getEmail())
            ->subject('Annulation de votre réservation — RoomBooking')
            ->html($this->buildCancellationEmail($reservation, $cancelledBy));

        $this->mailer->send($email);
    }

    private function buildReservationEmail(Reservation $reservation): string
    {
        return "
            <h1>Réservation confirmée ✅</h1>
            <p>Bonjour {$reservation->getOwner()->getFirstName()},</p>
            <p>Votre réservation a bien été enregistrée :</p>
            <ul>
                <li><strong>Salle :</strong> {$reservation->getRoom()->getName()}</li>
                <li><strong>Début :</strong> {$reservation->getStartDatetime()->format('d/m/Y H:i')}</li>
                <li><strong>Fin :</strong> {$reservation->getEndDatetime()->format('d/m/Y H:i')}</li>
            </ul>
            <p>À bientôt sur RoomBooking !</p>
        ";
    }

    private function buildCancellationEmail(Reservation $reservation, User $cancelledBy): string
    {
        return "
            <h1>Réservation annulée ❌</h1>
            <p>Bonjour {$reservation->getOwner()->getFirstName()},</p>
            <p>Votre réservation a été annulée par {$cancelledBy->getFirstName()} {$cancelledBy->getLastName()} :</p>
            <ul>
                <li><strong>Salle :</strong> {$reservation->getRoom()->getName()}</li>
                <li><strong>Début :</strong> {$reservation->getStartDatetime()->format('d/m/Y H:i')}</li>
                <li><strong>Fin :</strong> {$reservation->getEndDatetime()->format('d/m/Y H:i')}</li>
            </ul>
            <p>À bientôt sur RoomBooking !</p>
        ";
    }
}