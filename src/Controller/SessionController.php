<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SessionController extends AbstractController
{
    public function __construct(private SessionRepository $sessionRepository)
    {

    }

    #[Route('/api/me/sessions', name: 'app_sessions', methods: ['GET'])]

    public function getSessions():JsonResponse
    {
        $user = $this->getUser();
        $sessions = $this->sessionRepository->findActiveSessions($user);



        $data = [];
        foreach ($sessions as $session) {
            $data[] = [
                'id' => $session->getId(),
                'fingerprint' => $session->getFingerprint(),
                'ipAddress' => $session->getIpAddress(),
                'browser' => $session->getBrowser(),
                'device' => $session->getDevice(),
                'created_at' => $session->getCreatedAt()->format('Y-m-d H:i:s'),
                'expires_at' => $session->getExpiresAt()->format('Y-m-d H:i:s'),
                'last_activity_at' => $session->getLastActivityAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);


    }


    #[Route('/api/me/sessions/{id}', name: 'app_session_delete', methods: ['DELETE'])]
    public function deleteSession(string $id,  EntityManagerInterface $entityManager):JsonResponse
    {
        $user = $this->getUser();
        $session = $this->sessionRepository->findOneBy(['id' => $id, 'user' => $user, 'is_active' => true]);
        if (!$session) {
            return new JsonResponse(['error' => 'Session not found'], 404);
        }

        $session->invalidate();
        $entityManager->flush();
        return new JsonResponse(['message' => 'Session deleted successfully'], 200);


    }

    #[Route('/api/me/sessions/others', name: 'app_sessions_delete_others', methods: ['DELETE'])]

    public function deleteOtherSession(EntityManagerInterface $entityManager):JsonResponse
    {
        $user = $this->getUser();
        $sessions = $this->sessionRepository->findActiveSessions($user);

        foreach ($sessions as $session) {
            $session->invalidate();
        }
        $entityManager->flush();

        return new JsonResponse(['message' => 'Sessions deleted successfully'], 200);

    }
}
