<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * Az e-maillel azonos esemény rövid, megnyitható üzenete a mobilnak.
 */
class FlashNotice
{
    public static function deliver(
        object|array|null $person,
        string $subject,
        string $body,
        string $target = 'home',
        int|string|null $targetId = null,
        ?string $kind = null,
    ): bool {
        $userId = '';
        if (is_object($person)) {
            $userId = (string)($person->id ?? '');
        } elseif (is_array($person)) {
            $userId = (string)($person['id'] ?? '');
        }
        $userId = trim($userId);
        $subject = trim($subject);
        $body = trim($body);
        if ($userId === '' || $subject === '' || $body === '') {
            return false;
        }
        if ($kind !== null && !self::allowed($userId, $kind)) {
            return false;
        }
        $id = $targetId === null ? null : trim((string)$targetId);
        if ($id === '') {
            $id = null;
        }

        try {
            $table = TableRegistry::getTableLocator()->get('Flashes');
            $row = $table->newEntity([
                'user_id' => $userId,
                'subject' => mb_substr($subject, 0, 180),
                'body' => mb_substr($body, 0, 2000),
                'target' => mb_substr($target, 0, 40),
                'target_id' => $id,
            ]);
            if (!$table->save($row)) {
                Log::error('A flash üzenet nem menthető: ' . $subject);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::error('A flash üzenet nem menthető: ' . $exception->getMessage());

            return false;
        }
    }

    /**
     * A hiányzó oszlop és a ki nem töltött jelölés bekapcsolt üzenetet jelent.
     */
    private static function allowed(string $userId, string $kind): bool
    {
        try {
            $users = TableRegistry::getTableLocator()->get('Users');
            if (!$users->getSchema()->hasColumn($kind)) {
                return true;
            }
            $row = $users->find()
                ->select(['id', $kind])
                ->where(['Users.id' => $userId])
                ->disableHydration()
                ->first();
            if ($row === null) {
                return false;
            }

            return User::wantsFlash($row, $kind);
        } catch (\Throwable $exception) {
            Log::error('A flash beállítás nem olvasható: ' . $exception->getMessage());

            return true;
        }
    }
}
