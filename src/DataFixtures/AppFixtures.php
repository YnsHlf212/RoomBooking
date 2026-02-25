<?php

namespace App\DataFixtures;

use App\Entity\Equipment;
use App\Entity\Promotion;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // --- Équipements ---
        $equipmentNames = ['Vidéoprojecteur', 'Tableau blanc', 'Climatisation', 'PC fixe', 'Wifi'];
        $equipments = [];
        foreach ($equipmentNames as $name) {
            $eq = new Equipment();
            $eq->setName($name);
            $manager->persist($eq);
            $equipments[] = $eq;
        }

        // --- Promotions ---
        $promo1 = new Promotion();
        $promo1->setName('BTS SIO 1ère année');
        $promo1->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($promo1);

        $promo2 = new Promotion();
        $promo2->setName('BTS SIO 2ème année');
        $promo2->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($promo2);

        // --- Salles ---
        $roomsData = [
            ['Salle A101', 30],
            ['Salle B204', 20],
            ['Box Projet 1', 8],
            ['Labo TP', 25],
        ];
        $rooms = [];
        foreach ($roomsData as [$name, $capacity]) {
            $room = new Room();
            $room->setName($name);
            $room->setCapacity($capacity);
            $room->setIsActive(true);
            $room->setCreatedAt(new \DateTimeImmutable());
            $room->addEquipment($equipments[array_rand($equipments)]);
            $manager->persist($room);
            $rooms[] = $room;
        }

        // --- Admin ---
        $admin = new User();
        $admin->setEmail('admin@mediaschool.fr');
        $admin->setFirstName('Sophie');
        $admin->setLastName('Dupont');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin1234!'));
        $manager->persist($admin);

        // --- Coordinateur ---
        $coordinator = new User();
        $coordinator->setEmail('prof.martin@mediaschool.fr');
        $coordinator->setFirstName('Marc');
        $coordinator->setLastName('Martin');
        $coordinator->setRoles(['ROLE_COORDINATOR']);
        $coordinator->setCreatedAt(new \DateTimeImmutable());
        $coordinator->setPromotion($promo1);
        $coordinator->setPassword($this->hasher->hashPassword($coordinator, 'Coord1234!'));
        $manager->persist($coordinator);

        // --- Élève ---
        $student = new User();
        $student->setEmail('eleve.durand@mediaschool.fr');
        $student->setFirstName('Alice');
        $student->setLastName('Durand');
        $student->setRoles(['ROLE_STUDENT']);
        $student->setCreatedAt(new \DateTimeImmutable());
        $student->setPromotion($promo1);
        $student->setPassword($this->hasher->hashPassword($student, 'Student1234!'));
        $manager->persist($student);

        // --- Réservation de test ---
        $reservation = new Reservation();
        $reservation->setRoom($rooms[0]);
        $reservation->setOwner($coordinator);
        $reservation->setStartDatetime(new \DateTimeImmutable('tomorrow 09:00'));
        $reservation->setEndDatetime(new \DateTimeImmutable('tomorrow 11:00'));
        $reservation->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($reservation);

        $manager->flush();
    }
}