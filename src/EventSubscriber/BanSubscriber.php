<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class BanSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => 'onCheckPassport'];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $user = $event->getPassport()->getUser();
        if (!$user instanceof User){
            return;
        }
        if ($user->isBanned()){
            throw new CustomUserMessageAuthenticationException(
                'Votre compte a été suspendu jusqu\'au ' . $user->getBannedUntil()->format('d/m/Y à H:i') . ' - ' . $user->getBanReason()
            );
        }
        if (!$user->isVerified()){
            throw new CustomUserMessageAuthenticationException(
                'Vous devez confirmer votre adresse mail avant de vous connecter.'
            );
        }
    }
}
