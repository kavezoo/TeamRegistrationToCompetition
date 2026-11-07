<?php
declare(strict_types=1);

namespace App\Authenticator;

use App\Model\Entity\User;
use App\Service\JwtHs256;
use App\Service\MobileTokenService;
use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Authentication\Authenticator\StatelessInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Bearer access token. Nincs fejléc esetén továbbengedi a webes belépést.
 * A sikeres mobil belépés nem írja a webes Auth munkamenetet.
 */
class MobileTokenAuthenticator extends AbstractAuthenticator implements StatelessInterface
{
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (!str_starts_with($header, 'Bearer ')) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        }
        $jwt = trim(substr($header, 7));
        $payload = JwtHs256::decode($jwt, MobileTokenService::secret());
        if ($payload === null || ($payload['aud'] ?? '') !== MobileTokenService::AUDIENCE) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
        }
        $userId = (string)($payload['sub'] ?? '');
        $sessionId = (string)($payload['jti'] ?? '');
        if ($userId === '' || $sessionId === '') {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
        }
        try {
            $session = TableRegistry::getTableLocator()->get('MobileSessions')->get($sessionId);
        } catch (RecordNotFoundException) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
        } catch (\Throwable) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
        }
        if ((string)$session->get('user_id') !== $userId || $session->get('revoked') !== null) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
        }
        $expires = $session->get('expires');
        if ($expires instanceof \DateTimeInterface && $expires < new \DateTimeImmutable('now')) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
        }
        try {
            $user = TableRegistry::getTableLocator()->get('Users')->get($userId);
        } catch (RecordNotFoundException) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        }
        if (!User::isEnabled($user) || !filter_var($user->get('active'), FILTER_VALIDATE_BOOLEAN)) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
        }

        return new Result($user, Result::SUCCESS);
    }

    public function unauthorizedChallenge(ServerRequestInterface $request): void
    {
    }
}
