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
            // Ugyanaz a kör, mint a Jelentkezők lista: új szerep, mentett profil, még nincs elutasítva.
            $pendingCount = $users->find()
                ->where(User::openApplicantConditions($users))
                ->where([$users->aliasField('profile_saved') => true])
                ->count();
        } catch (\Throwable) {
            $pendingCount = 0;
        }
        $this->set('pendingCount', $pendingCount);
    }
}
