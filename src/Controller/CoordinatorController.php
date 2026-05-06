<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/coordinator')]
#[IsGranted('ROLE_COORDINATOR')]
class CoordinatorController extends AbstractController
{
    #[Route('/', name: 'app_coordinator_dashboard')]
    public function index(PromotionRepository $promotionRepository): Response
    {
        return $this->render('coordinator/index.html.twig', [
            'promotions' => $promotionRepository->findAll(),
        ]);
    }

    #[Route('/promotion/{id}', name: 'app_coordinator_promotion_show', methods: ['GET'])]
    public function showPromotion(int $id, PromotionRepository $promotionRepository): Response
    {
        $promotion = $promotionRepository->find($id);

        if (!$promotion) {
            throw $this->createNotFoundException('Promotion introuvable.');
        }

        return $this->render('coordinator/promotion_show.html.twig', [
            'promotion' => $promotion,
        ]);
    }

    #[Route('/promotion/{id}/student/new', name: 'app_coordinator_student_new', methods: ['GET', 'POST'])]
    public function newStudent(
        int $id,
        Request $request,
        PromotionRepository $promotionRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher
    ): Response
    {
        $promotion = $promotionRepository->find($id);

        if (!$promotion) {
            throw $this->createNotFoundException('Promotion introuvable.');
        }

        $student = new User();
        $student->setRoles(['ROLE_STUDENT']);
        $student->setPromotion($promotion);

        $form = $this->createForm(UserType::class, $student, [
            'require_password' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $student->setPassword($hasher->hashPassword($student, $plainPassword));
            $student->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($student);
            $entityManager->flush();

            $this->addFlash('success', 'Étudiant ajouté à la promotion avec succès !');
            return $this->redirectToRoute('app_coordinator_promotion_show', ['id' => $id]);
        }

        return $this->render('coordinator/student_new.html.twig', [
            'promotion' => $promotion,
            'form'      => $form,
        ]);
    }

    #[Route('/promotion/{promotionId}/student/{studentId}/remove', name: 'app_coordinator_student_remove', methods: ['POST'])]
    public function removeStudent(
        int $promotionId,
        int $studentId,
        Request $request,
        PromotionRepository $promotionRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $promotion = $promotionRepository->find($promotionId);
        $student   = $entityManager->getRepository(User::class)->find($studentId);

        if (!$promotion || !$student) {
            throw $this->createNotFoundException('Promotion ou étudiant introuvable.');
        }

        if ($this->isCsrfTokenValid('remove'.$studentId, $request->getPayload()->getString('_token'))) {
            // Retirer l'étudiant de la promotion sans le supprimer
            $student->setPromotion(null);
            $entityManager->flush();
            $this->addFlash('success', 'Étudiant retiré de la promotion.');
        }

        return $this->redirectToRoute('app_coordinator_promotion_show', ['id' => $promotionId]);
    }
}