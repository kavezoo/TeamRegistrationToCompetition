<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Model\Entity\User;
use App\Service\GoogleIdToken;
use App\Service\MobileAuthException;
use App\Service\MobileAuthService;
use App\Service\MobileTokenService;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Log\Log;

/**
 * A mobilalkalmazás belépése. A webes munkamenetet nem nyitja és nem zárja.
 */
class AuthController extends AppController
{
    public function google(): void
    {
        $this->request->allowMethod(['post']);
        $idToken = trim((string)$this->request->getData('id_token'));
        if ($idToken === '') {
            $this->fail(400, 'Hiányzik a Google azonosító token.', 'MISSING_ID_TOKEN');

            return;
        }
        try {
            $payload = (new GoogleIdToken())->verify($idToken);
            $user = (new MobileAuthService())->userFromGoogle($payload);
            $data = (new MobileTokenService())->login(
                $user,
                $this->text('device_name'),
                $this->text('platform'),
            );
            $this->set([
                'success' => true,
                'data' => $data,
            ]);
        } catch (MobileAuthException $exception) {
            $this->fail($exception->status(), $exception->getMessage(), $exception->errorCode());
        } catch (\Throwable $exception) {
            Log::error('Mobil Google belépés: ' . $exception->getMessage());
            $this->fail(401, 'A belépés nem sikerült.', 'AUTH_FAILED');
        }
    }

    public function refresh(): void
    {
        $this->request->allowMethod(['post']);
        $token = trim((string)$this->request->getData('refresh_token'));
        if ($token === '') {
            $this->fail(400, 'Hiányzik a refresh token.', 'MISSING_REFRESH_TOKEN');

            return;
        }
        try {
            $this->set([
                'success' => true,
                'data' => (new MobileTokenService())->refresh($token),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Mobil token megújítás: ' . $exception->getMessage());
            $this->fail(401, 'A munkamenet nem újítható. Jelentkezz be újra.', 'REFRESH_FAILED');
        }
    }

    public function logout(): void
    {
        $this->request->allowMethod(['post']);
        $token = trim((string)$this->request->getData('refresh_token'));
        if ($token !== '') {
            (new MobileTokenService())->logout($token);
        }
        $this->set([
            'success' => true,
        ]);
    }

    public function logoutAll(): void
    {
        $this->request->allowMethod(['post']);
        $id = $this->identityId();
        if ($id === null) {
            throw new UnauthorizedException('Bejelentkezés szükséges.');
        }
        (new MobileTokenService())->logoutAll($id);
        $this->set([
            'success' => true,
        ]);
    }

    public function me(): void
    {
        $this->request->allowMethod(['get']);
        $id = $this->identityId();
        if ($id === null) {
            throw new UnauthorizedException('Bejelentkezés szükséges.');
        }
        $user = $this->fetchTable('Users')->get($id);
        $this->set([
            'success' => true,
            'user' => User::apiProfile($user),
        ]);
    }

    private function text(string $field): ?string
    {
        $value = $this->request->getData($field);

        return is_string($value) ? $value : null;
    }

    private function fail(int $status, string $message, string $code): void
    {
        $this->response = $this->response->withStatus($status);
        $this->set([
            'success' => false,
            'message' => $message,
            'code' => $code,
        ]);
    }
}
