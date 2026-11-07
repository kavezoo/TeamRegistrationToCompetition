<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use Cake\Http\Response;
use Cake\I18n\DateTime;

/**
 * A klubelnök és a tag a saját felületén csak a nevét módosíthatja.
 */
trait OwnProfileTrait
{
    public function profile(): ?Response
    {
        $users = $this->fetchTable('Users');
        $identity = $this->request->getAttribute('identity');
        $user = $users->get($identity['id'], contain: ['SocialAccounts']);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $user = $users->patchEntity($user, $this->request->getData(), [
                'fields' => ['first_name', 'last_name'],
            ]);
            $isMember = (string)$user->get('role') === User::ROLE_MEMBER;
            if ($isMember && !filter_var($this->request->getData('accept_privacy'), FILTER_VALIDATE_BOOLEAN)) {
                $this->Flash->error('A mentéshez el kell fogadni az adatkezelési nyilatkozatot.');
            } else {
                if ($isMember) {
                    $user->set('tos_date', DateTime::now(), ['guard' => false]);
                    $user->setDirty('tos_date', true);
                }
                User::applyMailRequest($user, $this->request->getData(), $users->getSchema());
                if ($users->save($user)) {
                    $this->Flash->success('A profil mentve.');

                    return $this->redirectAfterSave($this->homeAfterForm());
                }
                $this->Flash->error('A profilt nem sikerült menteni.');
            }
        }
        $this->set(compact('user'));

        return null;
    }
}
