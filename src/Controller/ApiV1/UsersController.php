<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Model\Entity\User;
use Cake\Http\Exception\UnauthorizedException;

/**
 * Saját profil. Jelenleg a keresztnév és a vezetéknév írható.
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Később bővíthető mezőlista. Most csak a név.
     *
     * @var array<string>
     */
    private const WRITE_FIELDS = [
        'first_name',
        'last_name',
    ];

    public function profile(): void
    {
        $this->request->allowMethod(['get', 'post', 'put', 'patch']);

        $id = $this->identityId();
        if ($id === null) {
            throw new UnauthorizedException('Bejelentkezés szükséges.');
        }

        $user = $this->Users->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $user = $this->patchFromRequest($this->Users, $user, self::WRITE_FIELDS);
            if (!$this->Users->save($user)) {
                $this->respondValidationError($user);

                return;
            }
        }

        $this->set([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ],
        ]);
    }

    /**
     * Klubelnök vagy admin: kitiltás és visszavétel az enabled mezőn.
     */
    public function enabled(string $id): void
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $enabled = $this->readEnabled($this->request->getData('enabled'));
        if ($enabled === null) {
            $this->response = $this->response->withStatus(422);
            $this->set([
                'success' => false,
                'message' => 'Az enabled mező true vagy false legyen.',
            ]);

            return;
        }

        $user = $this->Users->get($id);
        $user->set('enabled', $enabled, ['guard' => false]);
        if (!$this->Users->save($user)) {
            $this->respondValidationError($user);

            return;
        }

        $this->set([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => User::displayName($user),
                'enabled' => User::isEnabled($user),
            ],
        ]);
    }

    private function readEnabled(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 1 || $value === '1' || $value === 'true') {
            return true;
        }
        if ($value === 0 || $value === '0' || $value === 'false') {
            return false;
        }

        return null;
    }
}
