<?php
declare(strict_types=1);

namespace App\Controller\Member;

class RegistrationsController extends AppController
{
    public function index(): void
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity !== null ? ($identity['id'] ?? null) : null;
        $registrations = $this->fetchTable('Registrations');
        $mine = $registrations->find('roster')
            ->where(['Registrations.user_id' => $userId])
            ->all();
        $rows = $registrations->find('roster')->all();
        $this->set(compact('mine', 'rows'));
    }
}
