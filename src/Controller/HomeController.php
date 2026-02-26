<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[IsGranted('ROLE_USER')]
    public function index(
        ReservationRepository $reservationRepository,
        RoomRepository $roomRepository
    ): Response
    {
        $user = $this->getUser();
        $now = new \DateTimeImmutable();

        // Réservations à venir de l'utilisateur
        $upcomingReservations = $reservationRepository->findUpcomingByUser($user);

        // Réservations passées de l'utilisateur
        $pastReservations = $reservationRepository->findPastByUser($user);

        // Salles disponibles
        $availableRooms = $roomRepository->findBy(['isActive' => true]);

        // Stats pour l'admin
        $stats = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $stats = [
                'totalRooms'        => count($availableRooms),
                'totalReservations' => count($reservationRepository->findAll()),
                'todayReservations' => count($reservationRepository->findTodayReservations()),
            ];
        }

        return $this->render('home/index.html.twig', [
            'upcomingReservations' => $upcomingReservations,
            'pastReservations'     => $pastReservations,
            'availableRooms'       => $availableRooms,
            'stats'                => $stats,
        ]);
    }
}