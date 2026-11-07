<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Model\Entity\Competition;
use App\Model\Entity\User;
use App\Service\RegistrationReminderNotifier;
use Cake\Http\Response;
use Cake\I18n\DateTime;

class MembersController extends AppController
{
    public function index(): void
    {
        $competition = $this->openReminderCompetition();
        $membersPage = $this->paginate($this->recipients(), [
            'limit' => 100,
            'maxLimit' => 100,
        ]);
        $members = $membersPage->items()->toList();
        foreach ($members as $member) {
            User::publishAvatar($member);
        }

        $entries = [];
        if ($competition !== null) {
            $rows = $this->fetchTable('Registrations')->find()
                ->contain(['Teams'])
                ->where(['Registrations.competition_id' => $competition->id])
                ->all();
            foreach ($rows as $row) {
                $team = trim((string)($row->team->name ?? ''));
                $entries[(string)$row->user_id] = $team !== '' ? $team : null;
            }
        }

        $recentSent = $competition === null ? [] : $this->recentReminderTimes((int)$competition->id);
        $registered = [];
        foreach (array_keys($entries) as $userId) {
            $registered[$userId] = true;
        }

        $missing = $competition === null ? 0 : $this->missingCount($registered, $recentSent);
        // A lapozó a nézet változói között keresi a lapozott eredményt.
        $this->set(compact('competition', 'members', 'membersPage', 'entries', 'recentSent', 'missing'));
    }

    public function remind(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $competition = $this->competitionForRemind();
        if (!$competition instanceof Competition) {
            return $this->remindJson(false, $competition);
        }
        $member = $this->activeMember($id);
        if ($member === null) {
            return $this->remindJson(false, 'Ennek a felhasználónak nem lehet emlékeztetőt küldeni.');
        }
        if ($this->alreadyRegistered($member, $competition)) {
            return $this->remindJson(false, User::displayName($member) . ' már jelentkezett erre a versenyre.');
        }
        if (trim((string)$member->email) === '') {
            return $this->remindJson(false, User::displayName($member) . ' nem kapott levelet, nincs e-mail címe.');
        }
        if (!User::wantsMail($member, User::MAIL_REMINDER)) {
            return $this->remindJson(false, User::displayName($member) . ' nem kért emlékeztető levelet.');
        }
        $recent = $this->recentReminderTimes((int)$competition->id);
        if (isset($recent[(string)$member->id])) {
            return $this->remindJson(false, 'Ennek a tagnak egy napon belül már ment emlékeztető.');
        }
        $row = (new RegistrationReminderNotifier())->remind($member, $competition, $this->senderId());
        if ($row === null) {
            return $this->remindJson(false, 'Az emlékeztető nem ment ki.');
        }
        $this->rememberLastRecord((string)$member->id);
        $sentAt = $row->created instanceof \DateTimeInterface
            ? $row->created->format('Y.m.d. H:i')
            : '';

        return $this->remindJson(true, 'Emlékeztető e-mail kiküldve', $sentAt, (string)$row->message);
    }

    public function remindMissing(): Response
    {
        $this->request->allowMethod(['post']);
        $competition = $this->openCompetition();
        if ($competition instanceof Response) {
            return $competition;
        }
        $members = $this->recipients()->all();
        $registered = [];
        $rows = $this->fetchTable('Registrations')->find()
            ->select(['user_id'])
            ->where(['competition_id' => $competition->id])
            ->all();
        foreach ($rows as $row) {
            $registered[(string)$row->user_id] = true;
        }

        $notifier = new RegistrationReminderNotifier();
        $senderId = $this->senderId();
        $recent = $this->recentReminderTimes((int)$competition->id);
        $sent = 0;
        $failed = 0;
        $lastId = null;
        foreach ($members as $member) {
            $memberId = (string)$member->id;
            if (
                isset($registered[$memberId])
                || isset($recent[$memberId])
                || trim((string)$member->email) === ''
                || !User::wantsMail($member, User::MAIL_REMINDER)
            ) {
                continue;
            }
            if ($notifier->remind($member, $competition, $senderId)) {
                $sent++;
                $lastId = (string)$member->id;
            } else {
                $failed++;
            }
        }
        if ($lastId !== null) {
            $this->rememberLastRecord($lastId);
        }
        if ($sent > 0) {
            $this->Flash->success($sent . ' emlékeztető elment.');
        }
        if ($failed > 0) {
            $this->Flash->error($failed . ' emlékeztető nem ment ki.');
        }
        if ($sent === 0 && $failed === 0) {
            $this->Flash->success('Nincs olyan aktív tag, aki még nem jelentkezett.');
        }

        return $this->back();
    }

