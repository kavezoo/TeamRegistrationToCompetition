<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\ProfileSaver;
use Cake\Http\Response;

/**
 * A tag, a klubelnök és az admin saját adata ugyanazzal a mentéssel megy.
 */
trait OwnProfileTrait
{
    public function profile(): ?Response
    {
        $users = $this->fetchTable('Users');
        $identity = $this->request->getAttribute('identity');
        $user = $users->get($identity['id'], contain: ['SocialAccounts']);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $error = (new ProfileSaver())->save($users, $user, $this->request->getData());
            if ($error === null) {
                $this->Flash->success('A profil mentve.');

                return $this->redirectAfterSave($this->homeAfterForm());
            }
            $this->Flash->error($error);
        }
        $this->set(compact('user'));

        return null;
    }
}
