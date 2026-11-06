<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Model\Entity\User;
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
        'date_from',
        'date_deadline',
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
        if ($this->role() === User::ROLE_MEMBER) {
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
        if ($this->role() === User::ROLE_MEMBER && !$this->memberCanSee((int)$competition->id, (bool)$competition->visible)) {
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
        if ($this->role() !== User::ROLE_ADMIN) {
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
        $this->respondSaved('competition', $competition, $created);
    }

    private function canRead(): bool
    {
        return in_array($this->role(), [User::ROLE_ADMIN, User::ROLE_PRESIDENT, User::ROLE_MEMBER], true);
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
        if ($this->role() !== User::ROLE_PRESIDENT && $this->role() !== User::ROLE_ADMIN) {
            return [];
        }

        return [
            'Registrations' => function (SelectQuery $query): SelectQuery {
                return $query->contain([
                    'Teams',
                    'Users' => function (SelectQuery $users): SelectQuery {
                        return $users->select(['id', 'username', 'email', 'first_name', 'last_name']);
                    },
                ]);
            },
        ];
    }
}
