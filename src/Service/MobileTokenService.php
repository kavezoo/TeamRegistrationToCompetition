<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Cake\Utility\Text;
use function Cake\Core\env;

/**
 * A mobil access token JWT, a refresh token csak hashként van a táblában.
 * A webes munkamenetet nem írja.
 */
class MobileTokenService
{
    public const ACCESS_SECONDS = 600;

    public const REFRESH_DAYS = 30;

    public const AUDIENCE = 'teamreg-mobile';

    public static function secret(): string
    {
        $secret = trim((string)env('MOBILE_JWT_SECRET'));
        if ($secret !== '') {
            return $secret;
        }

        return hash('sha256', Security::getSalt() . ':mobile');
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string, user: array<string, mixed>}
     */
    public function login(object $user, ?string $deviceName, ?string $platform): array
    {
        $sessions = TableRegistry::getTableLocator()->get('MobileSessions');
        $session = $sessions->newEmptyEntity();
        $session->set('id', Text::uuid());
        $session->set('user_id', (string)$user->get('id'));
        $session->set('device_name', $this->short($deviceName, 150));
        $session->set('platform', $this->short($platform, 30));
        $refresh = $this->refreshToken();
        $session->set('refresh_token_hash', $this->hash($refresh));
        $now = DateTime::now();
        $session->set('created', $now);
        $session->set('last_used', $now);
        $session->set('expires', DateTime::now()->addDays(self::REFRESH_DAYS));
        if (!$sessions->save($session)) {
            throw new \RuntimeException('A mobil munkamenet nem menthető.');
        }

        return $this->pair($user, (string)$session->get('id'), $refresh);
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string, user: array<string, mixed>}
     */
    public function refresh(string $refreshToken): array
    {
        $hash = $this->hash($refreshToken);
        $sessions = TableRegistry::getTableLocator()->get('MobileSessions');
        $session = $sessions->find()->where(['refresh_token_hash' => $hash])->first();
        if ($session === null) {
            $this->revokeReused($hash);

            throw new \RuntimeException('A refresh token érvénytelen.');
        }
        if ($session->get('revoked') !== null || $this->expired($session->get('expires'))) {
            throw new \RuntimeException('A refresh token lejárt.');
        }
        $user = TableRegistry::getTableLocator()->get('Users')->get((string)$session->get('user_id'));
        if (!User::isEnabled($user)) {
            throw new \RuntimeException('A fiók nincs engedélyezve.');
        }
        $next = $this->refreshToken();
        $session->set('previous_refresh_hash', $hash);
        $session->set('refresh_token_hash', $this->hash($next));
        $now = DateTime::now();
        $session->set('last_used', $now);
        $session->set('expires', DateTime::now()->addDays(self::REFRESH_DAYS));
        if (!$sessions->save($session)) {
            throw new \RuntimeException('A mobil munkamenet nem újítható.');
        }

        return $this->pair($user, (string)$session->get('id'), $next);
    }

    public function logout(string $refreshToken): void
    {
        $sessions = TableRegistry::getTableLocator()->get('MobileSessions');
        $session = $sessions->find()->where(['refresh_token_hash' => $this->hash($refreshToken)])->first();
        if ($session === null || $session->get('revoked') !== null) {
            return;
        }
        $session->set('revoked', DateTime::now());
        $sessions->save($session);
    }

    public function logoutAll(string $userId): void
    {
        TableRegistry::getTableLocator()->get('MobileSessions')->updateAll(
            ['revoked' => DateTime::now()],
            ['user_id' => $userId, 'revoked IS' => null],
        );
    }

    public function accessToken(string $userId, string $sessionId): string
    {
        $now = time();

        return JwtHs256::encode([
            'iss' => 'teamreg',
            'aud' => self::AUDIENCE,
            'sub' => $userId,
            'jti' => $sessionId,
            'iat' => $now,
            'exp' => $now + self::ACCESS_SECONDS,
        ], self::secret());
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    private function refreshToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function revokeReused(string $hash): void
    {
        $sessions = TableRegistry::getTableLocator()->get('MobileSessions');
        $session = $sessions->find()->where(['previous_refresh_hash' => $hash])->first();
        if ($session === null) {
            return;
        }
        $session->set('revoked', DateTime::now());
        $sessions->save($session);
    }

    private function expired(mixed $expires): bool
    {
        return $expires instanceof \DateTimeInterface && $expires < new \DateTimeImmutable('now');
    }

    private function short(?string $value, int $limit): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $limit);
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string, user: array<string, mixed>}
     */
    private function pair(object $user, string $sessionId, string $refresh): array
    {
        return [
            'access_token' => $this->accessToken((string)$user->get('id'), $sessionId),
            'refresh_token' => $refresh,
            'expires_in' => self::ACCESS_SECONDS,
            'token_type' => 'Bearer',
            'user' => User::apiProfile($user),
        ];
    }
}
