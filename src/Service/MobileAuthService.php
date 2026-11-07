<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * A Google subject a social_accounts.reference mezőben van.
 * Új fiók ugyanúgy new szerepet kap, mint a webes Google-belépés.
 */
class MobileAuthService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function userFromGoogle(array $payload): object
    {
        $sub = trim((string)($payload['sub'] ?? ''));
        $email = mb_strtolower(trim((string)($payload['email'] ?? '')));
        if ($sub === '' || $email === '') {
            throw new MobileAuthException('A Google fiókból hiányzik az azonosító vagy az e-mail.', 401, 'AUTH_FAILED');
        }

        $users = TableRegistry::getTableLocator()->get('Users');
        $accounts = $users->SocialAccounts;
        $this->removeOrphanAccounts($users, $accounts, $email, $sub);
        $linked = $accounts->find()
            ->where([
                'reference' => $sub,
                'LOWER(provider) =' => 'google',
            ])
            ->first();
        if ($linked !== null) {
            try {
                $user = $users->get((string)$linked->get('user_id'));
            } catch (RecordNotFoundException) {
                $accounts->delete($linked);
                $linked = null;
            }
            if ($linked !== null) {
                return $this->usable($user);
            }
        }

        $existing = $users->find()->where(['email' => $email])->first();
        if ($existing !== null) {
            $other = $accounts->find()
                ->where([
                    'user_id' => $existing->get('id'),
                    'provider' => 'google',
                ])
                ->first();
            if ($other !== null && (string)$other->get('reference') !== $sub) {
                throw new MobileAuthException(
                    'Ez az e-mail már egy másik Google-fiókhoz tartozik.',
                    409,
                    'EMAIL_TAKEN',
                );
            }
            $this->attachGoogle($existing, $payload, $email, $sub);

            return $this->usable($existing);
        }

        return $this->create($payload, $email, $sub);
    }

    private function usable(object $user): object
    {
        if (!User::isEnabled($user) || !filter_var($user->get('active'), FILTER_VALIDATE_BOOLEAN)) {
            throw new MobileAuthException('A fiók nincs engedélyezve.', 403, 'ACCOUNT_DISABLED');
        }
        $role = (string)$user->get('role');
        if (!in_array($role, [User::ROLE_MEMBER, User::ROLE_PRESIDENT, User::ROLE_ADMIN], true) && $role !== User::ROLE_NEW) {
            $user->set('role', User::ROLE_NEW, ['guard' => false]);
            TableRegistry::getTableLocator()->get('Users')->save($user);
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function attachGoogle(object $user, array $payload, string $email, string $sub): void
    {
        $accounts = TableRegistry::getTableLocator()->get('Users')->SocialAccounts;
        $already = $accounts->find()
            ->where([
                'user_id' => $user->get('id'),
                'provider' => 'google',
                'reference' => $sub,
            ])
            ->first();
        if ($already !== null) {
            return;
        }
        $account = $accounts->newEntity($this->accountData($payload, $email, $sub));
        $account->set('user_id', (string)$user->get('id'));
        if (!$accounts->save($account)) {
            Log::error('A Google-fiók nem kapcsolható a felhasználóhoz: ' . json_encode($account->getErrors()));
            throw new MobileAuthException('A Google-fiók nem kapcsolható össze.', 409, 'LINK_FAILED');
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function create(array $payload, string $email, string $sub): object
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->newEntity([
            'username' => $this->username($users, $email),
            'email' => $email,
            'password' => bin2hex(random_bytes(16)),
            'first_name' => $this->name($payload['family_name'] ?? ''),
            'last_name' => $this->name($payload['given_name'] ?? ''),
            'active' => true,
            'enabled' => true,
            'is_superuser' => false,
            'role' => User::ROLE_NEW,
            'profile_saved' => false,
            'token_send_requested' => false,
        ]);
        $connection = $users->getConnection();
        $connection->begin();
        try {
            if (!$users->save($user)) {
                Log::error('A mobil Google-felhasználó nem menthető: ' . json_encode($user->getErrors()));
                throw new MobileAuthException('A fiók nem hozható létre.', 409, 'USER_CREATE_FAILED');
            }
            $account = $users->SocialAccounts->newEntity($this->accountData($payload, $email, $sub));
            $account->set('user_id', (string)$user->get('id'));
            if (!$users->SocialAccounts->save($account)) {
                Log::error('A Google közösségi fiók nem menthető: ' . json_encode($account->getErrors()));
                throw new MobileAuthException('A fiók nem hozható létre.', 409, 'USER_CREATE_FAILED');
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollback();
            if ($exception instanceof MobileAuthException) {
                throw $exception;
            }
            Log::error('A mobil Google-felhasználó nem menthető: ' . $exception->getMessage());
            throw new MobileAuthException('A fiók nem hozható létre.', 409, 'USER_CREATE_FAILED');
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function accountData(array $payload, string $email, string $sub): array
    {
        $avatar = $payload['picture'] ?? null;
        if (!is_string($avatar) || $avatar === '') {
            $avatar = null;
        } else {
            $avatar = mb_substr($avatar, 0, 255);
        }

        return [
            'provider' => 'google',
            'username' => $email,
            'reference' => $sub,
            'avatar' => $avatar,
            'link' => 'https://accounts.google.com',
            'token' => 'mobile',
            'token_secret' => '',
            'active' => true,
            'data' => serialize([
                'sub' => $sub,
                'email' => $email,
                'email_verified' => filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'name' => is_string($payload['name'] ?? null) ? $payload['name'] : '',
            ]),
        ];
    }

    /**
     * A törölt felhasználó után maradt Google-kapcsolat megakadályozná az új belépést.
     */
    private function removeOrphanAccounts(object $users, object $accounts, string $email, string $sub): void
    {
        $rows = $accounts->find()
            ->where([
                'OR' => [
                    [
                        'reference' => $sub,
                        'LOWER(provider) =' => 'google',
                    ],
                    ['username' => $email],
                ],
            ])
            ->all();
        foreach ($rows as $row) {
            $userId = (string)$row->get('user_id');
            if ($userId === '' || !$users->exists(['id' => $userId])) {
                $accounts->delete($row);
            }
        }
    }

    private function username(object $users, string $email): string
    {
        $local = strstr($email, '@', true);
        $base = strtolower((string)preg_replace('/[^a-z0-9]+/i', '', (string)$local));
        if ($base === '') {
            $base = 'tag';
        }
        $base = mb_substr($base, 0, 240);
        $username = $base;
        $i = 0;
        while ($users->exists(['username' => $username])) {
            $i++;
            $username = $base . $i;
        }

        return $username;
    }

    private function name(mixed $value): string
    {
        return mb_substr(trim((string)$value), 0, 50);
    }
}
