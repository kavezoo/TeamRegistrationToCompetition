<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Model\Entity\Competition;
use App\Model\Entity\Registration;
use App\Service\AssignmentNotifier;
use App\Service\RegistrationSignupNotifier;
use Cake\Database\Exception\QueryException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\Log\Log;
use PDOException;

class RegistrationsController extends AppController
{
    private ?string $lastSaveError = null;

    /**
     * A kiírt versenyek, ugyanúgy, ahogy a tag látja őket.
     */
    public function index(): void
    {
        $userId = $this->userId();
        $registrations = $this->fetchTable('Registrations');
        $mine = $registrations->find('roster')
            ->where(['Registrations.user_id' => $userId])
            ->all();

        $mineIds = [];
        $mineByCompetition = [];
        foreach ($mine as $registration) {
            $competitionId = (int)$registration->competition_id;
            $mineIds[] = $competitionId;
            $mineByCompetition[$competitionId] = $registration;
        }

        $competitions = $this->fetchTable('Competitions')->find();
        if ($mineIds === []) {
            $competitions->where(['Competitions.visible' => true]);
        } else {
            $competitions->where([
                'OR' => [
                    'Competitions.visible' => true,
                    'Competitions.id IN' => $mineIds,
                ],
            ]);
        }
        $competitions = $competitions
            ->orderBy(['Competitions.date_from' => 'ASC', 'Competitions.name' => 'ASC'])
            ->all();

        $this->set(compact('competitions', 'mineByCompetition'));
    }

