<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getStatus() === 'blocked') {
            throw new CustomUserMessageAccountStatusException('Your account has been blocked.');
        }

        if ($user->getStatus() === 'deleted') {
            throw new CustomUserMessageAccountStatusException('Your account has been deleted.');
        }

    }

    public function checkPostAuth(UserInterface $user): void {}
}
