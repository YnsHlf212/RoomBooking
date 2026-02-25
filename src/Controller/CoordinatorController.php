<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/coordinator')]
#[IsGranted('ROLE_COORDINATOR')]
class CoordinatorController extends AbstractController
{
    #[Route('/', name: 'app_coordinator_dashboard')]
    public function index(): Response
    {
        return $this->render('coordinator/index.html.twig');
    }
}
