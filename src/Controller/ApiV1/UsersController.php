<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

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

    private function identityId(): ?string
    {
        $identity = $this->request->getAttribute('identity');
        if ($identity === null) {
            return null;
        }

        $id = $identity['id'] ?? null;
        if ($id === null || $id === '') {
            return null;
        }

        return (string)$id;
    }
}
