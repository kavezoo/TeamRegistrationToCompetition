<?php
declare(strict_types=1);

namespace App\Service;

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
    ): void {
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
            return;
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
            }
        } catch (\Throwable $exception) {
            Log::error('A flash üzenet nem menthető: ' . $exception->getMessage());
        }
    }
}
