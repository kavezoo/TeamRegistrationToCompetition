<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

/**
 * @property \App\Model\Table\TeamsTable $Teams
 */
class TeamsController extends AppController
{
    /**
     * @var array<string>
     */
    private const WRITE_FIELDS = [
        'name',
        'visible',
        'pos',
    ];

    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $teams = $this->paginate($this->Teams->find());
        $paging = $this->pagingFor($teams);
        $this->set('success', true);
        $this->set(compact('teams', 'paging'));
        $this->viewBuilder()->setOption('serialize', ['success', 'teams', 'paging']);
    }

    public function view(string|int|null $id = null): void
    {
        $this->request->allowMethod(['get']);
        $team = $this->Teams->get($id, contain: ['Registrations']);
        $this->set([
            'success' => true,
            'team' => $team,
        ]);
    }

    public function add(): void
    {
        $this->request->allowMethod(['post']);
        $team = $this->Teams->newEmptyEntity();
        $this->saveTeam($team, true);
    }

    public function edit(string|int|null $id = null): void
    {
        $this->request->allowMethod(['put', 'patch']);
        $team = $this->Teams->get($id);
        $this->saveTeam($team, false);
    }

    public function delete(string|int|null $id = null): void
    {
        $this->request->allowMethod(['delete']);
        $team = $this->Teams->get($id);
        if ($this->Teams->Registrations->exists(['team_id' => $team->id])) {
            $this->respondConflict('A csapat nem törölhető, mert tartozik hozzá nevezés.');

            return;
        }
        if (!$this->Teams->delete($team)) {
            $this->respondValidationError($team);

            return;
        }
        $this->respondDeleted();
    }

    private function saveTeam(\App\Model\Entity\Team $team, bool $created): void
    {
        $team = $this->patchFromRequest($this->Teams, $team, self::WRITE_FIELDS);
        if (!$this->Teams->save($team)) {
            $this->respondValidationError($team);

            return;
        }
        $this->respondSaved('team', $team, $created);
    }
}
