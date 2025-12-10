<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Entity\User;
use App\Repository\UserRepository;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;






final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }

    #[Route('/account/updateuser', name: 'account_updateuser', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Not logged in');
            return $this->redirectToRoute('app_account');
        }

        $submittedToken = $request->request->get('_token');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('updateProfile_accounts', $submittedToken))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_account');
        }

        $fullName = $request->request->get('fullName', $user->getFullName());
        $email = $request->request->get('email', $user->getEmail());

        $user->setFullName($fullName);
        $user->setEmail($email);

        $em->flush();

        $this->addFlash('success', 'Account updated successfully');

        return $this->redirectToRoute('app_account');
    }

    #[Route('/account/update-password', name: 'account_update_password', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updatePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Not logged in');
            return $this->redirectToRoute('app_account');
        }

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('updatePassword', $submittedToken))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_account');
        }

        $currentPassword = $request->request->get('current_password');
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_new_password');

        // Check if current password is correct
        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'Current password is incorrect.');
            return $this->redirectToRoute('app_account');
        }

        // Check if new password matches confirmation
        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'New password and confirmation do not match.');
            return $this->redirectToRoute('app_account');
        }

        // Optional: validate minimum length
        if (strlen($newPassword) < 6) {
            $this->addFlash('error', 'New password must be at least 6 characters long.');
            return $this->redirectToRoute('app_account');
        }

        // Hash and save the new password
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $em->flush();

        $this->addFlash('success', 'Password updated successfully.');
        return $this->redirectToRoute('app_account');
    }

}
