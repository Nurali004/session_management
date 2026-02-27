<?php

namespace App\Service;

use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SessionManager
{
    public function __construct(private EntityManagerInterface $entityManager, private SessionRepository $sessionRepository)
    {


    }

    public function createRefreshSession(User $user, Request $request): Session
    {
        $fingerprint = $this->getFingerprint($request);

        $checkSession = $this->sessionRepository->findOneBy([
            'fingerprint' => $fingerprint,
            'user' => $user,
            'is_active' => true
        ]);

        if (!is_null($checkSession)) {
            $checkSession->setLastActivityAt(new \DateTimeImmutable());
            $checkSession->setExpiresAt(new \DateTimeImmutable('+1 day'));
            $checkSession->setIpAddress($request->getClientIp($request));
            $this->entityManager->flush();
            return $checkSession;
        }

        $sessions = $this->sessionRepository->findBy([
            'user' => $user,
            'is_active' => true
        ]);

        if (count($sessions) >= 3) {

            $oldSession = $sessions[0];
            foreach ($sessions as $session) {
                if ($session->getLastActivityAt() < $oldSession->getLastActivityAt()) {
                    $oldSession = $session;
                }
            }
            $oldSession->invalidate();
            $this->entityManager->flush();


        }

        $session = new Session();
        $session->setUser($user);
        $session->setFingerprint($fingerprint);
        $session->setIpAddress($request->getClientIp());
        $session->setUserAgent($request->headers->get('User-Agent'));
        $session->setBrowser($this->detectBrowser($request->headers->get('User-Agent')));
        $session->setDevice($this->detectDevice($request->headers->get('User-Agent')));
        $session->setExpiresAt(new \DateTimeImmutable('+1 day'));
        $this->entityManager->persist($session);
        $this->entityManager->flush();
        return $session;


    }

    protected function getFingerprint(Request $request): string
    {
        $fingerprint = $request->headers->get('X-Device-Fingerprint');

        if ($fingerprint !== null && strlen($fingerprint) <= 64) {
            return $fingerprint;
        }

        return hash('sha256', $request->headers->get('User-Agent', '') . $request->headers->get('Accept-Language', ''));


    }


    protected function detectBrowser(string $userAgent): string
    {
        $ua = strtolower($userAgent);
        if (str_contains($ua, 'firefox')) return 'Firefox';
        if (str_contains($ua, 'chrome')) return 'Chrome';
        if (str_contains($ua, 'safari')) return 'Safari';
        if (str_contains($ua, 'opera')) return 'Opera';
        return 'Unknown';
    }

    protected function detectDevice(string $userAgent): string
    {
        $ua = strtolower($userAgent);
        if (str_contains($ua, 'iphone')) return 'iPhone';
        if (str_contains($ua, 'ipad')) return 'iPad';
        if (str_contains($ua, 'android')) return 'Android';
        if (str_contains($ua, 'windows')) return 'Windows';
        if (str_contains($ua, 'macintosh')) return 'Mac';
        return 'Unknown';
    }

}
