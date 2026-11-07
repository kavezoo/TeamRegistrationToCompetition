<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Model\Entity\Competition;
use App\Model\Entity\User;
use App\Service\RegistrationReminderNotifier;
use Cake\Http\Response;

class MembersController extends AppController
{
    public function index(): void
    {
        $competition = $this->openReminderCompetition();
        $membersPage = $this->paginate($this->recipients(), [
            'limit' => 100,
            'maxLimit' => 100,
        ]);
        $members = [];
        foreach ($membersPage as $member) {
            User::publishAvatar($member);
            $members[] = $member;
        }

        $registered = [];
        if ($competition !== null) {
            $rows = $this->fetchTable('Registrations')->find()
                ->select(['user_id'])
                ->where(['competition_id' => $competition->id])
                ->all();
            foreach ($rows as $row) {
                $registered[(string)$row->user_id] = true;
            }
        }

        $history = [];
        $memberIds = array_map(static fn ($member): string => (string)$member->id, $members);
        if ($memberIds !== []) {
            $reminders = $this->fetchTable('RegistrationReminders')->find()
                ->where(['user_id IN' => $memberIds])
                ->orderBy(['created' => 'DESC'])
                ->all();
            foreach ($reminders as $reminder) {
                $history[(string)$reminder->user_id][] = $reminder;
            }
        }

        $missing = $competition === null ? 0 : $this->missingCount($registered);
        // A lapozó a nézet változói között keresi a lapozott eredményt.
        $this->set(compact('competition', 'members', 'membersPage', 'registered', 'history', 'missing'));
    }

    public function remind(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $competition = $this->openCompetition();
        if ($competition instanceof Response) {
            return $competition;
        }
        $member = $this->activeMember($id);
        if ($member === null) {
            $this->Flash->error('Ennek a felhasználónak nem lehet emlékeztetőt küldeni.');

            return $this->back();
        }
        if ($this->alreadyRegistered($member, $competition)) {
            $this->Flash->error(User::displayName($member) . ' már jelentkezett erre a versenyre.');

            return $this->back();
        }
        if (trim((string)$member->email) === '') {
            $this->Flash->error(User::displayName($member) . ' nem kapott levelet, nincs e-mail címe.');

            return $this->back();
        }
        $sent = (new RegistrationReminderNotifier())->remind($member, $competition, $this->senderId());
        if ($sent) {
            $this->rememberLastRecord((string)$member->id);
            $this->Flash->success('Az emlékeztető elment: ' . User::displayName($member) . '.');
        } else {
            $this->Flash->error('Az emlékeztető nem ment ki.');
        }

        return $this->back();
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
        $sent = 0;
        $failed = 0;
        $lastId = null;
        foreach ($members as $member) {
            if (isset($registered[(string)$member->id]) || trim((string)$member->email) === '') {
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
     */
    private function missingCount(array $registered): int
    {
        $count = 0;
        foreach ($this->recipients()->all() as $member) {
            if (isset($registered[(string)$member->id]) || trim((string)$member->email) === '') {
                continue;
            }
            $count++;
        }

        return $count;
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
     * Aktív tagok, és a bejelentkezett elnök.
     */
    private function recipients(): \Cake\ORM\Query\SelectQuery
    {
        $presidentId = $this->senderId();

        return $this->fetchTable('Users')->find()
            ->contain(User::socialAvatarContain())
            ->where([
                'active' => true,
                'enabled' => true,
                'OR' => [
                    ['role' => User::ROLE_MEMBER],
                    [
                        'role' => User::ROLE_PRESIDENT,
                        'id' => $presidentId,
                    ],
                ],
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
