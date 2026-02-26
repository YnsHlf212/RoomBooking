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

    public function findUpcomingByUser(mixed $user): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.owner = :user')
            ->andWhere('r.startDatetime >= :now')
            ->andWhere('r.cancelledAt IS NULL')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('r.startDatetime', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    public function findPastByUser(mixed $user): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.owner = :user')
            ->andWhere('r.endDatetime < :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('r.startDatetime', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    public function findTodayReservations(): array
    {
        $start = new \DateTimeImmutable('today 00:00:00');
        $end   = new \DateTimeImmutable('today 23:59:59');

        return $this->createQueryBuilder('r')
            ->where('r.startDatetime BETWEEN :start AND :end')
            ->andWhere('r.cancelledAt IS NULL')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }
}