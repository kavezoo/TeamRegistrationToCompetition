<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Model\Entity\Competition;
use App\Model\Entity\User;
use App\Service\RegistrationReminderNotifier;
use Cake\I18n\DateTime;

/**
 * A klubelnök taglistája és a nevezési emlékeztetők.
 */
class MembersController extends AppController
{
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        if (!$this->isManager()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $competition = $this->openReminderCompetition();
        $membersPage = $this->paginate($this->recipients(), [
            'limit' => 100,
            'maxLimit' => 100,
        ]);
        $entries = [];
        $recent = [];
        if ($competition !== null) {
            $rows = $this->fetchTable('Registrations')->find()
                ->contain(['Teams'])
                ->where(['Registrations.competition_id' => $competition->id])
                ->all();
            foreach ($rows as $row) {
                $team = trim((string)($row->team->name ?? ''));
                $entries[(string)$row->user_id] = $team !== '' ? $team : null;
            }
            $recent = $this->recentReminderTimes((int)$competition->id);
        }
        $members = [];
        foreach ($membersPage->items() as $member) {
            User::publishAvatar($member);
            $id = (string)$member->id;
            $members[] = [
                'id' => $id,
                'name' => User::displayName($member),
                'email' => $member->email,
                'avatar' => User::avatarUrl($member),
                'role' => (string)$member->get('role'),
                'registered' => array_key_exists($id, $entries),
                'team' => $entries[$id] ?? null,
                'reminded_at' => $recent[$id] ?? null,
            ];
        }
        $registered = [];
        foreach (array_keys($entries) as $userId) {
            $registered[$userId] = true;
        }
        $this->set([
            'success' => true,
            'competition' => $competition === null ? null : [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
            'missing' => $competition === null ? 0 : $this->missingCount($registered, $recent),
            'members' => $members,
            'paging' => $this->pagingFor($membersPage),
        ]);
        $this->viewBuilder()->setOption('serialize', ['success', 'competition', 'missing', 'members', 'paging']);
    }

    public function remind(string $id): void
    {
        $this->request->allowMethod(['post']);
        if (!$this->isManager()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $competition = $this->competitionForRemind();
        if (!$competition instanceof Competition) {
            $this->respondConflict($competition);

            return;
        }
        $member = $this->recipients()->where(['Users.id' => $id])->first();
        if ($member === null) {
            $this->respondNotFound('Ennek a felhasználónak nem lehet emlékeztetőt küldeni.');

            return;
        }
        if ($this->alreadyRegistered($member, $competition)) {
            $this->respondConflict(User::displayName($member) . ' már jelentkezett erre a versenyre.');

            return;
        }
        if (trim((string)$member->email) === '') {
            $this->respondConflict(User::displayName($member) . ' nem kapott levelet, nincs e-mail címe.');

            return;
        }
        if (!User::wantsMail($member, User::MAIL_REMINDER)) {
            $this->respondConflict(User::displayName($member) . ' nem kért emlékeztető levelet.');

            return;
        }
        $recent = $this->recentReminderTimes((int)$competition->id);
        if (isset($recent[(string)$member->id])) {
            $this->respondConflict('Ennek a tagnak egy napon belül már ment emlékeztető.');

            return;
        }
        $row = (new RegistrationReminderNotifier())->remind($member, $competition, (string)$this->identityId());
        if ($row === null) {
            $this->response = $this->response->withStatus(502);
            $this->set([
                'success' => false,
                'message' => 'Az emlékeztető nem ment ki.',
            ]);

            return;
        }
        $sentAt = $row->created instanceof \DateTimeInterface
            ? $row->created->format('Y.m.d. H:i')
            : '';
        $this->set([
            'success' => true,
            'message' => 'Emlékeztető e-mail kiküldve.',
            'sent_at' => $sentAt,
        ]);
    }

    public function remindMissing(): void
    {
        $this->request->allowMethod(['post']);
        if (!$this->isManager()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $competition = $this->competitionForRemind();
        if (!$competition instanceof Competition) {
            $this->respondConflict($competition);

            return;
        }
        $registered = [];
        $rows = $this->fetchTable('Registrations')->find()
            ->select(['user_id'])
            ->where(['competition_id' => $competition->id])
            ->all();
        foreach ($rows as $row) {
            $registered[(string)$row->user_id] = true;
        }
        $notifier = new RegistrationReminderNotifier();
        $senderId = (string)$this->identityId();
        $recent = $this->recentReminderTimes((int)$competition->id);
        $sent = 0;
        $failed = 0;
        foreach ($this->recipients()->all() as $member) {
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
            } else {
                $failed++;
            }
        }
        $message = $sent > 0
            ? $sent . ' emlékeztető elment.'
            : 'Nincs olyan aktív tag, aki még nem jelentkezett.';
        $this->set([
            'success' => $failed === 0,
            'message' => $message,
            'sent' => $sent,
            'failed' => $failed,
        ]);
    }

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

    /**
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
            if (!User::wantsMail($member, User::MAIL_REMINDER)) {
                continue;
            }
            $count++;
        }

        return $count;
    }

    private function recipients(): \Cake\ORM\Query\SelectQuery
    {
        $presidentId = (string)$this->identityId();

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

    private function alreadyRegistered(object $member, Competition $competition): bool
    {
        return $this->fetchTable('Registrations')->exists([
            'user_id' => $member->id,
            'competition_id' => $competition->id,
        ]);
    }
}
