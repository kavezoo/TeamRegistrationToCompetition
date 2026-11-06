<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * A klubelnök és a tag a saját felületén csak a nevét módosíthatja.
 */
trait OwnProfileTrait
{
    public function profile(): ?Response
    {
        $users = $this->fetchTable('Users');
        $identity = $this->request->getAttribute('identity');
        $user = $users->get($identity['id']);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $user = $users->patchEntity($user, $this->request->getData(), [
                'fields' => ['first_name', 'last_name'],
            ]);
            if ($users->save($user)) {
                $this->Flash->success('A profil mentve.');

                return $this->redirect(['action' => 'profile']);
            }
            $this->Flash->error('A profilt nem sikerült menteni.');
        }
        $this->set(compact('user'));

        return null;
    }
}
