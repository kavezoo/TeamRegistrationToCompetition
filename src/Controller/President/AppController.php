<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Model\Entity\User;
use Cake\Event\EventInterface;
use JeffAdmin\Controller\AppController as JeffAdminAppController;

class AppController extends JeffAdminAppController
{
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        try {
            $users = $this->fetchTable('Users');
            $pendingCount = $users->find()
                ->where(User::openApplicantConditions($users))
                ->count();
        } catch (\Throwable) {
            $pendingCount = 0;
        }
        $this->set('pendingCount', $pendingCount);
    }
}
