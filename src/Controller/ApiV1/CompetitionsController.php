<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Model\Entity\User;
use App\Service\CompetitionNotifier;
use Cake\ORM\Query\SelectQuery;

/**
 * @property \App\Model\Table\CompetitionsTable $Competitions
 */
class CompetitionsController extends AppController
{
    /**
     * Írható mezők. A számláló és az időbélyegek nem jönnek a klienstől.
     *
     * @var array<string>
     */
    private const WRITE_FIELDS = [
        'name',
        'lunch_title',
        'special_lunch_title',
        'competition_pipe_title_1',
        'competition_pipe_title_2',
        'competition_pipe_title_3',
        'extra_title_1',
        'extra_title_2',
        'extra_title_3',
        'comment',
        'date_competition',
        'date_from',
        'date_deadline',
        'map_url',
        'info_url',
        'description',
        'visible',
        'pos',
    ];

    public function index(): void
    {
        $this->request->allowMethod(['get']);
        if (!$this->canRead()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $query = $this->Competitions->find()->orderBy(['date_from' => 'ASC', 'name' => 'ASC']);
        $this->hideFinished($query);
        if ($this->currentRole() === User::ROLE_MEMBER) {
            $this->hideNotYetListed($query);
            $this->limitMemberList($query);
        }
        $competitions = $this->paginate($query);
        $paging = $this->pagingFor($competitions);
        $this->set(compact('competitions', 'paging'));
        $this->viewBuilder()->setOption('serialize', ['success', 'competitions', 'paging']);
        $this->set('success', true);
    }

    /**
     * Lejárt versenyek, a legutóbbi elöl.
     */
    public function past(): void
    {
        $this->request->allowMethod(['get']);
        if (!$this->canRead()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $query = $this->Competitions->find()
            ->where([
                'Competitions.date_competition IS NOT' => null,
                'Competitions.date_competition <' => \Cake\I18n\DateTime::now(),
            ])
            ->orderBy(['Competitions.date_competition' => 'DESC', 'Competitions.name' => 'ASC']);
        if ($this->currentRole() === User::ROLE_MEMBER) {
            $this->limitMemberList($query);
        }
        $competitions = $this->paginate($query);
        $paging = $this->pagingFor($competitions);
        $this->set(compact('competitions', 'paging'));
        $this->viewBuilder()->setOption('serialize', ['success', 'competitions', 'paging']);
        $this->set('success', true);
    }

    public function view(string|int|null $id = null): void
    {
        $this->request->allowMethod(['get']);
        if (!$this->canRead()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $competition = $this->Competitions->get($id, contain: $this->viewContain());
        if ($competition->has('registrations')) {
            foreach ($competition->registrations as $registration) {
                if (is_object($registration->user)) {
                    User::publishAvatar($registration->user);
                }
            }
        }
        if ($this->currentRole() === User::ROLE_MEMBER && !$this->memberCanSee((int)$competition->id, (bool)$competition->visible)) {
            $this->respondNotFound('A verseny nem található.');

            return;
        }
        $this->set([
            'success' => true,
            'competition' => $competition,
        ]);
    }

    public function add(): void
    {
        $this->request->allowMethod(['post']);
        if (!$this->isManager()) {
            $this->respondForbidden('Versenyt csak a klubelnök vagy az admin írhat ki.');

            return;
        }
        $competition = $this->Competitions->newEmptyEntity();
        $this->saveCompetition($competition, true);
    }

    public function edit(string|int|null $id = null): void
    {
        $this->request->allowMethod(['put', 'patch']);
        if (!$this->isManager()) {
            $this->respondForbidden('Versenyt csak a klubelnök vagy az admin módosíthat.');

            return;
        }
        $competition = $this->Competitions->get($id);
        $this->saveCompetition($competition, false);
    }

    public function delete(string|int|null $id = null): void
    {
        $this->request->allowMethod(['delete']);
        if ($this->currentRole() !== User::ROLE_ADMIN) {
            $this->respondForbidden('A versenyt csak admin törölheti.');

            return;
        }
        $competition = $this->Competitions->get($id);
        if ($this->Competitions->Registrations->exists(['competition_id' => $competition->id])) {
            $this->respondConflict('A verseny nem törölhető, mert tartozik hozzá nevezés.');

            return;
        }
        if (!$this->Competitions->delete($competition)) {
            $this->respondValidationError($competition);

            return;
        }
        $this->respondDeleted();
    }

    private function saveCompetition(\App\Model\Entity\Competition $competition, bool $created): void
    {
        $competition = $this->patchFromRequest($this->Competitions, $competition, self::WRITE_FIELDS);
        if (!$this->Competitions->save($competition)) {
            $this->respondValidationError($competition);

            return;
        }
        if ($created) {
            (new CompetitionNotifier())->notifyMembers($competition);
        }
        $this->respondSaved('competition', $competition, $created);
    }

    /**
     * A versenyidő elmúltával a kiírás kikerül az aktuális listából.
     */
    private function hideFinished(SelectQuery $query): void
    {
        $query->where([
            'OR' => [
                'Competitions.date_competition IS' => null,
                'Competitions.date_competition >=' => \Cake\I18n\DateTime::now(),
            ],
        ]);
    }

    /**
     * A tag a date_from napjától látja a kiírást. Előtte csak az elnök szerkeszti.
     */
    private function hideNotYetListed(SelectQuery $query): void
    {
        $query->where([
            'OR' => [
                'Competitions.date_from IS' => null,
                'Competitions.date_from <=' => \Cake\I18n\Date::today(),
            ],
        ]);
    }

    private function canRead(): bool
    {
        return in_array($this->currentRole(), [User::ROLE_ADMIN, User::ROLE_PRESIDENT, User::ROLE_MEMBER], true);
    }

    private function limitMemberList(SelectQuery $query): void
    {
        $joined = $this->joinedCompetitionIds();
        if ($joined === []) {
            $query->where(['Competitions.visible' => true]);

            return;
        }
        $query->where([
            'OR' => [
                'Competitions.visible' => true,
                'Competitions.id IN' => $joined,
            ],
        ]);
    }

    private function memberCanSee(int $competitionId, bool $visible): bool
    {
        return $visible || in_array($competitionId, $this->joinedCompetitionIds(), true);
    }

    /**
     * @return array<int>
     */
    private function joinedCompetitionIds(): array
    {
        $userId = $this->identityId();
        if ($userId === null) {
            return [];
        }
        $ids = $this->Competitions->Registrations->find()
            ->select(['competition_id'])
            ->where(['Registrations.user_id' => $userId])
            ->all()
            ->extract('competition_id')
            ->toList();

        return array_map(intval(...), $ids);
    }

    /**
     * @return array<string, mixed>
     */
    private function viewContain(): array
    {
        if ($this->currentRole() !== User::ROLE_PRESIDENT && $this->currentRole() !== User::ROLE_ADMIN) {
            return [];
        }

        return [
            'Registrations' => function (SelectQuery $query): SelectQuery {
                return $query->contain([
                    'Teams',
                    'Users' => function (SelectQuery $users): SelectQuery {
                        return $users
                            ->select(['id', 'username', 'email', 'first_name', 'last_name'])
                            ->contain(User::socialAvatarContain());
                    },
                ]);
            },
        ];
    }
}