    /**
     * Üres nevezés létrehozása. A darabszámokat a szerkesztő lapon adja meg.
     */
    public function add(string|int|null $competitionId = null): Response
    {
        $this->request->allowMethod(['post']);
        $competition = $this->openCompetition($competitionId);
        if ($competition === null) {
            $this->Flash->error('Erre a versenyre nem lehet jelentkezni.');

            return $this->redirect(['action' => 'index']);
        }

        $registrations = $this->fetchTable('Registrations');
        $existing = $registrations->find()
            ->where([
                'Registrations.user_id' => $this->userId(),
                'Registrations.competition_id' => $competition->id,
            ])
            ->first();
        if ($existing !== null) {
            return $this->redirect(['action' => 'edit', $existing->id]);
        }

        $data = [
            'user_id' => $this->userId(),
            'competition_id' => $competition->id,
            'team_id' => null,
            'comment' => null,
            'visible' => true,
            'pos' => 0,
        ];
        foreach (Registration::TITLED_COUNTS as $countField) {
            $data[$countField] = 0;
        }
        $registration = $registrations->newEntity($data);
        if ($this->saveRegistration($registrations, $registration)) {
            (new RegistrationSignupNotifier())->notify($registration->id);
            $this->Flash->success('Jelentkeztél a versenyre. Add meg, miből mennyit kérsz.');

            return $this->redirect(['action' => 'edit', $registration->id]);
        }

        $this->Flash->error($this->saveFailureMessage($registration));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * A klubelnök saját jelentkezése: csak a darabszámok és a megjegyzés.
     */
    public function edit(string|int|null $id = null): ?Response
    {
        $registrations = $this->fetchTable('Registrations');
        try {
            $registration = $registrations->get($id, contain: ['Competitions']);
        } catch (RecordNotFoundException) {
            $this->Flash->error('A jelentkezés nem található.');

            return $this->redirect(['action' => 'index']);
        }
        if ((string)$registration->user_id !== $this->userId()) {
            $this->Flash->error('Csak a saját jelentkezésedet módosíthatod itt.');

            return $this->redirect(['action' => 'index']);
        }

        $competition = $registration->competition;
        if ($this->request->is(['post', 'put', 'patch'])) {
            $registration = $registrations->patchEntity(
                $registration,
                $this->countData($competition, $this->request->getData()),
                ['fields' => array_merge(array_values(Registration::TITLED_COUNTS), ['comment'])],
            );
            if ($registrations->save($registration)) {
                $this->Flash->success('A jelentkezésed mentve.');

                return $this->redirectAfterSave();
            }
            $this->Flash->error('A jelentkezést nem sikerült menteni.');
        }

        $returnUrl = $this->request->getSession()->read($this->formCallerKey());
        if (!is_string($returnUrl) || !$this->isSafeReturnPath($returnUrl)) {
            $returnUrl = ['action' => 'index'];
        }
        $this->set(compact('registration', 'competition', 'returnUrl'));

        return null;
    }

    public function deleteOwn(string|int|null $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $registrations = $this->fetchTable('Registrations');
        try {
            $registration = $registrations->get($id, contain: ['Competitions', 'Users']);
        } catch (RecordNotFoundException) {
            $this->Flash->error('A jelentkezés nem található.');

            return $this->redirect(['action' => 'index']);
        }
        if ((string)$registration->user_id !== $this->userId()) {
            $this->Flash->error('Csak a saját jelentkezésedet törölheted itt.');

            return $this->redirect(['action' => 'index']);
        }
        if ($registration->competition?->registrationClosed()) {
            $this->Flash->error('A nevezési határidő lejárt, a jelentkezést már nem lehet törölni.');

            return $this->redirect(['action' => 'index']);
        }
        if ($registrations->delete($registration)) {
            (new RegistrationSignupNotifier())->notifyPresidentsWithdrawn($registration);
            $this->Flash->success('A jelentkezésedet töröltük.');
        } else {
            $this->Flash->error('A jelentkezést nem sikerült törölni.');
        }

        return $this->redirect(['action' => 'index']);
    }

    public function assign(string|int|null $id = null): Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $registrations = $this->fetchTable('Registrations');
        $registration = $registrations->get($id);
        $previousTeamId = $registration->get('team_id');
        $data = $this->request->getData();
        if (($data['team_id'] ?? '') === '') {
            $data['team_id'] = null;
        }
        $registration = $registrations->patchEntity($registration, $data, [
            'fields' => ['team_id'],
        ]);
        $teamId = $registration->get('team_id');
        $teamChanged = (string)($previousTeamId ?? '') !== (string)($teamId ?? '');
        if ($registrations->save($registration)) {
            $this->Flash->success('Az alcsapat beállítva.');
            if ($teamChanged && $teamId !== null && $teamId !== '') {
                (new AssignmentNotifier())->notify($registration->id);
            }
        } else {
            $this->Flash->error('Az alcsapatot nem sikerült beállítani.');
        }

        $referer = $this->request->referer(true);
        if (is_string($referer) && $this->isSafeReturnPath($referer)) {
            return $this->redirect($referer);
        }

        return $this->redirect([
            'controller' => 'Competitions',
            'action' => 'view',
            $registration->competition_id,
        ]);
    }

    /**
     * @param \Cake\ORM\Table $registrations
     */
    private function saveRegistration($registrations, Registration $registration): bool
    {
        $this->lastSaveError = null;
        try {
            $saved = (bool)$registrations->save($registration);
            if (!$saved) {
                $this->lastSaveError = (string)json_encode($registration->getErrors(), JSON_UNESCAPED_UNICODE);
            }

            return $saved;
        } catch (QueryException | PDOException $exception) {
            $this->lastSaveError = $exception->getMessage();
            Log::error('A nevezés mentése adatbázis-hibával állt meg: ' . $exception->getMessage());

            return false;
        }
    }

    private function saveFailureMessage(Registration $registration): string
    {
        $detail = (string)$this->lastSaveError;
        Log::error('A nevezés mentése sikertelen: ' . $detail);
        if (stripos($detail, 'team_id') !== false) {
            return 'A jelentkezést nem sikerült rögzíteni, mert az alcsapat mezője az adatbázisban még kötelező.';
        }

        return 'A jelentkezést nem sikerült rögzíteni.';
    }

    private function openCompetition(string|int|null $id): ?Competition
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

    private function userId(): string
    {
        $identity = $this->request->getAttribute('identity');

        return (string)($identity['id'] ?? '');
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
}
