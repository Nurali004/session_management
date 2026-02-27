<?php

namespace App\EventSubscriber;

use App\Service\SessionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class JwtAuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SessionManager $sessionManager
    )
    {
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof \App\Entity\User) {
            return;
        }

        $request = $event->getRequest();
        $this->sessionManager->createRefreshSession($user, $request);

    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
        ];
    }


}
