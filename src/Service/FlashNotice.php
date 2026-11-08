<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Cache\Cache;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * Az e-maillel azonos esemény rövid, megnyitható üzenete a mobilnak.
 */
class FlashNotice
{
    /** off: a címzett kikapcsolta. missing: a flashes tábla nincs meg. Egyébként a mentés hibája. */
    public static ?string $lastError = null;

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
        self::$lastError = null;
        $userId = trim($userId);
        $subject = trim($subject);
        $body = trim($body);
        if ($userId === '' || $subject === '' || $body === '') {
            self::$lastError = 'A cím vagy az üzenet üres.';

            return false;
        }
        if ($kind !== null && !self::allowed($userId, $kind)) {
            self::$lastError = 'off';

            return false;
        }
        $id = $targetId === null ? null : trim((string)$targetId);
        if ($id === '') {
            $id = null;
        }

        $data = [
            'user_id' => $userId,
            'subject' => mb_substr($subject, 0, 180),
            'body' => mb_substr($body, 0, 2000),
            'target' => mb_substr($target, 0, 40),
            'target_id' => $id,
        ];
        try {
            return self::store($data, true);
        } catch (\Throwable $exception) {
            Log::error('A flash üzenet nem menthető: ' . $exception->getMessage());
            self::refreshModels();
            try {
                return self::store($data, false);
            } catch (\Throwable $again) {
                Log::error('A flash üzenet másodszor sem menthető: ' . $again->getMessage());
                self::$lastError = self::explain($again);

                return false;
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function store(array $data, bool $throw): bool
    {
        $table = TableRegistry::getTableLocator()->get('Flashes');
        $row = $table->newEntity($data);
        if ($table->save($row)) {
            return true;
        }
        $error = 'A flash üzenet nem menthető: ' . json_encode($row->getErrors(), JSON_UNESCAPED_UNICODE);
        Log::error($error);
        self::$lastError = 'A telefonos üzenet nem menthető.';
        if ($throw) {
            throw new \RuntimeException($error);
        }

        return false;
    }

    public static function refreshModels(): void
    {
        try {
            if (Cache::getConfig('_cake_model_') !== null) {
                Cache::clear('_cake_model_');
            }
            TableRegistry::getTableLocator()->clear();
        } catch (\Throwable $exception) {
            Log::error('A táblaséma gyorsítótára nem üríthető: ' . $exception->getMessage());
        }
    }

    public static function explain(\Throwable $exception): string
    {
        $text = $exception->getMessage();
        if (str_contains($text, 'flashes') && (str_contains($text, '1146') || str_contains($text, "doesn't exist") || str_contains($text, 'Base table'))) {
            return 'A flashes tábla még nincs a szerveren.';
        }
        if (str_contains($text, 'Unknown column') || str_contains($text, '1054')) {
            return 'A telefonos üzenet táblájából hiányzik egy oszlop.';
        }

        return 'A telefonos üzenet nem menthető.';
    }

    /** Rövid magyarázat, ha a flash nem ment ki. */
    public static function missedNotice(): string
    {
        if (self::$lastError === 'off') {
            return 'A címzett kikapcsolta a telefonos üzenetet.';
        }
        if (self::$lastError !== null && self::$lastError !== '') {
            return self::$lastError;
        }

        return 'A telefonra nem ment ki.';
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
