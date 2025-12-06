<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/admindashboard', name: 'admin_dashboard')]
    public function admin(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin_dashboard.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof \App\Entity\User) {
            $this->addFlash('error', 'You must log in.');
            return $this->redirectToRoute('app_login');
        }

        $statusValue =  $user->getStatus();

        if ($statusValue !== 'active') {
            if ($statusValue === 'unverified') {
                $this->addFlash('warning', 'Your account has not been verified. Access to the panel is denied.');
            } elseif ($statusValue === 'blocked') {
                $this->addFlash('error', 'Access denied. Your account has been blocked.');
            } elseif ($statusValue === 'deleted') {
                $this->addFlash('error', 'Access denied. Your account has been deleted.');
            }

            return $this->redirectToRoute('app_login');
        }

        $user->setLastSeen(new \DateTime());
        $em->flush();

        return $this->render('dashboard.html.twig', [
            'user' => $user,
        ]);
    }
}
