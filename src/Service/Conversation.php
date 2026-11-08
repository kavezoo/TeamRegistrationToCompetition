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

    public const KIND_DIRECT = 'direct';

    public const KIND_ALL = 'all';

    public const KIND_PRESIDENTS = 'presidents';

    /**
     * Tesztelés: a küldő saját magának is írhat. A teszt után ez false legyen.
     */
    public const INCLUDE_SELF = true;

    /**
     * @return list<string>
     */
    public static function roles(): array
    {
        return [User::ROLE_MEMBER, User::ROLE_PRESIDENT, User::ROLE_ADMIN];
    }

    /**
     * Címzettek: aktív tag, elnök és admin. A küldő csak teszteléskor van benne.
     *
     * @return list<object>
     */
    public static function people(string $exceptUserId, bool $includeSelf = false): array
    {
        try {
            return self::peopleQuery($exceptUserId, true, $includeSelf);
        } catch (\Throwable $exception) {
            $text = $exception->getMessage();
            if (!str_contains($text, 'enabled') && !str_contains($text, '1054') && !str_contains($text, 'Unknown column')) {
                throw $exception;
            }

            return self::peopleQuery($exceptUserId, false, $includeSelf);
        }
    }

    /**
     * A null enabled beléphet, csak a kifejezett kitiltás esik ki.
     *
     * @return list<object>
     */
    private static function peopleQuery(string $exceptUserId, bool $withEnabled, bool $includeSelf): array
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $where = [
            'active' => true,
            'role IN' => self::roles(),
        ];
        if (!$includeSelf) {
            $where['id !='] = $exceptUserId;
        }
        if ($withEnabled) {
            $where['OR'] = [
                ['enabled' => true],
                ['enabled IS' => null],
            ];
        }

        return $users->find()
            ->select(['id', 'first_name', 'last_name', 'username', 'email', 'role'])
            ->where($where)
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
        foreach (self::people($senderId, self::INCLUDE_SELF) as $person) {
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
        $saveError = null;
        $flashError = null;
        foreach (array_keys($wanted) as $recipientId) {
            if (!isset($allowed[$recipientId])) {
                continue;
            }
            $matched++;
            $stored = self::storeDirect($senderId, $recipientId, $subject, $body);
            if ($stored['id'] !== null) {
                $saved++;
            } else {
                $saveError = $stored['error'];
            }
            if (self::notify($allowed[$recipientId], $subject, $body, $stored['id'])) {
                $flashed++;
            } elseif (FlashNotice::$lastError !== null && FlashNotice::$lastError !== 'off') {
                $flashError = FlashNotice::$lastError;
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
            $empty['error'] = $saveError ?? 'Az üzenet nem menthető.';
            if ($flashed > 0) {
                $empty['error'] .= ' A telefonra ennek ellenére kiment.';
            } elseif ($flashError !== null) {
                $empty['error'] .= ' ' . $flashError;
            }
        } elseif ($flashed === 0 && $flashError !== null) {
            $empty['error'] = 'Az üzenet elment a weben. ' . $flashError;
        }

        return $empty;
    }

    /**
     * @return array{id: ?int, error: ?string}
     */
    private static function storeDirect(string $senderId, string $recipientId, string $subject, string $body): array
    {
        $existing = self::directThreadId($senderId, $recipientId);
        $data = [
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'subject' => $subject,
            'body' => $body,
            'thread_id' => $existing ?? 0,
        ];
        if (self::hasKind()) {
            $data['kind'] = self::KIND_DIRECT;
        }
        if ($existing !== null) {
            $root = TableRegistry::getTableLocator()->get('ClubMessages')->find()
                ->select(['id', 'subject'])
                ->where(['id' => $existing])
                ->first();
            $rootSubject = trim((string)($root?->subject ?? ''));
            if ($rootSubject !== '' && $rootSubject !== $subject) {
                $data['body'] = $subject . "\n\n" . $body;
                $data['subject'] = $rootSubject;
            }
        }
        try {
            return self::insertDirect($data, $existing);
        } catch (\Throwable $exception) {
            Log::error('Az üzenet nem menthető: ' . $exception->getMessage());
            FlashNotice::refreshModels();
            if (str_contains($exception->getMessage(), 'kind')) {
                unset($data['kind']);
            }
            try {
                return self::insertDirect($data, $existing);
            } catch (\Throwable $again) {
                Log::error('Az üzenet másodszor sem menthető: ' . $again->getMessage());

                return ['id' => null, 'error' => self::explain($again)];
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array{id: ?int, error: ?string}
     */
    private static function insertDirect(array $data, ?int $existingThreadId = null): array
    {
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $row = $table->newEntity($data);
        if (!$table->save($row)) {
            $detail = json_encode($row->getErrors(), JSON_UNESCAPED_UNICODE);
            Log::error('Az üzenet ellenőrzése nem ment át: ' . $detail);

            return ['id' => null, 'error' => 'Az üzenet nem menthető.'];
        }
        $threadId = $existingThreadId ?? (int)$row->id;
        if ((int)$row->thread_id !== $threadId) {
            $row->set('thread_id', $threadId);
            $table->save($row);
        }

        return ['id' => $threadId, 'error' => null];
    }

    /**
     * A két user már megnyitott közvetlen beszélgetése. Új téma is ebbe kerül.
     */
    private static function directThreadId(string $senderId, string $recipientId): ?int
    {
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $query = $table->find()
            ->select(['id', 'thread_id'])
            ->where([
                'OR' => [
                    ['sender_id' => $senderId, 'recipient_id' => $recipientId],
                    ['sender_id' => $recipientId, 'recipient_id' => $senderId],
                ],
            ])
            ->orderBy(['id' => 'DESC']);
        if (self::hasKind()) {
            $query->where(['kind' => self::KIND_DIRECT]);
        }
        $row = $query->first();
        if ($row === null) {
            return null;
        }
        $threadId = (int)$row->thread_id;
        if ($threadId <= 0) {
            $threadId = (int)$row->id;
        }

        return $threadId > 0 ? $threadId : null;
    }

    public static function explain(\Throwable $exception): string
    {
        if (self::missingTable($exception)) {
            return self::UNAVAILABLE;
        }
        $text = $exception->getMessage();
        if (str_contains($text, 'Unknown column') || str_contains($text, '1054')) {
            return 'Az üzenet táblájából hiányzik egy oszlop. Futtasd az SQL-t, és töröld a tmp/cache/models tartalmát.';
        }

        return 'Az üzenet nem menthető.';
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
        $rootRow = self::findRoot($threadId);
        if ($rootRow !== null && self::isShared((string)($rootRow->get('kind') ?? self::KIND_DIRECT))) {
            return self::replyShared($senderId, $rootRow, $body, $result);
        }
        $open = self::root($senderId, $threadId);
        if ($open === null) {
            $result['error'] = 'Az üzenet nem található.';

            return $result;
        }
        [$root, $peerId] = $open;
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $data = [
            'sender_id' => $senderId,
            'recipient_id' => $peerId,
            'subject' => (string)$root->subject,
            'body' => $body,
            'thread_id' => (int)$root->id,
        ];
        if (self::hasKind()) {
            $data['kind'] = self::KIND_DIRECT;
        }
        $row = $table->newEntity($data);
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
        $where = [
            'OR' => [
                ['sender_id' => $userId],
                ['recipient_id' => $userId],
            ],
        ];
        if (self::hasKind()) {
            $where['kind'] = self::KIND_DIRECT;
        }
        $rows = $table->find()
            ->where($where)
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
        $rootRow = self::findRoot($threadId);
        if ($rootRow !== null && self::isShared((string)($rootRow->get('kind') ?? self::KIND_DIRECT))) {
            if (!self::canRead($userId, (string)$rootRow->get('kind'))) {
                return null;
            }

            return self::present($userId, $rootRow, '', self::audienceLabel((string)$rootRow->get('kind')));
        }
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
        $people = self::peopleMap($ids);
        $posts = [];
        foreach ($rows as $row) {
            $senderId = (string)$row->sender_id;
            $person = $people[$senderId] ?? null;
            $posts[] = [
                'id' => (int)$row->id,
                'sender_id' => $senderId,
                'sender_name' => $person['name'] ?? 'Tag',
                'sender_avatar' => $person['avatar'] ?? null,
                'body' => (string)$row->body,
                'mine' => $senderId === $userId,
                'created' => self::stamp($row->created),
            ];
        }
        $peer = $people[$peerId] ?? null;

        return [
            'id' => (int)$root->id,
            'subject' => (string)$root->subject,
            'peer_id' => $peerId,
            'peer_name' => $peer['name'] ?? 'Tag',
            'peer_avatar' => $peer['avatar'] ?? null,
            'kind' => self::KIND_DIRECT,
            'posts' => $posts,
        ];
    }

    /**
     * Egy közös üzenetváltás: mindenkinek, vagy csak a klubelnököknek.
     * Egy sor a lista, a flash minden címzett telefonjára kimegy.
     *
     * @return array{error: ?string, matched: int, saved: int, flashed: int, thread_id: int}
     */
    public static function sendChannel(string $senderId, string $kind, string $subject, string $body): array
    {
        $empty = ['error' => null, 'matched' => 0, 'saved' => 0, 'flashed' => 0, 'thread_id' => 0];
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
        if (!self::isShared($kind)) {
            $empty['error'] = 'Ismeretlen címzettkör.';

            return $empty;
        }
        if (!self::hasKind()) {
            $empty['error'] = self::UNAVAILABLE;

            return $empty;
        }
        if (!self::canRead($senderId, $kind) || self::sender($senderId) === null) {
            $empty['error'] = 'Ebből a szerepből nem küldhetsz üzenetet.';

            return $empty;
        }
        $audience = self::audience($senderId, $kind);
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $row = $table->newEntity([
            'sender_id' => $senderId,
            'recipient_id' => $senderId,
            'subject' => $subject,
            'body' => $body,
            'thread_id' => 0,
            'kind' => $kind,
        ]);
        if (!$table->save($row)) {
            $empty['error'] = 'Az üzenet nem menthető.';

            return $empty;
        }
        $threadId = (int)$row->id;
        $row->set('thread_id', $threadId);
        $table->save($row);
        $flashed = 0;
        foreach ($audience as $person) {
            if (self::notify($person, $subject, $body, $threadId)) {
                $flashed++;
            }
        }
        $empty['matched'] = count($audience);
        $empty['saved'] = 1;
        $empty['flashed'] = $flashed;
        $empty['thread_id'] = $threadId;

        return $empty;
    }

    /**
     * A telefonnak címzettlista névvel és szereppel.
     *
     * @return list<array{id: string, name: string, role: string}>
     */
    public static function recipientPayload(string $exceptUserId): array
    {
        $payload = [];
        foreach (self::people($exceptUserId, self::INCLUDE_SELF) as $person) {
            $payload[] = [
                'id' => (string)$person->id,
                'name' => User::displayName($person),
                'role' => (string)$person->get('role'),
            ];
        }

        return $payload;
    }

    public static function canSee(string $userId, string $kind): bool
    {
        if ($kind === self::KIND_DIRECT) {
            return self::sender($userId) !== null;
        }

        return self::canRead($userId, $kind);
    }

    public static function hasKind(): bool
    {
        try {
            return TableRegistry::getTableLocator()->get('ClubMessages')->getSchema()->hasColumn('kind');
        } catch (\Throwable) {
            return false;
        }
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
        $linked = (int)$root->thread_id;
        if ($linked > 0 && $linked !== (int)$root->id) {
            $parent = $table->find()->where(['id' => $linked])->first();
            if ($parent !== null) {
                $root = $parent;
            }
        }
        $senderId = (string)$root->sender_id;
        $recipientId = (string)$root->recipient_id;
        if ($userId !== $senderId && $userId !== $recipientId) {
            return null;
        }
        $peerId = $userId === $senderId ? $recipientId : $senderId;

        return [$root, $peerId];
    }

    /**
     * @param array{error: ?string, thread_id: int, flashed: bool} $result
     * @return array{error: ?string, thread_id: int, flashed: bool}
     */
    private static function replyShared(string $senderId, object $root, string $body, array $result): array
    {
        $kind = (string)$root->get('kind');
        if (!self::canRead($senderId, $kind)) {
            $result['error'] = 'Ehhez nincs jogosultságod.';

            return $result;
        }
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $row = $table->newEntity([
            'sender_id' => $senderId,
            'recipient_id' => (string)$root->sender_id,
            'subject' => (string)$root->subject,
            'body' => $body,
            'thread_id' => (int)$root->id,
            'kind' => $kind,
        ]);
        if (!$table->save($row)) {
            $result['error'] = 'A válasz nem menthető.';

            return $result;
        }
        $subject = 'Válasz: ' . trim((string)$root->subject);
        if (mb_strlen($subject) > 180) {
            $subject = mb_substr($subject, 0, 180);
        }
        $sent = false;
        foreach (self::audience($senderId, $kind) as $person) {
            if (self::notify($person, $subject, $body, (int)$root->id)) {
                $sent = true;
            }
        }
        $result['flashed'] = $sent;
        $result['thread_id'] = (int)$root->id;

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private static function present(string $userId, object $root, string $peerId, string $peerName): array
    {
        $table = TableRegistry::getTableLocator()->get('ClubMessages');
        $rows = $table->find()
            ->where(['thread_id' => (int)$root->id])
            ->orderBy(['created' => 'ASC', 'id' => 'ASC'])
            ->all();
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (string)$row->sender_id;
        }
        $people = self::peopleMap($ids);
        $posts = [];
        foreach ($rows as $row) {
            $sender = (string)$row->sender_id;
            $person = $people[$sender] ?? null;
            $posts[] = [
                'id' => (int)$row->id,
                'sender_id' => $sender,
                'sender_name' => $person['name'] ?? 'Tag',
                'sender_avatar' => $person['avatar'] ?? null,
                'body' => (string)$row->body,
                'mine' => $sender === $userId,
                'created' => self::stamp($row->created),
            ];
        }

        return [
            'id' => (int)$root->id,
            'subject' => (string)$root->subject,
            'peer_id' => $peerId,
            'peer_name' => $peerName,
            'peer_avatar' => $people[$peerId]['avatar'] ?? null,
            'kind' => (string)$root->get('kind'),
            'posts' => $posts,
        ];
    }

    private static function findRoot(int $threadId): ?object
    {
        if ($threadId <= 0) {
            return null;
        }

        return TableRegistry::getTableLocator()->get('ClubMessages')->find()
            ->where(['id' => $threadId])
            ->first();
    }

    private static function isShared(string $kind): bool
    {
        return $kind === self::KIND_ALL || $kind === self::KIND_PRESIDENTS;
    }

    private static function audienceLabel(string $kind): string
    {
        return $kind === self::KIND_PRESIDENTS ? 'Klubelnökök' : 'Minden tag';
    }

    private static function canRead(string $userId, string $kind): bool
    {
        $role = self::roleOf($userId);
        if (!in_array($role, self::roles(), true)) {
            return false;
        }
        if ($kind === self::KIND_PRESIDENTS) {
            return in_array($role, [User::ROLE_PRESIDENT, User::ROLE_ADMIN], true);
        }

        return $kind === self::KIND_ALL;
    }

    /**
     * @return list<object>
     */
    private static function audience(string $exceptUserId, string $kind): array
    {
        if ($kind === self::KIND_ALL) {
            return self::people($exceptUserId, self::INCLUDE_SELF);
        }
        $users = TableRegistry::getTableLocator()->get('Users');

        return $users->find()
            ->select(['id', 'first_name', 'last_name', 'username', 'email', 'role'])
            ->where([
                'active' => true,
                'role IN' => [User::ROLE_PRESIDENT, User::ROLE_ADMIN],
                'id !=' => $exceptUserId,
                'OR' => [
                    ['enabled' => true],
                    ['enabled IS' => null],
                ],
            ])
            ->all()
            ->toList();
    }

    private static function roleOf(string $userId): string
    {
        $user = TableRegistry::getTableLocator()->get('Users')->find()
            ->select(['id', 'role', 'is_superuser'])
            ->where(['id' => $userId])
            ->first();
        if ($user === null) {
            return '';
        }
        if (!empty($user->get('is_superuser'))) {
            return User::ROLE_ADMIN;
        }

        return (string)$user->get('role');
    }

    private static function notify(object $recipient, string $subject, string $body, int $threadId): bool
    {
        // A klubüzenet mindig kimegy a telefonra; a válasz és az új üzenet is.
        return FlashNotice::deliver(
            $recipient,
            $subject,
            $body,
            'message',
            (string)$threadId,
            null,
        );
    }

    /**
     * @param list<string> $ids
     * @return array<string, string>
     */
    private static function names(array $ids): array
    {
        $names = [];
        foreach (self::peopleMap($ids) as $id => $person) {
            $names[$id] = $person['name'];
        }

        return $names;
    }

    /**
     * @param list<string> $ids
     * @return array<string, array{name: string, avatar: ?string}>
     */
    private static function peopleMap(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, static fn (string $id): bool => $id !== '')));
        if ($ids === []) {
            return [];
        }
        $users = TableRegistry::getTableLocator()->get('Users');
        $rows = $users->find()
            ->select(['id', 'first_name', 'last_name', 'username', 'email'])
            ->where(['id IN' => $ids])
            ->contain(User::socialAvatarContain())
            ->all();
        $people = [];
        foreach ($rows as $user) {
            $people[(string)$user->id] = [
                'name' => User::displayName($user),
                'avatar' => User::avatarUrl($user),
            ];
        }

        return $people;
    }

    private static function stamp(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return trim((string)$value);
    }
}
