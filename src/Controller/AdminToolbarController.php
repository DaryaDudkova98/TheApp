<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminToolbarController extends AbstractController
{
    #[Route('/admin/action', name: 'admin_action_toolbar', methods: ['POST'])]
    public function toolbar(Request $request, EntityManagerInterface $em): Response
    {
        $action = $request->request->get('action');
        $selectedIds = $request->request->all('selected');

        if (!$selectedIds || !$action) {
            $this->addFlash('warning', 'No users selected or no action specified.');
            return $this->redirectToRoute('admin_users');
        }


        $users = $em->getRepository(User::class)->findBy(['id' => $selectedIds]);

        foreach ($users as $user) {
            switch ($action) {
                case 'block':
                    $user->setStatus('blocked');
                    break;
                case 'unblock':
                    $user->setStatus('active');
                    break;
                case 'delete':
                    $user->setStatus('deleted');
                    break;
                case 'remove':
                    $em->remove($user);
                    break;
            }
        }

        $em->flush();

        $currentUser = $this->getUser();
        if ($currentUser instanceof User && in_array((string)$currentUser->getId(), $selectedIds, true)) {
            switch ($action) {
                case 'block':
                    $this->addFlash('error', 'You have been blocked.');
                    return $this->redirectToRoute('app_login');
                case 'delete':
                    $this->addFlash('error', 'Your account has been deleted.');
                    return $this->redirectToRoute('app_login');
                case 'remove':
                    $this->addFlash('error', 'Your account has been removed.');
                    return $this->redirectToRoute('app_login');
                case 'unblock':
                    $this->addFlash('success', 'Your account has been reactivated.');
                    break;
            }
        }

        $this->addFlash('success', 'Action applied to selected users.');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function users(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin_dashboard.html.twig', [
            'users' => $users,
        ]);
    }
}
