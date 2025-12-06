<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET'])]
    public function showForgotPasswordForm(): Response
    {
        return $this->render('forgot_password.html.twig');
    }

    #[Route('/forgot-password', name: 'app_forgot_password_post', methods: ['POST'])]
    public function handleForgotPassword(
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer,
        EntityManagerInterface $em
    ): Response {
        $email = $request->request->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('danger', 'User not found');
            return $this->redirectToRoute('app_forgot_password');
        }

        $token = bin2hex(random_bytes(32));
        $user->setResetToken($token);
        $user->setTokenExpiresAt(new \DateTime('+1 hour'));
        $em->flush();

        $resetLink = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        $emailMessage = (new Email())
            ->from('noreply@yourapp.com')
            ->to($user->getEmail())
            ->subject('Password recovery')
            ->html("<p>Follow the link to reset your password: <a href='$resetLink'>$resetLink</a></p>");

        $mailer->send($emailMessage);

        $this->addFlash('success', 'The link has been sent to your email.');
        return $this->redirectToRoute('app_login');
    }
}
