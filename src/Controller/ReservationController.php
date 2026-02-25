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
        ReservationRepository $reservationRepository
    ): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(Reservation1Type::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Vérifier les conflits de réservation
            $conflicts = $reservationRepository->findConflicts(
                $reservation->getRoom(),
                $reservation->getStartDatetime(),
                $reservation->getEndDatetime()
            );

            if (count($conflicts) > 0) {
                $this->addFlash('error', 'Cette salle est déjà réservée sur ce créneau. Veuillez choisir un autre horaire.');
                return $this->render('reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            // Vérifier que la date de fin est après la date de début
            if ($reservation->getEndDatetime() <= $reservation->getStartDatetime()) {
                $this->addFlash('error', 'La date de fin doit être après la date de début.');
                return $this->render('reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            // Vérifier que la réservation n'est pas dans le passé
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
}
