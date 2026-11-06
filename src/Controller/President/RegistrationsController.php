<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Model\Entity\Competition;
use App\Model\Entity\Registration;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;

class RegistrationsController extends AppController
{
    /**
     * A klubelnök saját nevezése a kiírt versenyre.
     */
    public function join(string|int|null $competitionId = null): Response
    {
        $this->request->allowMethod(['post']);
        $competitions = $this->fetchTable('Competitions');
        try {
            $competition = $competitions->get($competitionId);
        } catch (RecordNotFoundException) {
            $this->Flash->error('A verseny nem található.');

            return $this->redirect(['controller' => 'Competitions', 'action' => 'index']);
        }

        $registrations = $this->fetchTable('Registrations');
        $userId = $this->userId();
        $existing = $registrations->find()
            ->where([
                'Registrations.user_id' => $userId,
                'Registrations.competition_id' => $competition->id,
            ])
            ->first();
        if ($existing !== null) {
            return $this->redirect(['action' => 'edit', $existing->id]);
        }

        $data = [
            'user_id' => $userId,
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
        if ($registrations->save($registration)) {
            $this->Flash->success('Jelentkeztél a versenyre. Add meg, miből mennyit kérsz.');

            return $this->redirect(['action' => 'edit', $registration->id]);
        }

        $this->Flash->error('A jelentkezést nem sikerült rögzíteni.');

        return $this->redirect(['controller' => 'Competitions', 'action' => 'view', $competition->id]);
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

            return $this->redirect(['controller' => 'Competitions', 'action' => 'index']);
        }
        if ((string)$registration->user_id !== $this->userId()) {
            $this->Flash->error('Csak a saját jelentkezésedet módosíthatod itt.');

            return $this->redirect([
                'controller' => 'Competitions',
                'action' => 'view',
                $registration->competition_id,
            ]);
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

                return $this->redirectAfterSave([
                    'controller' => 'Competitions',
                    'action' => 'view',
                    $competition->id,
                ]);
            }
            $this->Flash->error('A jelentkezést nem sikerült menteni.');
        }

        $returnUrl = $this->request->getSession()->read($this->formCallerKey());
        if (!is_string($returnUrl) || !$this->isSafeReturnPath($returnUrl)) {
            $returnUrl = ['controller' => 'Competitions', 'action' => 'view', $competition->id];
        }
        $this->set(compact('registration', 'competition', 'returnUrl'));

        return null;
    }

    public function deleteOwn(string|int|null $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $registrations = $this->fetchTable('Registrations');
        try {
            $registration = $registrations->get($id);
        } catch (RecordNotFoundException) {
            $this->Flash->error('A jelentkezés nem található.');

            return $this->redirect(['controller' => 'Competitions', 'action' => 'index']);
        }
        $competitionId = $registration->competition_id;
        if ((string)$registration->user_id !== $this->userId()) {
            $this->Flash->error('Csak a saját jelentkezésedet törölheted itt.');

            return $this->redirect(['controller' => 'Competitions', 'action' => 'view', $competitionId]);
        }
        if ($registrations->delete($registration)) {
            $this->Flash->success('A jelentkezésedet töröltük.');
        } else {
            $this->Flash->error('A jelentkezést nem sikerült törölni.');
        }

        return $this->redirectAfterSave([
            'controller' => 'Competitions',
            'action' => 'view',
            $competitionId,
        ]);
    }

    public function assign(string|int|null $id = null): Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $registrations = $this->fetchTable('Registrations');
        $registration = $registrations->get($id);
        $registration = $registrations->patchEntity($registration, $this->request->getData(), [
            'fields' => ['team_id'],
        ]);
        if ($registrations->save($registration)) {
            $this->Flash->success('Az alcsapat beállítva.');
        } else {
            $this->Flash->error('Az alcsapatot nem sikerült beállítani.');
        }

        return $this->redirect([
            'controller' => 'Competitions',
            'action' => 'view',
            $registration->competition_id,
        ]);
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
