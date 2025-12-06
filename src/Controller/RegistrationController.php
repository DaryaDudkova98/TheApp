<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        if ($request->isMethod('POST')) {
            $emailInput = $request->request->get('email');
            $passwordInput = $request->request->get('password');

            if (!$emailInput || !$passwordInput) {
                return new Response('Email и пароль обязательны', 400);
            }

            $user = new User();
            $user->setEmail($emailInput);

            $hashedPassword = $passwordHasher->hashPassword($user, $passwordInput);
            $user->setPassword($hashedPassword);
            $user->setFirstName($request->request->get('first_name'));
            $user->setLastName($request->request->get('last_name'));
            $user->setStatus('unverified');
            $token = Uuid::v4()->toRfc4122();
            $user->setConfirmationToken($token);

            try {
                $em->persist($user);
                $em->flush();
            } catch (\Exception $e) {
                return new Response('Error saving user: ' . $e->getMessage(), 500);
            }

            $link = $this->generateUrl('app_confirm_email', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            $email = (new Email())
                ->from('dukovadaryadmitrievna@man.com')
                ->to($user->getEmail())
                ->subject('Welcome to The App!')
                ->html("
                <p>Hello, {$user->getFirstName()}!</p>
                <p>Thanks for registering. To activate your account, follow the link:</p>
                <p><a href='$link' style='color:#0d6efd;font-weight:bold;'>Verify account</a></p>
            ");

            $mailer->send($email);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('register.html.twig');
    }
}
