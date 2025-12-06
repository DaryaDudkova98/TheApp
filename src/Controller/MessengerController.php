<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email as MailMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Email as EmailEntity;
use App\Repository\UserRepository;

class MessengerController extends AbstractController
{
    #[Route('/schedule-mail/{email}', name: 'schedule_mail')]
    public function scheduleMail(
        string $email,
        MessageBusInterface $bus,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new Response('User not found', 404);
        }

        $recipientEmail = $user->getEmail();

        $mail = (new MailMessage())
            ->from('dudkovadaryadmitrievna@gmail.com')
            ->to($recipientEmail)
            ->subject('Postponed letter')
            ->text('Hello, ' . ($user->getName() ?? 'friend') . '! This letter will be sent in 1 minute.');

        $bus->dispatch(
            new \Symfony\Component\Mailer\Messenger\SendEmailMessage($mail),
            [new DelayStamp(60000)]
        );

        $emailEntity = new EmailEntity();
        $emailEntity->setRecipient($recipientEmail);
        $emailEntity->setSubject('Postponed letter');
        $emailEntity->setBody('Hello, ' . ($user->getName() ?? 'friend') . '!This letter will be sent in 1 minute.');
        $emailEntity->setScheduledAt(new \DateTime('+1 minute'));
        $emailEntity->setStatus('pending');

        $em->persist($emailEntity);
        $em->flush();

        return new Response('The letter has been scheduled and saved in the database!');
    }
}
