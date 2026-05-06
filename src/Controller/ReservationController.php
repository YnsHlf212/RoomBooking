<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\Reservation1Type;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\MailService;
use App\Entity\Room;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        MailService $mailService
    ): Response
    {
        $reservation = new Reservation();

        // Pré-sélectionner la salle si passée en paramètre URL
        $roomId = $request->query->get('room');
        if ($roomId) {
            $room = $entityManager->getRepository(Room::class)->find($roomId);
            if ($room) {
                $reservation->setRoom($room);
            }
        }

        $form = $this->createForm(Reservation1Type::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $conflicts = $reservationRepository->findConflicts(
                $reservation->getRoom(),
                $reservation->getStartDatetime(),
                $reservation->getEndDatetime()
            );

            if (count($conflicts) > 0) {
                $this->addFlash('error', 'Cette salle est déjà réservée sur ce créneau.');
                return $this->render('reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            if ($reservation->getEndDatetime() <= $reservation->getStartDatetime()) {
                $this->addFlash('error', 'La date de fin doit être après la date de début.');
                return $this->render('reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            if ($reservation->getStartDatetime() < new \DateTimeImmutable()) {
                $this->addFlash('error', 'Impossible de réserver dans le passé.');
                return $this->render('reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            $reservation->setOwner($this->getUser());
            $reservation->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($reservation);
            $entityManager->flush();

            // Envoi de l'email de confirmation (optionnel)
            try {
                $mailService->sendReservationConfirmation($reservation);
            } catch (\Exception $e) {
                // On ne bloque pas si l'email échoue
            }

            $this->addFlash('success', 'Réservation créée avec succès !');
            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository
    ): Response
    {
        if ($reservation->getOwner() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette réservation.');
        }

        $form = $this->createForm(Reservation1Type::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifier les conflits en excluant la réservation actuelle
            $conflicts = $reservationRepository->findConflicts(
                $reservation->getRoom(),
                $reservation->getStartDatetime(),
                $reservation->getEndDatetime(),
                $reservation->getId() // on exclut la résa en cours d'édition
            );

            if (count($conflicts) > 0) {
                $this->addFlash('error', 'Cette salle est déjà réservée sur ce créneau.');
                return $this->render('reservation/edit.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Réservation modifiée avec succès !');
            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Vérifier que l'user est bien le propriétaire ou admin
        if ($reservation->getOwner() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette réservation.');
        }

        // Vérifier que la réservation n'est pas déjà annulée
        if ($reservation->getCancelledAt()) {
            $this->addFlash('error', 'Cette réservation est déjà annulée.');
            return $this->redirectToRoute('app_reservation_show', ['id' => $reservation->getId()]);
        }

        if ($this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            // On met à jour la date d'annulation au lieu de supprimer
            $reservation->setCancelledAt(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Réservation annulée avec succès.');
        }

        return $this->redirectToRoute('app_reservation_index');
    }
}
