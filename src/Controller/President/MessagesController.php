<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Model\Entity\User;
use App\Service\FlashNotice;
use Cake\Http\Response;

class MessagesController extends AppController
{
    public function index(): ?Response
    {
        $users = $this->fetchTable('Users');
        $members = $users->find()
            ->where([
                'active' => true,
                'enabled' => true,
                'role IN' => [User::ROLE_MEMBER, User::ROLE_PRESIDENT],
            ])
            ->orderBy(['first_name' => 'ASC', 'last_name' => 'ASC'])
            ->all();
        $selected = trim((string)$this->request->getQuery('user'));

        if ($this->request->is('post')) {
            $subject = trim((string)$this->request->getData('subject'));
            $body = trim((string)$this->request->getData('body'));
            $userId = trim((string)$this->request->getData('user_id'));
            if (mb_strlen($subject) > 180) {
                $subject = mb_substr($subject, 0, 180);
            }
            if ($subject === '' || $body === '') {
                $this->Flash->error('A cím és az üzenet is kell.');
            } else {
                $sent = 0;
                foreach ($members as $member) {
                    if ($userId !== '' && (string)$member->id !== $userId) {
                        continue;
                    }
                    FlashNotice::deliver($member, $subject, $body, 'message');
                    $sent++;
                }
                if ($sent === 0) {
                    $this->Flash->error('Nincs ilyen tag.');
                } else {
                    $this->Flash->success(
                        $sent === 1
                            ? 'Az üzenet kiment a telefonra.'
                            : $sent . ' telefonra kiment az üzenet.',
                    );

                    return $this->redirect(['action' => 'index']);
                }
            }
            $selected = $userId;
        }

        $this->set(compact('members', 'selected'));

        return null;
    }
}
