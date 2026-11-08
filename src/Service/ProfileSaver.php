<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Table;

/**
 * A saját adatok mentése minden szerepnél ugyanígy megy:
 * új tag, klubtag, klubelnök és admin.
 *
 * A Google-kapcsolatot nem menti újra. Ha a mentés sikerült,
 * az elnök értesítése akkor sem töri el a választ, ha a levél hibázik.
 */
class ProfileSaver
{
    /**
     * @param array<string, mixed> $data
     * @return string|null Hibaüzenet. Null, ha a profil megvan.
     */
    public function save(Table $users, EntityInterface $user, array $data): ?string
    {
        $user = $users->patchEntity($user, $data, [
            'fields' => ['first_name', 'last_name'],
            'associated' => false,
        ]);
        $invalid = $this->errorText($user);
        if ($invalid !== null) {
            return $invalid;
        }

        $role = (string)$user->get('role');
        if (!in_array($role, [User::ROLE_MEMBER, User::ROLE_PRESIDENT, User::ROLE_ADMIN, User::ROLE_NEW], true)) {
            $role = User::ROLE_NEW;
            $user->set('role', User::ROLE_NEW, ['guard' => false]);
        }
        if ($role === User::ROLE_NEW && (string)$user->get('membership_request') === 'denied') {
            return User::DENIED_NOTICE;
        }

        $family = trim((string)$user->get('first_name'));
        $given = trim((string)$user->get('last_name'));
        if ($family === '' || $given === '') {
            return 'A vezetéknév és a keresztnév is kell.';
        }

        // Új tag és klubtag fogadja el a nyilatkozatot, de csak az első mentéskor.
        $needsPrivacy = $role === User::ROLE_NEW || $role === User::ROLE_MEMBER;
        $alreadyAccepted = $user->get('tos_date') !== null;
        $acceptsPrivacy = filter_var($data['accept_privacy'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($needsPrivacy && !$alreadyAccepted && !$acceptsPrivacy) {
            return 'A mentéshez el kell fogadni az adatkezelési nyilatkozatot.';
        }
        if ($needsPrivacy && $acceptsPrivacy && !$alreadyAccepted) {
            $user->set('tos_date', DateTime::now(), ['guard' => false]);
        }
        if ($role === User::ROLE_NEW) {
            $user->set('profile_saved', true, ['guard' => false]);
        }
        User::applyMailRequest($user, $data, $users->getSchema());

        $notify = $role === User::ROLE_NEW && (string)$user->get('membership_request') !== 'pending';
        if ($notify) {
            $user->set('membership_request', 'pending', ['guard' => false]);
        }

        // A betöltött Google-fiók újraméntése a profilt is elrontja.
        if (!$users->save($user, ['associated' => false])) {
            return $this->errorText($user) ?? 'A profilt nem sikerült menteni.';
        }

        if ($notify) {
            try {
                (new MembershipNotifier())->notifyPresidents($user);
            } catch (\Throwable $exception) {
                Log::error('A klubelnök értesítése a profil mentése után nem ment ki: ' . $exception->getMessage());
            }
        }

        return null;
    }

    private function errorText(EntityInterface $user): ?string
    {
        $parts = [];
        $walk = function (mixed $errors) use (&$walk, &$parts): void {
            if (!is_array($errors)) {
                return;
            }
            foreach ($errors as $error) {
                if (is_array($error)) {
                    $walk($error);
                } elseif (is_string($error) && trim($error) !== '') {
                    $parts[] = trim($error);
                }
            }
        };
        $walk($user->getErrors());
        $parts = array_values(array_unique($parts));
        if ($parts === []) {
            return null;
        }

        return implode(' ', $parts);
    }
}
