<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{

    #[Route('/reset_password/{token}', name: 'app_reset_password')]
    public function resetPassword( 
        Request $request, string $token, UserRepository $userRepository, 
        UserPasswordHasherInterface $hasher, EntityManagerInterface $em 
        ): Response {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getTokenExpiresAt() < new \DateTime()) {
            throw $this->createNotFoundException('The link is invalid');
        }

        $newPassword = $request->request->get('password');
        $user->setPassword($hasher->hashPassword($user, $newPassword));
        $user->setResetToken(null);
        $user->setTokenExpiresAt(null);

        $em->flush();

        $this->addFlash('success', 'Password updated successfully');
        return $this->redirectToRoute('app_login');
    }
}
