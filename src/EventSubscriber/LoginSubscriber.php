<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Log;
use App\Entity\User;

use Symfony\Component\HttpFoundation\RequestStack;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $em, private RequestStack $requestStack) {}

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        // Ensure this is your User entity (avoid security tokens like InMemoryUser)
        if (!$user instanceof User) {
            return;
        }

        $log = new Log();
        $log->setUserId($user);
        $log->setAction('Login');
        $log->setTarget(null); 
        $log->setDatetimestamp(new \DateTime());

        // Get IP address from the current request
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $log->setIpAddress($request->getClientIp());
        }

        $this->em->persist($log);
        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }
}
