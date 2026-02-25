<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use App\Repository\RoomRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/room')]
#[IsGranted('ROLE_USER')]
final class RoomController extends AbstractController
{
    // Tout le monde peut voir la liste des salles
    #[Route(name: 'app_room_index', methods: ['GET'])]
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('room/index.html.twig', [
            'rooms' => $roomRepository->findBy(['isActive' => true]),
        ]);
    }

    // Seul l'admin peut créer une salle
    #[Route('/new', name: 'app_room_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $room->setCreatedAt(new \DateTimeImmutable());
            $room->setIsActive(true);
            $entityManager->persist($room);
            $entityManager->flush();

            $this->addFlash('success', 'Salle créée avec succès !');
            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('room/new.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    // Tout le monde peut voir le détail d'une salle
    #[Route('/{id}', name: 'app_room_show', methods: ['GET'])]
    public function show(Room $room, ReservationRepository $reservationRepository): Response
    {
        return $this->render('room/show.html.twig', [
            'room' => $room,
            'reservations' => $reservationRepository->findActiveByRoom($room),
        ]);
    }

    // Seul l'admin peut modifier une salle
    #[Route('/{id}/edit', name: 'app_room_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Room $room, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Salle modifiée avec succès !');
            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('room/edit.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    // Seul l'admin peut supprimer une salle
    #[Route('/{id}', name: 'app_room_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Room $room, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->getPayload()->getString('_token'))) {
            
            // Vérifier qu'il n'y a pas de réservations actives
            if (!$room->getReservations()->isEmpty()) {
                $this->addFlash('error', 'Impossible de supprimer une salle avec des réservations actives.');
                return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
            }

            $entityManager->remove($room);
            $entityManager->flush();
            $this->addFlash('success', 'Salle supprimée avec succès !');
        }

        return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
    }
}