<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\SecurityBundle\Security;

class LogServices
{
    public function __construct(private EntityManagerInterface $em, private Security $security) {}

    public function logAction( int $entityID, string $action, ?string $entityName = null, bool $flush = false ): void 
    {
        // Get logged-in user
        $user = $this->security->getUser();
        if (!$user) return;

        // Build full action string
        $fullAction = $entityName
            ? $action . ' - ' . $entityName
            : $action;

        $log = new Log();
        $log->setUserId($user);
        $log->setAction($fullAction);
        $log->setTarget('Order #' . $entityID);   // Or Material #ID, depends on context
        $log->setDatetimestamp(new \DateTime());

        $this->em->persist($log);

        if ($flush) {
            $this->em->flush();
        }
    }

}
