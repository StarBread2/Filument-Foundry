<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Log;
use App\Entity\User;

class LogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();

        // Make sure this is your User entity
        if (!$user instanceof User) {
            return;
        }

        $log = new Log();
        $log->setUserId($user);
        $log->setAction('Logout'); // action = logout
        $log->setTarget(null);
        $log->setDatetimestamp(new \DateTime());

        $this->em->persist($log);
        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
