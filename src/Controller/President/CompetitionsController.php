<?php
declare(strict_types=1);

namespace App\Controller\President;

use Cake\Http\Response;
use Cake\ORM\Query\SelectQuery;

class CompetitionsController extends AppController
{
    /**
     * @var array<string>
     */
    private const FIELDS = [
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
        $competitions = $this->fetchTable('Competitions')->find()
            ->orderBy(['date_from' => 'DESC', 'name' => 'ASC'])
            ->all();
        $this->set(compact('competitions'));
    }

    public function add(): ?Response
    {
        $competitions = $this->fetchTable('Competitions');
        $competition = $competitions->newEmptyEntity();
        if ($this->request->is('post')) {
            $competition = $competitions->patchEntity($competition, $this->withDefaults($this->request->getData()), [
                'fields' => self::FIELDS,
            ]);
            if ($competitions->save($competition)) {
                $this->Flash->success('A verseny kiírva.');

                return $this->redirectAfterSave();
            }
            $this->Flash->error('A versenyt nem sikerült menteni.');
        }
        $this->set(compact('competition'));

        return null;
    }

    public function edit(string|int|null $id = null): ?Response
    {
        $competitions = $this->fetchTable('Competitions');
        $competition = $competitions->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $competition = $competitions->patchEntity($competition, $this->withDefaults($this->request->getData()), [
                'fields' => self::FIELDS,
            ]);
            if ($competitions->save($competition)) {
                $this->Flash->success('A verseny mentve.');

                return $this->redirectAfterSave();
            }
            $this->Flash->error('A versenyt nem sikerült menteni.');
        }
        $this->set(compact('competition'));

        return null;
    }

    public function view(string|int|null $id = null): void
    {
        $competition = $this->fetchTable('Competitions')->get($id, contain: [
            'Registrations' => function (SelectQuery $query): SelectQuery {
                return $query
                    ->contain([
                        'Teams',
                        'Users' => function (SelectQuery $users): SelectQuery {
                            return $users->select(['id', 'username', 'email', 'first_name', 'last_name']);
                        },
                    ])
                    ->orderBy(['Users.first_name' => 'ASC', 'Users.last_name' => 'ASC']);
            },
        ]);
        $teams = $this->fetchTable('Teams')->find('list')->orderByAsc('name')->toArray();
        $this->set(compact('competition', 'teams'));
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
