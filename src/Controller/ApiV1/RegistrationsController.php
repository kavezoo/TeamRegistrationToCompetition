<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Model\Entity\Competition;
use App\Model\Entity\Registration;
use App\Model\Entity\User;
use Cake\Datasource\Exception\RecordNotFoundException;
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
        'comment',
        'visible',
        'pos',
    ];

    public function index(): void
    {
        $this->request->allowMethod(['get']);
        if (!$this->canUse()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $query = $this->Registrations->find()->contain($this->contain());
        if ($this->role() === User::ROLE_MEMBER) {
            $query->where(['Registrations.user_id' => $this->identityId()]);
        }
        $registrations = $this->paginate($query);
        $paging = $this->pagingFor($registrations);
        $this->set('success', true);
        $this->set(compact('registrations', 'paging'));
        $this->viewBuilder()->setOption('serialize', ['success', 'registrations', 'paging']);
    }

    public function view(string|int|null $id = null): void
    {
        $this->request->allowMethod(['get']);
        if (!$this->canUse()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $registration = $this->Registrations->get($id, contain: $this->contain());
        if ($this->role() === User::ROLE_MEMBER && (string)$registration->user_id !== $this->identityId()) {
            $this->respondNotFound('A jelentkezés nem található.');

            return;
        }
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
        $registrations = $this->ownRows(
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
        if ($this->role() === User::ROLE_ADMIN) {
            $registration = $this->Registrations->newEmptyEntity();
            $this->saveRegistration($registration, true);

            return;
        }
        if ($this->role() !== User::ROLE_MEMBER && $this->role() !== User::ROLE_PRESIDENT) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $this->addOwn();
    }

    public function edit(string|int|null $id = null): void
    {
        $this->request->allowMethod(['put', 'patch']);
        $registration = $this->Registrations->get($id, contain: ['Competitions']);
        if ($this->role() === User::ROLE_ADMIN) {
            $this->saveRegistration($registration, false);

            return;
        }
        if (!$this->canUse()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $this->editOwnOrAssign($registration);
    }

    public function delete(string|int|null $id = null): void
    {
        $this->request->allowMethod(['delete']);
        $registration = $this->Registrations->get($id);
        if ($this->role() !== User::ROLE_ADMIN && (string)$registration->user_id !== $this->identityId()) {
            $this->respondForbidden('Csak a saját jelentkezésedet törölheted.');

            return;
        }
        if (!$this->canUse() && $this->role() !== User::ROLE_ADMIN) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
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

    private function addOwn(): void
    {
        $competition = $this->openCompetition($this->request->getData('competition_id'));
        if ($competition === null) {
            $this->response = $this->response->withStatus(422);
            $this->set([
                'success' => false,
                'message' => 'Erre a versenyre nem lehet jelentkezni.',
            ]);

            return;
        }
        $userId = (string)$this->identityId();
        $existing = $this->Registrations->find()
            ->where([
                'Registrations.user_id' => $userId,
                'Registrations.competition_id' => $competition->id,
            ])
            ->first();
        if ($existing !== null) {
            $existing = $this->Registrations->get($existing->id, contain: $this->contain());
            $this->respondSaved('registration', $existing, false);

            return;
        }
        $data = $this->countData($competition, $this->request->getData());
        $data['user_id'] = $userId;
        $data['competition_id'] = $competition->id;
        $data['team_id'] = null;
        $data['visible'] = true;
        $data['pos'] = 0;
        $registration = $this->Registrations->newEntity($data);
        if (!$this->Registrations->save($registration)) {
            $this->respondValidationError($registration);

            return;
        }
        $registration = $this->Registrations->get($registration->id, contain: $this->contain());
        $this->respondSaved('registration', $registration, true);
    }

    private function editOwnOrAssign(Registration $registration): void
    {
        $isOwner = (string)$registration->user_id === $this->identityId();
        $posted = $this->request->getData();
        $data = [];
        $fields = [];
        if ($isOwner && $registration->competition instanceof Competition) {
            $data = $this->countData($registration->competition, $posted);
            $fields = array_merge(array_values(Registration::TITLED_COUNTS), ['comment']);
        }
        if ($this->role() === User::ROLE_PRESIDENT && array_key_exists('team_id', $posted)) {
            $teamId = $posted['team_id'];
            $data['team_id'] = ($teamId === null || $teamId === '') ? null : (int)$teamId;
            $fields[] = 'team_id';
        }
        if ($fields === []) {
            $this->respondForbidden('Csak a saját jelentkezésedet módosíthatod.');

            return;
        }
        $registration = $this->Registrations->patchEntity($registration, $data, [
            'fields' => $fields,
        ]);
        if (!$this->Registrations->save($registration)) {
            $this->respondValidationError($registration);

            return;
        }
        $registration = $this->Registrations->get($registration->id, contain: $this->contain());
        $this->respondSaved('registration', $registration, false);
    }

    private function canUse(): bool
    {
        return in_array($this->role(), [User::ROLE_ADMIN, User::ROLE_PRESIDENT, User::ROLE_MEMBER], true);
    }

    private function openCompetition(mixed $id): ?Competition
    {
        if ($id === null || $id === '') {
            return null;
        }
        try {
            $competition = $this->fetchTable('Competitions')->get($id);
        } catch (RecordNotFoundException) {
            return null;
        }
        if (!(bool)$competition->visible) {
            return null;
        }
        if ($competition->date_deadline !== null && $competition->date_deadline->isPast()) {
            return null;
        }

        return $competition;
    }

    /**
     * @param array<string, mixed> $posted
     * @return array<string, int|string|null>
     */
    private function countData(Competition $competition, array $posted): array
    {
        $data = [];
        foreach (Registration::TITLED_COUNTS as $titleField => $countField) {
            $title = trim((string)$competition->get($titleField));
            if ($title === '') {
                $data[$countField] = 0;
                continue;
            }
            $value = (int)($posted[$countField] ?? 0);
            if ($value < 0) {
                $value = 0;
            }
            if ($value > 1000) {
                $value = 1000;
            }
            $data[$countField] = $value;
        }
        $comment = trim((string)($posted['comment'] ?? ''));
        $data['comment'] = $comment === '' ? null : mb_substr($comment, 0, 1000);

        return $data;
    }

    /**
     * @param iterable<\App\Model\Entity\Registration> $rows
     * @return array<int, array<string, mixed>>
     */
    private function ownRows(iterable $rows): array
    {
        $list = [];
        foreach ($rows as $row) {
            $item = [
                'id' => $row->id,
                'name' => User::displayName($row->user),
                'team' => (string)($row->team->name ?? ''),
                'team_id' => $row->team_id,
                'competition' => (string)($row->competition->name ?? ''),
                'competition_id' => $row->competition_id,
                'description' => (string)($row->competition->description ?? ''),
                'comment' => $row->comment,
                'date_from' => $row->competition->date_from ?? null,
                'date_deadline' => $row->competition->date_deadline ?? null,
            ];
            foreach (Registration::TITLED_COUNTS as $titleField => $countField) {
                $item[$titleField] = (string)($row->competition->get($titleField) ?? '');
                $item[$countField] = (int)$row->get($countField);
            }
            $list[] = $item;
        }

        return $list;
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
