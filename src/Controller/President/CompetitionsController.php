<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Model\Entity\Competition;
use App\Service\CompetitionNotifier;
use Cake\Http\Response;

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
        $competitions = $this->fetchTable('Competitions')->find()
            ->orderBy(['created' => 'DESC', 'id' => 'DESC'])
            ->all();
        $registrations = $this->fetchTable('Registrations')->find('roster')->all();
        $this->set(compact('competitions', 'registrations'));
    }

    public function add(): ?Response
    {
        $competitions = $this->fetchTable('Competitions');
        $competition = $competitions->newEmptyEntity();
        $competition->set(Competition::TITLE_DEFAULTS);
        if ($this->request->is('post')) {
            $competition = $competitions->patchEntity($competition, $this->withDefaults($this->request->getData()), [
                'fields' => self::FIELDS,
            ]);
            if ($competitions->save($competition)) {
                (new CompetitionNotifier())->notifyMembers($competition);
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
        $competitions = $this->fetchTable('Competitions');
        $competition = $competitions->get($id);
        $competition->set('registrations', $this->fetchTable('Registrations')->find('roster')
            ->where(['Registrations.competition_id' => $competition->id])
            ->all()
            ->toList());
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
