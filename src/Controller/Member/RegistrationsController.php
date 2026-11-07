<?php
declare(strict_types=1);

namespace App\Controller\Member;

use App\Model\Entity\Competition;
use App\Model\Entity\Registration;
use Cake\Database\Exception\QueryException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\Log\Log;
use PDOException;

class RegistrationsController extends AppController
{
    private ?string $lastSaveError = null;

    public function index(): void
    {
        $userId = $this->userId();
        $registrations = $this->fetchTable('Registrations');
        $mine = $registrations->find('roster')
            ->where(['Registrations.user_id' => $userId])
            ->all();
        $rows = $registrations->find('roster')->all();

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

        $rosterRows = [];
        $rosterCompetition = $this->currentRosterCompetition($competitions);
        if (
            $rosterCompetition !== null
            && isset($mineByCompetition[(int)$rosterCompetition->id])
        ) {
            foreach ($rows as $row) {
                if ((int)$row->competition_id === (int)$rosterCompetition->id) {
                    $rosterRows[] = $row;
                }
            }
        }

        $this->set(compact('rosterRows', 'rosterCompetition', 'competitions', 'mineByCompetition'));
    }

    /**
     * A legközelebbi látható verseny, aminek az ideje még nem múlt el.
     *
     * @param iterable<\App\Model\Entity\Competition> $competitions
     */
    private function currentRosterCompetition(iterable $competitions): ?Competition
    {
        $current = null;
        foreach ($competitions as $competition) {
            if (!$competition->visible) {
                continue;
            }
            if ($competition->date_competition !== null && $competition->date_competition->isPast()) {
                continue;
            }
            $current = $this->soonerCompetition($current, $competition);
        }

        return $current;
    }

    /**
     * A közelebbi versenyidő az aktuális. Dátum nélkül a frissebben mentett marad.
     */
    private function soonerCompetition(?Competition $current, Competition $candidate): Competition
    {
        if ($current === null) {
            return $candidate;
        }
        $currentWhen = $current->date_competition;
        $candidateWhen = $candidate->date_competition;
        if ($candidateWhen !== null && ($currentWhen === null || $candidateWhen < $currentWhen)) {
            return $candidate;
        }
        if ($candidateWhen === null && $currentWhen === null) {
            $currentCreated = $current->created;
            $candidateCreated = $candidate->created;
            if ($candidateCreated !== null && ($currentCreated === null || $candidateCreated > $currentCreated)) {
                return $candidate;
            }
        }

        return $current;
    }

    /**
     * Üres nevezés létrehozása. A darabszámokat a szerkesztő lapon adja meg a tag.
     */
    public function add(?string $competitionId = null): ?Response
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
            $this->Flash->success('Jelentkeztél a versenyre. Add meg, miből mennyit kérsz.');

            return $this->redirect(['action' => 'edit', $registration->id]);
        }

        $this->Flash->error($this->saveFailureMessage($registration));

        return $this->redirect(['action' => 'index']);
    }

    public function edit(?string $id = null): ?Response
    {
        $registrations = $this->fetchTable('Registrations');
        try {
            $registration = $registrations->get($id, contain: ['Competitions']);
        } catch (RecordNotFoundException) {
            $this->Flash->error('A jelentkezés nem található.');

            return $this->redirect(['action' => 'index']);
        }
        if ((string)$registration->user_id !== $this->userId()) {
            $this->Flash->error('Ehhez a jelentkezéshez nincs hozzáférésed.');

            return $this->redirect(['action' => 'index']);
        }

        $competition = $registration->competition;
        if ($this->request->is(['patch', 'post', 'put'])) {
            $registration = $registrations->patchEntity(
                $registration,
                $this->countData($competition, $this->request->getData()),
                ['fields' => array_merge(array_values(Registration::TITLED_COUNTS), ['comment'])],
            );
            if ($this->saveRegistration($registrations, $registration)) {
                $this->Flash->success('A jelentkezésedet elmentettük.');

                return $this->redirectAfterSave();
            }
            $this->Flash->error($this->saveFailureMessage($registration));
        }

        $this->set(compact('registration', 'competition'));

        return null;
    }

    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $registrations = $this->fetchTable('Registrations');
        try {
            $registration = $registrations->get($id, contain: ['Competitions']);
        } catch (RecordNotFoundException) {
            $this->Flash->error('A jelentkezés nem található.');

            return $this->redirect(['action' => 'index']);
        }
        if ((string)$registration->user_id !== $this->userId()) {
            $this->Flash->error('Ehhez a jelentkezéshez nincs hozzáférésed.');

            return $this->redirect(['action' => 'index']);
        }
        if ($registration->competition?->registrationClosed()) {
            $this->Flash->error('A nevezési határidő lejárt, a jelentkezést már nem lehet törölni.');

            return $this->redirect(['action' => 'index']);
        }
        if ($registrations->delete($registration)) {
            $this->Flash->success('A jelentkezésedet töröltük.');
        } else {
            $this->Flash->error('A jelentkezést nem sikerült törölni.');
        }

        return $this->redirect(['action' => 'index']);
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

    private function userId(): string
    {
        $identity = $this->request->getAttribute('identity');

        return (string)($identity['id'] ?? '');
    }

    private function openCompetition(?string $id): ?Competition
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
     * Csak a kitöltött című darabszámok menthetők. A többi 0 marad.
     *
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
