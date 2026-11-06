<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Model\Entity\User;
use Cake\Http\Response;

class ApplicantsController extends AppController
{
    public function index(): void
    {
        $users = $this->fetchTable('Users');
        $pending = $users->find()
            ->contain(User::socialAvatarContain())
            ->where(User::openApplicantConditions($users))
            ->orderBy(['first_name' => 'ASC', 'last_name' => 'ASC'])
            ->all();
        $denied = $users->find()
            ->contain(User::socialAvatarContain())
            ->where([
                'role' => User::ROLE_NEW,
                'membership_request' => 'denied',
            ])
            ->orderBy(['first_name' => 'ASC', 'last_name' => 'ASC'])
            ->all();
        $this->set(compact('pending', 'denied'));
    }

    public function approve(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $users = $this->fetchTable('Users');
        $user = $users->get($id);
        if ((string)$user->get('role') !== User::ROLE_NEW) {
            $this->Flash->error('Csak új jelentkező fogadható el.');

            return $this->redirect(['action' => 'index']);
        }
        $user->set('role', User::ROLE_MEMBER, ['guard' => false]);
        $user->set('membership_request', null, ['guard' => false]);
        if ($users->save($user)) {
            $this->Flash->success(User::displayName($user) . ' mostantól tag.');
        } else {
            $this->Flash->error('A jelentkezőt nem sikerült elfogadni.');
        }

        return $this->redirect(['action' => 'index']);
    }

    public function deny(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $users = $this->fetchTable('Users');
        $user = $users->get($id);
        if ((string)$user->get('role') !== User::ROLE_NEW) {
            $this->Flash->error('Csak új jelentkező utasítható el.');

            return $this->redirect(['action' => 'index']);
        }
        $user->set('membership_request', 'denied', ['guard' => false]);
        if ($users->save($user)) {
            $this->Flash->success(User::displayName($user) . ' jelentkezése elutasítva.');
        } else {
            $this->Flash->error('A jelentkezőt nem sikerült elutasítani.');
        }

        return $this->redirect(['action' => 'index']);
    }
}
