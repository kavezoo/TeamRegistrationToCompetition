<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

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
        'visible',
        'pos',
    ];

    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $competitions = $this->paginate($this->Competitions->find());
        $paging = $this->pagingFor($competitions);
        $this->set(compact('competitions', 'paging'));
        $this->viewBuilder()->setOption('serialize', ['success', 'competitions', 'paging']);
        $this->set('success', true);
    }

    public function view(string|int|null $id = null): void
    {
        $this->request->allowMethod(['get']);
        $competition = $this->Competitions->get($id, contain: ['Registrations']);
        $this->set([
            'success' => true,
            'competition' => $competition,
        ]);
    }

    public function add(): void
    {
        $this->request->allowMethod(['post']);
        $competition = $this->Competitions->newEmptyEntity();
        $this->saveCompetition($competition, true);
    }

    public function edit(string|int|null $id = null): void
    {
        $this->request->allowMethod(['put', 'patch']);
        $competition = $this->Competitions->get($id);
        $this->saveCompetition($competition, false);
    }

    public function delete(string|int|null $id = null): void
    {
        $this->request->allowMethod(['delete']);
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
}
