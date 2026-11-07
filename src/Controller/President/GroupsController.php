<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Model\Entity\User;
use App\Service\AssignmentNotifier;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\ORM\Query\SelectQuery;

class GroupsController extends AppController
{
    /**
     * Minden jelentkező, a klubelnök saját nevezésével együtt, versenyenként.
     */
    public function index(): void
    {
        $rows = $this->fetchTable('Registrations')->find('roster')->all();
        $teams = $this->fetchTable('Teams')->find('list')->orderByAsc('name')->toArray();
        $this->set(compact('rows', 'teams'));
    }

    /**
     * Melyik jelentkező melyik alcsapatba kerül.
     */
    public function edit(string|int|null $id = null): ?Response
    {
        $registrations = $this->fetchTable('Registrations');
        try {
            $registration = $registrations->get($id, contain: [
                'Competitions',
                'Teams',
                'Users' => function (SelectQuery $users): SelectQuery {
                    return $users
                        ->select(['id', 'username', 'email', 'first_name', 'last_name'])
                        ->contain(User::socialAvatarContain());
                },
            ]);
        } catch (RecordNotFoundException) {
            $this->Flash->error('A jelentkezés nem található.');

            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
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

                return $this->redirectAfterSave();
            }
            $this->Flash->error('Az alcsapatot nem sikerült beállítani.');
        }

        $teams = $this->fetchTable('Teams')->find('list')->orderByAsc('name')->toArray();
        $returnUrl = $this->request->getSession()->read($this->formCallerKey());
        if (!is_string($returnUrl) || !$this->isSafeReturnPath($returnUrl)) {
            $returnUrl = ['action' => 'index'];
        }
        $this->set(compact('registration', 'teams', 'returnUrl'));

        return null;
    }
}
