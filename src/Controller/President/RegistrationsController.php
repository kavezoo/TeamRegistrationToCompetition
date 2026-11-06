<?php
declare(strict_types=1);

namespace App\Controller\President;

use Cake\Http\Response;

class RegistrationsController extends AppController
{
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
}
