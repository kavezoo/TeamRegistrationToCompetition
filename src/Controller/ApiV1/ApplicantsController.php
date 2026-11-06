<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Model\Entity\User;

/**
 * Klubfelvételi kérelmek. Ugyanaz, mint a klubelnök webes listája.
 */
class ApplicantsController extends AppController
{
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        if (!$this->isManager()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $users = $this->fetchTable('Users');
        $this->set([
            'success' => true,
            'pending' => $this->rows($users, 'pending'),
            'denied' => $this->rows($users, 'denied'),
        ]);
        $this->viewBuilder()->setOption('serialize', ['success', 'pending', 'denied']);
    }

    public function approve(string $id): void
    {
        $this->request->allowMethod(['post']);
        $this->decide($id, true);
    }

    public function deny(string $id): void
    {
        $this->request->allowMethod(['post']);
        $this->decide($id, false);
    }

    private function decide(string $id, bool $approve): void
    {
        if (!$this->isManager()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $users = $this->fetchTable('Users');
        $user = $users->get($id);
        if ((string)$user->get('role') !== User::ROLE_NEW) {
            $this->respondConflict($approve ? 'Csak új jelentkező fogadható el.' : 'Csak új jelentkező utasítható el.');

            return;
        }
        if ($approve) {
            $user->set('role', User::ROLE_MEMBER, ['guard' => false]);
            $user->set('membership_request', null, ['guard' => false]);
        } else {
            $user->set('membership_request', 'denied', ['guard' => false]);
        }
        if (!$users->save($user)) {
            $this->respondValidationError($user);

            return;
        }
        $this->set([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => User::displayName($user),
                'email' => $user->email,
                'role' => $user->get('role'),
                'membership_request' => $user->get('membership_request'),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(\Cake\ORM\Table $users, string $state): array
    {
        $list = [];
        $found = $users->find()
            ->where([
                'role' => User::ROLE_NEW,
                'membership_request' => $state,
            ])
            ->orderBy(['first_name' => 'ASC', 'last_name' => 'ASC'])
            ->all();
        foreach ($found as $user) {
            $list[] = [
                'id' => $user->id,
                'name' => User::displayName($user),
                'email' => $user->email,
                'membership_request' => $state,
            ];
        }

        return $list;
    }
}
