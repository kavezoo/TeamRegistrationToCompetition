<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use DateTimeInterface;

/**
 * Tagok, elnökök és adminok egymásnak írt üzenete.
 * Minden új sor mellé flash is megy a címzett telefonjára, ha azt kéri.
 */
class Conversation
{
    public const UNAVAILABLE = 'Az üzenetek táblája még nincs a szerveren.';

    /**
     * @return list<string>
     */
    public static function roles(): array
    {
        return [User::ROLE_MEMBER, User::ROLE_PRESIDENT, User::ROLE_ADMIN];
    }

    /**
     * Címzettek: aktív tag, elnök és admin, a küldő nélkül.
     *
     * @return list<object>
     */
    public static function people(string $exceptUserId): array
    {
        $users = TableRegistry::getTableLocator()->get('Users');

        return $users->find()
            ->select(['id', 'first_name', 'last_name', 'username', 'email', 'role'])
            ->where([
                'active' => true,
                'enabled' => true,
                'role IN' => self::roles(),
                'id !=' => $exceptUserId,
            ])
            ->orderBy(['first_name' => 'ASC', 'last_name' => 'ASC', 'email' => 'ASC'])
            ->all()
            ->toList();
    }

    /**
     * Új beszélgetés egy vagy több címzettnek. Üres címzettlista a people() minden tagja.
     *
     * @param list<string> $recipientIds
     * @return array{error: ?string, matched: int, saved: int, flashed: int}
     */
    public static function send(string $senderId, array $recipientIds, string $subject, string $body): array
    {
        $empty = ['error' => null, 'matched' => 0, 'saved' => 0, 'flashed' => 0];
        $subject = trim($subject);
        $body = trim($body);
        if (mb_strlen($subject) > 180) {
            $subject = mb_substr($subject, 0, 180);
        }
        if (mb_strlen($body) > 2000) {
            $body = mb_substr($body, 0, 2000);
        }
        if ($subject === '' || $body === '') {
            $empty['error'] = 'A cím és az üzenet is kell.';

            return $empty;
        }
        if (self::sender($senderId) === null) {
            $empty['error'] = 'Ebből a szerepből nem küldhetsz üzenetet.';

            return $empty;
        }

        $allowed = [];
        foreach (self::people($senderId) as $person) {
            $allowed[(string)$person->id] = $person;
        }
        $wanted = [];
        foreach ($recipientIds as $recipientId) {
            $recipientId = trim($recipientId);
            if ($recipientId !== '') {
                $wanted[$recipientId] = true;
            }
        }
        if ($wanted === []) {
            $wanted = $allowed;
        }

        $matched = 0;
        $saved = 0;
        $flashed = 0;
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        foreach (array_keys($wanted) as $recipientId) {
            if (!isset($allowed[$recipientId])) {
                continue;
            }
            $matched++;
            $row = $table->newEntity([
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'subject' => $subject,
                'body' => $body,
                'thread_id' => 0,
            ]);
            if (!$table->save($row)) {
                Log::error('Az üzenet nem menthető: ' . $subject);
                continue;
            }
            $threadId = (int)$row->id;
            $row->set('thread_id', $threadId);
            $table->save($row);
            $saved++;
            if (self::notify($allowed[$recipientId], $subject, $body, $threadId)) {
                $flashed++;
            }
        }
        if ($matched === 0) {
            $empty['error'] = 'Nincs ilyen tag.';

            return $empty;
        }
        $empty['matched'] = $matched;
        $empty['saved'] = $saved;
        $empty['flashed'] = $flashed;
        if ($saved === 0) {
            $empty['error'] = 'Az üzenet nem menthető.';
        }

        return $empty;
    }

    /**
     * Válasz egy meglévő beszélgetésre. A flash a másik félnek megy.
     *
     * @return array{error: ?string, thread_id: int, flashed: bool}
     */
    public static function reply(string $senderId, int $threadId, string $body): array
    {
        $result = ['error' => null, 'thread_id' => $threadId, 'flashed' => false];
        $body = trim($body);
        if (mb_strlen($body) > 2000) {
            $body = mb_substr($body, 0, 2000);
        }
        if ($body === '') {
            $result['error'] = 'A válasz szövege kell.';

            return $result;
        }
        if (self::sender($senderId) === null) {
            $result['error'] = 'Ebből a szerepből nem küldhetsz üzenetet.';

            return $result;
        }
        $open = self::root($senderId, $threadId);
        if ($open === null) {
            $result['error'] = 'Az üzenet nem található.';

            return $result;
        }
        [$root, $peerId] = $open;
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $row = $table->newEntity([
            'sender_id' => $senderId,
            'recipient_id' => $peerId,
            'subject' => (string)$root->subject,
            'body' => $body,
            'thread_id' => (int)$root->id,
        ]);
        if (!$table->save($row)) {
            $result['error'] = 'A válasz nem menthető.';

            return $result;
        }
        $subject = 'Válasz: ' . trim((string)$root->subject);
        if (mb_strlen($subject) > 180) {
            $subject = mb_substr($subject, 0, 180);
        }
        $users = TableRegistry::getTableLocator()->get('Users');
        $peer = $users->find()
            ->select(['id', 'first_name', 'last_name', 'username', 'email', 'role'])
            ->where(['id' => $peerId])
            ->first();
        $result['flashed'] = $peer !== null && self::notify($peer, $subject, $body, (int)$root->id);

        return $result;
    }

