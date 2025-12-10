<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Log;
use App\Entity\User;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $em) {}

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
