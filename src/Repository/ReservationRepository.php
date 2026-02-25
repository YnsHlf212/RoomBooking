<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Vérifie si une salle est déjà réservée sur un créneau donné
     */
    public function findConflicts(Room $room, \DateTimeImmutable $start, \DateTimeImmutable $end, ?int $excludeId = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.room = :room')
            ->andWhere('r.cancelledAt IS NULL')
            ->andWhere('r.startDatetime < :end')
            ->andWhere('r.endDatetime > :start')
            ->setParameter('room', $room)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($excludeId) {
            $qb->andWhere('r.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère toutes les réservations actives d'une salle
     */
    public function findActiveByRoom(Room $room): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.room = :room')
            ->andWhere('r.cancelledAt IS NULL')
            ->andWhere('r.endDatetime > :now')
            ->setParameter('room', $room)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('r.startDatetime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}