<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use App\Entity\User;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // Only check your User entity
        if (!$user instanceof User) {
            return;
        }

        // Check if user is active
        if (!$user->isActive()) {
            // Throw an exception with a custom message
            throw new CustomUserMessageAccountStatusException('⚠️ Your account is disabled. Please contact support.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Optional: post-login checks
    }
}