    /**
     * A küldő vagy a címzett beszélgetései, a legutóbbi üzenet szerint.
     *
     * @return list<array<string, mixed>>
     */
    public static function threads(string $userId): array
    {
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $rows = $table->find()
            ->where([
                'OR' => [
                    ['sender_id' => $userId],
                    ['recipient_id' => $userId],
                ],
            ])
            ->orderBy(['created' => 'DESC', 'id' => 'DESC'])
            ->limit(400)
            ->all();
        $latest = [];
        $ids = [];
        foreach ($rows as $row) {
            $threadId = (int)$row->thread_id;
            if ($threadId === 0) {
                $threadId = (int)$row->id;
            }
            if (isset($latest[$threadId])) {
                continue;
            }
            $latest[$threadId] = $row;
            $ids[] = (string)$row->sender_id;
            $ids[] = (string)$row->recipient_id;
        }
        $names = self::names($ids);
        $threads = [];
        foreach ($latest as $threadId => $row) {
            $mine = (string)$row->sender_id === $userId;
            $peerId = $mine ? (string)$row->recipient_id : (string)$row->sender_id;
            $threads[] = [
                'id' => $threadId,
                'subject' => (string)$row->subject,
                'peer_id' => $peerId,
                'peer_name' => $names[$peerId] ?? 'Tag',
                'last_body' => (string)$row->body,
                'last_at' => self::stamp($row->created),
                'mine_last' => $mine,
            ];
        }

        return $threads;
    }

    /**
     * Egy beszélgetés, ha a felhasználó résztvevő. Különben null.
     *
     * @return array<string, mixed>|null
     */
    public static function open(string $userId, int $threadId): ?array
    {
        $found = self::root($userId, $threadId);
        if ($found === null) {
            return null;
        }
        [$root, $peerId] = $found;
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $rows = $table->find()
            ->where(['thread_id' => (int)$root->id])
            ->orderBy(['created' => 'ASC', 'id' => 'ASC'])
            ->all();
        $ids = [$userId, $peerId];
        foreach ($rows as $row) {
            $ids[] = (string)$row->sender_id;
        }
        $names = self::names($ids);
        $posts = [];
        foreach ($rows as $row) {
            $senderId = (string)$row->sender_id;
            $posts[] = [
                'id' => (int)$row->id,
                'sender_id' => $senderId,
                'sender_name' => $names[$senderId] ?? 'Tag',
                'body' => (string)$row->body,
                'mine' => $senderId === $userId,
                'created' => self::stamp($row->created),
            ];
        }

        return [
            'id' => (int)$root->id,
            'subject' => (string)$root->subject,
            'peer_id' => $peerId,
            'peer_name' => $names[$peerId] ?? 'Tag',
            'posts' => $posts,
        ];
    }

    /**
     * A telefonnak címzettlista névvel és szereppel.
     *
     * @return list<array{id: string, name: string, role: string}>
     */
    public static function recipientPayload(string $exceptUserId): array
    {
        $payload = [];
        foreach (self::people($exceptUserId) as $person) {
            $payload[] = [
                'id' => (string)$person->id,
                'name' => User::displayName($person),
                'role' => (string)$person->get('role'),
            ];
        }

        return $payload;
    }

    public static function missingTable(\Throwable $exception): bool
    {
        $text = $exception->getMessage();

        return str_contains($text, 'club_messages')
            || str_contains($text, '1146')
            || str_contains($text, "doesn't exist")
            || str_contains($text, 'Base table or view not found');
    }

    private static function sender(string $senderId): ?object
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $sender = $users->find()
            ->select(['id', 'role', 'active', 'enabled'])
            ->where(['id' => $senderId])
            ->first();
        if ($sender === null) {
            return null;
        }
        $role = (string)$sender->get('role');
        if (!in_array($role, self::roles(), true)) {
            return null;
        }
        if (!filter_var($sender->get('active'), FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }
        if (!User::isEnabled($sender)) {
            return null;
        }

        return $sender;
    }

    /**
     * @return array{0: object, 1: string}|null
     */
    private static function root(string $userId, int $threadId): ?array
    {
        if ($threadId <= 0) {
            return null;
        }
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $root = $table->find()->where(['id' => $threadId])->first();
        if ($root === null) {
            return null;
        }
        $senderId = (string)$root->sender_id;
        $recipientId = (string)$root->recipient_id;
        if ($userId !== $senderId && $userId !== $recipientId) {
            return null;
        }
        $peerId = $userId === $senderId ? $recipientId : $senderId;

        return [$root, $peerId];
    }

    private static function notify(object $recipient, string $subject, string $body, int $threadId): bool
    {
        return FlashNotice::deliver(
            $recipient,
            $subject,
            $body,
            'message',
            (string)$threadId,
            User::FLASH_MESSAGE,
        );
    }

    /**
     * @param list<string> $ids
     * @return array<string, string>
     */
    private static function names(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, static fn (string $id): bool => $id !== '')));
        if ($ids === []) {
            return [];
        }
        $users = TableRegistry::getTableLocator()->get('Users');
        $rows = $users->find()
            ->select(['id', 'first_name', 'last_name', 'username', 'email'])
            ->where(['id IN' => $ids])
            ->all();
        $names = [];
        foreach ($rows as $user) {
            $names[(string)$user->id] = User::displayName($user);
        }

        return $names;
    }

    private static function stamp(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return trim((string)$value);
    }
}