    /**
     * A legutóbb kiírt, a tagoknak látható és még nyitott verseny.
     */
    private function openReminderCompetition(): ?Competition
    {
        $rows = $this->fetchTable('Competitions')->find()
            ->where(['visible' => true])
            ->orderBy(['created' => 'DESC', 'id' => 'DESC'])
            ->all();
        foreach ($rows as $competition) {
            if ($competition instanceof Competition && !$competition->registrationClosed()) {
                return $competition;
            }
        }

        return null;
    }

    /**
     * @param array<string, true> $registered
     * @param array<string, string> $recentSent
     */
    private function missingCount(array $registered, array $recentSent): int
    {
        $count = 0;
        foreach ($this->recipients()->all() as $member) {
            $memberId = (string)$member->id;
            if (isset($registered[$memberId]) || isset($recentSent[$memberId]) || trim((string)$member->email) === '') {
                continue;
            }
            $count++;
        }

        return $count;
    }

    /**
     * Az elmúlt egy napban kiküldött emlékeztetők ideje ennél a versenynél.
     *
     * @return array<string, string>
     */
    private function recentReminderTimes(int $competitionId): array
    {
        $rows = $this->fetchTable('RegistrationReminders')->find()
            ->select(['user_id', 'created'])
            ->where([
                'competition_id' => $competitionId,
                'created >=' => DateTime::now()->subDays(1),
            ])
            ->orderBy(['created' => 'DESC'])
            ->all();
        $sent = [];
        foreach ($rows as $row) {
            $userId = (string)$row->user_id;
            if (isset($sent[$userId]) || !$row->created instanceof \DateTimeInterface) {
                continue;
            }
            $sent[$userId] = $row->created->format('Y.m.d. H:i');
        }

        return $sent;
    }

    /**
     * @return \App\Model\Entity\Competition|string
     */
    private function competitionForRemind(): Competition|string
    {
        $id = (int)$this->request->getData('competition_id');
        $competition = $id > 0
            ? $this->fetchTable('Competitions')->find()->where(['id' => $id])->first()
            : $this->openReminderCompetition();
        if (!$competition instanceof Competition) {
            return $id > 0
                ? 'A verseny nem található.'
                : 'Nincs nyitott verseny, emlékeztetőt nem lehet küldeni.';
        }
        if (!$competition->visible) {
            return 'A verseny a tagoknak nem látható, emlékeztetőt nem küldök.';
        }
        if ($competition->registrationClosed()) {
            return 'A nevezési határidő lejárt, emlékeztetőt nem küldök.';
        }

        return $competition;
    }

    private function remindJson(bool $ok, string $message, ?string $sentAt = null, ?string $body = null): Response
    {
        $payload = ['ok' => $ok, 'message' => $message];
        if ($sentAt !== null && $sentAt !== '') {
            $payload['sentAt'] = $sentAt;
        }
        if ($body !== null && $body !== '') {
            $payload['body'] = $body;
        }
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        return $this->response
            ->withType('application/json')
            ->withStringBody($json === false ? '{"ok":false,"message":"Az emlékeztető nem ment ki."}' : $json);
    }

    private function openCompetition(): Competition|Response
    {
        $id = (int)$this->request->getData('competition_id');
        $competitions = $this->fetchTable('Competitions');
        $competition = $competitions->find()->where(['id' => $id])->first();
        if (!$competition instanceof Competition) {
            $this->Flash->error('A verseny nem található.');

            return $this->redirect(['action' => 'index']);
        }
        if (!$competition->visible) {
            $this->Flash->error('A verseny a tagoknak nem látható, emlékeztetőt nem küldök.');

            return $this->back();
        }
        if ($competition->registrationClosed()) {
            $this->Flash->error('A nevezési határidő lejárt, emlékeztetőt nem küldök.');

            return $this->back();
        }

        return $competition;
    }

    /**
     * Aktív tagok és az összes aktív elnök.
     */
    private function recipients(): \Cake\ORM\Query\SelectQuery
    {
        return $this->fetchTable('Users')->find()
            ->contain(User::socialAvatarContain())
            ->where([
                'active' => true,
                'enabled' => true,
                'role IN' => [User::ROLE_MEMBER, User::ROLE_PRESIDENT],
            ])
            ->orderBy(['first_name' => 'ASC', 'last_name' => 'ASC']);
    }

    private function activeMember(string $id): ?object
    {
        $person = $this->recipients()->where(['Users.id' => $id])->first();

        return $person;
    }

    private function alreadyRegistered(object $member, Competition $competition): bool
    {
        return $this->fetchTable('Registrations')->exists([
            'user_id' => $member->id,
            'competition_id' => $competition->id,
        ]);
    }

    private function senderId(): string
    {
        $identity = $this->request->getAttribute('identity');

        return (string)($identity['id'] ?? '');
    }

    private function back(): Response
    {
        $page = (int)$this->request->getQuery('page');
        if ($page > 1) {
            return $this->redirect(['action' => 'index', '?' => ['page' => $page]]);
        }

        return $this->redirect(['action' => 'index']);
    }
}
