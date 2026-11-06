<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;

/**
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class RegistrationsController extends AppController
{
    /**
     * @var array<string>
     */
    private const WRITE_FIELDS = [
        'user_id',
        'team_id',
        'competition_id',
        'lunch_count',
        'extra_lunch_count',
        'competition_pipe_1',
        'competition_pipe_2',
        'competition_pipe_3',
        'extra_count_1',
        'extra_count_2',
        'extra_count_3',
        'visible',
        'pos',
    ];

    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $registrations = $this->paginate($this->Registrations->find()->contain($this->contain()));
        $paging = $this->pagingFor($registrations);
        $this->set('success', true);
        $this->set(compact('registrations', 'paging'));
        $this->viewBuilder()->setOption('serialize', ['success', 'registrations', 'paging']);
    }

    public function view(string|int|null $id = null): void
    {
        $this->request->allowMethod(['get']);
        $registration = $this->Registrations->get($id, contain: $this->contain());
        $this->set([
            'success' => true,
            'registration' => $registration,
        ]);
    }

    /**
     * Jelentkezők névsora és a klubelnök által beállított alcsapat.
     */
    public function roster(): void
    {
        $this->request->allowMethod(['get']);
        $registrations = $this->publicRows($this->Registrations->find('roster')->all());
        $this->set([
            'success' => true,
            'registrations' => $registrations,
        ]);
        $this->viewBuilder()->setOption('serialize', ['success', 'registrations']);
    }

    /**
     * A belépett tag saját nevezései és a hozzájuk rendelt alcsapat.
     */
    public function mine(): void
    {
        $this->request->allowMethod(['get']);
        $identity = $this->request->getAttribute('identity');
        $userId = $identity !== null ? ($identity['id'] ?? null) : null;
        $registrations = $this->publicRows(
            $this->Registrations->find('roster')
                ->where(['Registrations.user_id' => $userId])
                ->all()
        );
        $this->set([
            'success' => true,
            'registrations' => $registrations,
        ]);
        $this->viewBuilder()->setOption('serialize', ['success', 'registrations']);
    }

    public function add(): void
    {
        $this->request->allowMethod(['post']);
        $registration = $this->Registrations->newEmptyEntity();
        $this->saveRegistration($registration, true);
    }

    public function edit(string|int|null $id = null): void
    {
        $this->request->allowMethod(['put', 'patch']);
        $registration = $this->Registrations->get($id);
        $this->saveRegistration($registration, false);
    }

    public function delete(string|int|null $id = null): void
    {
        $this->request->allowMethod(['delete']);
        $registration = $this->Registrations->get($id);
        if (!$this->Registrations->delete($registration)) {
            $this->respondValidationError($registration);

            return;
        }
        $this->respondDeleted();
    }

    private function saveRegistration(\App\Model\Entity\Registration $registration, bool $created): void
    {
        $registration = $this->patchFromRequest($this->Registrations, $registration, self::WRITE_FIELDS);
        if (!$this->Registrations->save($registration)) {
            $this->respondValidationError($registration);

            return;
        }
        $registration = $this->Registrations->get($registration->id, contain: $this->contain());
        $this->respondSaved('registration', $registration, $created);
    }

    /**
     * @param iterable<\App\Model\Entity\Registration> $rows
     * @return array<int, array<string, string>>
     */
    private function publicRows(iterable $rows): array
    {
        $list = [];
        foreach ($rows as $row) {
            $list[] = [
                'name' => User::displayName($row->user),
                'team' => (string)($row->team->name ?? ''),
                'competition' => (string)($row->competition->name ?? ''),
            ];
        }

        return $list;
    }

    /**
     * A felhasználóból csak a nyilvános mezők mennek ki.
     *
     * @return array<string, mixed>
     */
    private function contain(): array
    {
        return [
            'Teams',
            'Competitions',
            'Users' => function (SelectQuery $query): SelectQuery {
                return $query->select(['id', 'username', 'email', 'first_name', 'last_name']);
            },
        ];
    }
}
