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
            $pendingCount = $this->fetchTable('Users')->find()
                ->where([
                    'role' => User::ROLE_NEW,
                    'membership_request' => 'pending',
                ])
                ->count();
        } catch (\Throwable) {
            $pendingCount = 0;
        }
        $this->set('pendingCount', $pendingCount);
    }
}
