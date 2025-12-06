<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;


class ConfirmationSuccessController extends AbstractController
{

    #[Route('/confirm/{token}', name: 'app_confirm_email')]
    public function confirm(string $token, UserRepository $userRepo, EntityManagerInterface $em): Response
    {
        $user = $userRepo->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'The link is invalid or already used.');
            return $this->redirectToRoute('app_login');
        }

        $user->setStatus('active');
        $user->setConfirmationToken(null);
        $em->flush();

        $this->addFlash('success', 'Account successfully verified!');
        return $this->redirectToRoute('app_login');
    }
}
