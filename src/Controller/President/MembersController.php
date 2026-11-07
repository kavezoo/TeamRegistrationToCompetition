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
        $competitions = $this->fetchTable('Competitions')->find()
            ->orderBy(['date_from' => 'ASC', 'name' => 'ASC'])
            ->all()
            ->toList();
        $competition = $this->selectedCompetition($competitions);
        $members = $this->fetchTable('Users')->find()
            ->contain(User::socialAvatarContain())
            ->where([
                'role' => User::ROLE_MEMBER,
                'active' => true,
                'enabled' => true,
            ])
            ->orderBy(['first_name' => 'ASC', 'last_name' => 'ASC'])
            ->all()
            ->toList();
        foreach ($members as $member) {
            User::publishAvatar($member);
        }

        $registered = [];
        if ($competition !== null && $members !== []) {
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

        $this->set(compact('competitions', 'competition', 'members', 'registered', 'history'));
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
            $this->Flash->error('Csak aktív tagnak lehet emlékeztetőt küldeni.');

            return $this->back($competition);
        }
        if ($this->alreadyRegistered($member, $competition)) {
            $this->Flash->error(User::displayName($member) . ' már jelentkezett erre a versenyre.');

            return $this->back($competition);
        }
        if (trim((string)$member->email) === '') {
            $this->Flash->error(User::displayName($member) . ' nem kapott levelet, nincs e-mail címe.');

            return $this->back($competition);
        }
        $sent = (new RegistrationReminderNotifier())->remind($member, $competition, $this->senderId());
        if ($sent) {
            $this->rememberLastRecord((string)$member->id);
            $this->Flash->success('Az emlékeztető elment: ' . User::displayName($member) . '.');
        } else {
            $this->Flash->error('Az emlékeztető nem ment ki.');
        }

        return $this->back($competition);
    }

    public function remindMissing(): Response
    {
        $this->request->allowMethod(['post']);
        $competition = $this->openCompetition();
        if ($competition instanceof Response) {
            return $competition;
        }
        $members = $this->fetchTable('Users')->find()
            ->where([
                'role' => User::ROLE_MEMBER,
                'active' => true,
                'enabled' => true,
            ])
            ->all();
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

        return $this->back($competition);
    }

    /**
     * @param array<int, \App\Model\Entity\Competition> $competitions
     */
    private function selectedCompetition(array $competitions): ?Competition
    {
        $requested = (int)$this->request->getQuery('competition_id');
        foreach ($competitions as $competition) {
            if ((int)$competition->id === $requested) {
                return $competition;
            }
        }
        foreach ($competitions as $competition) {
            if ($competition->visible && !$competition->registrationClosed()) {
                return $competition;
            }
        }

        return $competitions[0] ?? null;
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

            return $this->back($competition);
        }
        if ($competition->registrationClosed()) {
            $this->Flash->error('A nevezési határidő lejárt, emlékeztetőt nem küldök.');

            return $this->back($competition);
        }

        return $competition;
    }

    private function activeMember(string $id): ?object
    {
        return $this->fetchTable('Users')->find()
            ->where([
                'id' => $id,
                'role' => User::ROLE_MEMBER,
                'active' => true,
                'enabled' => true,
            ])
            ->first();
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

    private function back(Competition $competition): Response
    {
        return $this->redirect(['action' => 'index', '?' => ['competition_id' => $competition->id]]);
    }
}
