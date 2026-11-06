<?php
declare(strict_types=1);

namespace App\Controller\President;

use Cake\Http\Response;

class TeamsController extends AppController
{
    /**
     * @var array<string>
     */
    private const FIELDS = ['name', 'visible', 'pos'];

    public function index(): void
    {
        $teams = $this->fetchTable('Teams')->find()
            ->orderBy(['pos' => 'ASC', 'name' => 'ASC'])
            ->all();
        $this->set(compact('teams'));
    }

    public function add(): ?Response
    {
        $table = $this->fetchTable('Teams');
        $team = $table->newEmptyEntity();
        if ($this->request->is('post')) {
            $team = $table->patchEntity($team, $this->withDefaults($this->request->getData()), [
                'fields' => self::FIELDS,
            ]);
            if ($table->save($team)) {
                $this->Flash->success('Az alcsapat mentve.');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Az alcsapatot nem sikerült menteni.');
        }
        $this->set(compact('team'));

        return $this->render('form');
    }

    public function edit(string|int|null $id = null): ?Response
    {
        $table = $this->fetchTable('Teams');
        $team = $table->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $team = $table->patchEntity($team, $this->withDefaults($this->request->getData()), [
                'fields' => self::FIELDS,
            ]);
            if ($table->save($team)) {
                $this->Flash->success('Az alcsapat mentve.');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Az alcsapatot nem sikerült menteni.');
        }
        $this->set(compact('team'));

        return $this->render('form');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withDefaults(array $data): array
    {
        if (!array_key_exists('visible', $data)) {
            $data['visible'] = 0;
        }
        if (($data['pos'] ?? '') === '') {
            $data['pos'] = 0;
        }

        return $data;
    }
}
