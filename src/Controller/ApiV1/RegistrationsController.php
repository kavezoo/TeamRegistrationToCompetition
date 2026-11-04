<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

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
